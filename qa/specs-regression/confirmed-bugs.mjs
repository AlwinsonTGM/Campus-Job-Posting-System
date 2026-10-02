/**
 * REGRESSION SUITE — one pinned test per confirmed bug.
 *
 * Every check below FAILS while its bug is open and is expected to PASS once
 * fixed. Run with:
 *     qa\run-batch.cmd qa\specs-regression\confirmed-bugs.mjs
 *
 * Bug ids match qa/reports/BUG_REPORT.md.
 */
import * as path from 'node:path';
import * as fs from 'node:fs';
import { launch, Results, QA_BASE, httpGet, sleep, saveHtml } from '../lib/harness.mjs';
import { completeStudentRegistration } from '../lib/register.mjs';
import { execFileSync } from 'node:child_process';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'regression');
fs.mkdirSync(ART, { recursive: true });
const res = new Results('regression-confirmed-bugs');

const MYSQL = 'C:\\xampp\\mysql\\bin\\mysql.exe';
const qa = (sql) => execFileSync(MYSQL, ['-u', 'root', '-N', '-e', sql], { encoding: 'utf8' }).trim();
const FATAL = /(Fatal error|Uncaught (TypeError|Error))/i;

const { browser, page } = await launch();
try {
  // =====================================================================
  // BUG-01 — student registration must not crash with a TypeError
  // =====================================================================
  console.log('\n--- BUG-01: registration must not fatal ---');
  {
    let postBody = '';
    const onResp = async (r) => {
      if (r.request().method() === 'POST' && /register\.php/.test(r.url())) {
        try { postBody = await r.text(); } catch { /* ignore */ }
      }
    };
    page.on('response', onResp);

    const stamp = Date.now();
    await completeStudentRegistration(page, { email: `qa_reg_${stamp}@gmail.com`, stamp });
    await sleep(600);
    page.off('response', onResp);

    const fatal = FATAL.test(postBody);
    res.check('BUG-01: registration POST does not return a PHP fatal', !fatal,
      fatal ? (postBody.match(/Uncaught \w+: [^<\n]{0,160}/) || [''])[0] : '');
    res.check('BUG-01: registration reaches verify-email.php', /verify-email\.php/.test(page.url()),
      `url=${page.url()}`);
  }

  // =====================================================================
  // BUG-02 — committed rows and files must agree
  // =====================================================================
  console.log('\n--- BUG-02: no orphaned upload without a DB row ---');
  {
    const proofDir = path.resolve(import.meta.dirname, '..', '..', 'uploads', 'proofs');
    const files = fs.existsSync(proofDir) ? fs.readdirSync(proofDir).length : 0;
    const rows = Number(qa(
      "SELECT COUNT(*) FROM campus_job_portal_e2e.student_profiles WHERE registration_proof IS NOT NULL AND registration_proof <> ''"));
    res.check('BUG-02: stored proof files are referenced by a student profile', files <= rows + 2,
      `files=${files} referenced_rows=${rows} (orphans accumulate when registration fails)`);
  }

  // =====================================================================
  // BUG-03 — array query params must not fatal (systemic)
  // =====================================================================
  console.log('\n--- BUG-03: array params handled on every route ---');
  {
    const routes = [
      '/student/job-details.php?id[]=1',
      '/update-detail.php?id[]=1',
      '/student/jobs.php?job_type[]=a',
      '/student/jobs.php?keyword[]=a',
      '/student/jobs.php?category[]=a',
    ];
    for (const r of routes) {
      const resp = await httpGet(r);
      const bad = FATAL.test(resp.text);
      res.check(`BUG-03: ${r.split('?')[0]} handles an array param`, !bad,
        bad ? (resp.text.match(/Uncaught \w+: [^<\n]{0,120}/) || [''])[0] : `HTTP ${resp.status}`);
    }
  }

  // =====================================================================
  // BUG-04 — data-toggle must require auth + POST + CSRF
  // (verified fixed: see qa/verification/verify-cross-employer-idor.mjs
  //  sibling probes; this check guards against regression)
  // =====================================================================
  console.log('\n--- BUG-04: unauthenticated GET must not switch datasets ---');
  {
    const before = Number(qa('SELECT COUNT(*) FROM campus_job_portal_e2e.users'));
    // Use Playwright's request API: Node's global fetch intermittently throws
    // "AssertionError: !this.paused" on abrupt socket close in this environment.
    const r = await page.request.get(`${QA_BASE}/data-toggle.php?action=switch_mode&mode=real`, {
      maxRedirects: 0,
      failOnStatusCode: false,
    });
    await sleep(2500);
    const after = Number(qa('SELECT COUNT(*) FROM campus_job_portal_e2e.users'));

    // Refusal may be expressed either as an explicit 401/403/405 or as a
    // redirect to login (require_auth runs first). What must never happen is
    // the dataset changing - that is the security guarantee.
    const refused = [401, 403, 405].includes(r.status()) || [301, 302, 303, 307, 308].includes(r.status());
    res.check('BUG-04: anonymous GET is refused (403/405 or redirect to login)', refused, `status=${r.status()}`);
    res.check('BUG-04: anonymous GET does not destroy data', after === before,
      `users ${before} -> ${after}`);

    if (!refused && after !== before) {
      // leave the fixtures usable for the rest of the suite
      await page.request.get(`${QA_BASE}/data-toggle.php?action=switch_mode&mode=demo`, { failOnStatusCode: false });
      await sleep(2500);
      await page.request.get(`${QA_BASE}/login.php?reset=1`, { failOnStatusCode: false });
      await sleep(2500);
    }
  }

  // =====================================================================
  // BUG-05 — employer dashboard + job editor must render (csrf_field)
  // =====================================================================
  console.log('\n--- BUG-05: employer screens must render ---');
  {
    await page.goto(`${QA_BASE}/login.php?demo=employer`, { waitUntil: 'domcontentloaded' });
    await sleep(900);

    for (const url of ['/employer/dashboard.php', '/employer/edit-job.php?id=3']) {
      await page.goto(`${QA_BASE}${url}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      await sleep(700);
      const body = await page.content();
      const bad = FATAL.test(body);
      res.check(`BUG-05: ${url.split('?')[0]} renders without a fatal`, !bad,
        bad ? (body.match(/Uncaught \w+: [^<\n]{0,140}/) || [''])[0] : '');
      if (bad) await saveHtml(body, ART, 'bug05' + url.replace(/[^a-z]/gi, '_'));
    }

    // The dashboard must list ALL of the employer's vacancies, not just the first.
    await page.goto(`${QA_BASE}/employer/dashboard.php`, { waitUntil: 'domcontentloaded' });
    await sleep(800);
    const owned = Number(qa('SELECT COUNT(*) FROM campus_job_portal_e2e.jobs WHERE employer_id=3 AND is_archived=0'));
    const links = await page.evaluate(() =>
      [...new Set([...document.querySelectorAll('a[href*="edit-job.php?id="]')].map((a) => a.getAttribute('href')))]);
    res.check('BUG-05: dashboard lists every vacancy the employer owns', links.length === owned,
      `rendered ${links.length} of ${owned} (page truncates at the first fatal)`);
  }

  // =====================================================================
  // BUG-06 — seed data must satisfy its own ENUM
  // =====================================================================
  console.log('\n--- BUG-06: no out-of-ENUM application statuses ---');
  {
    const empty = Number(qa("SELECT COUNT(*) FROM campus_job_portal_e2e.applications WHERE status=''"));
    res.check('BUG-06: no application row has an empty status', empty === 0,
      `${empty} row(s) coerced to '' by the ENUM`);
  }

  // =====================================================================
  // BUG-07 — listing facets must match real taxonomy values
  // =====================================================================
  console.log('\n--- BUG-07: listing facets return results ---');
  {
    const stored = qa("SELECT DISTINCT job_type FROM campus_job_portal_e2e.jobs WHERE status='active'")
      .split('\n').map((s) => s.trim()).filter(Boolean);
    // The facet values the UI offers must exist verbatim in jobs.job_type,
    // because student/jobs.php matches them with LIKE '%<value>%'.
    for (const facet of ['Part-Time Job', 'Peer Tutor']) {
      const offered = stored.includes(facet);
      res.check(`BUG-07: offered facet "${facet}" exists verbatim in jobs.job_type`, offered,
        `stored job_type values = [${stored.join(', ')}]`);
    }
  }

  // =====================================================================
  // BUG-08 — dbtest.php must not be publicly reachable
  // =====================================================================
  console.log('\n--- BUG-08: dbtest.php must not expose internals ---');
  {
    const r = await httpGet('/dbtest.php');
    const leaks = /PDO CONNECTION|DB_USER|DB_HOST|open_basedir|document_root/i.test(r.text);
    res.check('BUG-08: /dbtest.php does not disclose DB internals', !(r.status === 200 && leaks),
      `HTTP ${r.status}, leaks=${leaks}`);
  }

  // =====================================================================
  // BUG-09 — every referenced asset must resolve
  // =====================================================================
  console.log('\n--- BUG-09: /about-us.php references only existing assets ---');
  {
    const missing = [];
    page.on('response', (r) => { if (r.status() === 404) missing.push(r.url()); });
    await page.goto(`${QA_BASE}/about-us.php`, { waitUntil: 'networkidle' }).catch(() => {});
    await sleep(1200);
    res.check('BUG-09: no asset 404s on /about-us.php', missing.length === 0,
      missing.slice(0, 3).join(', '));
  }

  // =====================================================================
  // BUG-10 — FAQ accordion must never end up with every panel closed
  // =====================================================================
  console.log('\n--- BUG-10: FAQ accordion always keeps one question open ---');
  {
    await page.goto(`${QA_BASE}/faqs.php`, { waitUntil: 'domcontentloaded' });
    await sleep(900);

    const read = () => page.evaluate(() =>
      [...document.querySelectorAll('[data-bs-toggle="collapse"]')].map((b) => {
        const sel = b.getAttribute('data-bs-target') || b.getAttribute('aria-controls');
        const p = sel ? document.querySelector(sel.startsWith('#') ? sel : '#' + sel) : null;
        return { open: !!(p && p.classList.contains('show')), aria: b.getAttribute('aria-expanded') };
      }));

    // Q1 ships open by default. Clicking an ALREADY-OPEN question in an
    // accordion group (data-bs-parent) should leave a panel open, not close
    // everything - "one question open" is the documented contract.
    const btns = page.locator('[data-bs-toggle="collapse"]');
    const before = await read();
    const openIdx = before.findIndex((s) => s.open);
    res.check('BUG-10: a FAQ question is open on load', openIdx >= 0, JSON.stringify(before.map((s) => s.open)));

    await btns.nth(openIdx).click().catch(() => {});
    await sleep(1200);
    const after = await read();

    res.check('BUG-10: clicking the open question leaves a question open',
      after.some((s) => s.open),
      `all panels closed — before=${before.map((s) => (s.open ? 1 : 0)).join('')} after=${after.map((s) => (s.open ? 1 : 0)).join('')}`);
    res.check('BUG-10: ARIA agrees with the visible panels',
      after.every((s) => String(s.open) === s.aria),
      JSON.stringify(after.map((s) => ({ open: s.open, aria: s.aria }))));
  }

  res.save(ART);
} catch (e) {
  res.check('regression suite completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(300);
  await browser.close();
  process.exit(0);
}
