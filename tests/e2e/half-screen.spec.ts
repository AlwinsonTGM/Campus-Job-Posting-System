import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';

const halfScreenViewports = [
  { width: 768, height: 900, name: '768px (iPad/Narrow Half-screen)' },
  { width: 800, height: 900, name: '800px (1600px Half-screen)' },
  { width: 900, height: 900, name: '900px (1800px Half-screen)' },
  { width: 960, height: 900, name: '960px (1920px Half-screen)' },
  { width: 680, height: 900, name: '680px (1366px Half-screen)' },
];

test.beforeAll(async ({ browser }) => {
  const page = await browser.newPage();
  await resetDatastore(page);
  await page.close();
});

for (const vp of halfScreenViewports) {
  test.describe(`Half Screen Viewport checks: ${vp.name}`, () => {
    test.use({ viewport: { width: vp.width, height: vp.height } });

    test.beforeEach(async () => {
      test.setTimeout(60000);
    });

    test(`Student pages at ${vp.width}px`, async ({ page }) => {
      await loginAsDemo(page, 'student');

      // 1. Student Dashboard
      await page.goto('/student/dashboard.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/student_dashboard_${vp.width}.png`, fullPage: true });

      // Check overflow
      const overflow = await page.evaluate(() => {
        return document.documentElement.scrollWidth > window.innerWidth;
      });
      expect(overflow).toBe(false);

      // Check metric cards and icons
      const metrics = page.locator('.metric');
      const count = await metrics.count();
      for (let i = 0; i < count; i++) {
        const m = metrics.nth(i);
        const icon = m.locator('.icon-circle, i.bi');
        await expect(icon.first()).toBeVisible();
      }

      // 2. Student Jobs Directory
      await page.goto('/student/jobs.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/student_jobs_${vp.width}.png`, fullPage: true });

      // 3. Student My Applications
      await page.goto('/student/my-applications.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/student_myapps_${vp.width}.png`, fullPage: true });

      // 4. Student Job Details
      await page.goto('/student/job-details.php?id=1', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/student_jobdetails_${vp.width}.png`, fullPage: true });

      // 5. Student Apply
      await page.goto('/student/apply.php?id=1', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/student_apply_${vp.width}.png`, fullPage: true });
    });

    test(`Employer pages at ${vp.width}px`, async ({ page }) => {
      await loginAsDemo(page, 'employer');

      // 1. Employer Dashboard
      await page.goto('/employer/dashboard.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/employer_dashboard_${vp.width}.png`, fullPage: true });

      // Check overflow
      const overflow = await page.evaluate(() => {
        return document.documentElement.scrollWidth > window.innerWidth;
      });
      expect(overflow).toBe(false);

      // 2. Employer Applicants
      await page.goto('/employer/applicants.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/employer_applicants_${vp.width}.png`, fullPage: true });

      // 3. Employer Create Job
      await page.goto('/employer/create-job.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/employer_createjob_${vp.width}.png`, fullPage: true });

      // 4. Employer Edit Job
      await page.goto('/employer/edit-job.php?id=1', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/employer_editjob_${vp.width}.png`, fullPage: true });

      // 5. Employer Review App
      await page.goto('/employer/review-app.php?id=1', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/employer_reviewapp_${vp.width}.png`, fullPage: true });

      // 6. Employer Updates & Post Announcement Modal
      await page.goto('/employer/updates.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/employer_updates_${vp.width}.png`, fullPage: true });

      const postAnnouncementBtn = page.locator('button[data-bs-target="#newEmployerDispatchModal"]');
      if (await postAnnouncementBtn.isVisible()) {
        await postAnnouncementBtn.click();
        await page.waitForSelector('#newEmployerDispatchModal.show', { state: 'visible' });
        await page.waitForTimeout(500);
        await page.screenshot({ path: `test-results/screenshots/employer_updates_modal_${vp.width}.png` });
        const closeBtn = page.locator('#newEmployerDispatchModal button[data-bs-dismiss="modal"]').first();
        await closeBtn.click();
        await page.waitForTimeout(300);
      }
    });

    test(`Admin pages at ${vp.width}px`, async ({ page }) => {
      await loginAsDemo(page, 'admin');

      // 1. Admin Reports
      await page.goto('/admin/reports.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/admin_reports_${vp.width}.png`, fullPage: true });

      // 2. Admin Users
      await page.goto('/admin/users.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/admin_users_${vp.width}.png`, fullPage: true });

      // 3. Admin Categories
      await page.goto('/admin/categories.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/admin_categories_${vp.width}.png`, fullPage: true });

      // 4. Admin Updates
      await page.goto('/admin/updates.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/admin_updates_${vp.width}.png`, fullPage: true });
    });

    test(`Common & Public pages at ${vp.width}px`, async ({ page }) => {
      // 1. Settings
      await loginAsDemo(page, 'student');
      await page.goto('/settings.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/settings_${vp.width}.png`, fullPage: true });

      // 2. Landing page
      await page.goto('/index.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/landing_${vp.width}.png`, fullPage: true });

      // 3. About
      await page.goto('/about-us.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/about_${vp.width}.png`, fullPage: true });

      // 4. FAQs
      await page.goto('/faqs.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/faqs_${vp.width}.png`, fullPage: true });

      // 5. Updates
      await page.goto('/updates.php', { waitUntil: 'domcontentloaded' });
      await page.screenshot({ path: `test-results/screenshots/updates_${vp.width}.png`, fullPage: true });
    });
  });
}
