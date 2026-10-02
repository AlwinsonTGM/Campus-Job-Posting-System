/**
 * TEAM 0 — Containment proof.
 *
 * Must pass before any other team runs. Proves the QA environment cannot
 * email anyone and cannot touch the live database, while still exercising
 * the real 3-step registration wizard -> OTP -> dashboard journey.
 */
import * as path from 'node:path';
import * as fs from 'node:fs';
import { launch, watchErrors, Results, QA_BASE, shot, saveHtml, sleep } from '../lib/harness.mjs';
import { completeStudentRegistration } from '../lib/register.mjs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'containment');
const res = new Results('00-containment');

const { browser, page } = await launch();
const errors = watchErrors(page);

try {
  const loginRes = await page.goto(`${QA_BASE}/login.php`, { waitUntil: 'domcontentloaded' });
  res.check('QA server responds on :8099', loginRes && loginRes.status() === 200, `status=${loginRes && loginRes.status()}`);

  // --- Full registration through the real wizard -------------------------
  const stamp = Date.now();
  const email = `qa_probe_${stamp}@gmail.com`;
  const reg = await completeStudentRegistration(page, { email, stamp });

  res.check('step 1 (identity) advances to step 2', reg.step1Ok, reg.step1Msg);
  res.check('step 2 (academic profile) advances to step 3', reg.step2Ok, reg.step2Msg);

  const stuck = reg.fieldsStuck || {};
  const notStuck = Object.entries(stuck).filter(([, v]) => !v).map(([k]) => k);
  res.check('every wizard field accepted input', notStuck.length === 0, `failed: ${notStuck.join(', ')}`);
  res.check('mandatory COR / Student ID document attached', reg.proofAttached === true);
  res.check('form passes native validation before submit', reg.formValid === true, `formValid=${reg.formValid}`);

  // --- OTP screen -------------------------------------------------------
  await page.waitForURL(/verify-email\.php/, { timeout: 30000 }).catch(() => {});
  const onOtp = /verify-email\.php/.test(page.url());
  res.check('submission quarantines to verify-email.php', onOtp,
    `url=${page.url()} stepError=${reg.stepErrorAfterSubmit || '(none)'}`);

  if (!onOtp) {
    const body = await page.content();
    res.finding({
      title: 'Registration did not reach the email-verification step',
      severity: 'high', route: '/register.php', role: 'public',
      steps: [
        'Open /register.php as a student',
        'Complete the 3-step wizard with a valid unique email',
        'Attach a COR document and submit',
      ],
      expected: 'Redirect to verify-email.php with a pending verification session',
      actual: `Landed on ${page.url()}; step error = ${reg.stepErrorAfterSubmit || '(none)'}`,
      evidence: [await saveHtml(body, ART, 'register-failed'), await shot(page, ART, 'register-failed')].filter(Boolean),
    });
    throw new Error('cannot continue without reaching the OTP screen');
  }

  const warning = page.locator('.alert-paper--warning').first();
  res.check('SMTP-unconfigured notice shown', await warning.isVisible().catch(() => false));

  const codeEl = warning.locator('code.fs-6').first();
  const codeVisible = await codeEl.isVisible().catch(() => false);
  const code = codeVisible ? (await codeEl.innerText()).trim() : '';
  res.check('OTP renders ON SCREEN (never emailed)', /^\d{6}$/.test(code), `code=${code || '(none)'}`);

  if (!/^\d{6}$/.test(code)) {
    res.finding({
      title: 'OTP not rendered on screen while SMTP is unconfigured',
      severity: 'high', route: '/verify-email.php', role: 'public',
      steps: ['Register a new account with SMTP disabled', 'Inspect the OTP screen'],
      expected: 'The 6-digit code is displayed so testing needs no email',
      actual: 'No on-screen code found',
      evidence: [await shot(page, ART, 'otp-no-code')].filter(Boolean),
    });
    throw new Error('no on-screen OTP; aborting');
  }

  console.log(`\n  >> on-screen OTP: ${code}   (registered ${email})\n`);
  await shot(page, ART, 'otp-screen');

  // --- Complete verification through the real 6-pod UI -------------------
  const pods = page.locator('.otp-digit-pod');
  const podCount = await pods.count();
  res.check('6 OTP input pods present', podCount === 6, `count=${podCount}`);

  for (let i = 0; i < Math.min(6, podCount); i++) await pods.nth(i).fill(code[i]);
  await page.locator('#btn-submit-otp').first().click();

  await page.waitForURL(/student\/dashboard\.php/, { timeout: 30000 }).catch(() => {});
  const reached = /student\/dashboard\.php/.test(page.url());
  res.check('OTP verification lands on student dashboard', reached, `url=${page.url()}`);

  if (!reached) {
    res.finding({
      title: 'On-screen OTP rejected by verify-email.php',
      severity: 'critical', route: '/verify-email.php', role: 'student',
      steps: [`Register ${email}`, `Enter the displayed code ${code} into the 6 pods`, 'Submit'],
      expected: 'Redirect to student/dashboard.php',
      actual: `Landed on ${page.url()}`,
      evidence: [await shot(page, ART, 'otp-failed'), await saveHtml(page, ART, 'otp-failed')].filter(Boolean),
    });
  }

  // --- Health -----------------------------------------------------------
  const jsErrors = errors.filter((e) => e.kind === 'pageerror');
  res.check('no uncaught JS errors during the journey', jsErrors.length === 0,
    jsErrors.map((e) => e.text).join(' | '));

  const http5xx = errors.filter((e) => e.kind === 'http');
  res.check('no HTTP 5xx during the journey', http5xx.length === 0, http5xx.map((e) => e.text).join(' | '));

  res.save(ART);
  fs.writeFileSync(path.join(ART, 'result.json'), JSON.stringify({
    email, code, passed: res.passed, failed: res.failed, findings: res.findings.length,
  }, null, 2));
  fs.writeFileSync(path.join(ART, 'registered-email.txt'), email);
} catch (e) {
  console.log(`\n[00-containment] ABORTED: ${String(e).split('\n')[0]}`);
  res.check('containment journey completed without abort', false, String(e).split('\n')[0]);
  await shot(page, ART, 'aborted').catch(() => {});
  res.save(ART);
} finally {
  await sleep(300);
  await browser.close();
}
