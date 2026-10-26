import assert from 'node:assert/strict';
import { chromium } from '@playwright/test';

const base = process.env.CAMPUS_TEST_URL || 'http://localhost/final-campus-job-posting-system';
const browser = await chromium.launch({ channel: 'chrome', headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
const errors = [];
page.on('pageerror', error => errors.push(error.message));
const open = async route => {
  const response = await page.goto(`${base}/${route}`);
  assert.equal(response.status(), 200, route);
};
// Exercise live GET workflows and inspect submissions without mutating campus records.
await page.route('**/*', async route => {
  if (route.request().method() === 'POST') throw new Error(`Unexpected write: ${route.request().url()}`);
  await route.continue();
});
try {
  await open('login.php?demo=employer');
  assert.match(page.url(), /employer\/dashboard.php/);
  assert.equal(await page.locator('.employer-workspace-nav a').count(), 3);
  assert.equal(await page.locator('.paper-nav-search-wrap').count(), 0);
  assert.equal(await page.locator('main .page-head-actions a').count(), 1);
  assert.match(await page.locator('main .page-head-actions a').getAttribute('href'), /create-job.php/);

  for (const theme of ['dark', 'light']) {
    await page.evaluate(theme => localStorage.setItem('campus_hire_theme', theme), theme);
    for (const route of ['dashboard.php', 'applicants.php?job_id=2', 'updates.php']) {
      await open(`employer/${route}`);
      assert.equal(await page.locator('.employer-workspace-nav [aria-current=page]').count(), 1);
      for (const width of [375, 768, 992, 1200, 1440, 1920]) {
        await page.setViewportSize({ width, height: 1000 });
        const issues = await page.locator('main').evaluate(main => {
          const actions = [...main.querySelectorAll('.employer-actions')];
          return actions.flatMap(row => {
            const box = row.getBoundingClientRect();
            return [...row.querySelectorAll('.employer-button')].filter(button => {
              const rect = button.getBoundingClientRect();
              return rect.width < 43 || rect.height < 43 || rect.left < box.left - 1 || rect.right > box.right + 1;
            }).map(button => button.textContent.trim() || button.getAttribute('aria-label'));
          });
        });
        assert.deepEqual(issues, [], `${theme} ${route} ${width}: action geometry`);
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
      }
    }
  }

  await page.setViewportSize({ width: 375, height: 800 });
  await page.locator('.navbar-toggler').click();
  await page.locator('#navbarMain.show').waitFor();
  await page.locator('.employer-workspace-nav a[href$="applicants.php"]').click();
  assert.match(page.url(), /applicants.php/);
  await page.locator('#filter-results-container tbody tr').first().waitFor();
  const initialRows = await page.locator('#filter-results-container tbody tr').count();
  assert.ok(initialRows > 0, 'Live candidates are available for filter/navigation checks');
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.locator('#search-candidate').fill('no-such-candidate-employer-ui-check');
  await page.waitForURL(/q=no-such-candidate/);
  await page.getByRole('heading', { name: 'No applicants found' }).waitFor();
  await page.locator('.employer-filter-reset').click();
  await page.locator('#filter-results-container tbody tr').first().waitFor();
  assert.equal(await page.locator('#filter-results-container tbody tr').count(), initialRows);
  await page.locator('summary').click();
  await page.locator('#filter-sort').selectOption('name_asc');
  await page.waitForURL(/sort=name_asc/);
  await page.waitForFunction(() => document.querySelector('#filter-results-container').style.opacity === '1');
  const sortedNames = await page.locator('#filter-results-container tbody tr td:first-child > strong').allTextContents();
  assert.deepEqual(sortedNames, [...sortedNames].sort((a,b) => a.localeCompare(b)));
  await page.locator('#filter-status').selectOption('pending');
  await page.waitForURL(/status=pending/);
  await page.waitForFunction(() => document.querySelector('#filter-results-container').style.opacity === '1');
  const stages = await page.locator('#filter-results-container td[data-label="Application stage"]').allTextContents();
  assert.ok(stages.length && stages.every(stage => stage.trim() === 'Pending Review'));
  await page.locator('.employer-filter-reset').click();
  const review = page.locator('.employer-actions--applicants a[href*="review-app.php"]').first();
  await review.click();
  assert.match(page.url(), /review-app.php\?id=/);
  await page.locator('form input[name="csrf_token"]').first().waitFor({ state: 'attached' });
  assert.ok(await page.locator('form input[name="csrf_token"]').count());
  assert.ok(await page.locator('form [name="supervisor_notes"]').count());

  await open('employer/dashboard.php');
  const form = page.locator('.employer-actions form').first();
  assert.equal(await form.getAttribute('method'), 'POST');
  assert.equal(await form.locator('[name=action]').inputValue(), 'archive_job');
  assert.ok((await form.locator('[name=csrf_token]').inputValue()).length > 20);
  assert.ok(Number(await form.locator('[name=job_id]').inputValue()) > 0);
  await page.locator('.employer-actions a[href*="edit-job.php"]').first().click();
  assert.match(page.url(), /edit-job.php\?id=/);
  await page.locator('form [name="csrf_token"]').first().waitFor({ state: 'attached' });
  assert.ok(await page.locator('form [name="csrf_token"]').count());
  await open('employer/dashboard.php?tab=archived');
  assert.equal(await page.locator('.employer-filters [aria-current=page]').textContent().then(text => text.trim().startsWith('Archived')), true);
  assert.equal(await page.locator('button:has-text("Restore")').count(), 0, 'Employer cannot restore archived jobs');

  await open('employer/updates.php');
  assert.doesNotMatch(await page.locator('main').textContent(), /Auto-Approved|& Live/);
  assert.equal(await page.locator('#bulletins-title .bi').count(), 0);
  const emptyIcon = page.locator('.employer-empty > .bi-megaphone');
  if (await emptyIcon.count()) assert.equal(await emptyIcon.evaluate(el => getComputedStyle(el).backgroundColor), 'rgba(0, 0, 0, 0)');
  for (const width of [375, 1440]) {
    await page.setViewportSize({ width, height: 800 });
    await page.locator('button[data-bs-target="#newEmployerDispatchModal"]').first().click();
    await page.locator('#newEmployerDispatchModal.show').waitFor();
    const modal = page.locator('#newEmployerDispatchModal');
    assert.ok((await modal.locator('[name=csrf_token]').inputValue()).length > 20);
    assert.equal(await modal.locator('[name=action]').inputValue(), 'create');
    assert.equal(await modal.locator('[name=title]').getAttribute('required'), '');
    await modal.locator('button[data-bs-dismiss=modal]').first().click();
    await modal.waitFor({ state: 'hidden' });
  }

  for (const role of ['student', 'admin']) {
    await open(`login.php?demo=${role}`);
    assert.equal(await page.locator('.employer-workspace-nav').count(), 0, `${role} navbar retained`);
    assert.equal(await page.locator(role === 'student' ? '.paper-nav-search-wrap' : '.navbar-nav').count(), 1);
  }
  assert.deepEqual(errors, []);
  console.log('PASS: Employer navigation, 36 layouts, action sizing/alignment, live search/sort/status filters, review/edit links, CSRF-protected archive/publish forms, responsive modal, and student/admin navbar isolation. No records changed.');
} finally {
  await browser.close();
}
