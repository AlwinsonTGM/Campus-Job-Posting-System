/**
 * VERIFICATION of candidate "Cross-employer IDOR" (raised by the lifecycle team).
 *
 * The candidate's only evidence was `status=""`, which is a seed-data artefact
 * (applications 18/21 store 'reviewed'/'rejected', absent from the ENUM, so
 * MySQL coerces them to ''). That proves nothing about authorisation.
 *
 * This test settles it properly: log in as an employer who does NOT own the
 * target, obtain a REAL CSRF token, attempt GET and POST against a foreign job
 * and a foreign application, and compare the stored rows before/after.
 */
import { launch, Results, QA_BASE, sleep } from '../lib/harness.mjs';
import * as path from 'node:path';
import { execFileSync } from 'node:child_process';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'verify-idor');
const res = new Results('verify-cross-employer-idor');

const MYSQL = 'C:\\xampp\\mysql\\bin\\mysql.exe';
const qa = (sql) => execFileSync(MYSQL, ['-u', 'root', '-N', '-e', sql], { encoding: 'utf8' }).trim();

// ---- Fixtures: who owns what -------------------------------------------
console.log('=== fixture ownership ===');
const jobs = qa("SELECT CONCAT(id,'|',employer_id,'|',title,'|',status) FROM campus_job_portal_e2e.jobs ORDER BY id").split('\n');
for (const j of jobs.slice(0, 8)) console.log('  job ' + j);

// employer 3 (registrar@) must NOT own job 1 (owned by employer 4)
const ATTACKER = 3;
const foreignJob = qa(`SELECT id FROM campus_job_portal_e2e.jobs WHERE employer_id <> ${ATTACKER} ORDER BY id LIMIT 1`);
const foreignJobTitle = qa(`SELECT title FROM campus_job_portal_e2e.jobs WHERE id=${foreignJob}`);
const foreignApp = qa(`SELECT a.id FROM campus_job_portal_e2e.applications a JOIN campus_job_portal_e2e.jobs j ON j.id=a.job_id WHERE j.employer_id <> ${ATTACKER} ORDER BY a.id LIMIT 1`);
const foreignAppStatus = qa(`SELECT CONCAT('[',status,']') FROM campus_job_portal_e2e.applications WHERE id=${foreignApp}`);

console.log(`\nattacker employer id = ${ATTACKER}`);
console.log(`foreign job          = ${foreignJob} ("${foreignJobTitle}")`);
console.log(`foreign application  = ${foreignApp} status ${foreignAppStatus}`);

const { browser, page } = await launch();
try {
  // ---- Log in as the attacker -------------------------------------------
  await page.goto(`${QA_BASE}/login.php?demo=employer`, { waitUntil: 'domcontentloaded' });
  await sleep(800);
  res.check('logged in as the demo employer', /employer\/dashboard\.php/.test(page.url()), page.url());

  const me = qa('SELECT id FROM campus_job_portal_e2e.users WHERE email="registrar@kld.edu.ph"');
  console.log(`logged-in user id    = ${me} (expect ${ATTACKER})`);

  // ---- 1. GET the foreign job editor ------------------------------------
  await page.goto(`${QA_BASE}/employer/edit-job.php?id=${foreignJob}`, { waitUntil: 'domcontentloaded' });
  await sleep(600);
  const landedAfterGet = page.url();
  const bodyAfterGet = await page.content();
  const titleLeaked = bodyAfterGet.includes(foreignJobTitle);
  const refused = /Unauthorized|only edit requisitions/i.test(bodyAfterGet);

  res.check('GET foreign job editor does NOT render the foreign title', !titleLeaked,
    `url=${landedAfterGet}`);
  res.check('GET foreign job editor is explicitly refused', refused || !/edit-job\.php/.test(landedAfterGet),
    `refusal message present=${refused}`);

  // ---- 2. POST a title change to the foreign job ------------------------
  // Grab a genuine CSRF token from one of OUR OWN forms so the POST would
  // succeed if authorisation were missing.
  const ourJob = qa(`SELECT id FROM campus_job_portal_e2e.jobs WHERE employer_id=${ATTACKER} ORDER BY id LIMIT 1`);
  await page.goto(`${QA_BASE}/employer/edit-job.php?id=${ourJob}`, { waitUntil: 'domcontentloaded' });
  await sleep(500);
  const token = await page.evaluate(() =>
    document.querySelector('input[name="csrf_token"]')?.value || '');
  res.check('obtained a real CSRF token from our own form', token.length > 10, `len=${token.length}`);

  const marker = 'IDOR_VERIFY_MARKER_' + Date.now();
  const post = await page.request.post(`${QA_BASE}/employer/edit-job.php?id=${foreignJob}`, {
    form: { csrf_token: token, title: marker, category: 'IT & Technical Support', job_type: 'Student Assistant', work_setup: 'On-Campus', location: 'X' },
    maxRedirects: 0,
  }).catch((e) => ({ status: () => 'ERR', text: async () => String(e) }));

  await sleep(800);
  const titleNow = qa(`SELECT title FROM campus_job_portal_e2e.jobs WHERE id=${foreignJob}`);
  console.log(`\nPOST status          = ${post.status()}`);
  console.log(`foreign job title now= "${titleNow}"`);

  res.check('POST to a foreign job does NOT modify it', titleNow !== marker,
    `title is now "${titleNow}"`);

  // ---- 3. POST a status change to the foreign application ---------------
  await page.goto(`${QA_BASE}/employer/review-app.php?id=${ourJob}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
  await sleep(400);
  await page.goto(`${QA_BASE}/employer/applicants.php`, { waitUntil: 'domcontentloaded' });
  await sleep(500);
  const token2 = await page.evaluate(() =>
    document.querySelector('input[name="csrf_token"]')?.value || '');
  const before2 = qa(`SELECT CONCAT('[',status,']') FROM campus_job_portal_e2e.applications WHERE id=${foreignApp}`);

  const post2 = await page.request.post(`${QA_BASE}/employer/review-app.php?id=${foreignApp}`, {
    form: { csrf_token: token2, status: 'declined', supervisor_notes: 'IDOR_VERIFY_NOTE' },
    maxRedirects: 0,
  }).catch((e) => ({ status: () => 'ERR', text: async () => String(e) }));

  await sleep(800);
  const after2 = qa(`SELECT CONCAT('[',status,']') FROM campus_job_portal_e2e.applications WHERE id=${foreignApp}`);
  const note2 = qa(`SELECT IFNULL(supervisor_notes,'') FROM campus_job_portal_e2e.applications WHERE id=${foreignApp}`);

  console.log(`\nPOST#2 status        = ${post2.status()}`);
  console.log(`foreign app status   = ${before2} -> ${after2}`);
  console.log(`foreign app note     = "${note2}"`);

  res.check('POST to a foreign application does NOT change its status', before2 === after2,
    `${before2} -> ${after2}`);
  res.check('POST to a foreign application writes no supervisor note', !note2.includes('IDOR_VERIFY_NOTE'),
    `note="${note2}"`);

  // ---- 4. Control: we CAN still manage our own job ----------------------
  const ownBefore = qa(`SELECT title FROM campus_job_portal_e2e.jobs WHERE id=${ourJob}`);
  const post3 = await page.request.post(`${QA_BASE}/employer/edit-job.php?id=${ourJob}`, {
    form: { csrf_token: token, title: ownBefore + ' (own-edit-probe)', category: 'IT & Technical Support', job_type: 'Student Assistant', work_setup: 'On-Campus', location: 'X' },
    maxRedirects: 0,
  }).catch(() => null);
  await sleep(800);
  const ownAfter = qa(`SELECT title FROM campus_job_portal_e2e.jobs WHERE id=${ourJob}`);
  console.log(`\ncontrol: own job title "${ownBefore}" -> "${ownAfter}"  (post ${post3 ? post3.status() : 'ERR'})`);
  res.check('control: the same POST shape DOES edit our own job (probe validity)',
    ownAfter !== ownBefore, `"${ownBefore}" -> "${ownAfter}"`);

  res.check('VERDICT: cross-employer IDOR is NOT reproducible', titleNow !== marker && before2 === after2,
    'authorisation held on both foreign job and foreign application');
  res.save(ART);
} catch (e) {
  res.check('verification completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
  process.exit(0);
}
