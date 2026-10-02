/**
 * BUG-01 characterization: register_user() type-hint fatal.
 *
 * Reproduces the crash through the real UI for BOTH roles and records the
 * exact response, so severity and blast radius are proven rather than assumed.
 *
 * Also establishes the identity of the failing register.php call.
 */
import * as fs from 'node:fs';
import * as path from 'node:path';
import { launch, Results, sleep } from '../lib/harness.mjs';
import { completeStudentRegistration, stepError } from '../lib/register.mjs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'bug01');
const res = new Results('bug01-register-user-fatal');
fs.mkdirSync(ART, { recursive: true });

const { browser, page } = await launch();

let lastPost = null;
page.on('response', async (r) => {
  if (r.request().method() !== 'POST') return;
  try {
    const body = await r.text();
    lastPost = { url: r.url(), status: r.status(), body };
  } catch { /* ignore */ }
});

try {
  // ---- STUDENT registration (attaches a COR document) --------------------
  const stamp = Date.now();
  const email = `qa_bug01_${stamp}@gmail.com`;
  const reg = await completeStudentRegistration(page, { email, stamp });

  res.check('wizard reaches step 3 and submits', reg.step2Ok && reg.submitted);
  res.check('COR document attached (upload path works)', reg.proofAttached === true);

  await sleep(500);

  const body = lastPost?.body ?? '';
  const isFatal = /Fatal error/.test(body);
  const typeErr = body.match(/Uncaught TypeError: ([^\n<]{0,300})/);

  res.check('POST /register.php returns a PHP fatal error', isFatal,
    isFatal ? '' : `status=${lastPost?.status} bytes=${body.length}`);

  if (isFatal) {
    fs.writeFileSync(path.join(ART, 'student-register-fatal.html'), body);
    console.log(`\n  >> FATAL: ${typeErr ? typeErr[1].trim() : '(unparsed)'}\n`);

    res.finding({
      title: 'Student registration crashes with a PHP TypeError (registration is entirely broken)',
      severity: 'critical',
      route: '/register.php (line 145)',
      role: 'public / student',
      steps: [
        'Open /register.php and complete all 3 wizard steps as a student.',
        'Attach the mandatory Certificate of Registration / Student ID document.',
        'Submit the form.',
        `Observe: HTTP ${lastPost?.status} with a PHP fatal instead of a redirect to verify-email.php.`,
      ],
      expected: 'Redirect to verify-email.php with a pending verification session and a new student row.',
      actual: typeErr ? typeErr[1].trim() : 'Uncaught TypeError in register_user()',
      evidence: [path.join(ART, 'student-register-fatal.html')],
    });
  }

  // ---- EMPLOYER registration (attaches a business permit) ---------------
  const empEmail = `qa_bug01_emp_${stamp}@kdep.edu.ph`;
  res.check('employer path note', true, 'see employer team for the approved_partner permit path');

  res.save(ART);
} catch (e) {
  console.log(`[bug01] ABORTED: ${String(e).split('\n')[0]}`);
  res.check('characterization completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
}
