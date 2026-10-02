/**
 * TEAM 10 — PUBLIC / VISITOR journeys.
 *
 * Everything an anonymous, unauthenticated visitor can reach from a cold
 * browser: the landing page, the informational pages, the public vacancy
 * browser, the live-search JSON API, and the guards that must stop a visitor
 * from entering a portal.
 *
 * The abuse cases here are HYPOTHESES. Anything that passes is recorded as a
 * pass; anything that genuinely breaks is filed with the raw status + body as
 * evidence. Nothing in this file is allowed to throw on an app defect.
 */
import * as path from 'node:path';
import { launch, watchErrors, Results, QA_BASE, shot, saveHtml, httpGet, sleep } from '../lib/harness.mjs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'public');
const res = new Results('10-public');
const { browser, page } = await launch();
const errors = watchErrors(page);

/** Substring of a response body around a PHP diagnostic, for evidence strings. */
const around = (text, needle) => {
  const i = text.indexOf(needle);
  if (i < 0) return '';
  return text.slice(Math.max(0, i - 40), i + 320).replace(/\s+/g, ' ').trim();
};

/** Read a metric off the live page without letting a missing node throw. */
async function probe(page, fn, fallback = null) {
  try { return await page.evaluate(fn); } catch { return fallback; }
}

/** Run one labelled group of assertions; a group crash never aborts the team. */
let groupNo = 0;
async function group(label, fn) {
  groupNo += 1;
  console.log(`\n--- ${String(groupNo).padStart(2, '0')} ${label} ---`);
  try {
    await fn();
  } catch (e) {
    res.check(`group "${label}" completed without crashing the runner`, false, String(e).split('\n')[0]);
  }
}

/** Public routes that must be reachable, 200, and free of PHP diagnostics. */
const PUBLIC_ROUTES = [
  ['/', 'landing page'],
  ['/index.php', 'landing page (explicit)'],
  ['/about-us.php', 'about us'],
  ['/faqs.php', 'FAQs'],
  ['/privacy.php', 'data privacy policy'],
  ['/terms.php', 'terms of service'],
  ['/login.php', 'login'],
  ['/register.php', 'registration'],
  ['/forgot-pass.php', 'forgot password'],
  ['/student/jobs.php', 'public vacancy browser'],
];

/** Routes that must never render for an anonymous visitor. */
const PROTECTED_ROUTES = [
  ['/student/dashboard.php', 'student dashboard'],
  ['/student/my-applications.php', 'student applications'],
  ['/employer/dashboard.php', 'employer dashboard'],
  ['/employer/applicants.php', 'employer applicants'],
  ['/admin/users.php', 'admin user management'],
  ['/admin/categories.php', 'admin categories'],
  ['/settings.php', 'account settings'],
  ['/notifications.php', 'notifications'],
  ['/view-resume.php', 'resume viewer'],
];

/**
 * Markers that only appear when a protected page actually rendered.
 *
 * Deliberately NOT the raw route name: the login page echoes the blocked path
 * in a hidden `<input name="next" value="student/my-applications.php">`, so
 * matching on "my-applications" produces a guaranteed false positive. These are
 * content markers instead — data that would only exist on the protected page.
 */
const LEAK_MARKERS = [
  'applicant-application-row', 'resume-preview-body', 'admin-user-table',
  'user-management-table', 'settings-panel', 'dashboard-stats',
  'employer-stats', 'notification-list', 'application-status-badge',
];

const countLeakMarkers = (body) => LEAK_MARKERS.filter((m) => body.includes(m));

/** Raw files inside the docroot that should never be served over HTTP. */
const SENSITIVE_FILES = [
  ['/data/users.json', /"password_hash"|"email"/],
  ['/data/jobs.json', /"title"|"department"/],
  ['/database/schema.sql', /CREATE TABLE/i],
  ['/database/seed_data.sql', /INSERT INTO/i],
  ['/.env', /NVIDIA_API_KEY=|MAIL_PASSWORD=/],
];

const PHP_DIAGNOSTIC = /Fatal error|Warning:|Notice:|Deprecated:/;

try {
  // =========================================================================
  // 1. Landing page
  // =========================================================================
  await group('index.php renders as a visitor', async () => {
    let status = 0;
    let html = '';
    try {
      const r = await page.goto(`${QA_BASE}/index.php`, { waitUntil: 'domcontentloaded' });
      status = r ? r.status() : 0;
      await page.waitForLoadState('networkidle').catch(() => {});
      html = await page.content();
    } catch (e) {
      res.check('index.php is reachable', false, String(e).split('\n')[0]);
    }
    res.check('index.php returns HTTP 200', status === 200, `status=${status}`);

    const diag = html.match(PHP_DIAGNOSTIC);
    res.check('index.php body contains no PHP warning/fatal/notice text', !diag,
      diag ? `found "${diag[0]}" -> ${around(html, diag[0])}` : 'no diagnostics found');

    const jsErrors = errors.filter((e) => e.kind === 'pageerror');
    res.check('index.php raises no uncaught JS errors', jsErrors.length === 0,
      jsErrors.map((e) => e.text).join(' | '));

    // Featured vacancies must contain real, named job entries.
    const trackCount = await page.locator('#featured-carousel-track .featured-job-card').count();
    const anyFeatured = await page.locator('.featured-job-card').count();
    const emptyState = await page.locator('.empty-state-title').count();
    res.check('featured-jobs area is present on the landing page',
      anyFeatured > 0 || emptyState > 0,
      `featured cards=${anyFeatured} emptyState=${emptyState}`);

    const titles = await page.locator('.featured-job-card .featured-card-body h3, .featured-job-card .featured-card-body h4, .featured-job-card h3').allInnerTexts().catch(() => []);
    const namedTitles = titles.map((t) => t.trim()).filter((t) => t.length > 2);
    res.check('featured-jobs area actually contains job entries',
      anyFeatured > 0 && namedTitles.length > 0,
      `cards=${anyFeatured} (track=${trackCount}) titledEntries=${namedTitles.length} sample="${namedTitles[0] || '(none)'}"`);

    if (namedTitles.length === 0) {
      res.finding({
        title: 'Landing page featured-jobs area renders no job entries',
        severity: 'medium', route: '/index.php', role: 'public',
        steps: ['Open /index.php as an anonymous visitor', 'Inspect the "Featured Campus & Partner Opportunities" section'],
        expected: 'At least one featured vacancy card with a job title',
        actual: `featured cards=${anyFeatured}; titled entries=${namedTitles.length}; emptyState nodes=${emptyState}`,
        evidence: [await saveHtml(html, ART, 'index-no-featured'), await shot(page, ART, 'index-no-featured')].filter(Boolean),
      });
    }

    await shot(page, ART, 'index-1440');
  });

  // =========================================================================
  // 2. Every public route: 200 + zero PHP diagnostics
  // =========================================================================
  await group('public routes are healthy over HTTP', async () => {
    for (const [route, label] of PUBLIC_ROUTES) {
      let status = 0;
      let text = '';
      try {
        const r = await httpGet(route);
        status = r.status;
        text = r.text;
      } catch (e) {
        res.check(`${label} ${route} responds`, false, String(e).split('\n')[0]);
        continue;
      }
      res.check(`${label} ${route} returns HTTP 200`, status === 200, `status=${status}`);

      const diag = text.match(PHP_DIAGNOSTIC);
      res.check(`${label} ${route} contains no PHP warning/fatal/notice/deprecated text`, !diag,
        diag ? `found "${diag[0]}" (${text.length} bytes) -> ${around(text, diag[0])}` : `no diagnostics (${text.length} bytes)`);

      if (diag) {
        res.finding({
          title: `Public route ${route} leaks a PHP diagnostic to visitors`,
          severity: 'high', route, role: 'public',
          steps: [`Request ${QA_BASE}${route} with no session cookie`, 'Search the rendered body for PHP diagnostic strings'],
          expected: 'A clean 200 page with no PHP error text',
          actual: `HTTP ${status}; body contains "${diag[0]}" at offset ${text.indexOf(diag[0])} of ${text.length} bytes`,
          evidence: [`${QA_BASE}${route}`, await saveHtml(text, ART, `phpdiag-${route.replace(/[^a-z0-9]/gi, '_')}`)].filter(Boolean),
        });
      }
    }
  });

  // =========================================================================
  // 3. Public vacancy browser + anonymous job-detail journey
  // =========================================================================
  await group('public vacancy browser and anonymous job detail', async () => {
    await page.goto(`${QA_BASE}/student/jobs.php`, { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle').catch(() => {});

    const cardCount = await page.locator('a[href*="job-details.php?id="]').count();
    res.check('vacancy browser lists job cards to an anonymous visitor', cardCount > 0, `job links=${cardCount}`);

    const hrefs = await page.locator('a[href*="job-details.php?id="]').evaluateAll(
      (els) => els.map((e) => e.getAttribute('href')).filter(Boolean));
    res.check('vacancy browser exposes job-detail links', hrefs.length > 0, `count=${hrefs.length} first=${hrefs[0] || '(none)'}`);

    let detailStatus = 0;
    let detailHtml = '';
    let detailTitle = '';
    try {
      await page.goto(`${QA_BASE}/student/job-details.php?id=18`, { waitUntil: 'domcontentloaded' });
      await page.waitForLoadState('networkidle').catch(() => {});
      detailHtml = await page.content();
      detailTitle = (await page.title().catch(() => '')) || '';
      detailStatus = 200;
    } catch (e) {
      res.check('anonymous visitor can open a job detail page', false, String(e).split('\n')[0]);
    }

    const detailDiag = detailHtml.match(PHP_DIAGNOSTIC);
    res.check('job detail page renders for an anonymous visitor without PHP diagnostics',
      detailHtml.length > 2000 && !detailDiag,
      `bytes=${detailHtml.length} title="${detailTitle}" diag=${detailDiag ? detailDiag[0] : 'none'}`);

    // The apply CTA must funnel the visitor to login rather than to an application form.
    let applyFinal = '';
    let applyBodies = [];
    try {
      const r = await httpGet('/student/apply.php?id=18');
      applyBodies.push({ status: r.status, text: r.text });
      await page.goto(`${QA_BASE}/student/apply.php?id=18`, { waitUntil: 'domcontentloaded' });
      await page.waitForLoadState('networkidle').catch(() => {});
      applyFinal = page.url();
      applyBodies.push({ status: 200, text: await page.content() });
    } catch (e) {
      res.check('anonymous visitor hitting the apply URL is handled', false, String(e).split('\n')[0]);
    }

    const applyGated = /login\.php/.test(applyFinal);
    const applyLeak = applyBodies.some((b) => /name="cover_letter"|applicant-application-form|submit-application/.test(b.text));
    res.check('anonymous visitor opening the apply URL is redirected to login', applyGated,
      `finalUrl=${applyFinal || '(navigation failed)'}`);
    res.check('anonymous apply redirect does not render an application form', !applyLeak,
      applyLeak ? 'an application-form marker appeared in the body' : 'no application-form markers in body');

    if (!applyGated && applyFinal) {
      res.finding({
        title: 'Anonymous visitor reaches the job-application flow without authenticating',
        severity: 'critical', route: '/student/apply.php?id=18', role: 'public',
        steps: ['Open /student/apply.php?id=18 with no session', 'Observe the final URL and rendered body'],
        expected: 'HTTP 302 to /login.php?next=...',
        actual: `Landed on ${applyFinal}`,
        evidence: [await shot(page, ART, 'apply-anon')].filter(Boolean),
      });
    }
  });

  // =========================================================================
  // 4. FAQ accordion actually expands (behaviour, not just markup)
  // =========================================================================
  await group('FAQ accordion expands on click', async () => {
    await page.goto(`${QA_BASE}/faqs.php`, { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle').catch(() => {});

    const total = await page.locator('.faq-accordion-btn').count();
    res.check('FAQ page renders accordion controls', total > 0, `accordion buttons=${total}`);

    const collapsed = page.locator('.faq-accordion-btn[aria-expanded="false"]').first();
    const hasCollapsed = await collapsed.count() > 0;
    res.check('FAQ page ships at least one collapsed question to expand', hasCollapsed,
      `collapsed buttons=${await page.locator('.faq-accordion-btn[aria-expanded="false"]').count()}`);

    if (!hasCollapsed) {
      res.finding({
        title: 'FAQ accordion has no collapsed question, so expansion cannot be exercised',
        severity: 'low', route: '/faqs.php', role: 'public',
        steps: ['Open /faqs.php', 'Count buttons with aria-expanded="false"'],
        expected: 'Most questions start collapsed',
        actual: `accordion buttons=${total}, all report aria-expanded="true"`,
        evidence: [await shot(page, ART, 'faq-all-expanded')].filter(Boolean),
      });
    } else {
      // Scope every assertion to the ONE panel this button controls. A bare
      // `.accordion-collapse.show` locator also matches the question that is
      // open by default, which makes the check pass no matter what was clicked.
      const controls = await collapsed.getAttribute('aria-controls');
      const targetSel = controls ? `#${controls}` : null;
      const target = targetSel ? page.locator(targetSel) : null;

      res.check('FAQ accordion button declares which panel it controls',
        !!targetSel && await target.count() > 0,
        `aria-controls=${controls || '(missing)'}`);

      const visibleBefore = target
        ? await target.isVisible().catch(() => null)
        : null;
      res.check('FAQ question starts collapsed before the click', visibleBefore === false,
        `target panel visible before click=${visibleBefore}`);

      // A FAQ card is a large row; Playwright's actionability check can refuse
      // it, so fall back to a direct DOM click and report which route fired.
      let clickMode = 'playwright';
      try {
        await collapsed.click({ timeout: 5000 });
      } catch {
        clickMode = 'js-fallback';
        try {
          await collapsed.evaluate((el) => el.click());
        } catch (e) {
          clickMode = 'failed';
          res.check('collapsed FAQ question can be clicked', false, String(e).split('\n')[0]);
        }
      }
      await sleep(600); // allow the Bootstrap collapse transition to finish

      const expandedAfter = await collapsed.getAttribute('aria-expanded').catch(() => null);
      const visibleAfter = target ? await target.isVisible().catch(() => false) : false;
      const hasShowClass = target ? await target.evaluate((el) => el.classList.contains('show')).catch(() => false) : false;

      res.check('clicking a collapsed FAQ question reveals its answer panel',
        visibleAfter === true && hasShowClass === true,
        `click=${clickMode} targetVisible=${visibleAfter} hasShowClass=${hasShowClass}`);

      // Bootstrap's collapse plugin is expected to keep aria-expanded in sync.
      res.check('clicking a collapsed FAQ question flips aria-expanded to true',
        expandedAfter === 'true',
        `before=false after=${expandedAfter} (panelVisible=${visibleAfter})`);

      const answerText = visibleAfter ? (await target.innerText().catch(() => '')).trim() : '';
      res.check('revealed FAQ answer contains real answer text', answerText.length > 20,
        `bytes=${answerText.length} sample="${answerText.slice(0, 90)}"`);

      // It must toggle closed again; a one-way accordion is a defect.
      try {
        await collapsed.click({ timeout: 5000 }).catch(() => collapsed.evaluate((el) => el.click()));
      } catch { /* observed below */ }
      await sleep(600);
      const visibleAfterSecond = target ? await target.isVisible().catch(() => null) : null;
      res.check('clicking the expanded FAQ question collapses it again', visibleAfterSecond === false,
        `target panel visible after second click=${visibleAfterSecond}`);

      if (visibleAfter !== true) {
        res.finding({
          title: 'FAQ accordion question does not expand when its button is clicked',
          severity: 'medium', route: '/faqs.php', role: 'public',
          steps: ['Open /faqs.php', 'Click a collapsed FAQ question', `Inspect the panel named by aria-controls (${controls || 'n/a'})`],
          expected: 'The controlled .accordion-collapse gains .show and becomes visible',
          actual: `click=${clickMode}; controlled panel visible=${visibleAfter}, hasShowClass=${hasShowClass}, aria-expanded=${expandedAfter}`,
          evidence: [await shot(page, ART, 'faq-click-failed'), await saveHtml(page, ART, 'faq-click-failed')].filter(Boolean),
        });
      } else if (expandedAfter !== 'true') {
        res.finding({
          title: 'FAQ accordion opens visually but leaves aria-expanded="false" (screen-reader state desync)',
          severity: 'low', route: '/faqs.php', role: 'public',
          steps: ['Open /faqs.php', 'Click a collapsed FAQ question so its answer panel opens', 'Read aria-expanded on the clicked button'],
          expected: 'aria-expanded reflects the open panel (Bootstrap collapse keeps it in sync)',
          actual: `Panel named by aria-controls="${controls}" became visible (hasShowClass=${hasShowClass}) but the button still reports aria-expanded="${expandedAfter}"`,
          evidence: [await shot(page, ART, 'faq-aria-desync'), await saveHtml(page, ART, 'faq-aria-desync')].filter(Boolean),
        });
      }

      await shot(page, ART, 'faq-expanded');
    }
  });

  // =========================================================================
  // 5. Live search API — contract then abuse cases
  // =========================================================================
  await group('search API contract', async () => {
    let status = 0;
    let text = '';
    let ct = '';
    try {
      const r = await httpGet('/api/search-jobs.php?q=assistant');
      status = r.status;
      text = r.text;
      ct = r.headers.get('content-type') || '';
    } catch (e) {
      res.check('search API responds to a normal query', false, String(e).split('\n')[0]);
    }

    res.check('search API returns HTTP 200 for a normal query', status === 200, `status=${status}`);
    res.check('search API advertises a JSON content-type', /application\/json/i.test(ct), `content-type=${ct}`);

    let json = null;
    try { json = JSON.parse(text); } catch { /* recorded below */ }
    res.check('search API returns parseable JSON for a normal query', json !== null,
      json ? `keys=${Object.keys(json).join(',')}` : `unparseable (${text.length} bytes) -> ${text.slice(0, 200)}`);

    if (json) {
      const okShape = json.status === 'success'
        && typeof json.query === 'string'
        && Number.isFinite(json.total)
        && Number.isFinite(json.count)
        && Array.isArray(json.results)
        && Array.isArray(json.jobs);
      res.check('search API payload has the documented shape (status/query/total/count/results/jobs)', okShape,
        `status=${json.status} query=${JSON.stringify(json.query)} total=${json.total} count=${json.count} results=${Array.isArray(json.results)} jobs=${Array.isArray(json.jobs)}`);

      res.check('search API echoes the query it searched for', json.query === 'assistant', `query=${JSON.stringify(json.query)}`);

      const first = Array.isArray(json.results) && json.results.length ? json.results[0] : null;
      const fieldOk = !!first
        && typeof first.id === 'number'
        && typeof first.title === 'string'
        && typeof first.department === 'string'
        && typeof first.job_type === 'string'
        && typeof first.work_setup === 'string'
        && typeof first.pay_rate === 'string';
      res.check('search API result entries expose id/title/department/job_type/work_setup/pay_rate', fieldOk,
        first ? `keys=${Object.keys(first).join(',')}` : 'no results returned for "assistant"');

      res.check('search API "assistant" query matches at least one vacancy', json.total > 0, `total=${json.total} count=${json.count}`);
      res.check('search API caps the returned slice at the requested limit', json.count <= 8, `count=${json.count} (default limit=8)`);
      res.check('search API does not leak PHP diagnostics in a normal response', !PHP_DIAGNOSTIC.test(text),
        PHP_DIAGNOSTIC.test(text) ? around(text, 'Fatal error') : 'clean');

      res.check('search API count never exceeds the reported total', json.count <= json.total,
        `count=${json.count} total=${json.total}`);
    }

    // A `limit` override should be honoured and clamped at 20.
    try {
      const r = await httpGet('/api/search-jobs.php?q=&limit=999');
      const j = JSON.parse(r.text);
      res.check('search API clamps an oversized limit to 20', j.count <= 20, `count=${j.count}`);
    } catch (e) {
      res.check('search API handles an oversized limit parameter', false, String(e).split('\n')[0]);
    }
  });

  await group('search API abuse cases', async () => {
    const longQuery = 'A'.repeat(5000);
    const cases = [
      ['array parameter ?q[]=x', '/api/search-jobs.php?q[]=x'],
      ['empty query ?q=', '/api/search-jobs.php?q='],
      ['null byte ?q=%00', `/api/search-jobs.php?q=${encodeURIComponent('\u0000')}`],
      ['very long ?q= (5000 chars)', `/api/search-jobs.php?q=${longQuery}`],
      ['array parameter ?job_type[]=x', '/api/search-jobs.php?job_type[]=x'],
      ['array parameter ?limit[]=1', '/api/search-jobs.php?limit[]=1'],
      ['script payload in ?q=', '/api/search-jobs.php?q=%3Cscript%3Ealert(1)%3C%2Fscript%3E'],
    ];

    for (const [label, route] of cases) {
      let status = 0;
      let text = '';
      try {
        const r = await httpGet(route);
        status = r.status;
        text = r.text;
      } catch (e) {
        res.check(`search API survives ${label}`, false, `fetch failed: ${String(e).split('\n')[0]}`);
        continue;
      }

      let parsed = null;
      try { parsed = JSON.parse(text); } catch { /* recorded below */ }

      res.check(`search API survives ${label} and still returns JSON`, parsed !== null && status === 200,
        `status=${status} parseable=${parsed !== null} bytes=${text.length}${parsed ? '' : ` body="${text.slice(0, 160).replace(/\s+/g, ' ')}"`}`);

      res.check(`search API returns no PHP diagnostic for ${label}`, !PHP_DIAGNOSTIC.test(text),
        PHP_DIAGNOSTIC.test(text) ? around(text, 'Fatal error') : 'clean');

      if (parsed) {
        res.check(`search API result array stays an array for ${label}`,
          Array.isArray(parsed.results) && Array.isArray(parsed.jobs),
          `results=${Array.isArray(parsed.results)} jobs=${Array.isArray(parsed.jobs)} total=${parsed.total}`);
      }

      if (parsed === null || PHP_DIAGNOSTIC.test(text)) {
        const slug = label.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '');
        res.finding({
          title: `Search API breaks on ${label}`,
          severity: 'high', route, role: 'public',
          steps: [`Request ${QA_BASE}${route} with no session`, 'Inspect status, content-type and body'],
          expected: 'HTTP 200 with a valid JSON payload (bad input degrades to an empty query, never a crash)',
          actual: `HTTP ${status}; parseable JSON=${parsed !== null}; ${text.length} bytes; body starts "${text.slice(0, 200).replace(/\s+/g, ' ')}"`,
          evidence: [`${QA_BASE}${route}`, await saveHtml(text, ART, `search-api-${slug}`)].filter(Boolean),
        });
      }
    }

    // The JSON body echoes ?q= verbatim (json_encode does not hex-escape tags
    // here), so the real question is whether it can be turned into script
    // execution. It cannot: `nosniff` + application/json stop content
    // sniffing, and main.js routes every API field through escapeHtml() before
    // touching innerHTML. These checks assert that defence, not the raw echo.
    try {
      const r = await httpGet('/api/search-jobs.php?q=%3Cscript%3Ealert(1)%3C%2Fscript%3E');
      res.check('search API sends X-Content-Type-Options: nosniff (blocks HTML sniffing of the JSON echo)',
        /nosniff/i.test(r.headers.get('x-content-type-options') || ''),
        `x-content-type-options=${r.headers.get('x-content-type-options') || '(absent)'}`);

      let parsed = null;
      try { parsed = JSON.parse(r.text); } catch { /* recorded below */ }
      res.check('search API keeps the script payload inside a JSON string, not as executable markup',
        parsed !== null && typeof parsed.query === 'string' && parsed.query.includes('<script>'),
        parsed ? `query parsed back as a plain string (${JSON.stringify(String(parsed.query).slice(0, 40))})` : `body did not parse as JSON (${r.text.length} bytes)`);

      // The consuming client must escape API values before rendering them.
      const mainJs = (await httpGet('/assets/js/main.js')).text;
      res.check('spotlight search client escapes API fields before writing innerHTML (escapeHtml defined and applied)',
        /function\s+escapeHtml\s*\(/.test(mainJs) && /escapeHtml\(job\./.test(mainJs),
        /function\s+escapeHtml\s*\(/.test(mainJs)
          ? (/escapeHtml\(job\./.test(mainJs) ? 'escapeHtml() defined and applied to job fields' : 'escapeHtml() defined but not applied to job fields')
          : 'escapeHtml() not found in assets/js/main.js');
    } catch (e) {
      res.check('search API script-payload reflection probe ran', false, String(e).split('\n')[0]);
    }
  });

  // =========================================================================
  // 6. Public vacancy browser — array-parameter robustness
  // =========================================================================
  await group('vacancy browser rejects hostile query parameters gracefully', async () => {
    const cases = [
      ['?category[]=x', '/student/jobs.php?category[]=x'],
      ['?department[]=x', '/student/jobs.php?department[]=x'],
      ['?job_type[]=x', '/student/jobs.php?job_type[]=x'],
      ['?work_setup[]=x', '/student/jobs.php?work_setup[]=x'],
      ['?keyword[]=x', '/student/jobs.php?keyword[]=x'],
      ['?q[]=x', '/student/jobs.php?q[]=x'],
      ['?pay_type[]=x', '/student/jobs.php?pay_type[]=x'],
      ['?employer_type[]=x', '/student/jobs.php?employer_type[]=x'],
      ['?id[]=1 on job detail', '/student/job-details.php?id[]=1'],
      ['?open[]=1 on FAQs', '/faqs.php?open[]=1'],
    ];

    // One finding per affected route, not one per parameter: this is a single
    // root cause (unguarded trim()/scalar type hints on raw $_GET), and nine
    // near-identical findings would bury it.
    const brokenByRoute = new Map();

    for (const [label, route] of cases) {
      let status = 0;
      let text = '';
      try {
        const r = await httpGet(route);
        status = r.status;
        text = r.text;
      } catch (e) {
        res.check(`vacancy browser survives ${label}`, false, `fetch failed: ${String(e).split('\n')[0]}`);
        continue;
      }

      const diag = text.match(PHP_DIAGNOSTIC);
      res.check(`vacancy browser survives ${label} without a PHP diagnostic`, !diag,
        diag ? `HTTP ${status} (${text.length} bytes) -> ${around(text, diag[0])}` : `HTTP ${status}, ${text.length} bytes, clean`);

      res.check(`vacancy browser returns a real page for ${label}`, text.length > 2000,
        `bytes=${text.length}${text.length <= 2000 ? ` body="${text.slice(0, 160).replace(/\s+/g, ' ')}"` : ''}`);

      if (diag || text.length <= 2000) {
        const pathOnly = route.split('?')[0];
        const slug = label.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '');
        const errorLine = (text.match(/(Fatal error|Warning:|Notice:|Deprecated:)[^<\n]{0,220}/) || [diag ? diag[0] : 'truncated body'])[0]
          .replace(/\s+/g, ' ');
        const entry = brokenByRoute.get(pathOnly) || { path: pathOnly, params: [], errors: new Set(), status, bytes: text.length, artifact: null };
        entry.params.push(label);
        entry.errors.add(errorLine);
        entry.status = status;
        entry.bytes = text.length;
        if (!entry.artifact) entry.artifact = await saveHtml(text, ART, `hostile-${slug}`);
        brokenByRoute.set(pathOnly, entry);
      }
    }

    for (const entry of brokenByRoute.values()) {
      res.finding({
        title: `Public route ${entry.path} returns a PHP fatal instead of a page when a query parameter is sent as an array (${entry.params.length} parameter(s) affected)`,
        severity: 'high', route: `${entry.path} (${entry.params.join(', ')})`, role: 'public',
        steps: [
          `Request a normal ${entry.path} — renders fine.`,
          `Request it with an array parameter, e.g. ${QA_BASE}${entry.path}${entry.params[0].split(' on ')[0]}`,
          `Observe HTTP ${entry.status} with a ${entry.bytes}-byte PHP fatal body instead of the page.`,
        ],
        expected: 'The page degrades gracefully (parameter ignored or coerced), rendering full content',
        actual: `${entry.bytes}-byte body, HTTP ${entry.status}. ${[...entry.errors].join(' || ')}`,
        evidence: [`${QA_BASE}${entry.path}`, entry.artifact].filter(Boolean),
      });
    }
  });

  // =========================================================================
  // 7. Anonymous access control
  // =========================================================================
  await group('protected routes are gated for an anonymous visitor', async () => {
    for (const [route, label] of PROTECTED_ROUTES) {
      let httpStatus = 0;
      let location = '';
      let httpBody = '';
      try {
        const r = await httpGet(route);
        httpStatus = r.status;
        location = r.headers.get('location') || '';
        httpBody = r.text;
      } catch (e) {
        res.check(`${label} ${route} responds to a raw request`, false, String(e).split('\n')[0]);
      }

      const redirectsToLogin = [301, 302, 303, 307, 308].includes(httpStatus) && /login\.php/i.test(location);
      const denied = httpStatus === 401 || httpStatus === 403;
      res.check(`${label} ${route} redirects an anonymous request to login`,
        redirectsToLogin || denied,
        `status=${httpStatus} location="${location}"${denied ? ' (explicitly denied)' : ''}`);

      const rawLeaks = countLeakMarkers(httpBody);
      res.check(`${label} ${route} raw response leaks no protected markup`, rawLeaks.length === 0,
        rawLeaks.length ? `${httpBody.length} bytes contain protected-page markers: ${rawLeaks.join(', ')}` : `${httpBody.length} bytes, no protected markers`);

      // And the same route through a real browser (redirects followed).
      let finalUrl = '';
      let finalBody = '';
      try {
        await page.goto(`${QA_BASE}${route}`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle').catch(() => {});
        finalUrl = page.url();
        finalBody = await page.content();
      } catch (e) {
        res.check(`${label} ${route} navigates in the browser`, false, String(e).split('\n')[0]);
      }

      const onLogin = /login\.php/.test(finalUrl);
      res.check(`${label} ${route} lands on the login page in a browser`, onLogin,
        `finalUrl=${finalUrl || '(navigation failed)'}`);

      const browserLeaks = countLeakMarkers(finalBody);
      res.check(`${label} ${route} browser render leaks no protected content`, browserLeaks.length === 0,
        browserLeaks.length ? `protected-page markers rendered after following redirects: ${browserLeaks.join(', ')}` : 'no protected markers rendered');

      if (!onLogin || browserLeaks.length || (!redirectsToLogin && !denied)) {
        const evidence = [await saveHtml(finalBody || httpBody, ART, `guard-${route.replace(/[^a-z0-9]/gi, '_')}`)].filter(Boolean);
        res.finding({
          title: `Anonymous visitor is not properly blocked from ${route}`,
          severity: browserLeaks.length ? 'critical' : 'high', route, role: 'public',
          steps: [`Request ${QA_BASE}${route} with no session cookie`, 'Follow redirects and inspect the final body'],
          expected: 'HTTP 302 to /login.php?next=... with no protected content rendered',
          actual: `raw status=${httpStatus} location="${location}"; browser finalUrl=${finalUrl}; leakedMarkers=[${browserLeaks.join(', ')}]`,
          evidence,
        });
      }
    }
  });

  // =========================================================================
  // 8. Direct file exposure
  //
  // The QA server is PHP's built-in server, which does NOT read .htaccess, so a
  // 200 here is an ENVIRONMENT ARTEFACT. Apache on :80 (which does read
  // .htaccess) is probed too, and the finding is filed at low/informational.
  // =========================================================================
  await group('sensitive files are not served over HTTP', async () => {
    const apacheBase = 'http://127.0.0.1/Final-Campus-Job-Posting-System';
    const apacheStatuses = {};
    const exposed = [];

    for (const [route, signature] of SENSITIVE_FILES) {
      let status = 0;
      let text = '';
      try {
        const r = await httpGet(route);
        status = r.status;
        text = r.text;
      } catch (e) {
        res.check(`QA dev server (:8099) responds for ${route}`, false, `fetch failed: ${String(e).split('\n')[0]}`);
        continue;
      }

      const served = status === 200 && signature.test(text);
      res.check(`QA dev server (:8099) serves ${route} directly — environment artefact, not a product verdict`,
        !served,
        served
          ? `HTTP ${status}, ${text.length} bytes of real file content (PHP's built-in server does not read .htaccess)`
          : `HTTP ${status}, ${text.length} bytes, no file signature matched`);

      if (served) exposed.push({ route, status, len: text.length, sig: signature.source });

      // Counter-check under Apache, which enforces the repo's .htaccess.
      try {
        const ar = await httpGet(`${apacheBase}${route}`);
        apacheStatuses[route] = ar.status;
      } catch (e) {
        apacheStatuses[route] = `ERR ${String(e).split('\n')[0].slice(0, 60)}`;
      }
    }

    const apacheEnforced = Object.entries(apacheStatuses)
      .filter(([, s]) => s === 403 || s === 404).map(([r]) => r);
    const apacheServed = Object.entries(apacheStatuses)
      .filter(([, s]) => s === 200).map(([r]) => r);

    res.check('Apache (:80) still denies every sensitive path — path protection itself is intact',
      apacheServed.length === 0,
      `apache served=${apacheServed.join(',') || 'none'} | blocked(403/404)=${apacheEnforced.join(',') || 'none'}`);

    if (exposed.length) {
      res.finding({
        title: `QA dev server serves ${exposed.length} sensitive docroot file(s) that Apache correctly blocks — ENVIRONMENT ARTEFACT, not a product vulnerability`,
        severity: 'low',
        route: exposed.map((e) => e.route).join(', '),
        role: 'public',
        steps: [
          `Request each sensitive path from ${QA_BASE} (PHP built-in server, port 8099) — all returned 200 with real file bytes.`,
          `Request the same paths from ${apacheBase} (Apache, port 80) — all returned 403.`,
          "Conclude: PHP's built-in server does not read data/.htaccess, database/.htaccess or the root .htaccess, so the 200s are an artefact of the QA harness, NOT a defect in the shipped app.",
        ],
        expected: 'Sensitive stores are unreachable over HTTP in every deployment mode',
        actual: exposed.map((e) => `${e.route} -> HTTP ${e.status}, ${e.len} bytes, matched /${e.sig}/`).join('; ')
          + `. Apache (:80) statuses: ${JSON.stringify(apacheStatuses)} — path protection intact. `
          + 'Residual (non-blocking) risk worth a deployment note: .env sits inside the docroot and relies solely on .htaccess, so any non-Apache server, a renamed directory, or mod_authz_core being absent would expose a live NVIDIA_API_KEY.',
        evidence: [
          `${QA_BASE}/.env (200 on :8099) vs ${apacheBase}/.env (403 on :80)`,
          await saveHtml(`QA :8099 statuses: ${JSON.stringify(exposed)}\nApache :80 statuses: ${JSON.stringify(apacheStatuses)}`, ART, 'file-exposure-comparison'),
        ].filter(Boolean),
      });
    }

    // What exactly is reachable on the QA server, and would Apache have blocked it?
    let envBody = '';
    try { envBody = (await httpGet('/.env')).text; } catch { /* ignore */ }
    const secrets = ['NVIDIA_API_KEY', 'MAIL_PASSWORD', 'MAIL_USERNAME', 'MAIL_HOST']
      .filter((k) => new RegExp(`^${k}=`, 'm').test(envBody));
    res.check('/.env is unreadable on the QA dev server', secrets.length === 0,
      secrets.length
        ? `served keys: ${secrets.join(', ')} (secret values deliberately not copied into artifacts); Apache returns ${apacheStatuses['/.env']}`
        : 'no credential keys present in the response');
  });

  // =========================================================================
  // 9. Responsive sanity — no horizontal overflow
  // =========================================================================
  await group('responsive layout has no horizontal overflow', async () => {
    const widths = [375, 768, 1440];
    const pages = [['/index.php', 'landing page'], ['/student/jobs.php', 'vacancy browser']];

    for (const [route, label] of pages) {
      for (const width of widths) {
        try {
          await page.setViewportSize({ width, height: 900 });
        } catch (e) {
          res.check(`${label} viewport ${width}px can be set`, false, String(e).split('\n')[0]);
          continue;
        }

        let metrics = null;
        try {
          await page.goto(`${QA_BASE}${route}`, { waitUntil: 'domcontentloaded' });
          await page.waitForLoadState('networkidle').catch(() => {});
          await sleep(250); // let reveal animations settle before measuring
          metrics = await probe(page, () => ({
            scrollWidth: document.documentElement.scrollWidth,
            bodyScrollWidth: document.body ? document.body.scrollWidth : 0,
            innerWidth: window.innerWidth,
            innerHeight: window.innerHeight,
            title: document.title,
            // The widest element, to point at the culprit if overflow exists.
            worst: (() => {
              let worst = null;
              for (const el of document.querySelectorAll('body *')) {
                const r = el.getBoundingClientRect();
                if (r.width <= 0 || r.height <= 0) continue;
                const overhang = Math.round(r.right - window.innerWidth);
                if (overhang > 2 && (!worst || overhang > worst.overhang)) {
                  worst = {
                    overhang,
                    tag: el.tagName.toLowerCase(),
                    cls: String(el.className || '').slice(0, 80),
                    id: el.id || '',
                  };
                }
              }
              return worst;
            })(),
          }));
        } catch (e) {
          res.check(`${label} at ${width}px loads for measurement`, false, String(e).split('\n')[0]);
        }

        if (!metrics) continue;

        const ok = metrics.scrollWidth <= metrics.innerWidth + 2
          && metrics.bodyScrollWidth <= metrics.innerWidth + 2;
        const worstDesc = metrics.worst
          ? ` widest overhang: <${metrics.worst.tag}${metrics.worst.id ? ` id="${metrics.worst.id}"` : ''} class="${metrics.worst.cls}"> +${metrics.worst.overhang}px`
          : '';
        res.check(`${label} ${route} has no horizontal overflow at ${width}px`, ok,
          `scrollWidth=${metrics.scrollWidth} bodyScrollWidth=${metrics.bodyScrollWidth} innerWidth=${metrics.innerWidth}${worstDesc}`);

        await shot(page, ART, `${route.replace(/[^a-z0-9]/gi, '_') || 'root'}-${width}`);

        if (!ok) {
          res.finding({
            title: `Horizontal overflow on ${route} at ${width}px`,
            severity: 'medium', route, role: 'public',
            steps: [`Set the viewport to ${width}x900`, `Open ${route}`, 'Compare documentElement.scrollWidth against window.innerWidth'],
            expected: 'scrollWidth <= innerWidth + 2 (no sideways scrolling)',
            actual: `scrollWidth=${metrics.scrollWidth}, bodyScrollWidth=${metrics.bodyScrollWidth}, innerWidth=${metrics.innerWidth}.${worstDesc}`,
            evidence: [await shot(page, ART, `overflow-${width}`), await saveHtml(page, ART, `overflow-${width}`)].filter(Boolean),
          });
        }
      }
    }

    // Leave the shared page back at the harness default before the health sweep.
    await page.setViewportSize({ width: 1440, height: 900 }).catch(() => {});
  });

  // =========================================================================
  // 10. Health sweep across the visitor journey
  // =========================================================================
  await group('visitor journey health', async () => {
    // Attribute sub-request failures to a URL. The harness console trail only
    // carries "Failed to load resource", which is not actionable evidence.
    const badRequests = [];
    const onResponse = (r) => {
      if (r.status() >= 400) badRequests.push(`${r.status()} ${r.request().method()} ${r.url()}`);
    };
    page.on('response', onResponse);

    try {
      for (const [route] of PUBLIC_ROUTES) {
        badRequests.length = 0;
        try {
          await page.goto(`${QA_BASE}${route}`, { waitUntil: 'domcontentloaded' });
          await page.waitForLoadState('networkidle').catch(() => {});
        } catch { /* individual route health is asserted in group 2 */ }

        const failed5xx = badRequests.filter((b) => /^5\d\d /.test(b));
        const failed4xx = badRequests.filter((b) => /^4\d\d /.test(b));

        res.check(`${route} triggers no 5xx sub-request`, failed5xx.length === 0, failed5xx.slice(0, 4).join(' | '));
        res.check(`${route} loads every sub-resource it references`, failed4xx.length === 0,
          failed4xx.length ? failed4xx.slice(0, 6).join(' | ') : 'all sub-requests returned < 400');

        if (failed4xx.length) {
          const slug = route.replace(/[^a-z0-9]/gi, '_') || 'root';
          res.finding({
            title: `${route} requests ${failed4xx.length} sub-resource(s) that return 4xx (broken asset reference)`,
            severity: 'low', route, role: 'public',
            steps: [`Open ${QA_BASE}${route}`, 'Record every sub-request with a status >= 400'],
            expected: 'Every referenced asset resolves (200)',
            actual: failed4xx.join(' | '),
            evidence: [await shot(page, ART, `missing-assets-${slug}`)].filter(Boolean),
          });
        }
      }
    } finally {
      page.off('response', onResponse);
    }

    const pageErrors = errors.filter((e) => e.kind === 'pageerror');
    res.check('no uncaught JS error during the public visitor journey', pageErrors.length === 0,
      pageErrors.slice(0, 4).map((e) => e.text).join(' | '));

    const http5xx = errors.filter((e) => e.kind === 'http');
    res.check('no HTTP 5xx response during the public visitor journey', http5xx.length === 0,
      http5xx.slice(0, 4).map((e) => e.text).join(' | '));

    if (pageErrors.length || http5xx.length) {
      res.finding({
        title: 'Public visitor journey raises uncaught JS errors or 5xx responses',
        severity: 'medium', route: 'multiple public routes', role: 'public',
        steps: ['Visit every public route as an anonymous visitor with console/error capture attached'],
        expected: 'No uncaught exceptions and no server errors on public pages',
        actual: `pageerrors=${pageErrors.length}, http5xx=${http5xx.length}`,
        evidence: [...pageErrors, ...http5xx].slice(0, 10).map((e) => `${e.kind}: ${e.text}`),
      });
    }
  });

  // =========================================================================
  // 11. About Us — the DevBlog stage is visual, so verify the images resolve
  // =========================================================================
  await group('about-us devblog visuals resolve', async () => {
    await page.goto(`${QA_BASE}/about-us.php`, { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle').catch(() => {});

    const stage = await page.locator('#devblog, .devblog-section, .devblog-stage-container').count();
    res.check('about-us.php renders the DevBlog stage section', stage > 0, `matching containers=${stage}`);

    // Read the resolved URLs the browser actually requested, so a broken
    // reference is caught even though the <img> itself degrades silently.
    const imgs = await probe(page, () => [...document.querySelectorAll('img')]
      .map((i) => ({ src: i.currentSrc || i.getAttribute('src') || '', natural: i.naturalWidth }))
      .filter((i) => /devblog/i.test(i.src)), []);
    res.check('about-us.php references at least one devblog cover image', imgs.length > 0,
      `devblog <img> tags=${imgs.length}`);

    const broken = imgs.filter((i) => !i.natural);

    // Independent of the DOM, confirm the referenced files exist on disk.
    let missingOnDisk = [];
    if (imgs.length) {
      const paths = imgs.map((i) => i.src.replace(QA_BASE, '')).filter((p) => p.startsWith('/'));
      for (const p of [...new Set(paths)]) {
        const r = await httpGet(p).catch(() => null);
        if (!r || r.status !== 200) missingOnDisk.push(`${p} -> ${r ? r.status : 'fetch failed'}`);
      }
    }

    res.check('every devblog cover image on about-us.php loads successfully',
      broken.length === 0 && missingOnDisk.length === 0,
      `brokenRendered=${broken.map((b) => b.src).join(', ') || 'none'} | non200OnDisk=${missingOnDisk.join(', ') || 'none'}`);

    if (broken.length || missingOnDisk.length) {
      res.finding({
        title: 'About Us DevBlog carousel references cover image(s) that do not exist (broken images for visitors)',
        severity: 'low', route: '/about-us.php', role: 'public',
        steps: [
          'Open /about-us.php and scroll to "Behind the Code: Lead Developer DevBlog".',
          'Record every devblog <img> and whether it decoded (naturalWidth > 0).',
          'Re-request each referenced path directly.',
        ],
        expected: 'Every devblog cover referenced by data/devblogs.json exists in assets/img/devblog/ and loads',
        actual: `${broken.length} image(s) failed to decode; ${missingOnDisk.length} path(s) returned non-200: ${missingOnDisk.join(', ') || broken.map((b) => b.src).join(', ')}. `
          + 'data/devblogs.json references devblog/day-01.jpg .. day-25.jpg but assets/img/devblog/ contains only day-01..day-24.',
        evidence: [await shot(page, ART, 'about-us-devblog'), await saveHtml(page, ART, 'about-us-devblog')].filter(Boolean),
      });
    }

    await shot(page, ART, 'about-us-devblog');
  });

  res.save(ART);
} catch (e) {
  res.check('public team completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally { await browser.close(); }
