import assert from 'node:assert/strict';
import { mkdir } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import { chromium } from '@playwright/test';

const phase = process.argv[2] || 'after';
const base = process.env.CAMPUS_TEST_URL || 'http://localhost/Final-Campus-Job-Posting-System';
const out = new URL('../screenshots/employer-form-changes/', import.meta.url);
await mkdir(out, { recursive: true });
const browser = await chromium.launch({ channel: 'chrome', headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 1100 } });
const errors = [];
page.on('pageerror', error => errors.push(error.message));
const shot = async name => page.screenshot({ path: new URL(`${phase}-${name}.png`, out).pathname.replace(/^\/(\w:)/, '$1'), fullPage: false, animations: 'disabled' });
try {
  await page.goto(`${base}/login.php?demo=employer`);
  await page.goto(`${base}/employer/applicants.php`);
  const review = await page.locator('a[href*="review-app.php?id="]').first().getAttribute('href');
  assert.ok(review, 'An existing employer application is available');
  await page.goto(`${base}/employer/${review}`);
  await page.locator('#eval-status').selectOption('interview_scheduled');
  await page.locator('#int-venue').scrollIntoViewIfNeeded();
  if (phase === 'after') {
    await page.locator('#int-venue').fill('Apex Training Annex, Room 405');
    assert.equal(await page.locator('#int-venue').inputValue(), 'Apex Training Annex, Room 405');
    assert.equal(await page.locator('#int-venue').getAttribute('list'), 'interview-venues');
    await page.locator('#eval-status').selectOption('under_review');
    assert.equal(await page.locator('#int-venue').isDisabled(), true);
    await page.locator('#eval-status').selectOption('interview_scheduled');
  }
  await shot('custom-venue');
  await page.goto(`${base}/employer/updates.php`);
  await page.locator('[data-bs-target="#newEmployerDispatchModal"]').first().click();
  await page.locator('#newEmployerDispatchModal.show').waitFor();
  await page.waitForFunction(() => getComputedStyle(document.querySelector('#newEmployerDispatchModal')).opacity === '1');
  if (phase === 'after') {
    assert.equal(await page.locator('#newEmployerDispatchModal form').getAttribute('enctype'), 'multipart/form-data');
    assert.equal(await page.locator('#newEmployerDispatchModal input[type=file][name=photo]').count(), 1);
    assert.equal(await page.locator('#editEmployerDispatchModal input[type=file][name=photo]').count(), 1);
  }
  await shot('announcement-upload');
  // Use the existing CLI login helper; keep account credentials and campus records unchanged.
  const session = execFileSync('C:/xampp/php/php.exe', ['-r', `require 'includes/data-helper.php'; $id = get_db_connection()->query("SELECT user_id FROM employer_profiles WHERE employer_type = 'approved_partner' AND verification_status = 'verified' LIMIT 1")->fetchColumn(); if (!$id || !quick_login('employer', $id)) exit(1); echo session_id();`], { encoding: 'utf8' }).trim();
  const partnerContext = await browser.newContext({ viewport: { width: 1440, height: 1100 } });
  await partnerContext.addCookies([{ name: 'PHPSESSID', value: session, url: base }]);
  const partner = await partnerContext.newPage();
  await partner.goto(`${base}/employer/create-job.php`);
  await partner.locator('#work-setup').scrollIntoViewIfNeeded();
  const options = await partner.locator('#work-setup option').allTextContents();
  if (phase === 'after') {
    assert.ok(!options.some(option => /On-Campus|Campus \+ Remote/.test(option)), 'Partners cannot select campus work');
    assert.ok(options.some(option => /Near-Campus/.test(option)));
    assert.ok(options.some(option => /Partner Office \+ Remote/.test(option)));
  }
  await partner.locator('#work-setup').focus();
  await partner.locator('#work-setup').press('Space');
  await partner.screenshot({ path: new URL(`${phase}-partner-workplace.png`, out).pathname.replace(/^\/(\w:)/, '$1'), animations: 'disabled' });
  console.log(JSON.stringify({ phase, partnerOptions: options, evidence: out.pathname }));
  assert.deepEqual(errors, []);
} finally {
  await browser.close();
}
