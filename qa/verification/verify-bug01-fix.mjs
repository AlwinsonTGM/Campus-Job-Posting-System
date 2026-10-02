/**
 * VERIFY BUG-01 FIX — student registration must complete end to end.
 *
 * The original defect: register_user() declared ?array for $permit_file/$proof_file
 * but register.php passes stored path STRINGS, so every submission died with an
 * uncaught TypeError before the function body ran.
 *
 * This drives the real 3-step wizard through a real browser and then checks the
 * database actually contains what the UI claimed - a registration that
 * "succeeds" without persisting would be a worse bug than the crash.
 */
import { launch, Results, QA_BASE, sleep } from '../lib/harness.mjs';
import { completeStudentRegistration } from '../lib/register.mjs';
import * as path from 'node:path';
import { execFileSync } from 'node:child_process';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'verify-bug01-fix');
const res = new Results('verify-bug01-fix');

const MYSQL = 'C:\\xampp\\mysql\\bin\\mysql.exe';
const qa = (sql) => execFileSync(MYSQL, ['-u', 'root', '-N', '-e', sql], { encoding: 'utf8' }).trim();

const { browser, page } = await launch();
try {
  let postBody = '';
  let postStatus = 0;
  page.on('response', async (r) => {
    if (r.request().method() === 'POST' && /register\.php/.test(r.url())) {
      postStatus = r.status();
      try { postBody = await r.text(); } catch { /* ignore */ }
    }
  });

  await page.goto(`${QA_BASE}/login.php?reset=1`, { waitUntil: 'domcontentloaded' });
  await sleep(1500);

  // ---- Drive the real wizard -------------------------------------------
  const stamp = Date.now();
  const email = `qa_reg_${stamp}@gmail.com`;
  const sid = `QA-${stamp}`;
  const reg = await completeStudentRegistration(page, { email, stamp, studentId: sid });

  console.log('  wizard:', JSON.stringify({
    step1: reg.step1Ok, step2: reg.step2Ok, proof: reg.proofAttached,
    formValid: reg.formValid, submitted: reg.submitted,
  }));
  console.log('  POST status:', postStatus, '| body bytes:', postBody.length);

  res.check('BUG-01: registration POST returns no PHP fatal',
    !/Fatal error|Uncaught \w+:/.test(postBody),
    (postBody.match(/Uncaught \w+: [^<\n]{0,140}/) || [''])[0]);

  // ---- Did it land on the OTP screen? ----------------------------------
  await page.waitForURL(/verify-email\.php/, { timeout: 25000 }).catch(() => {});
  const onOtp = /verify-email\.php/.test(page.url());
  console.log('  url after submit:', page.url());
  res.check('BUG-01: registration reaches verify-email.php', onOtp, `url=${page.url()}`);
  if (!onOtp) throw new Error('did not reach the OTP screen; see server error below');

  // ---- Did the row actually persist? -----------------------------------
  const userRow = qa(
    `SELECT CONCAT(id,'|',role,'|',is_email_verified) FROM campus_job_portal_e2e.users WHERE email='${email}'`);
  console.log('  users row:', userRow || '(none)');
  res.check('BUG-01: the new account was persisted to the database', userRow !== '', 'no users row');

  const userId = userRow.split('|')[0];
  const profile = qa(
    `SELECT CONCAT(student_id,'|',department,'|',IFNULL(registration_proof,'')) ` +
    `FROM campus_job_portal_e2e.student_profiles WHERE user_id=${userId}`);
  console.log('  student_profile:', profile || '(none)');
  res.check('BUG-01: the student profile was persisted', profile !== '', 'no student_profiles row');

  const proofRef = profile.split('|')[2];
  res.check('BUG-01: the COR document path was stored on the profile (not lost)',
    proofRef !== '', `registration_proof="${proofRef}"`);

  // ---- Complete the OTP journey ----------------------------------------
  const codeEl = page.locator('.alert-paper--warning:not(.alert-paper--floating) code.fs-6').first();
  const code = (await codeEl.isVisible().catch(() => false)) ? (await codeEl.innerText()).trim() : '';
  console.log('  on-screen OTP:', code);
  res.check('BUG-01: OTP renders on screen (no email sent)', /^\d{6}$/.test(code), `code=${code}`);

  const pods = page.locator('.otp-digit-pod');
  for (let i = 0; i < 6; i++) await pods.nth(i).fill(code[i]);
    await Promise.all([
    page.waitForURL(/student\/dashboard\.php/, { timeout: 25000 }).catch(() => {}),
    page.locator('#btn-submit-otp').click({ noWaitAfter: true }).catch(() => {}),
  ]);
  console.log('  url after OTP:', page.url());
  res.check('BUG-01: OTP verification lands on the student dashboard',
    /student\/dashboard\.php/.test(page.url()), `url=${page.url()}`);

  const verified = qa(`SELECT is_email_verified FROM campus_job_portal_e2e.users WHERE id=${userId}`);
  res.check('BUG-01: the account is marked email-verified', verified === '1', `is_email_verified=${verified}`);

  // ---- Regression guard: invalid input must not produce a fatal --------
  const dupRes = await page.request.post(`${QA_BASE}/register.php`, {
    form: {
      first_name: 'Dup', last_name: 'Probe', email,
      phone: '09171234567', password: 'QaProbe!2345', confirm_password: 'QaProbe!2345',
      role: 'student', student_id: sid,
    },
    maxRedirects: 0, failOnStatusCode: false,
  });
  const dupBody = await dupRes.text().catch(() => '');
  res.check('BUG-01: a duplicate registration is rejected cleanly, not fatally',
    !/Fatal error|Uncaught \w+:/.test(dupBody),
    (dupBody.match(/Uncaught \w+: [^<\n]{0,120}/) || [''])[0]);

  await page.goto(`${QA_BASE}/student/jobs.php`, { waitUntil: 'domcontentloaded' });
  await sleep(500);
  res.check('BUG-01: the site is still healthy after registering',
    !/Fatal error|Uncaught \w+:/.test(await page.content()));

  res.save(ART);
} catch (e) {
  console.log(`  ABORTED: ${String(e).split('\n')[0]}`);
  res.check('verification completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
  process.exit(0);
}
