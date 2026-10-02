/**
 * VERIFY BUG-04 FIX — the dataset switcher must be admin-only, POST-only, CSRF-checked.
 *
 * Asserts the attack is dead AND that the legitimate admin flow still works,
 * so the fix cannot be "it broke the feature".
 *
 * Note: switching modes re-seeds the dataset, so this probe finishes by putting
 * demo fixtures back.
 */
import { launch, Results, QA_BASE, sleep } from '../lib/harness.mjs';
import * as path from 'node:path';
import { execFileSync } from 'node:child_process';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'verify-bug04-fix');
const res = new Results('verify-bug04-fix');

const MYSQL = 'C:\\xampp\\mysql\\bin\\mysql.exe';
const qa = (sql) => execFileSync(MYSQL, ['-u', 'root', '-N', '-e', sql], { encoding: 'utf8' }).trim();
const users = () => Number(qa('SELECT COUNT(*) FROM campus_job_portal_e2e.users'));
const liveUsers = () => Number(qa('SELECT COUNT(*) FROM campus_job_portal.users'));

const { browser, page } = await launch();
try {
  const baseline = users();
  console.log(`baseline test-DB users = ${baseline}\n`);

  // 1. Anonymous GET — the original attack
  console.log('--- anonymous GET (original attack) ---');
  for (const q of [
    'action=switch_mode&mode=real',
    'mode=real',
    'action=wipe_real',
    'action=reset',
    'action=reset_current',
    'action=switch_mode&mode=real&csrf_token=anything',
  ]) {
    const r = await page.request.get(`${QA_BASE}/data-toggle.php?${q}`, {
      maxRedirects: 0, failOnStatusCode: false,
    });
    console.log(`  ${q.padEnd(48)} -> HTTP ${r.status()}  users=${users()}`);
    res.check(`anonymous GET refused: ${q}`, users() === baseline, `users ${baseline} -> ${users()}`);
  }
  res.check('data survived every anonymous GET', users() === baseline, `users=${users()}`);

  // 2. Authenticated NON-admin must also be refused
  console.log('\n--- authenticated student (wrong role) ---');
  await page.goto(`${QA_BASE}/login.php?demo=student`, { waitUntil: 'domcontentloaded' });
  await sleep(800);
  const s = await page.request.get(`${QA_BASE}/data-toggle.php?action=switch_mode&mode=real`, {
    maxRedirects: 0, failOnStatusCode: false,
  });
  console.log(`  student GET -> HTTP ${s.status()}  users=${users()}`);
  res.check('student cannot switch datasets', users() === baseline, `users=${users()}`);

  // 3. Admin POST WITHOUT a valid CSRF token must be refused
  console.log('\n--- admin POST, bad CSRF ---');
  await page.goto(`${QA_BASE}/login.php?demo=admin`, { waitUntil: 'domcontentloaded' });
  await sleep(900);
  const bad = await page.request.post(`${QA_BASE}/data-toggle.php`, {
    form: { action: 'switch_mode', mode: 'real', csrf_token: 'not-a-real-token' },
    maxRedirects: 0, failOnStatusCode: false,
  });
  console.log(`  bad-token POST -> HTTP ${bad.status()}  users=${users()}`);
  res.check('admin POST with an invalid CSRF token does not switch', users() === baseline,
    `users ${baseline} -> ${users()}`);

  // 4. The legitimate admin flow must still work
  console.log('\n--- admin POST with a real CSRF token (legitimate use) ---');
  await page.goto(`${QA_BASE}/settings.php`, { waitUntil: 'domcontentloaded' });
  await sleep(900);
  const token = await page.evaluate(() =>
    document.querySelector('#admin-dataset-settings input[name="csrf_token"]')?.value
    || [...document.querySelectorAll('input[name="csrf_token"]')].pop()?.value || '');
  console.log(`  token length = ${token.length}`);

  await page.request.post(`${QA_BASE}/data-toggle.php`, {
    form: { action: 'switch_mode', mode: 'real', csrf_token: token },
    maxRedirects: 0, failOnStatusCode: false,
  });
  await sleep(2500);
  console.log(`  users after switch = ${users()}`);
  res.check('legitimate admin switch still works (feature preserved)', users() !== baseline,
    `users ${baseline} -> ${users()}`);

  // 5. Restore demo fixtures (the switch reconciles the session, so just reset)
  console.log('\n--- restoring demo mode ---');
  await page.request.get(`${QA_BASE}/login.php?reset=1`, { maxRedirects: 5, failOnStatusCode: false })
    .catch(() => {});
  await sleep(3000);
  console.log(`  users after restore = ${users()}`);
  res.check('demo fixtures restored', users() === baseline, `users=${users()}`);

  res.check('live database never touched', liveUsers() === 30, `live users=${liveUsers()}`);
  res.save(ART);
} catch (e) {
  res.check('verification completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
  process.exit(0);
}
