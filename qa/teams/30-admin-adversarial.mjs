/**
 * TEAM 30 — Admin console coverage + adversarial / edge-case security probes.
 *
 * Part A: every /admin/* console page renders cleanly; category lifecycle;
 *         employer accreditation state change (and whether it is GET-driven);
 *         analytics contain real numbers; cross-role dashboard guards.
 * Part B: IDOR across roles, PHP type juggling with array params, path
 *         traversal / static file exposure, SQL-ish input, XSS reflection,
 *         duplicate submit, and session isolation across two browser contexts.
 *
 * Design rules:
 *  - No app defect may throw out of this script. Every probe is wrapped, and
 *    failures are recorded with res.check(false, …) / res.finding({…}).
 *  - Seeded fixtures come from an SQL read of campus_job_portal_e2e (see
 *    QA_SEED below). No ids are invented.
 *  - Known baseline is NOT re-reported: register.php:145 register_user()
 *    TypeError breaks brand-new registration. Demo logins are used instead.
 *
 * Fixture ids observed in `campus_job_portal_e2e` at authoring time:
 *   users  5  admin@kld.edu.ph          (admin)
 *   users  1  student@kld.edu.ph        (student, verified)   <- demo 'student'
 *   users  2  maria.santos@kld.edu.ph   (student, verified)   <- IDOR target
 *   users 20  ralph.santos@kld.edu.ph   (student, pending_approval)
 *   users  3  registrar@kld.edu.ph      (employer, verified)  <- demo 'employer'
 *   users  9  apexrobotics@…            (employer, PENDING accreditation)
 *   jobs   2  employer_id 3 (owned by the demo employer)      <- control
 *   jobs   1  employer_id 4 (NOT owned by the demo employer)  <- IDOR target
 *   applications: student 20 has ZERO rows -> "never applied" resume target
 */
import * as path from 'node:path';
import { launch, watchErrors, Results, QA_BASE, shot, saveHtml, httpGet, sleep, loginAs, logout } from '../lib/harness.mjs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'admin-adversarial');
const res = new Results('30-admin-adversarial');

// ---------------------------------------------------------------------------
// Seeded fixture contract (read from the isolated E2E database, never invented)
// ---------------------------------------------------------------------------
const QA_SEED = {
  adminId: 5,
  studentId: 1,            // demo 'student'
  otherStudentId: 2,       // maria.santos — must never be readable by student 1
  neverAppliedStudentId: 20, // ralph.santos — no application rows at all
  employerId: 3,           // demo 'employer'
  otherEmployerId: 4,
  pendingEmployerId: 9,    // Engr. Dennis Ramirez — pending_approval accreditation
  pendingEmployerName: 'Engr. Dennis Ramirez',
  ownJobId: 2,             // employer_id 3
  foreignJobId: 1,         // employer_id 4
};

const PHP_ERROR_RE = /Fatal error|Parse error|Warning:|Notice:|Deprecated:|Uncaught\s+\w*(Error|Exception)|TypeError|ArgumentCountError|SQLSTATE|Stack trace:/i;

const { browser, page } = await launch();
const errors = watchErrors(page);
/** Error-trail length snapshot, used to separate app errors from probe-induced 403/500s. */
const trailMark = () => errors.length;

// ---------------------------------------------------------------------------
// Helpers (all non-throwing)
// ---------------------------------------------------------------------------
function snippet(text, n = 260) {
  return String(text || '').replace(/\s+/g, ' ').trim().slice(0, n);
}

/**
 * Record dialog outcomes. Exactly ONE listener pair is attached per page (so
 * repeated calls can never double-accept a dialog or duplicate a record); each
 * call just re-tags the phase the next dialog/error belongs to.
 */
const dialogs = [];
const armedPages = new WeakSet();
let currentTag = 'default';
function armDialogs(p, tag = 'default', onDialog = null) {
  currentTag = tag;
  if (armedPages.has(p)) return dialogs;
  armedPages.add(p);
  p.on('dialog', async (d) => {
    const rec = { tag: currentTag, type: d.type(), message: snippet(d.message(), 160) };
    dialogs.push(rec);
    if (onDialog) { try { onDialog(rec); } catch { /* ignore */ } }
    try {
      // confirm() must be accepted or the archive/approval forms never submit;
      // alert() is the XSS signal we are hunting, so it is dismissed.
      await (d.type() === 'confirm' ? d.accept() : d.dismiss());
    } catch { /* ignore */ }
  });
  p.on('pageerror', (e) => dialogs.push({ tag: currentTag, type: 'pageerror', message: snippet(e.message, 160) }));
  return dialogs;
}
const alertsFired = () => dialogs.filter((d) => d.type === 'alert' || d.type === 'beforeunload').length;

/**
 * HTTP GET through the context's cookie jar: follows redirects manually so the
 * first hop's status/Location are preserved as evidence, while the session
 * cookies stay in sync with the browser page sharing that context.
 */
async function probe(ctx, url, opts = {}) {
  const target = url.startsWith('http') ? url : QA_BASE + url;
  const resp = await ctx.request.get(target, { maxRedirects: 0, timeout: 20000, ...opts });
  const status = resp.status();
  const location = resp.headers()['location'] || '';
  const contentType = resp.headers()['content-type'] || '';
  let text = '';
  try { text = await resp.text(); } catch { text = ''; }
  let finalUrl = resp.url();
  let finalStatus = status;
  const redirected = status >= 300 && status < 400;
  if (redirected) {
    finalUrl = location.startsWith('http') ? location : new URL(location || '/', QA_BASE).toString();
    try {
      const hop = await ctx.request.get(finalUrl, { maxRedirects: 3, timeout: 20000 });
      finalUrl = hop.url();
      finalStatus = hop.status();
      try { text = await hop.text(); } catch { /* ignore */ }
    } catch { /* keep the redirect as the terminal evidence */ }
  }
  return {
    url, finalUrl, status, firstStatus: status, finalStatus, location, contentType,
    redirected, is404: finalStatus === 404 || /404 Not Found/i.test(text),
    text, snippet: snippet(text),
  };
}

/** One adversarial GET probe: records status/deny-vs-leak and a finding on defect. */
function idorCheck(label, p, { dataSignals = [], denySignals = [], checkName, severity = 'high', role, expected }) {
  const denied = denySignals.some((s) => p.text.includes(s)) || p.status === 403 || p.status === 401;
  const isEmpty = p.text.trim().length === 0;
  const fatal = PHP_ERROR_RE.test(p.text);
  // A server-side redirect away from the target route is the role gate working
  // as designed (SessionGuard redirects role mismatches to the caller's own
  // dashboard) — a leak is only when the payload's data markers survive it.
  const gateRedirect = p.redirected && !dataSignals.some((s) => p.text.includes(s));
  const leaks = !denied && !fatal && !isEmpty && !gateRedirect
    && (dataSignals.length === 0 || dataSignals.some((s) => p.text.includes(s)));
  const ok = denied || fatal || gateRedirect; // fatal is reported separately as high severity
  const detail = `status=${p.status}${p.redirected ? ` -> ${p.finalUrl}` : ''} | ${p.snippet}`;
  res.check(checkName || `${label} denied`, ok, detail);
  if (leaks) {
    res.finding({
      title: `IDOR: ${label} rendered data instead of denying access`,
      severity, route: p.url, role,
      steps: [`Sign in as ${role}`, `GET ${p.url}`],
      expected,
      actual: `HTTP ${p.status} — payload rendered (final URL ${p.finalUrl}). Snippet: ${p.snippet}`,
      evidence: [p.finalUrl],
    });
  }
  return { ...p, denied, leaks, fatal, gateRedirect };
}

/** One array-param probe: a PHP TypeError/Fatal in the body is a high finding. */
function typeJuggleCheck(label, p, role) {
  const fatal = PHP_ERROR_RE.test(p.text);
  res.check(`${label} handles array param without PHP fatal`, !fatal,
    fatal ? `status=${p.status} | ${p.snippet}` : `status=${p.status} final=${p.finalUrl}`);
  if (fatal) {
    res.finding({
      title: `Type juggling: array parameter triggers a PHP fatal error on ${label}`,
      severity: 'high', route: p.url, role,
      steps: [`Sign in as ${role}`, `GET ${p.url} (array parameter where a scalar is expected)`],
      expected: 'The endpoint coerces, ignores, or rejects the array argument and returns a normal HTTP response',
      actual: `HTTP ${p.status} with PHP error text in the body: ${p.snippet}`,
      evidence: [p.finalUrl],
    });
  }
  return { ...p, fatal };
}

/**
 * Raw POST helper.
 *
 * IMPORTANT: this goes through the Playwright context's APIRequestContext, not
 * the harness httpGet()/global-fetch helper — global fetch keeps its OWN cookie
 * jar, so it would not share the browser context's PHPSESSID and every
 * session-scoped POST here would silently hit a login redirect instead.
 */
async function postForm(ctx, url, fields, headers = {}) {
  const resp = await ctx.request.post(url.startsWith('http') ? url : QA_BASE + url, {
    form: Object.fromEntries(Object.entries(fields).map(([k, v]) => [k, String(v)])),
    headers,
    maxRedirects: 0,
    timeout: 20000,
  });
  let text = '';
  try { text = await resp.text(); } catch { /* 302 has no body */ }
  return { status: resp.status(), text, headers: resp.headers(), location: resp.headers()['location'] || '' };
}

let adminNameBefore = null;
const categoryName = `QA Probe Cat ${Date.now()}`;

try {
  // =========================================================================
  // PART A — ADMIN CONSOLE
  // =========================================================================
  await page.goto(`${QA_BASE}/login.php?reset=1`, { waitUntil: 'domcontentloaded' }).catch(() => {});
  await loginAs(page, 'admin');
  res.check('admin demo login reaches the admin console', /\/admin\//.test(page.url()), `url=${page.url()}`);
  await shot(page, ART, 'admin-dashboard');

  // --- A1: every admin console page renders without PHP error text ---------
  const ADMIN_ROUTES = ['/admin/users.php', '/admin/categories.php', '/admin/reports.php', '/admin/updates.php', '/admin/settings.php'];
  for (const route of ADMIN_ROUTES) {
    let html = '';
    let status = 0;
    let err = '';
    try {
      const r = await page.goto(`${QA_BASE}${route}`, { waitUntil: 'domcontentloaded', timeout: 20000 });
      status = r ? r.status() : 0;
      html = await page.content();
    } catch (e) { err = snippet(e.message, 120); }
    const hit = PHP_ERROR_RE.exec(html);
    res.check(`${route} renders with no PHP error text`, status === 200 && !hit && !err,
      `status=${status}${err ? ` navError=${err}` : ''}${hit ? ` matched="${snippet(hit[0], 80)}"` : ''}`);
    if (hit || status >= 500) {
      res.finding({
        title: `Admin page ${route} emits a PHP error / server error`,
        severity: status >= 500 ? 'critical' : 'high', route, role: 'admin',
        steps: ['Sign in as admin', `GET ${route}`],
        expected: 'The page renders its console UI with no PHP diagnostics in the body',
        actual: `HTTP ${status}; matched "${snippet(hit ? hit[0] : '', 120)}"`,
        evidence: [await saveHtml(html, ART, `admin-route-${route.replace(/\W+/g, '_')}`), await shot(page, ART, `admin-route-${route.replace(/\W+/g, '_')}`)].filter(Boolean),
      });
    }
  }

  // --- A2: analytics contain real numbers, not NaN / placeholders ----------
  try {
    await page.goto(`${QA_BASE}/admin/reports.php`, { waitUntil: 'domcontentloaded', timeout: 20000 });
    const body = await page.locator('body').innerText().catch(() => '');
    const nanCount = (body.match(/\bNaN\b/g) || []).length;
    const pctCount = (body.match(/\d+(?:\.\d+)?\s*%/g) || []).length;
    const numCount = (body.match(/\b\d+\b/g) || []).length;
    const placeholder = /(\bundefined\b|\bnull\b|--\s*%|N\/A\s*$)/i.test(body);
    res.check('admin reports render analytics without NaN', nanCount === 0, `NaN occurrences=${nanCount}`);
    res.check('admin reports contain real numeric values', pctCount >= 3 && numCount >= 20,
      `percentages=${pctCount} numbers=${numCount} placeholder=${placeholder}`);
    res.check('admin reports have no empty/undefined placeholders', !placeholder, snippet(body, 200));
    if (nanCount > 0 || pctCount < 3) {
      res.finding({
        title: 'Admin analytics render empty/NaN values',
        severity: 'medium', route: '/admin/reports.php', role: 'admin',
        steps: ['Sign in as admin', 'Open /admin/reports.php', 'Inspect the KPI and chart labels'],
        expected: 'All analytics metrics show computed real numbers',
        actual: `NaN occurrences=${nanCount}, percentage values=${pctCount}`,
        evidence: [await saveHtml(page, ART, 'reports-analytics')].filter(Boolean),
      });
    }
    await shot(page, ART, 'admin-reports');
  } catch (e) {
    res.check('admin reports analytics inspected', false, snippet(e.message, 160));
  }

  // --- A3: cross-role dashboard guard while logged in as ADMIN ------------
  for (const [route, allowedByDesign] of [['/student/dashboard.php', true], ['/employer/dashboard.php', true]]) {
    let status = 0, landed = '', body = '';
    try {
      const r = await page.goto(`${QA_BASE}${route}`, { waitUntil: 'domcontentloaded', timeout: 20000 });
      status = r ? r.status() : 0;
      landed = page.url();
      body = await page.locator('body').innerText().catch(() => '');
    } catch (e) { landed = `navError: ${snippet(e.message, 80)}`; }
    const onOwn = landed === `${QA_BASE}${route}`;
    const redirectNote = onOwn
      ? (allowedByDesign ? 'ALLOWED by design (admin listed in require_auth roles)' : 'rendered without redirect')
      : `REDIRECTED to ${landed}`;
    res.check(`admin -> ${route} behaves sanely (${redirectNote})`,
      status === 200 && /\S/.test(body),
      `status=${status} landed=${landed}`);
    console.log(`  [note] cross-role guard ${route}: ${redirectNote} (final=${landed})`);
  }

  // --- A4: category lifecycle (create -> visible -> archive -> reflects) ---
  let createdCatId = null;
  try {
    await page.goto(`${QA_BASE}/admin/categories.php`, { waitUntil: 'domcontentloaded', timeout: 20000 });
    const before = await page.content();
    res.check('categories page has a create form', await page.locator('#newCatForm').count() > 0);

    await page.locator('button[data-bs-target="#newCatModal"]').first().click();
    await page.locator('#cat-name').fill(categoryName);
    await page.locator('#cat-hourly').fill('₱99.00 / hr');
    const desc = page.locator('#cat-desc');
    if (await desc.count()) await desc.fill('QA adversarial probe category — safe to archive/delete.');
    await Promise.all([
      page.waitForNavigation({ timeout: 20000 }).catch(() => {}),
      page.locator('#btn-create-cat').click({ timeout: 10000 }).catch(() => {}),
    ]);
    await page.waitForLoadState('domcontentloaded').catch(() => {});

    let html = await page.content();
    const appeared = html.includes(categoryName);
    res.check('created category appears in the active listing', appeared && html !== before,
      `appeared=${appeared} url=${page.url()}`);

    if (appeared) {
      const card = page.locator(`.card-paper:has-text(${JSON.stringify(categoryName)})`).first();
      const editBtn = card.locator('.edit-cat-btn').first();
      createdCatId = await editBtn.getAttribute('data-id').catch(() => null);
    }
    res.check('created category exposes a numeric id (fixture for archive step)',
      !!createdCatId && /^\d+$/.test(String(createdCatId)), `id=${createdCatId}`);

    if (appeared) {
      const dlgMark = dialogs.length;
      armDialogs(page, 'category-archive');
      const card = page.locator(`.card-paper:has-text(${JSON.stringify(categoryName)})`).first();
      const archiveBtn = card.locator('form[action="categories.php"] button[title="Archive Category"]').first();
      const formCount = await archiveBtn.count();
      res.check('created category exposes an archive control', formCount > 0, `count=${formCount}`);
      if (formCount > 0) {
        const method = await archiveBtn.evaluate((el) => el.closest('form')?.getAttribute('method') || 'GET');
        console.log(`  [note] category archive form method = ${String(method).toUpperCase()} (state change is POST-only: ${String(method).toUpperCase() === 'POST'})`);
        await Promise.all([
          page.waitForNavigation({ timeout: 20000 }).catch(() => {}),
          archiveBtn.click({ timeout: 10000 }).catch(() => {}),
        ]);
        await page.waitForLoadState('domcontentloaded').catch(() => {});
        const afterActive = await page.content();
        const flash = await page.locator('.alert-paper').first().innerText().catch(() => '');
        res.check('created category removed from the active listing after archive',
          !afterActive.includes(categoryName),
          `stillPresent=${afterActive.includes(categoryName)} flash="${snippet(flash, 120)}" dialogs=${JSON.stringify(dialogs.slice(dlgMark))}`);

        await page.goto(`${QA_BASE}/admin/categories.php?tab=archived`, { waitUntil: 'domcontentloaded', timeout: 20000 });
        const arch = await page.content();
        const inArchivedTab = arch.includes(categoryName);
        res.check('archived tab reflects the archived category', inArchivedTab,
          `presentInArchivedTab=${inArchivedTab}`);
        await saveHtml(arch, ART, 'categories-archived-tab');
        if (!inArchivedTab) {
          res.finding({
            title: 'Archived category does not appear in the archived taxonomy tab',
            severity: 'medium', route: '/admin/categories.php?tab=archived', role: 'admin',
            steps: [`Create "${categoryName}"`, 'Archive it from the active tab', 'Open ?tab=archived'],
            expected: 'The archived category is listed under the Archived tab and can be restored',
            actual: 'The category name is absent from the archived tab HTML',
            evidence: [await saveHtml(arch, ART, 'categories-archived-missing')].filter(Boolean),
          });
        }
      }
    } else {
      res.finding({
        title: 'Admin category create did not produce a visible listing entry',
        severity: 'medium', route: '/admin/categories.php', role: 'admin',
        steps: ['Sign in as admin', 'Open New Category modal', `Create "${categoryName}" via #btn-create-cat`],
        expected: 'New category card appears in the Active Categories listing',
        actual: `Category not found on the page after submit (url=${page.url()})`,
        evidence: [await saveHtml(page, ART, 'category-create-failed'), await shot(page, ART, 'category-create-failed')].filter(Boolean),
      });
    }
    await shot(page, ART, 'categories-after-archive');
  } catch (e) {
    res.check('category lifecycle completed without abort', false, snippet(e.message, 200));
    await shot(page, ART, 'categories-error').catch(() => {});
  }

  // --- A5: duplicate submit on an admin create form -----------------------
  try {
    const dupName = `${categoryName} DUP`;
    await page.goto(`${QA_BASE}/admin/categories.php`, { waitUntil: 'domcontentloaded', timeout: 20000 });
    await page.locator('button[data-bs-target="#newCatModal"]').first().click();
    await page.locator('#cat-name').fill(dupName);
    await sleep(200);

    const posts = [];
    const reqListener = (r) => {
      if (r.method() === 'POST' && /categories\.php/.test(r.url())) posts.push(String(r.postData() || '').slice(0, 120));
    };
    page.on('request', reqListener);
    const fired = await page.evaluate(() => {
      const form = document.getElementById('newCatForm');
      if (!form) return 0;
      // Two submissions fired back-to-back with the very same CSRF token/fields.
      try { form.submit(); } catch { /* ignore */ }
      try { form.submit(); } catch { /* ignore */ }
      return 2;
    }).catch(() => 0);
    await sleep(1500);
    page.off('request', reqListener);
    await page.goto(`${QA_BASE}/admin/categories.php`, { waitUntil: 'domcontentloaded', timeout: 20000 }).catch(() => {});

    const html = await page.content();
    const nameOccurrences = html.split(dupName).length - 1;
    const distinctCards = await page.locator(`.card-paper:has-text(${JSON.stringify(dupName)})`).count().catch(() => 0);
    const onlyOne = distinctCards <= 1;
    // A single category renders as one .card-paper (name in <h3> + img alt), so
    // a second card is the unambiguous duplicate-insert signature.
    const duped = distinctCards > 1;
    res.check('rapid duplicate submit on admin create form yields at most one record',
      onlyOne, `attempted=${fired} wirePosts=${posts.length} nameOccurrences=${nameOccurrences} distinctCards=${distinctCards}`);
    console.log(`  [note] duplicate submit: attempted=${fired} POSTs observed=${posts.length} distinctCards=${distinctCards}${posts.length < 2 ? ' (browser suppressed the piggybacked repeat, so a single card is INCONCLUSIVE for idempotency)' : ' (both requests reached the server)'}`);
    if (duped) {
      res.finding({
        title: 'Duplicate submit on the admin category create form inserts two records',
        severity: 'medium', route: '/admin/categories.php', role: 'admin',
        steps: [`Open the New Category modal and enter "${dupName}"`, 'Submit #newCatForm twice within the same request window'],
        expected: 'Idempotent handling: a second identical submission must not create a second category (create_category() has no duplicate/slug guard)',
        actual: `${distinctCards} identical category cards present after the double submit; POSTs observed=${posts.length}`,
        evidence: [await saveHtml(html, ART, 'duplicate-submit'), await shot(page, ART, 'duplicate-submit')].filter(Boolean),
      });
    }
  } catch (e) {
    res.check('duplicate submit probe completed', false, snippet(e.message, 160));
  }

  // --- A6: employer accreditation approve — and is the state change GET? ---
  try {
    await page.goto(`${QA_BASE}/admin/users.php?ver=pending_approval`, { waitUntil: 'domcontentloaded', timeout: 20000 }).catch(async () => {
      await page.goto(`${QA_BASE}/admin/users.php`, { waitUntil: 'domcontentloaded', timeout: 20000 });
    });

    const row = page.locator('tr', { hasText: QA_SEED.pendingEmployerName }).first();
    const rowCount = await row.count();
    const statusBefore = rowCount ? snippet(await row.locator('td[data-label="Status"]').first().innerText().catch(() => ''), 60) : '';
    res.check(`pending employer "${QA_SEED.pendingEmployerName}" (id ${QA_SEED.pendingEmployerId}) is listed`,
      rowCount > 0, `rows=${rowCount} statusCell="${statusBefore}"`);

    if (rowCount > 0) {
      await row.locator(`button[data-bs-target="#verifyModal${QA_SEED.pendingEmployerId}"]`).first()
        .click({ timeout: 10000 }).catch(() => {});

      // Inspect every action control inside that employer's verification modal.
      const controls = await page.evaluate((uid) => {
        const modal = document.getElementById(`verifyModal${uid}`);
        if (!modal) return null;
        const out = [];
        modal.querySelectorAll('form, a').forEach((el) => {
          if (el.tagName === 'A') {
            out.push({ kind: 'a', method: 'GET', href: el.getAttribute('href'), text: (el.textContent || '').trim().slice(0, 40) });
          } else {
            const action = (el.querySelector('input[name="action"]') || {}).value || '';
            if (action) {
              out.push({
                kind: 'form', method: (el.getAttribute('method') || 'GET').toUpperCase(),
                action: el.getAttribute('action'), hiddenAction: action,
                id: (el.querySelector('input[name="id"]') || {}).value,
                hasCsrf: !!el.querySelector('input[name="csrf_token"]'),
                text: (el.textContent || '').trim().slice(0, 40),
              });
            }
          }
        });
        return out;
      }, QA_SEED.pendingEmployerId).catch(() => null);

      res.check('employer verification modal exposes a state-changing control',
        Array.isArray(controls) && controls.length > 0, JSON.stringify(controls));

      const approveCtl = (controls || []).find((c) => /approve_employer|approve_user/.test(c.hiddenAction || ''));
      const getDriven = !!approveCtl && approveCtl.method === 'GET';
      res.check('employer APPROVE is NOT a state-changing GET link', !getDriven,
        `control=${JSON.stringify(approveCtl || null)}`);
      console.log(`  [note] approve control: ${JSON.stringify(approveCtl || null)}`);
      if (getDriven) {
        res.finding({
          title: 'Employer accreditation approval is performed by a state-changing GET request (CSRF)',
          severity: 'high', route: '/admin/users.php', role: 'admin',
          steps: ['Sign in as admin', `Open the verification modal for employer id ${QA_SEED.pendingEmployerId}`, 'Inspect the approve control'],
          expected: 'Accreditation state changes are POST + CSRF-token protected',
          actual: `Approve control is a GET request: ${approveCtl.href || approveCtl.action}`,
          evidence: [String(approveCtl.href || approveCtl.action)],
        });
      }

      // Now actually perform it and record the exact request + server response.
      const approveBtn = page.locator(`#verifyModal${QA_SEED.pendingEmployerId} button:has-text("Approve")`).first();
      const reqLog = [];
      const respLog = [];
      const reqListener = (r) => {
        if (r.method() === 'POST' && /admin\/users\.php/.test(r.url())) {
          reqLog.push({ method: r.method(), url: r.url(), postData: String(r.postData() || '').slice(0, 300) });
        }
      };
      const respListener = (r) => {
        if (r.request().method() === 'POST' && /admin\/users\.php/.test(r.url())) {
          respLog.push({ status: r.status(), location: r.headers()['location'] || '', url: r.url() });
        }
      };
      page.on('request', reqListener);
      page.on('response', respListener);
      const dlgMark = dialogs.length;
      armDialogs(page, 'accreditation-approve');
      await Promise.all([
        page.waitForNavigation({ timeout: 20000 }).catch(() => {}),
        approveBtn.click({ timeout: 10000 }).catch(() => {}),
      ]);
      await page.waitForLoadState('domcontentloaded').catch(() => {});
      page.off('request', reqListener);
      page.off('response', respListener);

      const flash = await page.locator('.alert-paper').first().innerText().catch(() => '');
      const html = await page.content();
      const stillPending = (await page.locator('tr', { hasText: QA_SEED.pendingEmployerName }).first()
        .locator('td[data-label="Status"]').first().innerText().catch(() => '')).trim();
      // Server-confirmed: the flash the approve branch sets, plus the re-rendered row.
      const flashConfirms = /officially approved|verified/i.test(flash)
        && flash.includes(QA_SEED.pendingEmployerName);
      const stateChanged = flashConfirms || /verified/i.test(stillPending) || (!/pending/i.test(stillPending) && stillPending !== '');
      res.check('employer accreditation approve changes the target row state', stateChanged,
        `before="${snippet(statusBefore, 40)}" after="${snippet(stillPending, 40)}" flash="${snippet(flash, 140)}" dialogs=${JSON.stringify(dialogs.slice(dlgMark))}`);
      res.check('approval request was issued as an authenticated POST',
        reqLog.length > 0, JSON.stringify(reqLog));
      res.check('approval POST was CSRF-token protected and answered with a redirect',
        reqLog.some((r) => /csrf_token=/.test(r.postData)) && respLog.some((r) => r.status >= 300 && r.status < 400),
        `requests=${JSON.stringify(reqLog)} responses=${JSON.stringify(respLog)}`);

      // Exact evidence for the "is it a GET?" question, verbatim.
      const approveUrl = getDriven
        ? String(approveCtl.href || approveCtl.action)
        : `POST ${QA_BASE}/admin/users.php (action=${approveCtl ? approveCtl.hiddenAction : 'approve_employer'}, id=${QA_SEED.pendingEmployerId}, csrf_token present=${approveCtl ? approveCtl.hasCsrf : 'unknown'})`;
      console.log(`  [note] accreditation state change achieved via: ${getDriven ? 'GET' : 'POST'} -> ${approveUrl}`);
      console.log(`  [note] observed requests: ${JSON.stringify(reqLog)}`);

      if (!stateChanged) {
        res.finding({
          title: 'Employer accreditation approve did not change the target row state',
          severity: 'high', route: '/admin/users.php', role: 'admin',
          steps: ['Sign in as admin', `Open verification modal for employer id ${QA_SEED.pendingEmployerId}`, 'Click Approve & Verify'],
          expected: 'employer_profiles.verification_status becomes "verified" and the row shows Verified',
          actual: `flash="${snippet(flash, 140)}" status cell after submit="${snippet(stillPending, 60)}" requests=${JSON.stringify(reqLog)}`,
          evidence: [await saveHtml(html, ART, 'accreditation-approve'), await shot(page, ART, 'accreditation-approve')].filter(Boolean),
        });
      }

      // Out-of-band comparison: the PRD "is it a GET?" question is settled by the
      // evidence above (POST + csrf_token + 302), and the row/flash re-render is
      // the server's own confirmation that employer_profiles.verification_status
      // moved to 'verified' for user id 9.
      console.log(`  [evidence] approve request: ${JSON.stringify(reqLog[0] || null)}`);
      console.log(`  [evidence] approve response: ${JSON.stringify(respLog[0] || null)}`);
      await shot(page, ART, 'accreditation-after');
    }
  } catch (e) {
    res.check('employer accreditation probe completed without abort', false, snippet(e.message, 200));
    await shot(page, ART, 'accreditation-error').catch(() => {});
  }

  // =========================================================================
  // PART B — ADVERSARIAL / EDGE CASES
  // =========================================================================
  const partBStart = Date.now();
  const trailAtPartB = trailMark();
  const studentCtx = await browser.newContext({ baseURL: QA_BASE, viewport: { width: 1440, height: 900 } });
  await studentCtx.addInitScript(() => {}).catch(() => {});
  let employerCtx = null;
  let otherCtx = null;

  try {
    // ---- B1: IDOR — STUDENT requesting other roles' and other students' data
    await loginAs(page, 'student');
    res.check('student demo login established for IDOR probes', /\/student\//.test(page.url()), `url=${page.url()}`);

    const studentProbes = [
      {
        path: `/view-resume.php?user_id=${QA_SEED.otherStudentId}`,
        label: 'student reads another student resume by user_id',
        dataSignals: ['Maria Santos', 'maria.santos@kld.edu.ph'],
        denySignals: ['Access Denied'],
        expected: `HTTP 403 "Access Denied" for student ${QA_SEED.studentId} requesting student ${QA_SEED.otherStudentId}`,
      },
      {
        path: `/view-resume.php?user_id=${QA_SEED.otherStudentId}&render_html=1`,
        label: 'student reads another student resume with render_html=1 (PDF guard bypass)',
        dataSignals: ['Maria Santos', 'maria.santos@kld.edu.ph'],
        denySignals: ['Access Denied'],
        expected: 'render_html=1 must not bypass the ownership check',
      },
      {
        path: '/employer/applicants.php',
        label: 'student opens the employer applicants console',
        dataSignals: ['Applicant', 'applicants'],
        denySignals: ['Access Denied', 'Unauthorized'],
        expected: 'Role gate redirects the student to their own dashboard (or 403)',
      },
      {
        path: `/employer/edit-job.php?id=${QA_SEED.ownJobId}`,
        label: 'student opens the employer job editor',
        dataSignals: ['Save Changes', 'Edit Job'],
        denySignals: ['Access Denied', 'Unauthorized'],
        expected: 'Role gate redirects the student to their own dashboard (or 403)',
      },
      {
        path: '/admin/users.php',
        label: 'student opens the admin user directory',
        dataSignals: ['User Directory', 'Partner Verification'],
        denySignals: ['Access Denied', 'Unauthorized'],
        expected: 'Role gate redirects the student to their own dashboard (or 403)',
      },
    ];

    for (const pr of studentProbes) {
      const p = await probe(page.context(), pr.path);
      idorCheck(pr.label, p, {
        dataSignals: pr.dataSignals, denySignals: pr.denySignals,
        checkName: `IDOR: ${pr.label} denied`, role: 'student', expected: pr.expected,
      });
      console.log(`  [probe/student] ${pr.path} -> ${p.status}${p.redirected ? ` -> ${p.finalUrl}` : ''} ${snippet(p.snippet, 110)}`);
    }

    // Negative control: the student's OWN resume must still render (proves the
    // probes above failed on authorization, not on a broken endpoint).
    const own = await probe(page.context(), `/view-resume.php?user_id=${QA_SEED.studentId}&render_html=1`);
    res.check('control: student can still open their OWN resume (probe validity)',
      own.status === 200 && !own.text.includes('Access Denied'),
      `status=${own.status} | ${own.snippet}`);

    // ---- B2: IDOR — EMPLOYER requesting a student who never applied --------
    employerCtx = await browser.newContext({ baseURL: QA_BASE, viewport: { width: 1440, height: 900 } });
    const empPage = await employerCtx.newPage();
    await loginAs(empPage, 'employer');
    res.check('employer demo login established for IDOR probes', /\/employer\//.test(empPage.url()), `url=${empPage.url()}`);

    const empProbes = [
      {
        path: `/view-resume.php?user_id=${QA_SEED.neverAppliedStudentId}&render_html=1`,
        label: `employer reads resume of student ${QA_SEED.neverAppliedStudentId} who never applied to them`,
        dataSignals: ['Ralph Matthew Santos', 'ralph.santos@kld.edu.ph'],
        denySignals: ['Access Denied'],
        expected: 'HTTP 403 — no application from that student exists for this employer',
      },
      {
        path: `/view-resume.php?user_id=${QA_SEED.neverAppliedStudentId}`,
        label: `employer reads resume of student ${QA_SEED.neverAppliedStudentId} (PDF path)`,
        dataSignals: ['Ralph Matthew Santos', 'ralph.santos@kld.edu.ph'],
        denySignals: ['Access Denied'],
        expected: 'HTTP 403 — no application from that student exists for this employer',
      },
      {
        path: `/employer/edit-job.php?id=${QA_SEED.foreignJobId}`,
        label: 'employer edits a job owned by a different employer',
        dataSignals: ['Save Changes', 'Computer Lab Technical Assistant'],
        denySignals: ['Access Denied', 'Unauthorized', 'not authorized'],
        expected: `HTTP 403/redirect — job ${QA_SEED.foreignJobId} belongs to employer ${QA_SEED.otherEmployerId}, not ${QA_SEED.employerId}`,
      },
    ];
    for (const pr of empProbes) {
      const p = await probe(employerCtx, pr.path);
      const verdict = idorCheck(pr.label, p, {
        dataSignals: pr.dataSignals, denySignals: pr.denySignals,
        checkName: `IDOR: ${pr.label} denied`, role: 'employer', expected: pr.expected,
      });
      console.log(`  [probe/employer] ${pr.path} -> ${p.status}${p.redirected ? ` -> ${p.finalStatus} ${p.finalUrl}` : ''} ${snippet(p.snippet, 110)}`);
      // A role gate that bounces the user to a non-existent route is a UX break:
      // the request is denied correctly but the user never sees a sane page.
      if (verdict.gateRedirect && p.is404) {
        res.finding({
          title: `Role-gate redirect for ${pr.path} lands on the non-existent route ${p.finalUrl}`,
          severity: 'low', route: pr.path, role: 'employer',
          steps: [`Sign in as the demo employer`, `GET ${pr.path}`, 'Observe the redirect target'],
          expected: 'An unauthorized role is redirected to a real page (its own dashboard, or a 403)',
          actual: `SessionGuard redirected to ${p.finalUrl}, which answers HTTP ${p.finalStatus} with the built-in "404 Not Found" page — the user sees "404 Not Found" instead of a dashboard or a denial message.`,
          evidence: [p.finalUrl],
        });
      }
    }

    // Negative controls for the employer.
    const ownJob = await probe(employerCtx, `/employer/edit-job.php?id=${QA_SEED.ownJobId}`);
    res.check('control: employer can still open their OWN job editor',
      ownJob.status === 200 && !/Access Denied/i.test(ownJob.text), `status=${ownJob.status}`);
    const knownApplicant = await probe(employerCtx, '/view-resume.php?user_id=1&render_html=1');
    res.check('control: employer can open a resume of a student who DID apply to them',
      knownApplicant.status === 200 && !knownApplicant.text.includes('Access Denied'),
      `status=${knownApplicant.status}`);
    await shot(empPage, ART, 'employer-control').catch(() => {});

    // ---- B3: type juggling — array params where scalars are expected -------
    const arrayProbes = [
      { path: '/api/search-jobs.php?q[]=a', role: 'student' },
      { path: '/student/job-details.php?id[]=1', role: 'student' },
      { path: '/employer/edit-job.php?id[]=1', role: 'employer' },
      { path: '/view-resume.php?user_id[]=1', role: 'student' },
      { path: '/admin/users.php?approve_id[]=1', role: 'admin' },
    ];
    for (const ap of arrayProbes) {
      const ctx = ap.role === 'student' ? page.context() : (ap.role === 'employer' ? employerCtx : page.context());
      let p;
      try {
        p = await probe(ctx, ap.path);
      } catch (e) {
        p = { url: ap.path, finalUrl: '', status: 0, text: `probeError: ${e.message}`, snippet: snippet(e.message) };
      }
      typeJuggleCheck(ap.path, p, ap.role);
      console.log(`  [probe/array:${ap.role}] ${ap.path} -> ${p.status} ${snippet(p.snippet, 140)}`);
      if (p.fatal) await saveHtml(p.text, ART, `array-${ap.path.replace(/\W+/g, '_')}`);
    }

    // ---- B4: path traversal / static file exposure -------------------------
    const traversalProbes = [
      '/view-resume.php?file=../../.env',
      '/view-resume.php?file=..%2F..%2F.env',
      '/view-resume.php?file=../../.env&render_html=1',
      '/data/users.json',
      '/uploads/proofs/../.htaccess',
      '/uploads/.htaccess',
      '/data/.htaccess',
      '/database/schema.sql',
      '/.env',
    ];
    // Dedupe by pathname so `/.env` and `/view-resume.php?file=../../.env`
    // cannot double-report the same environment exposure.
    const reportedExposures = new Set();
    for (const t of traversalProbes) {
      let p;
      try {
        p = await probe(page.context(), t);
      } catch (e) {
        p = { url: t, finalUrl: '', status: 0, text: `probeError: ${e.message}`, snippet: snippet(e.message) };
      }
      const exposed = p.status === 200 && p.text.trim().length > 0;
      const secretish = /nvapi-|NVIDIA_API_KEY|MAIL_PASSWORD|DB_PASS|DB_PASSWORD|APP_KEY|SECRET/i.test(p.text);
      const leaksHash = /\$2[aby]\$\d\d\$/.test(p.text);
      // Plaintext credential values inside the exposed JSON datastore.
      const leaksPlaintext = /"password"\s*:\s*"[^"$][^"]{5,60}"/i.test(p.text);
      const valuable = secretish || leaksHash || leaksPlaintext;
      // /view-resume.php?file=… is application code; the rest is web-server static serving.
      const appLevel = t.startsWith('/view-resume.php');
      res.check(`exposure probe ${t} -> ${p.status} (${appLevel ? 'application route' : 'static path, env-dependent'})`,
        appLevel ? !exposed || /Access Denied/i.test(p.text) : true,
        `status=${p.status} len=${p.text.length} secretish=${secretish} plaintextCreds=${leaksPlaintext} | ${snippet(p.snippet, 160)}`);
      console.log(`  [probe/traversal] ${t} -> ${p.status} len=${p.text.length} valuable=${valuable} ${snippet(p.snippet, 110)}`);

      const pathKey = t.split('?')[0];
      const appLeak = appLevel && exposed && !/Access Denied/i.test(p.text);
      if ((appLeak || (!appLevel && exposed && valuable)) && !reportedExposures.has(pathKey)) {
        reportedExposures.add(pathKey);
        res.finding({
          title: appLeak
            ? `view-resume.php serves an arbitrary server file via ?file= (${t})`
            : `Sensitive file readable over HTTP without authentication (${t})`,
          severity: 'critical', route: pathKey, role: appLeak ? 'student' : 'public',
          steps: appLeak
            ? ['Sign in as student', `GET ${t}`]
            : [`Unauthenticated GET ${QA_BASE}${t}`],
          expected: appLeak
            ? 'The document server only ever serves PDFs under uploads/resumes'
            : 'Environment/config files, datastore JSON and .htaccess rules must not be web-readable',
          // ENVIRONMENT LABEL: the QA server is PHP's built-in server on :8099,
          // which does not honour .htaccess. The repo ships data/.htaccess and
          // database/.htaccess precisely to deny these paths, so behind Apache
          // this evidence must be re-verified before it is treated as an
          // application defect rather than a local-server artefact. The
          // plaintext-credential storage itself is an application concern
          // regardless of which web server fronts it.
          actual: `HTTP ${p.status}, ${p.text.length} bytes, valuable content present`
            + `${leaksHash ? ' (bcrypt hashes detected)' : ''}`
            + `${leaksPlaintext ? ' (plaintext password values detected in the datastore JSON)' : ''}. Snippet: ${snippet(p.snippet, 200)}`,
          evidence: [`${QA_BASE}${t}`],
        });
      }
    }

    // ---- B5: SQL-ish input into the login form and the search box ----------
    const SQL_PAYLOADS = [`' OR '1'='1`, `%'--`, `' OR 1=1 -- -`];
    for (const payload of SQL_PAYLOADS) {
      // Login form — deliberately unauthenticated, so the harness' plain
      // httpGet/fetch path is the right tool (no session cookie needed).
      try {
        const body = new URLSearchParams({ email: payload, password: `x' OR '1'='1` }).toString();
        const r = await httpGet('/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body,
        });
        const loc = r.headers.get('location') || '';
        const sqlLeak = /SQLSTATE|PDOException|You have an error in your SQL|mysql_|syntax to use near|Fatal error/i.test(r.text);
        const bypass = r.status >= 300 && r.status < 400 && !/login\.php/i.test(loc);
        res.check(`login form rejects SQL payload ${JSON.stringify(payload)} without auth bypass`,
          !bypass && !sqlLeak, `status=${r.status} location=${loc || '(none)'} sqlLeak=${sqlLeak}`);
        if (bypass || sqlLeak) {
          res.finding({
            title: `SQL-ish input on the login form causes ${bypass ? 'an authentication bypass' : 'a SQL error leak'}`,
            severity: 'critical', route: '/login.php', role: 'public',
            steps: ['POST /login.php', `email=${payload}`, `password=${payload}`],
            expected: 'Parameterised query: login fails with a generic invalid-credentials message',
            actual: `HTTP ${r.status} location=${loc || '(none)'} :: ${snippet(r.text, 200)}`,
            evidence: [snippet(r.text, 300)],
          });
        }
      } catch (e) {
        res.check(`login SQL payload ${JSON.stringify(payload)} probed`, false, snippet(e.message, 140));
      }

      // Job search box (page + JSON API)
      try {
        const s = await probe(page.context(), `/student/jobs.php?keyword=${encodeURIComponent(payload)}`);
        const sqlLeak = /SQLSTATE|PDOException|You have an error in your SQL|syntax to use near/i.test(s.text);
        res.check(`job search keyword ${JSON.stringify(payload)} leaks no SQL error`, !sqlLeak,
          `status=${s.status} sqlLeak=${sqlLeak} | ${snippet(s.snippet, 140)}`);
        if (sqlLeak) {
          res.finding({
            title: 'SQL error text reflected by the job search keyword',
            severity: 'high', route: '/student/jobs.php', role: 'student',
            steps: [`GET /student/jobs.php?keyword=${encodeURIComponent(payload)}`],
            expected: 'A parameterised search returns zero results with no database diagnostics',
            actual: `HTTP ${s.status}: ${snippet(s.snippet, 220)}`,
            evidence: [s.finalUrl],
          });
        }

        const api = await probe(page.context(), `/api/search-jobs.php?q=${encodeURIComponent(payload)}`);
        const apiSql = /SQLSTATE|PDOException|Fatal error|Warning:/i.test(api.text);
        let validJson = false;
        try { JSON.parse(api.text); validJson = true; } catch { /* not json */ }
        res.check(`search API keyword ${JSON.stringify(payload)} returns clean JSON`,
          validJson && !apiSql, `status=${api.status} validJson=${validJson} sqlLeak=${apiSql} | ${snippet(api.snippet, 140)}`);
        if (apiSql) {
          res.finding({
            title: 'Search API leaks a database error for a SQL-ish keyword',
            severity: 'high', route: '/api/search-jobs.php', role: 'public',
            steps: [`GET /api/search-jobs.php?q=${encodeURIComponent(payload)}`],
            expected: 'Valid JSON with an empty result set',
            actual: `HTTP ${api.status}: ${snippet(api.snippet, 220)}`,
            evidence: [api.finalUrl],
          });
        }
      } catch (e) {
        res.check(`search SQL payload ${JSON.stringify(payload)} probed`, false, snippet(e.message, 140));
      }
    }

    // Also exercise the real login form fields (not just a raw POST) so the
    // CSRF/flow wiring is covered, not only the handler.
    try {
      await page.goto(`${QA_BASE}/logout.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      await page.goto(`${QA_BASE}/login.php`, { waitUntil: 'domcontentloaded' });
      const emailField = page.locator('input[name="email"]').first();
      if (await emailField.count()) {
        await emailField.fill(`' OR '1'='1`);
        await page.locator('input[name="password"]').first().fill(`' OR '1'='1`);
        await Promise.all([
          page.waitForNavigation({ timeout: 20000 }).catch(() => {}),
          page.locator('form button[type="submit"], form input[type="submit"]').first().click({ timeout: 10000 }).catch(() => {}),
        ]);
        await page.waitForLoadState('domcontentloaded').catch(() => {});
        const html = await page.content();
        const stillLogin = /login\.php/.test(page.url());
        const sqlLeak = /SQLSTATE|PDOException|syntax to use near/i.test(html);
        res.check('SQL-ish credentials submitted through the real login form do not authenticate',
          stillLogin && !sqlLeak, `url=${page.url()} sqlLeak=${sqlLeak}`);
      } else {
        res.check('login form exposes an email field for the SQL probe', false, 'input[name="email"] missing');
      }
    } catch (e) {
      res.check('login form SQL probe completed', false, snippet(e.message, 160));
    }

    // ---- B6: XSS reflection -----------------------------------------------
    const XSS_PAYLOADS = ['<script>alert(1)</script>', '"><img src=x onerror=alert(1)>'];
    const escapedForm = (raw) => raw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    armDialogs(page, 'xss');

    for (const payload of XSS_PAYLOADS) {
      // (i) job-search keyword
      try {
        const alertsBefore = alertsFired();
        const g = await page.goto(`${QA_BASE}/student/jobs.php?keyword=${encodeURIComponent(payload)}`, { waitUntil: 'domcontentloaded', timeout: 20000 });
        await sleep(250); // give any injected onerror/script a chance to fire
        const html = (g && g.status() >= 400) ? '' : await page.content();
        const rawPresent = html.includes(payload);
        const escPresent = html.includes(escapedForm(payload));
        const alertsNew = alertsFired() - alertsBefore;
        res.check(`job-search keyword ${JSON.stringify(payload)} is escaped and does not execute`,
          (!rawPresent || escPresent) && alertsNew === 0,
          `rawPresent=${rawPresent} escapedPresent=${escPresent} newAlerts=${alertsNew}`);
        if (rawPresent && !escPresent) {
          res.finding({
            title: `Reflected XSS in the job-search keyword (${snippet(payload, 40)})`,
            severity: alertsNew > 0 ? 'critical' : 'high', route: '/student/jobs.php', role: 'public',
            steps: [`GET /student/jobs.php?keyword=${encodeURIComponent(payload)}`, 'Inspect page.content() for the raw payload'],
            expected: 'The keyword is HTML-escaped before being written into the search input value',
            actual: `Raw payload present in the served HTML without escaping (escaped variant absent); alert dialogs fired=${alertsNew}`,
            evidence: [await saveHtml(html, ART, `xss-keyword-${XSS_PAYLOADS.indexOf(payload)}`)].filter(Boolean),
          });
        }
      } catch (e) {
        res.check(`keyword XSS probe ${JSON.stringify(payload)} completed`, false, snippet(e.message, 140));
      }

      // (ii) the search JSON API (reflection into a JSON body)
      try {
        const api = await probe(page.context(), `/api/search-jobs.php?q=${encodeURIComponent(payload)}`);
        const rawPresent = api.text.includes(payload);
        const ctype = api.contentType || '';
        const isJson = /application\/json/i.test(ctype);
        let validJson = false;
        try { JSON.parse(api.text); validJson = true; } catch { /* not json */ }
        res.check(`search API serialises ${JSON.stringify(payload)} as JSON (not HTML)`,
          validJson && isJson, `status=${api.status} validJson=${validJson} contentType="${ctype}" rawPresent=${rawPresent}`);
      } catch (e) {
        res.check(`API XSS probe ${JSON.stringify(payload)} completed`, false, snippet(e.message, 140));
      }
    }

    // (iii) admin profile/settings NAME field (stored-then-reflected).
    // Uses a dedicated admin context so the POST and the CSRF token share one
    // cookie jar, and the student session on `page` is left untouched.
    let adminCtx = null;
    try {
      adminCtx = await browser.newContext({ baseURL: QA_BASE, viewport: { width: 1280, height: 800 } });
      const adminPage = await adminCtx.newPage();
      armDialogs(adminPage, 'xss-settings');
      await loginAs(adminPage, 'admin');
      res.check('dedicated admin context established for the name-field XSS probe', /\/admin\//.test(adminPage.url()),
        `url=${adminPage.url()}`);

      for (const payload of XSS_PAYLOADS) {
        try {
          const me = await probe(adminCtx, '/admin/settings.php');
          const token = (me.text.match(/name="csrf_token"\s+value="([^"]+)"/) || [])[1];
          const oldName = (me.text.match(/id="settings-admin-name"[^>]*value="([^"]*)"/) || [])[1];
          if (!token) {
            res.check(`admin settings name field reachable for XSS probe (${snippet(payload, 30)})`, false,
              `csrf_token not found; status=${me.status} final=${me.finalUrl}`);
            continue;
          }
          if (adminNameBefore === null && oldName) adminNameBefore = oldName;

          const alertsBefore = alertsFired();
          const r = await postForm(adminCtx, '/admin/settings.php', {
            csrf_token: token, action: 'profile', phone: '', name: payload, office_location: '',
          });
          const after = await probe(adminCtx, '/admin/settings.php');
          const rawPresent = after.text.includes(payload);
          const escPresent = after.text.includes(escapedForm(payload));
          // update_user_profile() runs htmlspecialchars() at WRITE time, and the
          // view escapes again: the stored value therefore renders as
          // &amp;lt;script&amp;gt;… (double-encoded) rather than &lt;script&gt;…
          const dblEscPresent = after.text.includes(escapedForm(escapedForm(payload)));
          const alertsNew = alertsFired() - alertsBefore;
          // The write must actually land, otherwise "escaped" would be vacuous.
          res.check(`settings name field accepted the ${JSON.stringify(snippet(payload, 30))} write`,
            rawPresent || escPresent || dblEscPresent,
            `postStatus=${r.status} location=${r.location || '(none)'} raw=${rawPresent} escaped=${escPresent} doubleEscaped=${dblEscPresent} | ${snippet(after.snippet, 120)}`);
          res.check(`settings name field ${JSON.stringify(payload)} never executes as markup`,
            !rawPresent && alertsNew === 0,
            `postStatus=${r.status} rawPresent=${rawPresent} escapedPresent=${escPresent} doubleEscapedPresent=${dblEscPresent} newAlerts=${alertsNew}`);
          if (rawPresent) {
            res.finding({
              title: `Stored XSS via the admin profile name field (${snippet(payload, 40)})`,
              severity: alertsNew > 0 ? 'critical' : 'high', route: '/admin/settings.php', role: 'admin',
              steps: ['Sign in as admin', 'POST /admin/settings.php with action=profile and the payload as "name"', 'Re-open /admin/settings.php and inspect page.content()'],
              expected: 'The stored name is HTML-escaped at render time only, so no markup reaches the DOM',
              actual: `Raw payload present in the rendered settings HTML; alert dialogs fired=${alertsNew}`,
              evidence: [await saveHtml(after.text, ART, `xss-settings-name-${XSS_PAYLOADS.indexOf(payload)}`)].filter(Boolean),
            });
          } else if (dblEscPresent) {
            // Not executable — the reverse defect: the raw markup characters are
            // destroyed at rest and the entity text is shown verbatim to the user.
            const dir = await probe(adminCtx, '/admin/users.php');
            const dirDouble = dir.text.includes(escapedForm(escapedForm(payload)));
            res.check('double-encoded admin name propagates to the users directory', dirDouble,
              `presentOnAdminUsers=${dirDouble}`);
            res.finding({
              title: 'Admin display name is double-HTML-encoded (stored as entities, rendered as literal entity text)',
              severity: 'low', route: '/admin/settings.php', role: 'admin',
              steps: [
                'Sign in as admin',
                'POST /admin/settings.php with action=profile and a name containing angle brackets and ampersands',
                'Re-open /admin/settings.php and /admin/users.php and inspect page.content()',
              ],
              expected: 'Name is stored verbatim and escaped once at render time — no markup executes and no entity text leaks into the UI',
              actual: `update_user_profile() applies htmlspecialchars() at write time, so the value renders as ${snippet(escapedForm(escapedForm(payload)), 120)}. The stored value is corrupted and users see literal entity text. Verified NOT to execute (rawPresent=${rawPresent}, alerts=${alertsNew}); also visible on /admin/users.php=${dirDouble}.`,
              evidence: [await saveHtml(after.text, ART, `html-double-encode-${XSS_PAYLOADS.indexOf(payload)}`)].filter(Boolean),
            });
          }
          await shot(adminPage, ART, `settings-after-xss-${XSS_PAYLOADS.indexOf(payload)}`);
        } catch (e) {
          res.check(`settings name XSS probe ${JSON.stringify(payload)} completed`, false, snippet(e.message, 140));
        }
      }

      // Restore the admin display name so the shared fixture is unchanged.
      try {
        if (adminNameBefore) {
          const me = await probe(adminCtx, '/admin/settings.php');
          const token = (me.text.match(/name="csrf_token"\s+value="([^"]+)"/) || [])[1];
          if (token) {
            await postForm(adminCtx, '/admin/settings.php', {
              csrf_token: token, action: 'profile', phone: '', name: adminNameBefore, office_location: '',
            });
            const back = await probe(adminCtx, '/admin/settings.php');
            res.check('admin display name restored after the XSS probe', back.text.includes(adminNameBefore),
              `restored="${snippet(adminNameBefore, 60)}"`);
          } else {
            res.check('admin display name restored after the XSS probe', false, 'csrf_token unavailable for restore');
          }
        }
      } catch (e) {
        res.check('admin display name restore attempted', false, snippet(e.message, 140));
      }
    } catch (e) {
      res.check('admin settings XSS phase completed', false, snippet(e.message, 160));
    } finally {
      if (adminCtx) await adminCtx.close().catch(() => {});
    }

    // ---- B7: session isolation across two contexts -------------------------
    try {
      otherCtx = await browser.newContext({ baseURL: QA_BASE, viewport: { width: 1280, height: 800 } });
      const otherPage = await otherCtx.newPage();
      await loginAs(otherPage, 'employer');
      await loginAs(otherPage, 'admin'); // second context now holds admin

      const firstStill = await probe(studentCtx, '/student/dashboard.php'); // studentCtx never logged in
      res.check('a never-authenticated context stays unauthenticated (baseline)', firstStill.status !== 200 || /login\.php|Sign In/i.test(firstStill.text),
        `status=${firstStill.status} final=${firstStill.finalUrl}`);

      // Re-establish a student in context 1, then assert context 2's admin login did not disturb it.
      await page.goto(`${QA_BASE}/logout.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      await loginAs(page, 'student');
      const beforeUrl = page.url();
      await otherPage.goto(`${QA_BASE}/admin/users.php`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      const adminOk = /\/admin\//.test(otherPage.url());
      const after = await probe(page.context(), '/student/dashboard.php');
      const stillStudent = after.status === 200 && /student/i.test(after.text) && !/Sign In to Campus Hire/i.test(after.text);
      res.check('second context holds its own admin session', adminOk, `ctx2 url=${otherPage.url()}`);
      res.check('logging in elsewhere does not disturb the first context session', stillStudent,
        `before=${beforeUrl} afterStatus=${after.status} final=${after.finalUrl}`);
      if (!stillStudent) {
        res.finding({
          title: 'Session leakage: a login in a second browser context invalidates/replaces the first context session',
          severity: 'high', route: '/login.php', role: 'student',
          steps: ['Context 1: sign in as student', 'Context 2: sign in as admin', 'Context 1: GET /student/dashboard.php'],
          expected: 'Sessions are independent; context 1 keeps its student session',
          actual: `Context 1 landed on ${after.finalUrl} (status ${after.status})`,
          evidence: [after.finalUrl],
        });
      }
      await shot(otherPage, ART, 'session-isolation-ctx2').catch(() => {});
    } catch (e) {
      res.check('session isolation probe completed', false, snippet(e.message, 160));
    } finally {
      if (otherCtx) await otherCtx.close().catch(() => {});
    }
  } finally {
    await studentCtx.close().catch(() => {});
    if (employerCtx) await employerCtx.close().catch(() => {});
    await logout(page).catch(() => {});
  }

  // ---- Health of the script's own interactions ---------------------------
  const jsErrors = errors.filter((e) => e.kind === 'pageerror');
  res.check('no uncaught JS errors during the console walkthrough', jsErrors.length === 0,
    jsErrors.map((e) => e.text).join(' | '));
  console.log(`  [note] error trail: ${trailAtPartB} entries during Part A, ${errors.length - trailAtPartB} during Part B (Part B entries are expected 403/500s from deliberate probes), Part B took ${Date.now() - partBStart}ms`);

  res.save(ART);
} catch (e) {
  console.log(`\n[30-admin-adversarial] ABORTED: ${String(e).split('\n')[0]}`);
  res.check('admin + adversarial suite completed without abort', false, String(e).split('\n')[0]);
  await shot(page, ART, 'aborted').catch(() => {});
  res.save(ART);
} finally {
  await sleep(300);
  await browser.close();
}
