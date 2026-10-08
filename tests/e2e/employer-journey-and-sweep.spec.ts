import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';
import { runContinuousViewportSweep, assertLayoutIntegrity, DEFAULT_SWEEP_BREAKPOINTS } from './helpers/layout-invariants';

test.describe('Ticket 03: Employer Requisition Lifecycle & Candidate Pipeline Sweep', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
    await loginAsDemo(page, 'employer');
  });

  test('Criterion 1 & 2: Employer Dashboard & Vacancies Continuous Sliding Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/employer/dashboard.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*employer\/dashboard\.php/);

    // Verify key metrics exist
    await expect(page.locator('body')).toContainText(/Dashboard|Active Requisitions|Applicants/i);

    // Execute continuous stepped sweep
    const sweep = await runContinuousViewportSweep(page, '/employer/dashboard.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);
  });

  test('Criterion 3: Job Requisition Creation Form Layout (Mobile 375px vs Desktop 1280px)', async ({ page }) => {
    for (const width of [375, 1280]) {
      await page.setViewportSize({ width, height: 850 });
      await page.goto('/employer/create-job.php', { waitUntil: 'domcontentloaded' });

      // Zero-overflow invariant
      const formViolations = await assertLayoutIntegrity(page, `Create Job at ${width}px`);
      expect(formViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // Verify form inputs exist and are interactable
      const titleInput = page.locator('#job-title, input[name="title"]').first();
      await expect(titleInput).toBeVisible();
      const descInput = page.locator('#job-desc, textarea[name="description"]').first();
      await expect(descInput).toBeVisible();
    }
  });

  test('Criterion 4: Applicant Submissions Pipeline & Review Page', async ({ page }) => {
    for (const width of [375, 1280]) {
      await page.setViewportSize({ width, height: 850 });

      // 1. Applicants directory
      await page.goto('/employer/applicants.php', { waitUntil: 'domcontentloaded' });
      const listViolations = await assertLayoutIntegrity(page, `Applicants List at ${width}px`);
      expect(listViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // 2. Applicant review page
      await page.goto('/employer/review-app.php?id=1', { waitUntil: 'domcontentloaded' });
      const reviewViolations = await assertLayoutIntegrity(page, `Review App at ${width}px`);
      expect(reviewViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
      await expect(page.locator('body')).not.toContainText('Fatal error');
      await expect(page.locator('body')).toContainText(/Applicant|Evaluation|Candidate|Interview/i);
    }
  });

  test('Criterion 5: Employer Announcements Modal Responsive Fit', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/employer/updates.php', { waitUntil: 'domcontentloaded' });

    const pageViolations = await assertLayoutIntegrity(page, 'Employer Updates at 375px');
    expect(pageViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // Open Announcement Modal if trigger is available
    const postModalBtn = page.locator('button[data-bs-target="#newEmployerDispatchModal"], button:has-text("Post Announcement"), button:has-text("New Announcement")').first();
    if (await postModalBtn.isVisible()) {
      await postModalBtn.click();
      await page.waitForTimeout(400);

      // Verify modal is visible
      const modal = page.locator('#newEmployerDispatchModal.show, .modal.show');
      await expect(modal).toBeVisible();

      // Verify modal does not bleed offscreen
      const modalViolations = await assertLayoutIntegrity(page, 'Updates Modal Open at 375px');
      expect(modalViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // Close modal
      const closeBtn = page.locator('.modal.show button[data-bs-dismiss="modal"]').first();
      if (await closeBtn.isVisible()) {
        await closeBtn.click();
        await page.waitForTimeout(300);
      }
    }
  });
});
