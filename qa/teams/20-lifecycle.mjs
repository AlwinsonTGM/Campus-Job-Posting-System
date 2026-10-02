/**
 * TEAM 20 — Student + Employer hire lifecycle.
 *
 * Exercises the two-role hire journey end to end against the isolated QA
 * instance (http://127.0.0.1:8099, DB `campus_job_portal_e2e`):
 *
 *   STUDENT   dashboard -> job listing -> keyword/facet filtering -> vacancy
 *             detail -> real multipart application -> tracker -> duplicate
 *             application -> withdrawal -> re-application
 *   EMPLOYER  dashboard scoping -> publish vacancy (20-hour cap probe) ->
 *             edit vacancy -> applicant roster + stage filter -> decision on
 *             the student's own application
 *   ADVERSARY cross-employer IDOR probes on edit-job.php / review-app.php
 *
 * Two independent browser contexts are used so both roles are signed in at the
 * same time (loopback-only hook: login.php?demo=ROLE). No email is sent; no
 * account is registered (BUG-01 makes brand-new registration fatal — known
 * baseline, deliberately not re-reported here).
 *
 * FIXTURE IDS ARE NOT INVENTED. Every id below was read from
 * `campus_job_portal_e2e` and cross-checked against `data/seeds/demo/*.json`,
 * which is what `login.php?reset=1` re-seeds. The reset at the start of the run
 * therefore restores exactly the state these constants describe, which also
 * makes the script safely re-runnable (it withdraws, re-applies and decides).
 */
import * as path from 'node:path';
import * as fs from 'node:fs';
import { launch, watchErrors, Results, QA_BASE, shot, saveHtml, sleep } from '../lib/harness.mjs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'lifecycle');
const res = new Results('20-lifecycle');
fs.mkdirSync(ART, { recursive: true });

/* ------------------------------------------------------------------ *
 * Verified fixture facts (SELECT ... FROM campus_job_portal_e2e)
 * ------------------------------------------------------------------ */
const FX = {
  // users: id 1 is the lowest-id active student, id 3 the lowest-id employer,
  // which is exactly who quick_login()/login.php?demo= picks.
  student: { id: 1, name: 'Juan Dela Cruz', email: 'student@kld.edu.ph' },
  employer: { id: 3, name: 'Prof. Roberto Hernandez', org: 'Office of the University Registrar' },

  seedJobCount: 20,                       // jobs table after ?reset=1

  // The demo employer owns exactly jobs 2 and 3.
  ownJobIds: [2, 3],
  ownJobTitles: ['Student Records & Archival Assistant', 'Digital Cataloging & Library Aid'],

  // A vacancy owned by a DIFFERENT employer (jobs.employer_id = 4).
  foreignJob: { id: 1, employerId: 4, title: 'Computer Lab Technical Assistant' },
  // An application on that foreign job (applications.id = 1, student_id = 1).
  foreignAppId: 1,
  // Another foreign vacancy used only for "not mine" sampling.
  otherForeignJobIds: [4, 5, 20],

  // Student 1 has no application on job 3 in the pristine fixture, job 3 is
  // active, deadline 2026-11-25, slots 1/3 -> eligible to apply.
  applyJob: { id: 3, title: 'Digital Cataloging & Library Aid' },

  // Vacancy whose slots are exhausted in the fixture (slots_total 2 / filled 2).
  fullJob: { id: 16, title: 'Creative Graphics & Social Media Assistant' },

  // A second, unrelated pending application of the same student (job 9).
  otherPendingAppId: 4,

  // Seed row whose status is not representable in the ENUM (see F3 below).
  blankStatusApp: { id: 18, jobId: 15, jobTitle: 'Junior Frontend Web Assistant (React / CSS)' },

  // Filtering facts.
  keyword: 'Barista',
  keywordJobId: 7,
  hybridJobIds: [5, 6, 15, 16, 20],
  partTimeJobIds: [5, 6, 15, 16, 20],     // jobs.job_type = 'Part-Time'
  peerTutorCategoryJobIds: [4, 11],       // categories.name = 'Peer Tutor'
};

const PNG_1x1 = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==',
  'base64');

/* ------------------------------------------------------------------ *
 * Small helpers
 * ------------------------------------------------------------------ */

// PHP failure signatures. Fatal-ish signatures are searched in the HTML (they
// can be rendered outside body text); notice/warning signatures only in the
// rendered body text, to avoid matching inline JS strings.
const PHP_FATAL = /(Fatal error|Parse error|Uncaught (?:Error|TypeError|Exception|ValueError)|SQLSTATE\[|mysqli_sql_exception|PDOException|Call to undefined)/;
const PHP_SOFT = /(Warning:|Notice:|Deprecated:)/;

const jobTitle = (id) => ({ 1: 'Computer Lab Technical Assistant' }[id] || `#${id}`);

async function bodyText(p) {
  return (await p.evaluate(() => (document.body ? document.body.innerText : '')).catch(() => ''))
    .replace(/\s+/g, ' ').trim();
}

/** Returns the matched PHP signature, or '' when the page is clean. */
async function phpScan(p) {
  const html = await p.content().catch(() => '');
  const fatal = html.match(PHP_FATAL);
  if (fatal) return fatal[0];
  const soft = (await bodyText(p)).match(PHP_SOFT);
  return soft ? soft[0] : '';
}

async function settle(p, ms = 600) {
  await p.waitForLoadState('domcontentloaded').catch(() => {});
  await p.waitForLoadState('networkidle').catch(() => {});
  await sleep(ms);
}

/**
 * Click something that navigates. Falls back to a forced click (a lingering
 * flash toast or the double-submit guard must not be mistaken for an app bug).
 */
async function clickAndSettle(p, selector, ms = 900) {
  const el = p.locator(selector).first();
  let ok = await el.click({ timeout: 15000 }).then(() => true).catch(() => false);
  if (!ok) ok = await el.click({ timeout: 5000, force: true }).then(() => true).catch(() => false);
  await settle(p, ms);
  return ok;
}

/** Unique job ids linked from the listing/results container. */
async function listingIds(p) {
  return p.evaluate(() => {
    const c = document.getElementById('filter-results-container');
    if (!c) return [];
    const ids = new Set();
    c.querySelectorAll('a[href*="job-details.php?id="]').forEach((a) => {
      const m = (a.getAttribute('href') || '').match(/job-details\.php\?id=(\d+)/);
      if (m) ids.add(Number(m[1]));
    });
    return [...ids].sort((x, y) => x - y);
  }).catch(() => []);
}

/** Text key of the AJAX-replaced results container (listing pages). */
async function containerKey(p) {
  return p.evaluate(() => {
    const c = document.getElementById('filter-results-container');
    return c ? c.innerText.replace(/\s+/g, ' ').slice(0, 3000) : '';
  }).catch(() => '');
}

async function waitKeyChange(p, before, timeout = 8000) {
  try {
    await p.waitForFunction((k) => {
      const c = document.getElementById('filter-results-container');
      const now = c ? c.innerText.replace(/\s+/g, ' ').slice(0, 3000) : '';
      return now !== k;
    }, before, { timeout });
    return true;
  } catch { return false; }
}

/** The student's tracker card for one job id (status badge, #APP-n, app id). */
async function trackerCard(p, jobId) {
  return p.evaluate((jid) => {
    const list = document.getElementById('applications-list');
    if (!list) return null;
    for (const card of Array.from(list.children)) {
      const link = card.querySelector(`a[href*="job-details.php?id=${jid}"]`);
      if (!link) continue;
      const badge = card.querySelector('[class*="badge-status--"]');
      const wd = card.querySelector('input[name="withdraw_id"]');
      return {
        status: badge ? badge.innerText.replace(/\s+/g, ' ').trim() : '',
        hasBadge: !!badge,
        appNo: (card.innerText.match(/#APP-\d+/) || [''])[0],
        appId: wd ? Number(wd.value) : 0,
        snippet: card.innerText.replace(/\s+/g, ' ').slice(0, 320),
      };
    }
    return null;
  }, jobId).catch(() => null);
}

/** Every tracker card, so the whole tracker can be audited in one pass. */
async function trackerCards(p) {
  return p.evaluate(() => {
    const list = document.getElementById('applications-list');
    if (!list) return [];
    return Array.from(list.children).map((card) => {
      const badge = card.querySelector('[class*="badge-status--"]');
      const wd = card.querySelector('input[name="withdraw_id"]');
      const link = card.querySelector('h3 a[href*="job-details.php?id="]');
      const m = link ? (link.getAttribute('href') || '').match(/id=(\d+)/) : null;
      return {
        jobId: m ? Number(m[1]) : 0,
        title: link ? link.innerText.trim() : '',
        appNo: (card.innerText.match(/#APP-\d+/) || [''])[0],
        status: badge ? badge.innerText.replace(/\s+/g, ' ').trim() : '',
        hasBadge: !!badge,
        stepperActive: (card.querySelector('.stepper-step.is-active .stepper-label')?.innerText || '').trim(),
        appId: wd ? Number(wd.value) : 0,
      };
    });
  }).catch(() => []);
}

/** Number of tracker cards for one job (UNIQUE(job_id,student_id) -> must be 1). */
async function trackerCardCount(p, jobId) {
  return p.evaluate((jid) => {
    const list = document.getElementById('applications-list');
    if (!list) return 0;
    return Array.from(list.children)
      .filter((c) => c.querySelector(`a[href*="job-details.php?id=${jid}"]`)).length;
  }, jobId).catch(() => -1);
}

/** POST a real multipart form from inside the page (same cookies + origin). */
async function postForm(p, url, fields = {}, files = {}) {
  return p.evaluate(async ({ url, fields, files }) => {
    const fd = new FormData();
    for (const [k, v] of Object.entries(fields)) fd.append(k, String(v));
    for (const [k, f] of Object.entries(files)) {
      const bytes = Uint8Array.from(atob(f.b64), (c) => c.charCodeAt(0));
      fd.append(k, new File([bytes], f.name, { type: f.type }));
    }
    try {
      const r = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
      const text = await r.text();
      return { status: r.status, url: r.url, redirected: r.redirected, text: text.slice(0, 250000) };
    } catch (e) {
      return { status: 0, url, redirected: false, text: 'FETCH ERROR: ' + String(e) };
    }
  }, { url, fields, files }).catch((e) => ({ status: 0, url, redirected: false, text: 'EVAL ERROR: ' + String(e) }));
}

/** Run one phase; a thrown app defect is recorded, never propagated. */
async function section(name, p, fn) {
  try {
    await fn();
    return true;
  } catch (e) {
    const msg = String(e).split('\n')[0];
    console.log(`  [!!] section "${name}" threw: ${msg}`);
    res.check(`[${name}] section runs to completion without throwing`, false, msg);
    await shot(p, ART, `throw-${name.replace(/[^a-z0-9]+/gi, '-').toLowerCase()}`).catch(() => {});
    return false;
  }
}

const { browser, page } = await launch();
const errors = watchErrors(page);
// The tracker's withdraw button is guarded by confirm(); Playwright dismisses
// dialogs by default, which would silently cancel the submission.
page.on('dialog', (d) => d.accept().catch(() => {}));

let other = null;
let p2 = null;
let errors2 = [];        // watchErrors() array for the employer page
let studentToken = '';   // student session CSRF token, harvested on the apply form

/** ids/values discovered at runtime, echoed into result.json for the report. */
const seen = {};

try {
  /* ================================================================== *
   * PHASE 0 — environment + pristine fixtures
   * ================================================================== */
  await section('phase0', page, async () => {
    const r = await page.goto(`${QA_BASE}/login.php`, { waitUntil: 'domcontentloaded' }).catch(() => null);
    res.check('QA server responds on :8099', !!r && r.status() === 200, `status=${r && r.status()}`);

    // ?reset=1 re-seeds the test DB from data/seeds/demo and purges the session,
    // so it must run before any login.
    await page.goto(`${QA_BASE}/login.php?reset=1`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const txt = await bodyText(page);
    res.check('datastore reset to pristine demo fixtures',
      /reset to pristine baseline/i.test(txt) && /login\.php/.test(page.url()),
      `url=${page.url()} flash=${/reset to pristine baseline/i.test(txt)}`);

    const php = await phpScan(page);
    res.check('no PHP error text on the reset/landing page', !php, php || 'clean');
  });

  /* ================================================================== *
   * PHASE 1 — STUDENT
   * ================================================================== */
  await section('student-login', page, async () => {
    await page.goto(`${QA_BASE}/login.php?demo=student`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 600);
    res.check('S1 student demo login lands on student/dashboard.php',
      /\/student\/dashboard\.php/.test(page.url()), `url=${page.url()}`);

    const head = await page.locator('.page-head-title').first().innerText().catch(() => '');
    res.check('S1 student dashboard renders its page head', head.trim().length > 0, `h1="${head.trim()}"`);

    const txt = await bodyText(page);
    res.check('S1 student dashboard shows the applications metric',
      /Applications Filed/i.test(txt), txt.slice(0, 160));

    const php = await phpScan(page);
    res.check('S1 student dashboard has no PHP error text', !php, php || 'clean');
    await shot(page, ART, 's1-student-dashboard');
  });

  await section('student-listing', page, async () => {
    await page.goto(`${QA_BASE}/student/jobs.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);

    const base = await listingIds(page);
    seen.baselineListingIds = base;
    res.check('S2 job listing renders the full seeded catalogue',
      base.length === FX.seedJobCount, `rendered=${base.length} expected=${FX.seedJobCount}`);

    // ---- keyword search, URL driven (deterministic server render) ----
    await page.goto(`${QA_BASE}/student/jobs.php?keyword=${encodeURIComponent(FX.keyword)}`,
      { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);
    const kw = await listingIds(page);
    seen.keywordListingIds = kw;
    res.check('S2 keyword search narrows the listing to the matching vacancy',
      kw.length > 0 && kw.length < base.length && kw.every((id) => base.includes(id)),
      `keyword="${FX.keyword}" -> [${kw.join(',')}] of ${base.length}`);
    res.check('S2 keyword search returns the Barista vacancy',
      kw.includes(FX.keywordJobId), `expected #${FX.keywordJobId}, got [${kw.join(',')}]`);

    // ---- keyword search through the real live-search widget (AJAX) ----
    await page.goto(`${QA_BASE}/student/jobs.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);
    const beforeAjax = await containerKey(page);
    await page.locator('#filter-kw').first().fill(FX.keyword).catch(() => {});
    const changed = await waitKeyChange(page, beforeAjax, 9000);
    const ajaxIds = await listingIds(page);
    res.check('S2 live keyword search updates the results container in place',
      changed && ajaxIds.length > 0 && ajaxIds.length < base.length,
      `changed=${changed} ids=[${ajaxIds.join(',')}]`);

    // ---- facet: work setup (exact match, 5 hybrid vacancies in the fixture) ----
    await page.goto(`${QA_BASE}/student/jobs.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);
    const beforeFacet = await containerKey(page);
    await page.locator('#filter-setup').first().selectOption('Hybrid').catch(() => {});
    const facetChanged = await waitKeyChange(page, beforeFacet, 9000);
    const hybrid = await listingIds(page);
    seen.hybridListingIds = hybrid;
    res.check('S2 work-setup facet changes the result count',
      facetChanged && hybrid.length > 0 && hybrid.length < base.length,
      `Hybrid -> ${hybrid.length} of ${base.length} (ajax=${facetChanged})`);
    res.check('S2 work-setup facet returns exactly the hybrid vacancies',
      JSON.stringify(hybrid) === JSON.stringify(FX.hybridJobIds),
      `got [${hybrid.join(',')}] expected [${FX.hybridJobIds.join(',')}]`);
    res.check('S2 facet results are a strict subset of the unfiltered listing',
      hybrid.every((id) => base.includes(id)), `Hybrid=[${hybrid.join(',')}]`);

    // ---- facet integrity: does the offered Job Type value match stored data? ----
    await page.goto(`${QA_BASE}/student/jobs.php?job_type=${encodeURIComponent('Part-Time Job')}`,
      { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const partTimeFacet = await listingIds(page);
    const partTimeOk = partTimeFacet.length > 0;
    res.check('S2 job-type facet "Part-Time Job" returns matching vacancies', partTimeOk,
      `rendered=${partTimeFacet.length}; fixture has ${FX.partTimeJobIds.length} active vacancies with jobs.job_type='Part-Time'`);

    await page.goto(`${QA_BASE}/student/jobs.php?job_type=${encodeURIComponent('Peer Tutor')}`,
      { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const peerByType = await listingIds(page);
    await page.goto(`${QA_BASE}/student/jobs.php?category=${encodeURIComponent('Peer Tutor')}`,
      { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const peerByCat = await listingIds(page);
    seen.peerTutor = { byJobType: peerByType, byCategory: peerByCat };
    res.check('S2 "Peer Tutor" quick-filter chip returns the peer-tutor vacancies',
      peerByType.length > 0 && peerByCat.length > 0,
      `job_type=Peer Tutor -> [${peerByType.join(',')}] ; category=Peer Tutor -> [${peerByCat.join(',')}]`);

    if (!partTimeOk || peerByType.length === 0) {
      res.finding({
        title: 'Listing facets offer taxonomy values that no vacancy uses, so valid filters return an empty page',
        severity: 'medium',
        route: '/student/jobs.php (job_type facet + quick-filter chips)',
        role: 'student',
        steps: [
          'Open /student/jobs.php (20 live vacancies).',
          'Set "Job Type" = "Part-Time Job" (a value the filter dropdown itself offers) and search.',
          `Observe: ${partTimeFacet.length} results, although 5 active vacancies (ids ${FX.partTimeJobIds.join(', ')}) are part-time.`,
          'Click the "Peer Tutor" quick-filter chip, then the "Peer Tutor" category chip on the same page.',
          `Observe: job_type=Peer Tutor -> ${peerByType.length} results ; category=Peer Tutor -> ${peerByCat.length} results (ids ${FX.peerTutorCategoryJobIds.join(', ')}).`,
        ],
        expected: 'A facet the UI offers should match the vacancies it describes (facet values are matched to the stored taxonomy).',
        actual: `jobs.job_type stores 'Part-Time' / 'Student Assistant' only, but the facet posts 'Part-Time Job' and matches with LIKE '%<value>%', so it matches nothing; the Peer Tutor chip posts job_type=Peer Tutor while the same vacancies are filed under category 'Peer Tutor'. The listing therefore reports "No matching opportunities found" for a filter the page itself advertises.`,
        evidence: [
          `GET /student/jobs.php?job_type=Part-Time+Job -> ${partTimeFacet.length} job cards`,
          `GET /student/jobs.php?job_type=Peer+Tutor -> ${peerByType.length} job cards`,
          `GET /student/jobs.php?category=Peer+Tutor -> ${peerByCat.length} job cards`,
          `campus_job_portal_e2e: SELECT job_type,COUNT(*) FROM jobs GROUP BY job_type -> 'Part-Time'=5, 'Student Assistant'=15`,
          await saveHtml(page, ART, 's2-peertutor-category-listing'),
          await shot(page, ART, 's2-peertutor-category-listing'),
        ].filter(Boolean),
      });
    }
  });

  await section('student-job-detail', page, async () => {
    await page.goto(`${QA_BASE}/student/job-details.php?id=${FX.foreignJob.id}`,
      { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const h1 = (await page.locator('.page-head-title').first().innerText().catch(() => '')).trim();
    res.check('S3 vacancy detail renders the title stored on the job row',
      h1 === FX.foreignJob.title, `h1="${h1}" expected="${FX.foreignJob.title}"`);
    seen.foreignDetailTitle = h1;
    await shot(page, ART, 's3-job-detail');

    // ---- invalid ids ----
    for (const [label, id] of [['999999', '999999'], ['abc', 'abc']]) {
      await page.goto(`${QA_BASE}/student/job-details.php?id=${id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      await settle(page, 500);
      const php = await phpScan(page);
      const txt = await bodyText(page);
      const url = page.url();
      const title = (await page.locator('.page-head-title').first().innerText().catch(() => '')).trim();
      res.check(`S3 id=${label} does not produce a PHP fatal`, !php, php || `url=${url}`);
      res.check(`S3 id=${label} does not leak another vacancy record`,
        title !== FX.foreignJob.title && !txt.includes(FX.foreignJob.title),
        `title="${title}" url=${url}`);
      await saveHtml(page, ART, `s3-invalid-id-${label}`);
    }

    const page1 = await page.goto(`${QA_BASE}/student/job-details.php?id=${FX.foreignJob.id}`,
      { waitUntil: 'domcontentloaded' }).catch(() => null);
    res.check('S3 a valid id still renders after the invalid probes',
      !!page1 && page1.status() === 200 && /job-details\.php\?id=1/.test(page.url()),
      `status=${page1 && page1.status()} url=${page.url()}`);

    // ---- business rule: a vacancy with no free slots must refuse applications ----
    await page.goto(`${QA_BASE}/student/apply.php?job_id=${FX.fullJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 600);
    const fullTxt = await bodyText(page);
    const fullPhp = await phpScan(page);
    const fullHtml = await page.content().catch(() => '');
    seen.fullSlotProbe = {
      url: page.url(),
      message: (fullTxt.match(/[^.]*slots[^.]*\./i) || [''])[0].trim(),
      applyFormRendered: /submitAppBtn/.test(fullHtml),
    };
    res.check('S3 full-slot vacancy (job 16, 2/2 filled) refuses new applications',
      /job-details\.php\?id=16/.test(page.url()) && /slots .*filled|filled\./i.test(fullTxt) && !fullPhp,
      `url=${page.url()} msg="${seen.fullSlotProbe.message}" fatal=${fullPhp || 'none'}`);
    res.check('S3 refusing a full vacancy does not render the apply form',
      !seen.fullSlotProbe.applyFormRendered, `applyForm=${seen.fullSlotProbe.applyFormRendered}`);
    await saveHtml(page, ART, 's3-full-slots');
  });

  /* ---------------- application lifecycle ---------------- */
  await section('student-apply', page, async () => {
    await page.goto(`${QA_BASE}/student/apply.php?job_id=${FX.applyJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);

    // Field contract discovered from includes/templates/student-apply-view.php
    const contract = await page.evaluate(() => ({
      formAction: document.querySelector('form.form-paper')?.getAttribute('action') || '',
      hasPhone: !!document.querySelector('#app-phone'),
      hasCover: !!document.querySelector('#cover_letter'),
      matrix: document.querySelectorAll('input.matrix-check').length,
      hasResume: !!document.querySelector('input[name="resume"]'),
      resumeAccept: document.querySelector('input[name="resume"]')?.getAttribute('accept') || '',
      hasCsrf: !!document.querySelector('input[name="csrf_token"]'),
      submit: !!document.querySelector('#submitAppBtn'),
    })).catch(() => ({}));
    seen.applyContract = contract;
    res.check('S4 apply form exposes the expected field contract',
      !!contract.hasPhone && !!contract.hasCover && contract.matrix === 18 && !!contract.hasResume && !!contract.hasCsrf,
      JSON.stringify(contract));

    if (!contract.hasCsrf) throw new Error('apply form has no CSRF token; cannot drive it');

    // Real session token, reused later for the server-side duplicate probe.
    studentToken = await page.locator('#cover_letter').first()
      .evaluate((el) => el.form.querySelector('input[name="csrf_token"]').value).catch(() => '');

    // Fill the form the way a student would, then attach a genuine PNG where
    // the form accepts a file. `resume` is the only file input on this form.
    await page.locator('#app-phone').first().fill('09171234567').catch(() => {});
    const marker = `QA-LIFECYCLE-${Date.now()}`;
    await page.locator('#cover_letter').first()
      .fill(`${marker} — automated lifecycle probe: I am applying for the ${FX.applyJob.title} post.`).catch(() => {});
    await page.locator('input.matrix-check').nth(0).check({ force: true }).catch(() => {});
    await page.locator('input.matrix-check').nth(1).check({ force: true }).catch(() => {});
    const checked = await page.locator('input.matrix-check:checked').count().catch(() => 0);
    res.check('S4 availability matrix accepts at least one slot', checked >= 1, `checked=${checked}`);

    await page.locator('input[name="resume"]').first().setInputFiles(
      { name: 'qa-lifecycle-resume.png', mimeType: 'image/png', buffer: PNG_1x1 }, { timeout: 8000 }).catch(() => {});
    const attached = await page.evaluate(() => document.querySelector('input[name="resume"]')?.files?.length ?? 0).catch(() => 0);
    res.check('S4 PNG attached to the resume file input', attached === 1, `files=${attached}`);

    // Submit 1: PNG into a PDF/DOC-only field -> the validator must reject it
    // cleanly (no fatal, no silently stored image).
    await clickAndSettle(page, '#submitAppBtn', 1500);
    const rejectTxt = await bodyText(page);
    const rejectPhp = await phpScan(page);
    const rejectedCleanly = /invalid resume format/i.test(rejectTxt) && !rejectPhp;
    res.check('S4 PNG in the PDF/DOCX-only resume field is rejected cleanly',
      rejectedCleanly, `fatal=${rejectPhp || 'none'} msg=${(rejectTxt.match(/Invalid resume[^.]*\./i) || ['(none)'])[0]}`);
    if (!rejectedCleanly) {
      res.finding({
        title: 'Resume upload validation does not reject an image cleanly',
        severity: 'medium',
        route: 'POST /student/apply.php',
        role: 'student',
        steps: ['Open /student/apply.php?job_id=3', 'Attach a valid PNG to the "Resume / Study Load" field (accept=".pdf,.doc,.docx")', 'Submit'],
        expected: 'A user-facing "Invalid resume format" message and no application row, or a rejection with no PHP error output.',
        actual: `PHP signature: ${rejectPhp || 'none'}; message seen: ${(rejectTxt.match(/Invalid resume[^.]*\./i) || ['(none)'])[0]}`,
        evidence: [await saveHtml(page, ART, 's4-resume-reject'), await shot(page, ART, 's4-resume-reject')].filter(Boolean),
      });
    }
    seen.resumeRejectMessage = (rejectTxt.match(/Invalid resume[^.]*\./i) || [''])[0];

    // Submit 2: same form, no attachment (resume is optional) -> must succeed.
    await page.goto(`${QA_BASE}/student/apply.php?job_id=${FX.applyJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    await page.locator('#app-phone').first().fill('09171234567').catch(() => {});
    await page.locator('#cover_letter').first()
      .fill(`${marker} — automated lifecycle probe: I am applying for the ${FX.applyJob.title} post.`).catch(() => {});
    await page.locator('input.matrix-check').nth(0).check({ force: true }).catch(() => {});
    await page.locator('input.matrix-check').nth(2).check({ force: true }).catch(() => {});
    await clickAndSettle(page, '#submitAppBtn', 1600);

    const applyTxt = await bodyText(page);
    const applyPhp = await phpScan(page);
    const created = /my-applications\.php/.test(page.url());
    res.check('S4 application submits and lands on the tracker',
      created && !applyPhp, `url=${page.url()} fatal=${applyPhp || 'none'}`);
    seen.applyMarker = marker;

    if (!created) {
      res.finding({
        title: 'Student cannot submit an application for an open, eligible vacancy',
        severity: 'critical',
        route: 'POST /student/apply.php?job_id=3',
        role: 'student',
        steps: [
          `Sign in as the demo student (user ${FX.student.id})`,
          `Open /student/apply.php?job_id=${FX.applyJob.id} (status active, deadline 2026-11-25, slots 1/3, no prior application)`,
          'Fill phone + cover letter, tick availability slots, submit without a resume attachment',
        ],
        expected: 'Redirect to /student/my-applications.php with a new pending application row.',
        actual: `Landed on ${page.url()}; PHP signature=${applyPhp || 'none'}; page text=${applyTxt.slice(0, 200)}`,
        evidence: [await saveHtml(page, ART, 's4-apply-failed'), await shot(page, ART, 's4-apply-failed')].filter(Boolean),
      });
      throw new Error('application was not created; remaining lifecycle steps need it');
    }

    await settle(page, 400);
    const card = await trackerCard(page, FX.applyJob.id);
    seen.newAppId = card?.appId || 0;
    res.check('S4 tracker confirms the new submission',
      /successfully submitted/i.test(applyTxt) || !!card,
      `flash=${/successfully submitted/i.test(applyTxt)} card=${!!card} :: ${applyTxt.slice(0, 160)}`);
    res.check('S5 new application appears in /student/my-applications.php',
      !!card, card ? `#${card.appNo} status="${card.status}"` : 'no card for job 3');
    res.check('S5 new application starts at the Pending Review stage',
      !!card && /pending review/i.test(card.status), card ? `status="${card.status}"` : 'no card');
    await shot(page, ART, 's5-tracker-after-apply');

    // ---- duplicate application: GET path (eligibility gate) ----
    await page.goto(`${QA_BASE}/student/apply.php?job_id=${FX.applyJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 600);
    const dupTxt = await bodyText(page);
    const dupPhp = await phpScan(page);
    const blockedGet = /already submitted an application/i.test(dupTxt);
    res.check('S6 duplicate application (GET) is blocked with an explanatory message',
      blockedGet && !dupPhp, `url=${page.url()} fatal=${dupPhp || 'none'} msg=${(dupTxt.match(/You have already[^.]*\./i) || ['(none)'])[0]}`);
    seen.duplicateGet = { url: page.url(), message: (dupTxt.match(/You have already[^.]*\./i) || [''])[0] };
    await saveHtml(page, ART, 's6-duplicate-get');

    // ---- duplicate application: POST path (re-submits the real form) ----
    const dupPost = await postForm(page, `/student/apply.php?id=${FX.applyJob.id}&job_id=${FX.applyJob.id}`, {
      csrf_token: studentToken,
      phone: '09171234567',
      cover_letter: `${marker} duplicate attempt`,
      'availability[]': 'Mon - Morning (8AM–12NN)',
    });
    const dupPostFatal = PHP_FATAL.test(dupPost.text) || PHP_SOFT.test(dupPost.text);
    const dupPostBlocked = /already submitted an application/i.test(dupPost.text)
      && !/my-applications\.php$/.test(dupPost.url);
    res.check('S6 duplicate application (POST) is rejected by the server, not the DB',
      dupPostBlocked && !dupPostFatal,
      `status=${dupPost.status} url=${dupPost.url} dbError=${dupPostFatal ? (dupPost.text.match(PHP_FATAL) || [''])[0] : 'none'}`);
    seen.duplicatePost = { status: dupPost.status, url: dupPost.url, dbError: dupPostFatal ? 'yes' : 'no' };

    if (dupPostFatal || /UNIQUE|Duplicate entry/i.test(dupPost.text)) {
      res.finding({
        title: 'Duplicate application reaches the database and surfaces a raw SQL error',
        severity: 'high',
        route: 'POST /student/apply.php?job_id=3',
        role: 'student',
        steps: ['Apply for job 3 successfully', 'Re-POST the same application form (same session, valid CSRF token)'],
        expected: 'The duplicate is refused by the eligibility check with a readable message.',
        actual: `HTTP ${dupPost.status}; body contains ${(dupPost.text.match(PHP_FATAL) || ['an SQL error'])[0]}`,
        evidence: [await saveHtml(dupPost.text, ART, 's6-duplicate-post')].filter(Boolean),
      });
    } else if (!dupPostBlocked) {
      res.finding({
        title: 'Duplicate application is silently accepted on the POST path',
        severity: 'high',
        route: 'POST /student/apply.php?job_id=3',
        role: 'student',
        steps: ['Apply for job 3 successfully', 'Re-POST the same application form (same session, valid CSRF token)'],
        expected: 'Refused with "You have already submitted an application for this requisition."',
        actual: `HTTP ${dupPost.status} -> ${dupPost.url} (no duplicate message in the response)`,
        evidence: [await saveHtml(dupPost.text, ART, 's6-duplicate-post-accepted')].filter(Boolean),
      });
    }

    const dupCards = await (async () => {
      await page.goto(`${QA_BASE}/student/my-applications.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      await settle(page, 400);
      return trackerCardCount(page, FX.applyJob.id);
    })();
    seen.trackerCardsForJob = dupCards;
    res.check('S6 duplicate attempt left exactly one application row for the job',
      dupCards === 1, `tracker cards for job ${FX.applyJob.id} = ${dupCards}`);
  });

  await section('student-withdraw', page, async () => {
    await page.goto(`${QA_BASE}/student/my-applications.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);

    const before = await trackerCard(page, FX.applyJob.id);
    if (!before || !before.appId) throw new Error('no withdrawable card for the job-3 application');
    res.check('S7 application is withdrawable while Pending Review',
      /pending review/i.test(before.status), `status="${before.status}" appId=${before.appId}`);

    const withdrawSel = `#applications-list form:has(input[name="withdraw_id"][value="${before.appId}"]) button[type="submit"]`;
    const btnCount = await page.locator(withdrawSel).count().catch(() => 0);
    res.check('S7 withdraw control is rendered for the pending application', btnCount === 1, `buttons=${btnCount}`);
    if (btnCount === 1) await clickAndSettle(page, withdrawSel, 900);

    const afterTxt = await bodyText(page);
    const afterPhp = await phpScan(page);
    const after = await trackerCard(page, FX.applyJob.id);
    seen.afterWithdraw = after;
    res.check('S7 withdrawal is confirmed to the student',
      /withdrawn/i.test(afterTxt) || (!!after && /withdrawn/i.test(after.status)),
      `flash=${/withdrawn/i.test(afterTxt)} status="${after ? after.status : '(card missing)'}"`);
    res.check('S7 tracker shows the application as Withdrawn',
      !!after && /withdrawn/i.test(after.status), after ? `status="${after.status}"` : 'card missing');
    res.check('S7 tracker page has no PHP error text after withdrawing', !afterPhp, afterPhp || 'clean');
    await shot(page, ART, 's7-tracker-withdrawn');

    if (!after || !/withdrawn/i.test(after.status)) {
      res.finding({
        title: 'Withdrawing a pending application does not change its tracker stage',
        severity: 'high',
        route: 'POST /student/my-applications.php (withdraw_id)',
        role: 'student',
        steps: [`Open /student/my-applications.php as user ${FX.student.id}`,
          `Press Withdraw on application #${before.appId} (job ${FX.applyJob.id}, status pending)`,
          'Confirm the browser dialog'],
        expected: 'The card status becomes Withdrawn and a success flash is shown.',
        actual: `Card status after submit: "${after ? after.status : '(card missing)'}"; page text=${afterTxt.slice(0, 200)}`,
        evidence: [await saveHtml(page, ART, 's7-withdraw-failed'), await shot(page, ART, 's7-withdraw-failed')].filter(Boolean),
      });
    }

    // The flash for a *blocked* withdraw lives on the same page, so grab the
    // token while the tracker is open (used for the under-review probe later).
    seen.studentToken = (await page.evaluate(() => {
      const i = document.querySelector('input[name="csrf_token"]');
      return i ? i.value : '';
    }).catch(() => '')) || studentToken;
    if (!studentToken) studentToken = seen.studentToken;

    // ---- re-apply after withdrawal (also makes this script re-runnable) ----
    await page.goto(`${QA_BASE}/student/apply.php?job_id=${FX.applyJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);
    const reopen = await page.evaluate(() => !!document.querySelector('#submitAppBtn')).catch(() => false);
    res.check('S8 a withdrawn application re-opens the apply form', reopen, `url=${page.url()}`);
    if (reopen) {
      await page.locator('#app-phone').first().fill('09171234567').catch(() => {});
      await page.locator('#cover_letter').first()
        .fill(`${seen.applyMarker} — re-application after withdrawal.`).catch(() => {});
      await page.locator('input.matrix-check').nth(1).check({ force: true }).catch(() => {});
      await clickAndSettle(page, '#submitAppBtn', 1600);
      const reTxt = await bodyText(page);
      res.check('S8 re-application after withdrawal is accepted',
        /my-applications\.php/.test(page.url()),
        `url=${page.url()} flash=${/successfully submitted/i.test(reTxt)} msg=${reTxt.slice(0, 140)}`);

      const back = await trackerCard(page, FX.applyJob.id);
      const backCount = await trackerCardCount(page, FX.applyJob.id);
      seen.afterReapply = back;
      seen.afterReapplyRows = backCount;
      res.check('S8 re-application returns to Pending Review',
        !!back && /pending review/i.test(back.status), back ? `status="${back.status}" appId=${back.appId}` : 'card missing');
      res.check('S8 re-application keeps ONE row per (job, student)',
        backCount === 1, `tracker rows for job ${FX.applyJob.id} = ${backCount} (was appId ${before.appId})`);
      if (back && back.appId) seen.newAppId = back.appId;
    }

    // ---- tracker-wide audit ----
    await page.goto(`${QA_BASE}/student/my-applications.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);
    const cards = await trackerCards(page);
    seen.trackerCards = cards.map((c) => ({ jobId: c.jobId, appNo: c.appNo, status: c.status, stepperActive: c.stepperActive, appId: c.appId }));
    const blank = cards.filter((c) => !c.hasBadge || !c.status);
    res.check('S9 every tracker card renders a non-empty status badge',
      cards.length > 0 && blank.length === 0,
      `cards=${cards.length} blank=[${blank.map((c) => `${c.appNo}/job${c.jobId}`).join(', ')}]`);
    const php = await phpScan(page);
    res.check('S9 tracker page has no PHP error text', !php, php || 'clean');
    await shot(page, ART, 's9-tracker-full');

    if (blank.length) {
      const b = blank[0];
      res.finding({
        title: 'Application tracker renders an empty status pill for a seeded application',
        severity: 'low',
        route: '/student/my-applications.php',
        role: 'student',
        steps: [
          `Sign in as the demo student (user ${FX.student.id}) and open /student/my-applications.php`,
          `Inspect ${b.appNo} (${b.title || `job ${b.jobId}`})`,
        ],
        expected: 'Every application card shows a stage badge from render_status_badge().',
        actual: `The card renders no status badge at all (empty pill) because the row's status is the empty string. Worse, render_stepper('') falls through to its default and paints stage 1 as active, so the same card simultaneously claims "${b.stepperActive || '(no active stage)'}": the UI shows a stage the database does not hold. \`applications.status\` is an ENUM('pending','under_review','interview_scheduled','accepted','declined','withdrawn'); the seed fixture data/seeds/demo/applications.json stores 'reviewed'/'rejected' for applications ${FX.blankStatusApp.id} and 21, which MySQL coerces to '' on import. Filtering by any stage also cannot reach these rows (they match no status branch).`,
        evidence: [
          `tracker cards with an empty status badge: ${blank.map((c) => `${c.appNo}(job ${c.jobId}, stepper="${c.stepperActive}")`).join(', ')}`,
          `campus_job_portal_e2e: SELECT id,job_id,student_id,status FROM applications WHERE id IN (18,21) -> status = '' (empty)`,
          `data/seeds/demo/applications.json -> id 18 status "reviewed", id 21 status "rejected"`,
          await saveHtml(page, ART, 's9-blank-status'),
          await shot(page, ART, 's9-blank-status'),
        ].filter(Boolean),
      });
    }
  });

  /* ================================================================== *
   * PHASE 2 — EMPLOYER (second browser context: both roles at once)
   * ================================================================== */
  other = await browser.newContext({ baseURL: QA_BASE, viewport: { width: 1440, height: 900 } });
  p2 = await other.newPage();
  errors2 = watchErrors(p2);
  p2.on('dialog', (d) => d.accept().catch(() => {}));

  let marker = '';      // unique vacancy title
  let newJobId = 0;     // vacancy published by this run
  let empToken = '';    // employer session CSRF token

  await section('employer-dashboard', p2, async () => {
    await p2.goto(`${QA_BASE}/login.php?demo=employer`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 600);
    res.check('E0 employer demo login lands on employer/dashboard.php',
      /\/employer\/dashboard\.php/.test(p2.url()), `url=${p2.url()}`);

    const head = (await p2.locator('.page-head-title').first().innerText().catch(() => '')).trim();
    res.check('E1 employer dashboard renders its page head', head.length > 0, `h1="${head}"`);

    const owned = await p2.evaluate(() => {
      const ids = new Set();
      document.querySelectorAll('a[href*="edit-job.php?id="]').forEach((a) => {
        const m = (a.getAttribute('href') || '').match(/edit-job\.php\?id=(\d+)/);
        if (m) ids.add(Number(m[1]));
      });
      return [...ids].sort((x, y) => x - y);
    }).catch(() => []);
    seen.dashboardJobIds = owned;
    res.check('E1 employer dashboard lists this employer\'s own vacancies',
      FX.ownJobIds.every((id) => owned.includes(id)),
      `listed=[${owned.join(',')}] expected to include [${FX.ownJobIds.join(',')}]`);
    res.check('E1 employer dashboard lists no foreign vacancies',
      FX.otherForeignJobIds.every((id) => !owned.includes(id)) && !owned.includes(FX.foreignJob.id),
      `listed=[${owned.join(',')}] must exclude 1,${FX.otherForeignJobIds.join(',')}`);

    const php = await phpScan(p2);
    res.check('E1 employer dashboard has no PHP error text', !php, php || 'clean');
    await shot(p2, ART, 'e1-employer-dashboard');
  });

  await section('employer-create-job', p2, async () => {
    await p2.goto(`${QA_BASE}/employer/create-job.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 500);
    marker = `QA Lifecycle Vacancy ${Date.now()}`;
    seen.vacancyMarker = marker;

    // ---- step 1 ----
    await p2.locator('#job-title').first().fill(marker).catch(() => {});
    await p2.locator('#job-category').first().selectOption({ index: 1 }).catch(() => {});
    await p2.locator('#job-type').first().selectOption('Student Assistant').catch(() => {});
    await p2.locator('#work-setup').first().selectOption('On-Campus').catch(() => {});
    await p2.locator('#job-desc').first()
      .fill('Probe vacancy created by qa/teams/20-lifecycle.mjs to exercise the employer requisition wizard and the 20-hour weekly duty cap.').catch(() => {});
    const titleEcho = await p2.locator('#job-title').first().inputValue().catch(() => '');
    res.check('E2 step 1 accepts the vacancy title', titleEcho === marker, `read back="${titleEcho}"`);

    await p2.locator('#step-pane-1 .btn-step-next').first().click({ timeout: 15000 }).catch(() => {});
    await sleep(500);
    const onStep2 = await p2.evaluate(() => document.getElementById('step-pane-2')?.classList.contains('is-visible') ?? false).catch(() => false);
    res.check('E2 wizard advances to step 2 (Hiring Flyer)', onStep2, `step2Visible=${onStep2}`);

    // A PNG is genuinely valid here (accept="image/jpeg,image/png,image/webp"),
    // and the pane must be visible for the file input to be actionable.
    await p2.locator('#job-photo').first().setInputFiles(
      { name: 'qa-lifecycle-flyer.png', mimeType: 'image/png', buffer: PNG_1x1 }, { timeout: 8000 }).catch(() => {});
    const flyerOk = await p2.evaluate(() => document.getElementById('job-photo')?.files?.length ?? 0).catch(() => 0);
    res.check('E2 flyer PNG attached through the real file input', flyerOk === 1, `files=${flyerOk}`);

    await p2.locator('#step-pane-2 .btn-step-next').first().click({ timeout: 15000 }).catch(() => {});
    await sleep(500);
    const onStep3 = await p2.evaluate(() => document.getElementById('step-pane-3')?.classList.contains('is-visible') ?? false).catch(() => false);
    res.check('E2 wizard advances to step 3 (Terms & Quota)', onStep3, `step3Visible=${onStep3}`);

    // ---- step 3 ----
    await p2.locator('#job-dept').first().fill(FX.employer.org).catch(() => {});
    await p2.locator('#job-loc').first().fill('KLD Admin Building, 1st Floor, Room 102').catch(() => {});
    await p2.locator('#job-pay-amount').first().fill('123.50').catch(() => {});
    await p2.locator('#job-pay-period').first().selectOption('/ hour').catch(() => {});
    await p2.locator('#job-vac').first().fill('3').catch(() => {});
    const deadline = await p2.evaluate(() => {
      const el = document.getElementById('job-deadline');
      const base = (el && el.min) ? el.min : new Date().toISOString().slice(0, 10);
      const d = new Date(base + 'T00:00:00');
      d.setDate(d.getDate() + 60);
      return d.toISOString().slice(0, 10);
    }).catch(() => '2027-01-15');
    await p2.locator('#job-deadline').first().fill(deadline).catch(() => {});

    // ---- BUSINESS RULE PROBE: compensation must be a positive amount ----
    await p2.locator('#job-pay-amount').first().fill('0').catch(() => {});
    await clickAndSettle(p2, '#btn-publish-job', 700);
    const zeroUrl = p2.url();
    const zeroErrVisible = await p2.locator('#step-error-alert').first().isVisible().catch(() => false);
    const zeroMsg = await p2.evaluate(() => (document.getElementById('step-error-message')?.textContent || '').trim()).catch(() => '');
    res.check('E2 zero compensation is refused before submission',
      /create-job\.php/.test(zeroUrl) && zeroErrVisible && /stipend rate/i.test(zeroMsg),
      `url=${zeroUrl} errorVisible=${zeroErrVisible} msg="${zeroMsg}"`);
    await p2.locator('#job-pay-amount').first().fill('123.50').catch(() => {});

    // ---- BUSINESS RULE PROBE: the stated 20-hour weekly cap ----
    // The form only offers <=20 values, so an over-cap value is injected into
    // the real <select> and submitted through the real form.
    const injected = await p2.evaluate(() => {
      const sel = document.getElementById('job-hours');
      if (!sel) return '';
      const o = document.createElement('option');
      o.value = '25 hrs/week';
      o.textContent = '25 hrs/week (over cap)';
      sel.appendChild(o);
      sel.value = '25 hrs/week';
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      return sel.value;
    }).catch(() => '');
    res.check('E2 over-cap value 25 hrs/week is selectable in the form', injected === '25 hrs/week', `select value="${injected}"`);

    await p2.locator('#btn-publish-job').first().click({ timeout: 20000 }).catch(() => {});
    await settle(p2, 1800);

    const afterTxt = await bodyText(p2);
    const afterPhp = await phpScan(p2);
    const published = /\/employer\/dashboard\.php/.test(p2.url());
    const clientErrVisible = await p2.locator('#step-error-alert').first().isVisible().catch(() => false);
    const serverErrVisible = await p2.locator('#server-error-alert').first().isVisible().catch(() => false);
    const rejected = !published && (clientErrVisible || serverErrVisible);
    seen.publish = {
      url: p2.url(), published, rejected, clientErrVisible, serverErrVisible,
      flashed: /published successfully/i.test(afterTxt),
    };

    res.check('E2 vacancy publishing produces no PHP error', !afterPhp, afterPhp || 'clean');
    res.check('E2 over-cap weekly limit (25 hrs/week) is rejected with a validation error',
      rejected && !published,
      `published=${published} rejected=${rejected}; url=${p2.url()}`);

    if (published) {
      newJobId = await p2.evaluate((t) => {
        for (const a of Array.from(document.querySelectorAll('a[href*="edit-job.php?id="]'))) {
          const row = a.closest('tr');
          if (row && row.innerText.includes(t)) {
            const m = (a.getAttribute('href') || '').match(/edit-job\.php\?id=(\d+)/);
            if (m) return Number(m[1]);
          }
        }
        return 0;
      }, marker).catch(() => 0);
      seen.newJobId = newJobId;
      res.check('E2 published vacancy appears on the employer dashboard', newJobId > 0, `newJobId=${newJobId}`);
      res.check('E2 publish success flash is shown',
        /published successfully/i.test(afterTxt) || newJobId > 0,
        `flash=${/published successfully/i.test(afterTxt)} newJobId=${newJobId}`);
    }
    await shot(p2, ART, 'e2-after-publish');

    // ---- what did the app actually store for the weekly limit? ----
    if (newJobId > 0) {
      await p2.goto(`${QA_BASE}/employer/edit-job.php?id=${newJobId}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      await settle(p2, 500);
      const hours = await p2.evaluate(() => {
        const sel = document.getElementById('edit-hours');
        if (!sel) return null;
        const opt = sel.options[sel.selectedIndex];
        return { value: sel.value, label: opt ? opt.textContent.trim() : '', options: Array.from(sel.options).map((o) => o.value) };
      }).catch(() => null);
      seen.storedHours = hours;
      const overCapStored = !!hours && /2[1-9]|[3-9]\d/.test(hours.value);
      res.check('E2 stored weekly limit is capped at 20 hrs/week', !!hours && !overCapStored,
        `stored="${hours ? hours.value : '?'}" (submitted 25 hrs/week)`);

      const comp = await p2.evaluate(() => ({
        amount: document.getElementById('edit-pay-amount')?.value || '',
        period: document.getElementById('edit-pay-period')?.value || '',
        slots: document.getElementById('edit-vac')?.value || '',
        title: document.getElementById('edit-title')?.value || '',
        location: document.getElementById('edit-loc')?.value || '',
      })).catch(() => ({}));
      seen.storedCompensation = comp;
      res.check('E2 published vacancy keeps the submitted title',
        comp.title === marker, `stored="${comp.title}"`);
      res.check('E2 published vacancy keeps the submitted compensation',
        Math.abs(parseFloat(comp.amount) - 123.5) < 0.001 && comp.period === '/ hour',
        `amount=${comp.amount} period=${comp.period}`);
      res.check('E2 published vacancy keeps the submitted slot quota',
        String(comp.slots) === '3', `slots=${comp.slots}`);

      // The flyer PNG must have survived the multipart upload (edit-job.php
      // renders a "Current Banner" thumbnail only when jobs.image is set).
      const flyer = await p2.evaluate(() => {
        const img = document.querySelector('img[alt="Current Banner"]');
        return { src: img ? img.getAttribute('src') : '', featured: /Featured on Homepage/i.test(document.body.innerText) };
      }).catch(() => ({ src: '', featured: false }));
      seen.storedFlyer = flyer;
      res.check('E2 uploaded flyer PNG persisted as the vacancy image',
        /uploads\/jobs\//.test(flyer.src), `banner src="${flyer.src}" featured=${flyer.featured}`);
      await shot(p2, ART, 'e2-new-vacancy-edit-form');

      if (overCapStored) {
        res.finding({
          title: 'The 20-hour weekly cap can be exceeded through the requisition wizard',
          severity: 'high',
          route: 'POST /employer/create-job.php (hours_per_week)',
          role: 'employer',
          steps: [
            'Sign in as the demo employer and open /employer/create-job.php',
            'Complete steps 1-3, injecting hours_per_week=25 hrs/week into the Weekly Limit select (the form itself only offers <=20 values)',
            'Publish the requisition',
          ],
          expected: 'The server rejects an over-cap weekly limit with a validation error, or stores a capped value.',
          actual: `The vacancy was published with hours_per_week="${hours.value}".`,
          evidence: [await saveHtml(p2, ART, 'e2-over-cap-published')].filter(Boolean),
        });
      } else if (!rejected) {
        res.finding({
          title: 'Over-cap weekly limit is silently rewritten, with no feedback to the employer',
          severity: 'low',
          route: 'POST /employer/create-job.php (hours_per_week) + POST /employer/edit-job.php',
          role: 'employer',
          steps: [
            'Sign in as the demo employer and open /employer/create-job.php',
            'Complete steps 1-3 and set Weekly Limit to 25 hrs/week (injected into the select; the UI offers a maximum of "Up to 20 hrs/week")',
            'Publish the requisition and reopen it via /employer/edit-job.php?id=<new id>',
          ],
          expected: 'Either a validation error ("weekly limit cannot exceed 20 hours") or an explicit confirmation that the value was adjusted.',
          actual: `The requisition was published with no error and no warning, and the stored weekly limit is "${hours ? hours.value : '(unreadable)'}" — the submitted 25 hrs/week was silently replaced. The cap itself holds, but the employer is never told their input was overwritten, and the same silent substitution applies to any unrecognised value in create-job.php:80-84 / edit-job.php:72-76.`,
          evidence: [
            `POST hours_per_week=25 hrs/week -> dashboard flash "published successfully"`,
            `GET /employer/edit-job.php?id=${newJobId} -> #edit-hours value "${hours ? hours.value : '(unreadable)'}" (option "${hours ? hours.label : ''}")`,
            await saveHtml(p2, ART, 'e2-hours-coerced'),
            await shot(p2, ART, 'e2-hours-coerced'),
          ].filter(Boolean),
        });
      }
    }
  });

  await section('employer-edit-job', p2, async () => {
    if (!newJobId) throw new Error('no vacancy was published; edit step cannot run');
    await p2.goto(`${QA_BASE}/employer/edit-job.php?id=${newJobId}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 500);
    empToken = await p2.evaluate(() => document.querySelector('#edit-job-form input[name="csrf_token"]')?.value || '').catch(() => '');
    res.check('E3 employer session exposes a CSRF token for its own forms', !!empToken);

    const edited = `${marker} EDITED`;
    await p2.locator('#edit-title').first().fill(edited).catch(() => {});
    await p2.locator('#job-photo').first().setInputFiles(
      { name: 'qa-lifecycle-flyer-2.png', mimeType: 'image/png', buffer: PNG_1x1 }, { timeout: 8000 }).catch(() => {});
    await clickAndSettle(p2, '#edit-job-form button[type="submit"]', 1500);
    const editTxt = await bodyText(p2);
    const editPhp = await phpScan(p2);
    res.check('E3 edit submission is accepted without a PHP error',
      !editPhp, `fatal=${editPhp || 'none'} url=${p2.url()}`);
    res.check('E3 edit reports success',
      /updated successfully/i.test(editTxt) || editTxt.includes(edited),
      `flash=${/updated successfully/i.test(editTxt)} :: ${editTxt.slice(0, 160)}`);

    await p2.goto(`${QA_BASE}/employer/edit-job.php?id=${newJobId}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 400);
    const persisted = await p2.locator('#edit-title').first().inputValue().catch(() => '');
    res.check('E3 edited title persists on reload', persisted === edited, `stored="${persisted}"`);

    await p2.goto(`${QA_BASE}/employer/dashboard.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 400);
    const dashTxt = await bodyText(p2);
    res.check('E3 edited title is visible on the employer dashboard',
      dashTxt.includes(edited), `looking for "${edited}"`);
    await shot(p2, ART, 'e3-dashboard-edited');
  });

  await section('employer-roster', p2, async () => {
    const rosterNames = (p) => p.evaluate(() => {
      const rows = Array.from(document.querySelectorAll('table.table-paper-roster tbody tr'));
      return rows.map((r) => ({
        candidate: (r.querySelector('[data-label="Candidate Profile"]')?.innerText || '').replace(/\s+/g, ' ').trim().slice(0, 60),
        vacancy: (r.querySelector('[data-label="Target Vacancy"]')?.innerText || '').replace(/\s+/g, ' ').trim(),
        status: (r.querySelector('[data-label="Status"]')?.innerText || '').replace(/\s+/g, ' ').trim(),
        appId: Number(((r.querySelector('a[href*="review-app.php?id="]')?.getAttribute('href') || '').match(/id=(\d+)/) || [0, 0])[1]),
      }));
    }).catch(() => []);

    await p2.goto(`${QA_BASE}/employer/applicants.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 500);
    const all = await rosterNames(p2);
    seen.rosterAll = all;
    res.check('E4 applicant roster renders rows', all.length > 0, `rows=${all.length}`);

    const allowed = new Set([...FX.ownJobTitles, `${seen.vacancyMarker || ''} EDITED`, seen.vacancyMarker || '']);
    const foreign = all.filter((r) => r.vacancy && !allowed.has(r.vacancy));
    res.check('E4 roster is scoped to this employer\'s own vacancies',
      foreign.length === 0,
      foreign.length ? `foreign rows: ${foreign.map((f) => `${f.candidate} -> ${f.vacancy}`).join(' | ')}` : `vacancies=[${[...new Set(all.map((r) => r.vacancy))].join(' | ')}]`);
    await shot(p2, ART, 'e4-roster-all');

    // stage filter through the real select (AJAX) + URL fallback
    await p2.goto(`${QA_BASE}/employer/applicants.php?status=pending`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 400);
    const pending = await rosterNames(p2);
    await p2.goto(`${QA_BASE}/employer/applicants.php?status=accepted`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 400);
    const accepted = await rosterNames(p2);
    seen.rosterByStage = { pending, accepted };
    res.check('E4 stage filter changes the roster rows',
      JSON.stringify(pending.map((r) => r.candidate)) !== JSON.stringify(accepted.map((r) => r.candidate)),
      `pending=[${pending.map((r) => r.candidate).join(' | ')}] accepted=[${accepted.map((r) => r.candidate).join(' | ')}]`);

    await p2.goto(`${QA_BASE}/employer/applicants.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 400);
    const beforeKey = await containerKey(p2);
    await p2.locator('#filter-status').first().selectOption('accepted').catch(() => {});
    const changed = await waitKeyChange(p2, beforeKey, 9000);
    const ajaxRows = await rosterNames(p2);
    res.check('E4 stage filter select updates the roster in place',
      changed && ajaxRows.length === accepted.length,
      `changed=${changed} rows=${ajaxRows.length} expected=${accepted.length}`);
  });

  await section('employer-decision', p2, async () => {
    // Locate the student's own fresh application via the roster for job 3.
    await p2.goto(`${QA_BASE}/employer/applicants.php?job_id=${FX.applyJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 500);
    const rows = await p2.evaluate(() => Array.from(document.querySelectorAll('table.table-paper-roster tbody tr')).map((r) => ({
      text: r.innerText.replace(/\s+/g, ' '),
      appId: Number(((r.querySelector('a[href*="review-app.php?id="]')?.getAttribute('href') || '').match(/id=(\d+)/) || [0, 0])[1]),
    }))).catch(() => []);
    const mine = rows.find((r) => r.text.includes(FX.student.name) && r.text.includes(FX.applyJob.title));
    seen.rosterRowForStudent = mine || null;
    res.check('E5 the student\'s new application is on the employer roster',
      !!mine, mine ? `appId=${mine.appId}` : `rows=${rows.length}`);

    const targetAppId = (mine && mine.appId) || seen.newAppId;
    res.check('E5 roster application id matches the id in the student tracker',
      !mine || !seen.newAppId || mine.appId === seen.newAppId,
      `roster=${mine && mine.appId} tracker=${seen.newAppId}`);
    if (!targetAppId) throw new Error('no application id to review');

    await p2.goto(`${QA_BASE}/employer/review-app.php?id=${targetAppId}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 500);
    const reviewTxt = await bodyText(p2);
    const reviewPhp = await phpScan(p2);
    res.check('E5 review page opens for the student\'s application',
      /Review Applicant/i.test(reviewTxt) && !reviewPhp, `fatal=${reviewPhp || 'none'} url=${p2.url()}`);
    res.check('E5 review page shows the student\'s cover letter (data flows student -> employer)',
      !!seen.applyMarker && reviewTxt.includes(seen.applyMarker), `marker="${seen.applyMarker}"`);
    await shot(p2, ART, 'e5-review-app');

    await p2.locator('#eval-status').first().selectOption('under_review').catch(() => {});
    await p2.locator('#supervisor_notes').first().fill('QA lifecycle: moved to evaluation.').catch(() => {});
    await clickAndSettle(p2, '#btn-save-decision', 1500);

    await p2.goto(`${QA_BASE}/employer/review-app.php?id=${targetAppId}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 400);
    const stage = await p2.evaluate(() => {
      const s = document.getElementById('eval-status');
      return s ? s.value : '';
    }).catch(() => '');
    const badgeTxt = (await bodyText(p2)).match(/Under Evaluation/i) ? 'Under Evaluation' : '';
    seen.employerDecision = { targetAppId, stage, badgeTxt };
    res.check('E5 decision is saved as under_review',
      stage === 'under_review', `#eval-status="${stage}"`);
    res.check('E5 review page reflects the new stage',
      /Under Evaluation/i.test(badgeTxt), `stage text="${badgeTxt}"`);

    // ---- student side: is the decision visible to the student? ----
    await page.goto(`${QA_BASE}/student/my-applications.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 500);
    const card = await trackerCard(page, FX.applyJob.id);
    seen.studentSeesDecision = card;
    res.check('E5 student tracker reflects the employer decision',
      !!card && /under evaluation/i.test(card.status),
      card ? `status="${card.status}"` : 'card missing');
    await shot(page, ART, 'e5-student-sees-under-evaluation');

    // ---- rule: an application under review can no longer be withdrawn ----
    const wd = await postForm(page, '/student/my-applications.php',
      { csrf_token: seen.studentToken || '', withdraw_id: targetAppId });
    const wdFatal = PHP_FATAL.test(wd.text) || PHP_SOFT.test(wd.text);
    const wdRuleMessage = /cannot be withdrawn/i.test(wd.text);
    await page.goto(`${QA_BASE}/student/my-applications.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const after = await trackerCard(page, FX.applyJob.id);
    res.check('E5 an application under review cannot be withdrawn by the student',
      !!after && /under evaluation/i.test(after.status) && !wdFatal && wdRuleMessage,
      `status after withdraw POST="${after ? after.status : '(missing)'}" ruleMessage=${wdRuleMessage} fatal=${wdFatal ? 'yes' : 'no'}`);
    seen.withdrawUnderReview = {
      status: wd.status, url: wd.url, fatal: wdFatal, ruleMessage: wdRuleMessage, token: !!seen.studentToken,
    };
  });

  /* ---------------- cross-employer IDOR ---------------- */
  await section('employer-idor', p2, async () => {
    // 1) GET a foreign vacancy's edit form
    await p2.goto(`${QA_BASE}/employer/edit-job.php?id=${FX.foreignJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 600);
    const geUrl = p2.url();
    const geTxt = await bodyText(p2);
    const geEditForm = await p2.locator('#edit-job-form').count().catch(() => 0);
    const geTitle = await p2.locator('#edit-title').first().inputValue().catch(() => '');
    const gePhp = await phpScan(p2);
    seen.idorEditGet = { url: geUrl, editForm: geEditForm, title: geTitle, fatal: gePhp };
    res.check('E6 IDOR: GET edit-job.php for a foreign vacancy is refused',
      !/edit-job\.php/.test(geUrl) && geEditForm === 0,
      `url=${geUrl} editForm=${geEditForm} title="${geTitle}"`);
    res.check('E6 IDOR: refusal is explained to the employer',
      /only edit requisitions posted by your office/i.test(geTxt), geTxt.slice(0, 200));
    res.check('E6 IDOR: refused GET leaks no foreign vacancy data',
      !geTxt.includes(FX.foreignJob.title) && !gePhp, `titleLeaked=${geTxt.includes(FX.foreignJob.title)} fatal=${gePhp || 'none'}`);
    await saveHtml(p2, ART, 'e6-idor-edit-job-get');

    // 2) POST a modification to the same foreign vacancy (valid CSRF token)
    const idorTitle = 'QA-IDOR-ESCAPE';
    const iePost = await postForm(p2, `/employer/edit-job.php?id=${FX.foreignJob.id}`, {
      csrf_token: empToken,
      title: idorTitle,
      description: 'idor probe',
      location: 'idor probe',
      pay_amount: '999',
      pay_period: '/ hour',
      vacancies: '9',
      deadline: '2027-01-31',
      status: 'active',
    });
    const ieFatal = PHP_FATAL.test(iePost.text) || PHP_SOFT.test(iePost.text);
    // Verify against the student-visible record (independent oracle).
    await page.goto(`${QA_BASE}/student/job-details.php?id=${FX.foreignJob.id}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const stillTitle = (await page.locator('.page-head-title').first().innerText().catch(() => '')).trim();
    seen.idorEditPost = { status: iePost.status, url: iePost.url, unchangedTitle: stillTitle, fatal: ieFatal };
    res.check('E6 IDOR: POST edit-job.php for a foreign vacancy does not modify it',
      stillTitle === FX.foreignJob.title,
      `title now "${stillTitle}" (expected "${FX.foreignJob.title}"); POST ${iePost.status} -> ${iePost.url}`);
    res.check('E6 IDOR: the employer still owns exactly its own vacancies after the probe',
      await (async () => {
        await p2.goto(`${QA_BASE}/employer/dashboard.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
        await settle(p2, 400);
        const owned = await p2.evaluate(() => Array.from(document.querySelectorAll('a[href*="edit-job.php?id="]'))
          .map((a) => Number(((a.getAttribute('href') || '').match(/id=(\d+)/) || [0, 0])[1])));
        const uniq = [...new Set(owned)].sort((a, b) => a - b);
        seen.ownedAfterIdor = uniq;
        return FX.ownJobIds.every((id) => uniq.includes(id)) && !uniq.includes(FX.foreignJob.id);
      })().catch(() => false),
      `own=[${FX.ownJobIds.join(',')}] must remain owned; foreign #${FX.foreignJob.id} must not appear`);
    await saveHtml(iePost.text, ART, 'e6-idor-edit-job-post');

    // 3) GET a foreign application's review drawer
    await p2.goto(`${QA_BASE}/employer/review-app.php?id=${FX.foreignAppId}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(p2, 600);
    const raUrl = p2.url();
    const raTxt = await bodyText(p2);
    const raForm = await p2.locator('#eval-status').count().catch(() => 0);
    const raPhp = await phpScan(p2);
    seen.idorReviewGet = { url: raUrl, form: raForm, fatal: raPhp };
    res.check('E6 IDOR: GET review-app.php for a foreign application is refused',
      !/review-app\.php/.test(raUrl) && raForm === 0,
      `url=${raUrl} reviewForm=${raForm}`);
    res.check('E6 IDOR: review refusal is explained',
      /belongs to another department/i.test(raTxt), raTxt.slice(0, 200));
    await saveHtml(p2, ART, 'e6-idor-review-get');

    // 4) POST a decision onto that foreign application
    const idorPost = await postForm(p2, `/employer/review-app.php?id=${FX.foreignAppId}`, {
      csrf_token: empToken,
      status: 'declined',
      supervisor_notes: 'QA-IDOR-ESCAPE',
    });
    const idorFatal = PHP_FATAL.test(idorPost.text) || PHP_SOFT.test(idorPost.text);
    // Oracle: the owning student's tracker (user 1 owns application 1).
    await page.goto(`${QA_BASE}/student/my-applications.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
    await settle(page, 400);
    const foreignCard = await trackerCard(page, FX.foreignJob.id);
    seen.idorReviewPost = { status: idorPost.status, url: idorPost.url, card: foreignCard, fatal: idorFatal };
    res.check('E6 IDOR: POST review-app.php for a foreign application does not change its status',
      !!foreignCard && /interview scheduled/i.test(foreignCard.status),
      foreignCard ? `status="${foreignCard.status}" (expected "Interview Scheduled")` : 'card missing');
    res.check('E6 IDOR: foreign decision writes no supervisor note',
      !(foreignCard && /QA-IDOR-ESCAPE/i.test(foreignCard.snippet)), foreignCard ? foreignCard.snippet.slice(0, 140) : 'card missing');

    const idorSucceeded = stillTitle !== FX.foreignJob.title
      || (foreignCard && !/interview scheduled/i.test(foreignCard.status));
    if (idorSucceeded) {
      res.finding({
        title: 'Cross-employer IDOR: an employer can act on another department\'s vacancy/application',
        severity: 'high',
        route: '/employer/edit-job.php?id=1 and /employer/review-app.php?id=1',
        role: 'employer',
        steps: [
          `Sign in as employer ${FX.employer.id} (${FX.employer.org}) - owner of jobs ${FX.ownJobIds.join(', ')} only`,
          `GET /employer/edit-job.php?id=${FX.foreignJob.id} (owned by employer ${FX.foreignJob.employerId}) and POST a new title`,
          `GET /employer/review-app.php?id=${FX.foreignAppId} (application on job ${FX.foreignJob.id}) and POST status=declined`,
        ],
        expected: 'Both requests are refused (can_manage_job / can_review_application) and no record changes.',
        actual: `Foreign vacancy title is now "${stillTitle}"; foreign application status is now "${foreignCard ? foreignCard.status : '(unknown)'}".`,
        evidence: [
          `POST /employer/edit-job.php?id=${FX.foreignJob.id} -> HTTP ${iePost.status} ${iePost.url}`,
          `POST /employer/review-app.php?id=${FX.foreignAppId} -> HTTP ${idorPost.status} ${idorPost.url}`,
          await saveHtml(iePost.text, ART, 'e6-idor-edit-job-post'),
          await saveHtml(idorPost.text, ART, 'e6-idor-review-post'),
        ].filter(Boolean),
      });
    }
    await shot(p2, ART, 'e6-idor-final');
  });

  /* ================================================================== *
   * PHASE 3 — health
   * ================================================================== */
  await section('health', page, async () => {
    const js = errors.filter((e) => e.kind === 'pageerror');
    const js2 = errors2.filter((e) => e.kind === 'pageerror');
    res.check('no uncaught JS errors in either session',
      js.length === 0 && js2.length === 0,
      [...js, ...js2].map((e) => e.text).join(' | ') || 'clean');

    const five = [...errors, ...errors2].filter((e) => e.kind === 'http');
    res.check('no HTTP 5xx in either session', five.length === 0,
      five.map((e) => e.text).join(' | ') || 'clean');

    const consoleErrs = [...errors, ...errors2].filter((e) => e.kind === 'console');
    res.check('no console error noise beyond resource 404s',
      consoleErrs.filter((e) => !/404|Failed to load resource/i.test(e.text)).length === 0,
      consoleErrs.map((e) => e.text).slice(0, 5).join(' | ') || 'none');
  });

  res.save(ART);
  fs.writeFileSync(path.join(ART, 'result.json'), JSON.stringify({
    passed: res.passed,
    failed: res.failed,
    findings: res.findings.length,
    discovered: seen,
  }, null, 2));
} catch (e) {
  console.log(`\n[20-lifecycle] ABORTED: ${String(e).split('\n')[0]}`);
  res.check('lifecycle journey completed without abort', false, String(e).split('\n')[0]);
  await shot(page, ART, 'aborted').catch(() => {});
  res.save(ART);
} finally {
  await sleep(300);
  if (other) await other.close().catch(() => {});
  await browser.close();
}
