import assert from 'node:assert/strict';
import {execFileSync} from 'node:child_process';
import {chromium} from '@playwright/test';

// Render the real view with fictional session data; stub only verification/resend requests.
const base = process.env.AUTH_PREVIEW_URL || 'http://127.0.0.1:8001';
const render = (role = 'student', testing = false, cooldown = 2) => execFileSync('C:/xampp/php/php.exe', ['tests/fixtures/verify-email-view.php', role, testing ? 'testing' : '', String(cooldown)], {encoding: 'utf8'});
const browser = await chromium.launch({channel: 'chrome', headless: true});
const page = await browser.newPage({viewport: {width: 1440, height: 1000}});
const errors = [];
let html = render();
let response = 'invalid';
let verifies = 0;
let resends = 0;
page.on('pageerror', error => errors.push(error.message));
await page.route('**/verify-email.php', async route => {
  if (route.request().method() !== 'POST') return route.fulfill({contentType: 'text/html', body: html});
  const body = route.request().postData() || '';
  if (body.includes('resend')) {
    resends++;
    html = render('student', false, 60);
    return route.fulfill({contentType: 'text/html', body: html});
  }
  verifies++;
  if (response === 'offline') return route.fulfill({contentType: 'text/html', body: html});
  return route.fulfill({json: response === 'success' ? {success: true, message: 'Email verified. Awaiting administrator approval.', redirect: 'pending-approval.php'} : {success: false, message: response === 'expired' ? 'This code has expired. Please request another.' : 'Invalid verification code.'}});
});
await page.route('**/pending-approval.php', route => route.fulfill({contentType: 'text/html', body: '<h1>Pending approval fixture</h1>'}));
const open = async () => { await page.goto(base + '/verify-email.php'); await page.evaluate(() => document.fonts.ready); };
const paste = async (text, index = 0) => page.locator('.otp-digit-pod').nth(index).evaluate((input, text) => {
  const clipboardData = new DataTransfer(); clipboardData.setData('text', text);
  input.dispatchEvent(new ClipboardEvent('paste', {clipboardData, bubbles: true, cancelable: true}));
}, text);
try {
  await open();
  assert.equal(await page.locator('.otp-digit-pod').count(), 6);
  assert.equal(await page.locator('.otp-digit-pod').first().evaluate(input => document.activeElement === input), true);
  assert.equal(await page.locator('footer').count(), 1);
  assert.match(await page.locator('.verification-actions>a').getAttribute('href'), /action=cancel/);
  assert.equal(await page.locator('#btn-resend-otp').isDisabled(), true);
  await page.waitForFunction(() => !document.getElementById('btn-resend-otp').disabled);
  await Promise.all([page.waitForEvent('load'), page.locator('#btn-resend-otp').click()]);
  assert.equal(resends, 1);
  assert.equal(await page.locator('#btn-resend-otp').isDisabled(), true);
  await page.locator('.otp-digit-pod').first().fill('1');
  assert.equal(await page.locator('.otp-digit-pod').nth(1).evaluate(input => input === document.activeElement), true);
  await page.locator('.otp-digit-pod').nth(1).press('Backspace');
  assert.equal(await page.locator('#otp-code-hidden').inputValue(), '');
  await paste('12a3', 4);
  assert.equal(await page.locator('#otp-code-hidden').inputValue(), '123');
  await page.locator('.otp-digit-pod').nth(3).press('ArrowLeft');
  assert.equal(await page.locator('.otp-digit-pod').nth(2).evaluate(input => input === document.activeElement), true);
  await paste('123456', 3);
  await page.waitForFunction(() => document.getElementById('otp-inputs-wrapper').classList.contains('otp-shake'));
  assert.match(await page.locator('#otp-error-alert').innerText(), /Invalid/);
  assert.equal(await page.locator('.pod-error').count(), 6);
  await page.waitForFunction(() => document.getElementById('otp-code-hidden').value === '');
  assert.equal(verifies, 1);
  response = 'expired';
  await page.locator('.otp-digit-pod').first().pressSequentially('654321');
  await page.waitForFunction(() => document.getElementById('otp-error-text').textContent.includes('expired'));
  await page.waitForFunction(() => document.getElementById('otp-code-hidden').value === '');
  response = 'success';
  await paste('123456');
  await page.locator('#otp-success-view').waitFor({state: 'visible'});
  assert.match(await page.locator('#otp-success-msg').innerText(), /administrator approval/);
  assert.equal(await page.locator('.otp-checkmark-circle').evaluate(element => getComputedStyle(element).animationName), 'checkmarkCircleDraw');
  await page.screenshot({path: 'design-previews/auth-verification-success.png', fullPage: true});
  await page.waitForURL('**/pending-approval.php');
  assert.equal(verifies, 3);

  let layouts = 0;
  for (const role of ['student', 'partner']) {
    html = render(role);
    await open();
    assert.equal(await page.locator('.auth-layout.partner').count(), role === 'partner' ? 1 : 0);
    for (const dark of [false, true]) {
      await page.evaluate(dark => document.documentElement.dataset.theme = dark ? 'dark' : 'light', dark);
      for (const width of [375, 812, 1440, 1920]) {
        await page.setViewportSize({width, height: 1000});
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
        const widths = await page.evaluate(() => ({button: document.getElementById('btn-submit-otp').offsetWidth, form: document.querySelector('.form-area').offsetWidth}));
        assert.ok(widths.button < widths.form);
        const alignment = await page.evaluate(() => {
          const center = selector => { const rect = document.querySelector(selector).getBoundingClientRect(); return rect.left + rect.width / 2; };
          return ['.otp-inputs-grid', '.otp-email-chip', '#btn-submit-otp'].map(selector => Math.abs(center(selector) - center('.email-verification')));
        });
        assert.ok(alignment.every(offset => offset <= 1), `Verification content must share a centered axis: ${alignment}`);
        const card = await page.evaluate(() => {
          const shell = document.querySelector('.auth-layout');
          const style = getComputedStyle(shell);
          const form = document.querySelector('.form-area').getBoundingClientRect();
          const story = document.querySelector('.story').getBoundingClientRect();
          const code = document.querySelector('.otp-inputs-grid').getBoundingClientRect();
          return {border: parseFloat(style.borderTopWidth), radius: parseFloat(style.borderTopLeftRadius), formLeft: form.left, formRight: form.right, formTop: form.top, storyRight: story.right, storyBottom: story.bottom, codeLeft: code.left, codeRight: code.right};
        });
        assert.ok(card.border >= 1 && card.radius >= 16, 'Verification belongs inside a rounded split card');
        assert.ok(card.codeLeft >= card.formLeft && card.codeRight <= card.formRight, 'Code inputs stay inside the right card panel');
        if (width > 1000) assert.ok(card.formLeft >= card.storyRight - 1, 'Verification panel sits to the right of the story');
        else assert.ok(card.formTop >= card.storyBottom - 1, 'The split card stacks on smaller screens');
        layouts++;
        if ((width === 375 && dark) || (width === 1440 && !dark)) await page.screenshot({path: `design-previews/auth-verification-${role}-${dark ? 'dark' : 'light'}-${width}.png`, fullPage: true});
      }
    }
  }
  html = render('long', true);
  await open();
  await page.setViewportSize({width: 375, height: 812});
  assert.match(await page.locator('.verification-notice').innerText(), /123456/);
  assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
  await page.emulateMedia({reducedMotion: 'reduce'});
  await paste('123456');
  await page.locator('#otp-success-view').waitFor({state: 'visible'});
  assert.equal(await page.locator('.otp-checkmark-circle').evaluate(element => getComputedStyle(element).animationName), 'none');
  await page.waitForURL('**/pending-approval.php');
  assert.deepEqual(errors, []);
  console.log(JSON.stringify({result: 'PASS', layoutChecks: layouts, verifiedBehaviors: ['auto-advance', 'backspace and arrow keys', 'paste into any box', 'auto-submit', 'invalid and expired code', 'resend cooldown', 'success animation and redirect', 'testing code display', 'long email', 'reduced motion'], realEmailsSent: 0, realAccountsChanged: 0}, null, 2));
} finally { await browser.close(); }
