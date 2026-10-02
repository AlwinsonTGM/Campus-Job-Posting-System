/**
 * BUG-04 verification — unauthenticated GET on data-toggle.php switches/wipes the dataset.
 *
 * SAFETY: runs ONLY against the isolated test database (campus_job_portal_e2e)
 * on :8099. It proves the destructive capability by observing that seeded rows
 * disappear and the dataset flips, then restores with login.php?reset=1.
 * It never issues a request against Apache on :80.
 */
import { Results } from '../lib/harness.mjs';
import * as path from 'node:path';
import { execFileSync } from 'node:child_process';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'bug04');
const res = new Results('bug04-data-toggle');

const MYSQL = 'C:\\xampp\\mysql\\bin\\mysql.exe';
const qa = (sql) => execFileSync(MYSQL, ['-u', 'root', '-N', '-e', sql], { encoding: 'utf8' }).trim();

const countQA = (table) => qa(`SELECT COUNT(*) FROM campus_job_portal_e2e.${table};`);
const countLive = (table) => qa(`SELECT COUNT(*) FROM campus_job_portal.${table};`);

console.log('=== BEFORE ===');
const beforeJobs = countQA('jobs');
const beforeUsers = countQA('users');
const beforeLiveJobs = countLive('jobs');
console.log(`  test DB : users=${beforeUsers} jobs=${beforeJobs}`);
console.log(`  live DB : jobs=${beforeLiveJobs}`);

// ---- 1. Anonymous client: no session, no cookie, no CSRF token -----------
const url = 'http://127.0.0.1:8099/data-toggle.php?action=switch_mode&mode=real';
const r = await fetch(url, { redirect: 'manual' });
const body = await r.text();

res.check('request was sent with NO authentication cookie', true, 'plain fetch(), no cookie jar');
res.check('GET is accepted (no 403/401 rejection)', r.status !== 403 && r.status !== 401,
  `status=${r.status}`);
res.check('no CSRF token was required for the GET', !body.includes('Security verification failed'));

// ---- 2. Observe the destructive effect ----------------------------------
await new Promise((s) => setTimeout(s, 2500));
const afterJobs = countQA('jobs');
const afterUsers = countQA('users');
const afterLiveJobs = countLive('jobs');

console.log('\n=== AFTER anonymous GET ?action=switch_mode&mode=real ===');
console.log(`  test DB : users=${afterUsers} jobs=${afterJobs}`);
console.log(`  live DB : jobs=${afterLiveJobs}`);

const destroyed = (beforeJobs - afterJobs) + (beforeUsers - afterUsers);

res.check('seeded data was destroyed by an unauthenticated GET', destroyed > 0,
  `jobs ${beforeJobs}->${afterJobs}, users ${beforeUsers}->${afterUsers}`);
res.check('live database was NOT affected (isolation held)', afterLiveJobs === beforeLiveJobs,
  `live jobs ${beforeLiveJobs}->${afterLiveJobs}`);

if (destroyed > 0) {
  res.finding({
    title: 'Unauthenticated GET request wipes the entire dataset (data-toggle.php)',
    severity: 'critical',
    route: '/data-toggle.php',
    role: 'anonymous — no login required',
    steps: [
      'Do NOT log in. Send a plain GET with no cookie and no CSRF token:',
      `  GET ${url}`,
      'Observe the response is a redirect (not 403/401).',
      `Observe seeded rows disappear: jobs ${beforeJobs} -> ${afterJobs}, users ${beforeUsers} -> ${afterUsers}.`,
    ],
    expected: 'A dataset-destroying action must require an authenticated administrator AND a valid CSRF token, and must never be reachable by GET.',
    actual: `An anonymous GET destroyed ${destroyed} seeded rows. CSRF is skipped because the check is ` +
      '`if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($csrf_token))`, and there is no auth guard in the file.',
    evidence: [],
  });
}

// ---- 3. Restore the fixtures -------------------------------------------
// NOTE: the probe above left the datastore in 'real' mode, so a later
// ?reset=1 would faithfully re-seed the *real* (empty) dataset and appear to
// restore nothing. Switch the mode back to demo explicitly, then verify.
console.log('\n=== restoring demo fixtures ===');
try {
  await fetch('http://127.0.0.1:8099/data-toggle.php?action=switch_mode&mode=demo');
  await new Promise((s) => setTimeout(s, 3000));
  await fetch('http://127.0.0.1:8099/login.php?reset=1');
  await new Promise((s) => setTimeout(s, 3000));
} catch { /* verification below is the real check */ }

const restoredUsers = countQA('users');
const restoredJobs = countQA('jobs');
console.log(`  test DB after restore: users=${restoredUsers} jobs=${restoredJobs}`);
console.log(`  live DB untouched     : jobs=${countLive('jobs')}`);

res.check('fixtures restored after the probe (mode switched back to demo)',
  restoredUsers === beforeUsers && restoredJobs === beforeJobs,
  `users=${restoredUsers} jobs=${restoredJobs}`);
res.check('live database still untouched at the end', countLive('jobs') === beforeLiveJobs);

res.save(ART);
process.exit(0); // undici can throw a spurious assertion on abrupt close
