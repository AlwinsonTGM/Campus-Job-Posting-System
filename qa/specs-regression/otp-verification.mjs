/**
 * REGRESSION SPEC — email-verification (OTP) journey completes with NO email sent.
 *
 * This is the answer to "test the OTP flow without spamming my inbox":
 * SMTP is blinded on the QA server, so verify-email.php writes the code into
 * the session and renders it on screen. The full 6-digit UI is still exercised.
 *
 * Expected: FAIL until BUG-01 (register.php:145 register_user() TypeError) is
 * fixed, because the journey cannot start while registration crashes.
 */
import * as path from 'node:path';
import { launch, Results, QA_BASE, shot, sleep } from '../lib/harness.mjs';
import { completeStudentRegistration } from '../lib/register.mjs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'regression');
const res = new Results('regression-otp');

const { browser, page } = await launch();
try {
  const stamp = Date.now();
  const email = `qa_otp_${stamp}@gmail.com`;
  const reg = await completeStudentRegistration(page, { email, stamp });

  res.check('registration wizard completes and submits', reg.step2Ok && reg.submitted === true,
    reg.step1Msg || reg.step2Msg);

  await page.waitForURL(/verify-email\.php/, { timeout: 20000 }).catch(() => {});
  const onOtp = /verify-email\.php/.test(page.url());
  res.check('lands on verify-email.php (blocked by BUG-01 until fixed)', onOtp,
    `url=${page.url()} serverError=${reg.stepErrorAfterSubmit || '(none)'}`);
  if (!onOtp) throw new Error('cannot reach OTP screen; see BUG-01');

  const codeEl = page.locator('.alert-paper--warning code.fs-6').first();
  const code = (await codeEl.isVisible().catch(() => false)) ? (await codeEl.innerText()).trim() : '';
  res.check('OTP is rendered on screen (proves no email was needed)', /^\d{6}$/.test(code), `code=${code}`);

  const pods = page.locator('.otp-digit-pod');
  res.check('6 OTP pods present', (await pods.count()) === 6);
  for (let i = 0; i < 6; i++) await pods.nth(i).fill(code[i]);
  await page.locator('#btn-submit-otp').click();

  await page.waitForURL(/student\/dashboard\.php/, { timeout: 20000 }).catch(() => {});
  res.check('verification graduates the account to the dashboard',
    /student\/dashboard\.php/.test(page.url()), `url=${page.url()}`);

  await shot(page, ART, 'otp-regression');
  res.save(ART);
} catch (e) {
  res.check('regression-otp completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
}
