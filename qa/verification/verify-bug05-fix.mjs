/** Confirm BUG-05 is fixed and the archive/restore controls still carry CSRF tokens. */
import { launch, Results, QA_BASE, sleep } from '../lib/harness.mjs';
import * as path from 'node:path';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'verify-bug05-fix');
const res = new Results('verify-bug05-fix');

const { browser, page } = await launch();
try {
  await page.goto(`${QA_BASE}/login.php?demo=employer`, { waitUntil: 'domcontentloaded' });
  await sleep(900);

  // Dashboard: both owned vacancies must be listed, no fatal.
  await page.goto(`${QA_BASE}/employer/dashboard.php`, { waitUntil: 'domcontentloaded' });
  await sleep(900);
  const body = await page.content();
  res.check('BUG-05: employer dashboard has no PHP fatal', !/Fatal error|Uncaught \w+:/.test(body));

  const links = await page.evaluate(() =>
    [...new Set([...document.querySelectorAll('a[href*="edit-job.php?id="]')].map((a) => a.getAttribute('href')))]);
  console.log('  edit-job links rendered:', JSON.stringify(links));
  res.check('BUG-05: both owned vacancies are listed (2 and 3)', links.length === 2, JSON.stringify(links));

  // The archive form must carry a real CSRF token.
  const archive = await page.evaluate(() => {
    const f = [...document.querySelectorAll('form[action="dashboard.php"]')]
      .find((x) => x.querySelector('input[name="action"]')?.value === 'archive_job');
    if (!f) return null;
    return { token: f.querySelector('input[name="csrf_token"]')?.value || '', jobId: f.querySelector('input[name="job_id"]')?.value || '' };
  });
  console.log('  archive form:', JSON.stringify({ jobId: archive?.jobId, tokenLen: archive?.token?.length }));
  res.check('BUG-05: archive form present with a CSRF token', !!archive && archive.token.length >= 32,
    `token length ${archive ? archive.token.length : 'no form'}`);

  // Edit page must render too.
  await page.goto(`${QA_BASE}/employer/edit-job.php?id=3`, { waitUntil: 'domcontentloaded' });
  await sleep(900);
  const editBody = await page.content();
  res.check('BUG-05: employer edit-job page has no PHP fatal',
    !/Fatal error|Uncaught \w+:/.test(editBody));
  res.check('BUG-05: edit page shows the real vacancy title',
    // the title contains "&", which is correctly rendered as &amp;
    /Digital Cataloging (&amp;|&) Library Aid/.test(editBody));

  const editTokens = await page.evaluate(() =>
    [...document.querySelectorAll('input[name="csrf_token"]')].map((i) => i.value.length));
  console.log('  edit page csrf token lengths:', JSON.stringify(editTokens));
  res.check('BUG-05: every form on the edit page has a CSRF token',
    editTokens.length >= 2 && editTokens.every((l) => l >= 32), JSON.stringify(editTokens));

  res.save(ART);
} catch (e) {
  res.check('verification completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
  process.exit(0);
}
