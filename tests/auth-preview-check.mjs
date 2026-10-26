import assert from 'node:assert/strict';
import {chromium} from '@playwright/test';

const base = process.env.AUTH_PREVIEW_URL || 'http://localhost/final-campus-job-posting-system';
const browser = await chromium.launch({channel: 'chrome', headless: true});
const page = await browser.newPage({viewport: {width: 1440, height: 1000}});
const errors = [];
const posts = [];
page.on('pageerror', error => errors.push(error.message));
page.on('request', request => { if (request.method() === 'POST') posts.push(request.url()); });
const open = async query => {
  const response = await page.goto(`${base}/auth-design-preview.php?${query}`);
  assert.equal(response.status(), 200);
  await page.evaluate(() => document.fonts.ready);
};
try {
  await open('screen=verify');
  await page.locator('.otp-digit-pod').first().pressSequentially('987654');
  await page.locator('#otp-success-view').waitFor({state: 'visible'});
  assert.equal(await page.locator('.otp-checkmark-check').evaluate(el => getComputedStyle(el).animationName), 'checkmarkStrokeDraw');
  await page.waitForURL('**/*screen=pending*');
  assert.equal(await page.locator('.review-timeline [aria-current=step]').count(), 1);
  await page.locator('#preview-play').click();
  await page.locator('#otp-success-view').waitFor({state: 'visible'});
  await page.waitForURL('**/*screen=pending*');
  await open('screen=verify');
  await page.locator('#btn-submit-otp').click();
  await page.locator('#otp-success-view').waitFor({state: 'visible'});
  await page.waitForURL('**/*screen=pending*');
  await open('screen=verify');
  await page.locator('#btn-resend-otp').click();
  assert.match(await page.locator('#resend-text').textContent(), /Preview code ready/);
  for (const role of ['student', 'partner']) {
    for (const screen of ['pending', 'rejected', 'revoked']) {
      await open(`role=${role}&screen=${screen}`);
      assert.equal(await page.locator('.auth-layout.partner').count(), role === 'partner' ? 1 : 0);
      assert.equal(await page.locator('.review-resubmit').count(), screen === 'rejected' ? 1 : 0);
      if (screen === 'rejected') {
        assert.equal(await page.locator(`input[name=${role === 'partner' ? 'permit_file' : 'proof_file'}]`).count(), 1);
      }
      for (const theme of ['light', 'dark']) {
        await page.evaluate(theme => document.documentElement.dataset.theme = theme, theme);
        for (const width of [375, 1440]) {
          await page.setViewportSize({width, height: 1000});
          assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, `${role} ${screen} ${theme} ${width} overflows`);
          assert.equal(await page.locator('footer').count(), 1);
          if (screen === 'pending') await page.screenshot({path: `design-previews/approval-${role}-${theme}-${width}.png`, fullPage: true});
        }
      }
      if (screen === 'rejected') {
        await page.locator('.review-resubmit button[type=submit]').click();
        await page.waitForURL('**/*screen=pending*');
      }
    }
  }
  const realPage = await page.goto(`${base}/pending-approval.php`);
  assert.equal(realPage.status(), 200);
  assert.match(page.url(), /login\.php/);
  assert.deepEqual(posts, [], 'Preview must never post to real controllers');
  assert.deepEqual(errors, []);
  console.log('Passed preview animation, replay, empty submit, resend, resubmission, 24 layouts, no real POSTs, and real-page access guard.');
} finally { await browser.close(); }
