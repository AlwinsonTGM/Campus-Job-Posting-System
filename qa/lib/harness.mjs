/**
 * Minimal QA harness primitives.
 *
 * Deliberately does NOT use @playwright/test: its runner forks worker
 * processes with piped stdio, which is blocked in this environment
 * (spawn EPERM). This harness launches Chromium in-process instead.
 */
import { chromium } from 'playwright';
import * as fs from 'node:fs';
import * as path from 'node:path';

export const QA_BASE = process.env.QA_BASE || 'http://127.0.0.1:8099';

// ---------------------------------------------------------------------------
// Result collection
// ---------------------------------------------------------------------------
export class Results {
  constructor(teamName) {
    this.team = teamName;
    this.checks = [];
    this.findings = [];
    this.startedAt = new Date().toISOString();
  }

  /** Record an assertion outcome. Never throws — collection is non-fatal. */
  check(name, ok, detail = '') {
    this.checks.push({ name, ok: !!ok, detail: String(detail).slice(0, 800) });
    const mark = ok ? 'PASS' : 'FAIL';
    console.log(`  [${mark}] ${name}${detail && !ok ? ` :: ${String(detail).slice(0, 300)}` : ''}`);
    return !!ok;
  }

  /** Record a suspected bug for the verification phase. */
  finding({ title, severity, route, role, steps, expected, actual, evidence }) {
    this.findings.push({
      id: `${this.team}-${String(this.findings.length + 1).padStart(2, '0')}`,
      team: this.team,
      title, severity, route, role,
      steps: Array.isArray(steps) ? steps : [steps],
      expected, actual,
      evidence: evidence || [],
      discoveredAt: new Date().toISOString(),
    });
    console.log(`  [BUG?] (${severity}) ${title}`);
  }

  get passed() { return this.checks.filter((c) => c.ok).length; }
  get failed() { return this.checks.filter((c) => !c.ok).length; }

  save(dir) {
    fs.mkdirSync(dir, { recursive: true });
    const out = {
      team: this.team,
      startedAt: this.startedAt,
      finishedAt: new Date().toISOString(),
      passed: this.passed,
      failed: this.failed,
      checks: this.checks,
      findings: this.findings,
    };
    const file = path.join(dir, `${this.team}.json`);
    fs.writeFileSync(file, JSON.stringify(out, null, 2));
    console.log(`\n[${this.team}] ${this.passed} passed / ${this.failed} failed / ${this.findings.length} candidate bug(s)`);
    console.log(`[${this.team}] -> ${file}`);
    return out;
  }
}

// ---------------------------------------------------------------------------
// Browser + page helpers
// ---------------------------------------------------------------------------
export async function launch() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    // Relative page.goto('/x.php') needs this; the test-runner config supplied
    // it before, but this harness talks to Playwright directly.
    baseURL: QA_BASE,
    viewport: { width: 1440, height: 900 },
  });
  const page = await context.newPage();
  return { browser, context, page };
}

/**
 * Capture a console/page-error trail. Real bugs often surface as JS errors
 * or failed requests rather than visible breakage.
 */
export function watchErrors(page) {
  const errors = [];
  page.on('pageerror', (e) => errors.push({ kind: 'pageerror', text: String(e.message).slice(0, 400) }));
  page.on('console', (m) => {
    if (m.type() === 'error') errors.push({ kind: 'console', text: m.text().slice(0, 400) });
  });
  page.on('response', (r) => {
    if (r.status() >= 500) errors.push({ kind: 'http', text: `${r.status()} ${r.url()}` });
  });
  return errors;
}

/** Login through the local-only demo hook (whitelisted roles only). */
export async function loginAs(page, role) {
  await page.goto(`${QA_BASE}/login.php?demo=${role}`, { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle').catch(() => {});
  return page.url();
}

export async function logout(page) {
  await page.goto(`${QA_BASE}/logout.php`, { waitUntil: 'domcontentloaded' });
}

/**
 * Re-seed the ISOLATED test database from the JSON fixtures.
 *
 * login.php?reset=1 calls DatastoreManager::resetToDemo() -> switchMode('demo')
 * -> execute_migration_and_seed(), which re-imports data/seeds/demo/*.json over
 * MySQL. It is guarded to loopback + non-production, and it only ever touches
 * $DB_NAME - which qa/serve-qa.cmd pins to campus_job_portal_e2e.
 *
 * Verified: a row inserted into campus_job_portal_e2e disappears after this
 * call while campus_job_portal is unchanged.
 */
export async function resetDatastore(page) {
  await page.goto(`${QA_BASE}/login.php?reset=1`, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await sleep(500);
}

/** Raw HTTP helper for API/route assertions without rendering. */
export async function httpGet(url, opts = {}) {
  const res = await fetch(url.startsWith('http') ? url : QA_BASE + url, { redirect: 'manual', ...opts });
  const text = await res.text();
  return { status: res.status, text, headers: res.headers };
}

/** Screenshot into the artifact store (best-effort). */
export async function shot(page, dir, name) {
  try {
    fs.mkdirSync(dir, { recursive: true });
    const p = path.join(dir, `${name}.png`);
    await page.screenshot({ path: p, fullPage: true });
    return p;
  } catch { return null; }
}

/** Save the current page HTML for evidence. */
export function saveHtml(page_or_html, dir, name) {
  return (async () => {
    try {
      fs.mkdirSync(dir, { recursive: true });
      const html = typeof page_or_html === 'string' ? page_or_html : await page_or_html.content();
      const p = path.join(dir, `${name}.html`);
      fs.writeFileSync(p, html);
      return p;
    } catch { return null; }
  })();
}

export const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
