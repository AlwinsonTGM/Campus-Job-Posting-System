import assert from 'node:assert/strict';
import fs from 'node:fs';
import { chromium } from '@playwright/test';

const view = fs.readFileSync(new URL('../includes/templates/admin-users-view.php', import.meta.url), 'utf8');
const handler = view.match(/document\.addEventListener\('submit', function\(e\) \{[\s\S]*?\n    \}\);/)[0];
const archiveForm = view.match(/<form action="users.php" method="POST" class="d-inline" onsubmit="return confirm\('Archive[\s\S]*?<\/form>/)[0]
  .replace(/<\?= \$u\['id'\] \?>/g, '42')
  .replace(/<\?= generate_csrf_token\(\) \?>/g, 'test-token');
const browser = await chromium.launch({ headless: true });
try {
  const page = await browser.newPage();
  let submission;
  await page.route('http://archive.test/**', async route => {
    if (route.request().method() === 'POST') {
      submission = new URLSearchParams(route.request().postData());
      await route.fulfill({ body: 'Archived' });
    } else {
      await route.fulfill({ contentType: 'text/html', body: archiveForm + '<script>' + handler + '</script>' });
    }
  });
  await page.goto('http://archive.test/admin/users.php');
  page.once('dialog', dialog => dialog.dismiss());
  await page.getByTitle('Archive & Remove from Website').click();
  await page.waitForTimeout(100);
  assert.equal(submission, undefined, 'Cancel must not send an archive request');
  assert.equal(await page.getByTitle('Archive & Remove from Website').isDisabled(), false, 'Cancel must not leave the archive button loading');
  assert.equal(await page.getByText('Processing...').count(), 0, 'Cancel must not show Processing');
  page.once('dialog', dialog => dialog.accept());
  await Promise.all([page.waitForURL('**/users.php', { waitUntil: 'load' }), page.getByTitle('Archive & Remove from Website').click()]);
  await page.waitForFunction(() => document.body.textContent === 'Archived');
  assert.equal(submission.get('action'), 'suspend_user');
  assert.equal(submission.get('id'), '42');
  assert.equal(submission.get('csrf_token'), 'test-token');
  console.log('PASS: Cancel leaves archive usable; confirmation sends the archive POST.');
} finally {
  await browser.close();
}
