import assert from 'node:assert/strict';
import { chromium } from '@playwright/test';

// Read-only browser checks. Availability responses are stubbed; no accounts are created.
const base = process.env.AUTH_PREVIEW_URL || 'http://127.0.0.1:8001';
const browser = await chromium.launch({channel: 'chrome', headless: true});
const page = await browser.newPage({viewport: {width: 1440, height: 1000}});
const errors = [];
let submissions = 0;
let studentIdState = 'available';
page.on('pageerror', error => errors.push(error.message));
page.on('request', request => { if (request.method() === 'POST' && request.url().includes('register.php')) submissions++; });
await page.route('**/api/check-account.php?**', route => {
  const checkingId = new URL(route.request().url()).searchParams.has('student_id');
  if (checkingId && studentIdState === 'offline') return route.abort('failed');
  const taken = checkingId && studentIdState === 'taken';
  return route.fulfill({json: {available: !taken, email_exists: false, student_id_exists: taken, message: taken ? 'This Student ID Number is already registered.' : ''}});
});
const visit = async path => { await page.goto(base + path); await page.evaluate(() => document.fonts.ready); };
const fillDetails = async (partner = false) => {
  if (partner) await page.locator('#reg-name').fill('Example Partner');
  else {
    await page.locator('#reg-first-name').fill('Sample');
    await page.locator('#reg-last-name').fill('Student');
  }
  await page.locator('#reg-email').fill(partner ? 'person@example.com' : 'sample@kld.edu.ph');
  await page.locator('#reg-phone').fill('0917 123 4567');
};
const verifyPasswordGuidance = async role => {
  const password = page.locator('#password');
  const meter = page.locator('#password-strength-meter');
  assert.match(await page.locator('#password-minimum').innerText(), /Minimum: 8 characters/);
  for (const [value, label, score, met] of [
    ['abc', 'Weak', 1, 1],
    ['abcdefgh', 'Weak', 1, 2],
    ['Abcdefgh', 'Fair', 2, 3],
    ['Abcdefg1', 'Good', 3, 4],
    ['Abcdef1!', 'Strong', 4, 5]
  ]) {
    await password.fill(value);
    assert.equal(await page.locator('#password-strength-text').innerText(), label);
    assert.equal(await meter.getAttribute('aria-valuenow'), String(score));
    assert.equal(await page.locator('[data-requirement][data-met=true]').count(), met);
  }
  await page.getByRole('button', {name: 'Show password', exact: true}).click();
  assert.equal(await password.getAttribute('type'), 'text');
  assert.equal(await meter.getAttribute('aria-valuenow'), '4');
  await page.getByRole('button', {name: 'Hide password', exact: true}).click();
  await page.screenshot({path: `design-previews/auth-password-${role}-desktop.png`, fullPage: true});
  await page.setViewportSize({width: 375, height: 812});
  await page.evaluate(() => document.documentElement.dataset.theme = 'dark');
  assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
  await page.screenshot({path: `design-previews/auth-password-${role}-mobile-dark.png`, fullPage: true});
  await page.setViewportSize({width: 1440, height: 1000});
  await page.evaluate(() => document.documentElement.dataset.theme = 'light');
  await password.fill('');
  assert.equal(await meter.getAttribute('aria-valuenow'), '0');
  assert.equal(await page.locator('[data-requirement][data-met=true]').count(), 0);
  assert.equal(await page.locator('#password-strength-text').innerText(), 'Not entered');
};
try {
  await visit('/login.php?next=student/dashboard.php');
  assert.equal(await page.locator('input[name=next]').inputValue(), 'student/dashboard.php');
  assert.match(await page.locator('#link-portal-login').getAttribute('href'), /next=/);
  assert.equal(await page.locator('.story-dot').count(), 0);
  await page.locator('#login-password').fill('SamplePassword123!');
  await page.getByRole('button', {name: 'Show password', exact: true}).click();
  assert.equal(await page.locator('#login-password').getAttribute('type'), 'text');
  await page.getByRole('button', {name: 'Hide password', exact: true}).click();
  assert.equal(await page.locator('#login-password').getAttribute('type'), 'password');

  await visit('/register.php');
  assert.equal(await page.locator('#course-wrap').isVisible(), false);
  assert.deepEqual(await page.locator('#reg-sex option').evaluateAll(options => options.map(option => option.value)), ['', 'Male', 'Female']);
  await fillDetails();
  await page.locator('#reg-phone').fill('0917+1234567');
  assert.equal(await page.locator('#reg-phone').evaluate(input => input.checkValidity()), false);
  await page.locator('[data-next]').first().click();
  assert.equal(await page.locator('#step-pane-1').isVisible(), true);
  assert.match(await page.locator('#step-error-alert').innerText(), /phone number/);
  assert.equal(await page.locator('#step-error-alert').evaluate(e => e === document.activeElement), true);
  await page.locator('#reg-phone').fill('+63 917 123 4567');
  assert.equal(await page.locator('#reg-phone').evaluate(input => input.checkValidity()), true);
  await page.locator('[data-next]').first().click();
  await page.locator('#step-pane-2').waitFor({state: 'visible'});
  const institutes = await page.locator('#department-select option').evaluateAll(options => options.map(option => option.value).filter(Boolean));
  await page.locator('#department-select').selectOption(institutes[0]);
  assert.equal(await page.locator('#course-wrap').isVisible(), true);
  assert.equal(await page.locator('#course-select').inputValue(), '');
  const firstCourses = await page.locator('#course-select option').evaluateAll(options => options.map(option => option.value).filter(Boolean));
  assert.equal(firstCourses.length, 3);
  await page.locator('#course-select').selectOption(firstCourses[0]);
  await page.locator('#department-select').selectOption(institutes[1]);
  assert.equal(await page.locator('#course-select').inputValue(), '');
  assert.equal(await page.locator('#course-select option').count(), 2);
  await page.locator('#department-select').selectOption(institutes[0]);
  await page.locator('#course-select').selectOption(firstCourses[0]);
  await page.locator('#student_id').fill('2025-2-00065');
  await page.locator('#year_level').selectOption('1st Year');
  await page.locator('#reg-sex').selectOption('Female');
  await page.locator('#reg-birthdate').fill('2010-01-01');
  await page.locator('#step-pane-2 [data-next]').click();
  assert.match(await page.locator('#step-error-alert').innerText(), /YYYY-T-XXXXXX/);
  assert.match(await page.locator('#step-error-alert').innerText(), /18 years/);
  await page.locator('#student_id').fill('2025-2-000065');
  await page.locator('#reg-birthdate').fill(await page.locator('#reg-birthdate').getAttribute('max'));
  studentIdState = 'taken';
  await page.locator('#student_id').fill('2025-2-000066');
  await page.waitForFunction(() => document.getElementById('student-id-duplicate-feedback').textContent.includes('already registered'));
  assert.equal(await page.locator('#student_id').evaluate(input => input === document.activeElement), true, 'Live availability checks must not steal focus');
  await page.locator('#step-pane-2 [data-next]').click();
  await page.waitForFunction(() => document.getElementById('step-error-alert').textContent.includes('already registered'));
  assert.equal(await page.locator('#step-pane-2').isVisible(), true);
  assert.match(await page.locator('#step-error-alert').innerText(), /already registered/);
  studentIdState = 'offline';
  await page.locator('#student_id').fill('2025-2-000067');
  await page.waitForFunction(() => document.getElementById('student-id-duplicate-feedback').textContent.includes('Could not check'));
  await page.locator('#step-pane-2 [data-next]').click();
  await page.waitForFunction(() => document.getElementById('step-error-alert').textContent.includes('try again before continuing'));
  assert.equal(await page.locator('#step-pane-2').isVisible(), true);
  assert.match(await page.locator('#step-error-alert').innerText(), /try again before continuing/);
  studentIdState = 'available';
  await page.locator('#student_id').fill('2025-2-000065');
  await page.waitForFunction(() => document.getElementById('student-id-duplicate-feedback').textContent.includes('is available'));
  await page.screenshot({path: 'design-previews/auth-live-student-profile.png', fullPage: true});
  await page.locator('#step-pane-2 [data-next]').click();
  await page.locator('#step-pane-3').waitFor({state: 'visible'});
  assert.match(await page.locator('#student-proof-label').innerText(), /Certificate of Registration/);
  assert.equal(await page.locator('#reg-student-proof').getAttribute('required'), '');
  await verifyPasswordGuidance('student');
  await page.locator('#step-pane-3 [data-back]').click();
  await page.locator('#year_level').selectOption('Alumnus / Graduated');
  await page.locator('#step-pane-2 [data-next]').click();
  await page.locator('#step-pane-3').waitFor({state: 'visible'});
  assert.match(await page.locator('#student-proof-label').innerText(), /KLD diploma/);
  await page.screenshot({path: 'design-previews/auth-live-alumni-verification.png', fullPage: true});
  await page.locator('#btn-submit-registration').click();
  assert.match(await page.locator('#step-error-alert').innerText(), /diploma/);
  assert.equal(await page.locator('#student_id').inputValue(), '2025-2-000065');
  await page.locator('#step-pane-3 [data-back]').click();
  await page.locator('#step-pane-2 [data-back]').click();
  assert.equal(await page.locator('#reg-first-name').inputValue(), 'Sample');
  await page.locator('#reg-email').fill('invalid-email');
  await page.locator('[data-next]').first().click();
  assert.match(await page.locator('#step-error-alert').innerText(), /valid email/);

  await visit('/register.php?role=employer');
  await fillDetails(true);
  await page.locator('#reg-email').fill('office@kld.edu.ph');
  await page.locator('[data-next]').first().click();
  assert.match(await page.locator('#step-error-alert').innerText(), /company email/);
  await page.locator('#reg-email').fill('person@example.com');
  await page.locator('[data-next]').first().click();
  await page.locator('#step-pane-2').waitFor({state: 'visible'});
  await page.locator('#organization_name').fill('Example Company');
  await page.locator('#step-pane-2 [data-next]').click();
  await page.locator('#step-pane-3').waitFor({state: 'visible'});
  assert.equal(await page.locator('#reg-permit-photo').count(), 1);
  assert.equal(await page.locator('#reg-student-proof').count(), 0);
  await verifyPasswordGuidance('partner');

  const stableSwitches = [];
  for (const width of [375, 812, 1440, 1920]) {
    await page.setViewportSize({width, height: 1000});
    const positions = [];
    await visit('/login.php');
    for (const portal of ['student', 'partner']) {
      if (portal === 'partner') {
        await page.locator('#link-portal-login').click();
        await page.waitForURL('**/portal-login.php');
      }
      positions.push(await page.evaluate(() => {
        const top = selector => Math.round(document.querySelector(selector).getBoundingClientRect().top + scrollY);
        const button = document.querySelector('.auth-login-form .primary').getBoundingClientRect();
        const input = document.querySelector('#login-email').getBoundingClientRect();
        return {tabs: top('.audience-nav'), email: top('#login-email'), button: top('.auth-login-form .primary'), footer: top('footer'), compact: button.width < input.width * .8};
      }));
    }
    for (const key of ['tabs', 'email', 'button', 'footer']) assert.ok(Math.abs(positions[0][key] - positions[1][key]) <= 1, `${width}px ${key} shifts between portals: ${JSON.stringify(positions)}`);
    assert.ok(positions.every(position => position.compact), 'Sign-in buttons should be narrower than the inputs');
    stableSwitches.push(width);
  }
  const layouts = [];
  for (const path of ['/login.php', '/portal-login.php', '/register.php', '/register.php?role=employer']) {
    await visit(path);
    assert.equal(await page.locator('footer').count(), 1);
    assert.equal(await page.locator('footer').isVisible(), true);
    assert.match(await page.locator('footer').innerText(), /Campus Career Center/i);
    assert.equal(await page.locator('footer a[href="privacy.php"]').count(), 1);
    for (const dark of [false, true]) {
      await page.evaluate(dark => document.documentElement.dataset.theme = dark ? 'dark' : 'light', dark);
      for (const size of [{width: 1920, height: 1080}, {width: 1440, height: 1000}, {width: 375, height: 812}, {width: 812, height: 375}]) {
        await page.setViewportSize(size);
        await page.emulateMedia({reducedMotion: 'reduce'});
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth);
        assert.equal(overflow, false, `${path} ${JSON.stringify(size)} dark=${dark}`);
        if (size.width >= 1440) {
          const geometry = await page.evaluate(() => {
            const paper = document.querySelector('.paper').getBoundingClientRect();
            const story = document.querySelector('.story').getBoundingClientRect();
            const form = document.querySelector('.form-area').getBoundingClientRect();
            return {left: paper.left, right: paper.right, width: innerWidth, story: story.width, form: form.width};
          });
          assert.equal(geometry.left, 0);
          assert.equal(geometry.right, geometry.width);
          assert.ok(geometry.form > geometry.story * 1.5, 'Form receives more width than the supporting panel');
        }
        layouts.push({path, dark, width: size.width, overflow});
        if (size.width !== 812) {
          const name = path.includes('register') ? path.includes('employer') ? 'partner-register' : 'student-register' : path.includes('portal') ? 'partner-login' : 'student-login';
          await page.screenshot({path: `design-previews/auth-live-${name}-${dark ? 'dark' : 'light'}-${size.width}.png`, fullPage: true});
        }
      }
    }
  }
  assert.deepEqual(errors, []);
  assert.equal(submissions, 0);
  console.log(JSON.stringify({result: 'PASS', layoutChecks: layouts.length, stablePortalSwitches: stableSwitches, javascriptErrors: errors, realRegistrationSubmissions: submissions}, null, 2));
} finally { await browser.close(); }
