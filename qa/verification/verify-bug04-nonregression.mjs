/**
 * Non-regression check for the BUG-04 fix.
 * The footer modal is included on every page and settings.php renders the admin
 * control, so confirm those still render cleanly for every role - and that the
 * switcher UI is only offered to administrators.
 */
import { launch, Results, QA_BASE, sleep } from '../lib/harness.mjs';
import * as path from 'node:path';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'verify-bug04-nonregression');
const res = new Results('verify-bug04-nonregression');

const PHP_ERR = /(Fatal error|Warning:|Notice:|Deprecated:|Uncaught \w+:)/;

const { browser, page } = await launch();
try {
  // Footer partial is on every page -> check a public page renders.
  for (const url of ['/index.php', '/faqs.php', '/student/jobs.php']) {
    const r = await page.goto(`${QA_BASE}${url}`, { waitUntil: 'domcontentloaded' }).catch(() => null);
    await sleep(600);
    const body = await page.content();
    const bad = body.match(PHP_ERR);
    res.check(`${url} renders clean after the footer change`, !bad && r && r.status() === 200,
      bad ? bad[0].slice(0, 160) : `HTTP ${r ? r.status() : 'n/a'}`);
  }

  // Anonymous visitor must NOT be offered the switcher.
  await page.goto(`${QA_BASE}/index.php`, { waitUntil: 'domcontentloaded' });
  await sleep(600);
  let modal = await page.locator('#dataModeModal').count();
  res.check('anonymous visitor is not offered the dataset switcher', modal === 0, `found ${modal}`);

  // Student must NOT be offered it either.
  await page.goto(`${QA_BASE}/login.php?demo=student`, { waitUntil: 'domcontentloaded' });
  await sleep(800);
  await page.goto(`${QA_BASE}/student/dashboard.php`, { waitUntil: 'domcontentloaded' });
  await sleep(700);
  modal = await page.locator('#dataModeModal').count();
  res.check('student is not offered the dataset switcher', modal === 0, `found ${modal}`);

  // Admin: settings must render, and the switch control must be present.
  await page.goto(`${QA_BASE}/login.php?demo=admin`, { waitUntil: 'domcontentloaded' });
  await sleep(900);
  const r = await page.goto(`${QA_BASE}/settings.php`, { waitUntil: 'domcontentloaded' });
  await sleep(900);
  const body = await page.content();
  const bad = body.match(PHP_ERR);
  res.check('admin settings.php renders clean after the change', !bad && r.status() === 200,
    bad ? bad[0].slice(0, 160) : `HTTP ${r.status()}`);

  const control = await page.locator('#admin-dataset-settings form[action$="data-toggle.php"]').count();
  res.check('admin still gets the dataset switch control', control === 1, `found ${control}`);

  const token = await page.evaluate(() =>
    document.querySelector('#admin-dataset-settings input[name="csrf_token"]')?.value || '');
  res.check('the switch control carries a CSRF token', token.length >= 32, `len=${token.length}`);

  const modalAdmin = await page.locator('#dataModeModal').count();
  res.check('admin is offered the footer switcher', modalAdmin === 1, `found ${modalAdmin}`);

  res.save(ART);
} catch (e) {
  res.check('non-regression check completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
  process.exit(0);
}
