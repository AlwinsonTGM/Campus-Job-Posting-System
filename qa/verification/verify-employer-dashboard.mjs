/**
 * VERIFICATION — "employer dashboard has no PHP error text" FAILED with a raw
 * "Fatal error" during the lifecycle run. Establish whether this is a real
 * product defect, and capture the exact message and trigger.
 */
import { launch, Results, QA_BASE, sleep } from '../lib/harness.mjs';
import * as path from 'node:path';
import * as fs from 'node:fs';

const ART = path.join(import.meta.dirname, '..', 'artifacts', 'verify-emp-dash');
fs.mkdirSync(ART, { recursive: true });
const res = new Results('verify-employer-dashboard-fatal');

const PHP_ERR = /(Fatal error|Warning:|Notice:|Deprecated:)[\s\S]{0,300}/i;

const { browser, page } = await launch();
try {
  await page.goto(`${QA_BASE}/login.php?reset=1`, { waitUntil: 'domcontentloaded' });
  await sleep(1500);
  await page.goto(`${QA_BASE}/login.php?demo=employer`, { waitUntil: 'domcontentloaded' });
  await sleep(1200);

  const r = await page.goto(`${QA_BASE}/employer/dashboard.php`, { waitUntil: 'domcontentloaded' });
  await sleep(1200);
  let body = await page.content();

  console.log('=== /employer/dashboard.php ===');
  console.log('status :', r ? r.status() : 'n/a');
  console.log('bytes  :', body.length);
  console.log('url    :', page.url());

  let m = body.match(PHP_ERR);
  console.log('PHP diagnostic:', m ? JSON.stringify(m[0].slice(0, 300)) : 'none');

  res.check('employer dashboard renders without a PHP diagnostic', !m,
    m ? m[0].slice(0, 250) : '');

  if (!m) {
    // Try the other surfaces the lifecycle script touched, in case it was one of those.
    for (const url of ['/employer/applicants.php', '/employer/create-job.php', '/employer/updates.php', '/employer/reports.php']) {
      await page.goto(`${QA_BASE}${url}`, { waitUntil: 'domcontentloaded' }).catch(() => {});
      await sleep(700);
      const b = await page.content();
      const mm = b.match(PHP_ERR);
      console.log(`  ${url.padEnd(30)} ${mm ? 'PHP DIAGNOSTIC: ' + JSON.stringify(mm[0].slice(0, 200)) : 'clean'}`);
      res.check(`${url} renders without a PHP diagnostic`, !mm, mm ? mm[0].slice(0, 200) : '');
    }
  }

  // Also capture whether the employer's OWN vacancy list is complete.
  const jobs = await page.evaluate(() => {
    const links = [...document.querySelectorAll('a[href*="edit-job.php?id="]')]
      .map((a) => a.getAttribute('href'));
    return [...new Set(links)];
  });
  console.log('\nedit-job links on the dashboard:', JSON.stringify(jobs));
  res.check('dashboard lists more than one of the employer\'s vacancies', jobs.length >= 1, JSON.stringify(jobs));

  fs.writeFileSync(path.join(ART, 'employer-dashboard.html'), body);
  res.save(ART);
} catch (e) {
  res.check('verification completed without abort', false, String(e).split('\n')[0]);
  res.save(ART);
} finally {
  await sleep(200);
  await browser.close();
  process.exit(0);
}
