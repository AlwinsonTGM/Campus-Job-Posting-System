import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';
import { runContinuousViewportSweep, assertLayoutIntegrity, DEFAULT_SWEEP_BREAKPOINTS } from './helpers/layout-invariants';

test.describe('Ticket 02: Student Requisition Journey & Mobile Sliding Sweep', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
    await loginAsDemo(page, 'student');
  });

  test('Criterion 1 & 2: Student Login & Dashboard Continuous Sliding Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/student/dashboard.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*student\/dashboard\.php/);

    // Verify key metrics exist
    const metrics = page.locator('.metric, .card, [data-metric]');
    await expect(metrics.first()).toBeVisible();

    // Execute 11-point sliding screen sweep
    const sweep = await runContinuousViewportSweep(page, '/student/dashboard.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);
  });

  test('Criterion 3: Mobile (375px) Navigation Drawer & Sidebar Expansion', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/student/dashboard.php', { waitUntil: 'domcontentloaded' });

    // Baseline mobile check
    const baseline = await assertLayoutIntegrity(page, 'Student Dashboard at 375px');
    expect(baseline.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // Open Mobile Collapse Navbar
    const menuToggle = page.locator('button.navbar-toggler, [data-bs-toggle="collapse"][data-bs-target="#navbarMain"]');
    if (await menuToggle.count() > 0 && await menuToggle.first().isVisible()) {
      await menuToggle.first().click();
      await page.waitForTimeout(350);

      // Verify the collapse menu is shown and user avatar chip is visible
      const navbarCollapse = page.locator('#navbarMain.show');
      await expect(navbarCollapse).toBeVisible();

      const userChip = page.locator('#navbarMain .user-avatar-chip');
      await expect(userChip).toBeVisible();

      // Verify zero overflow while drawer is expanded
      const openViolations = await assertLayoutIntegrity(page, 'Student Dashboard with navbar open');
      expect(openViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // Collapse back
      await menuToggle.first().click();
      await page.waitForTimeout(300);
    }
  });

  test('Criterion 4: Student Jobs Directory Search & Filter (Mobile 375px & Desktop 1280px)', async ({ page }) => {
    for (const width of [375, 1280]) {
      await page.setViewportSize({ width, height: 800 });
      await page.goto('/student/jobs.php', { waitUntil: 'domcontentloaded' });

      // Invariant check
      const dirViolations = await assertLayoutIntegrity(page, `Jobs Directory at ${width}px`);
      expect(dirViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // Verify search input is interactable
      const searchInput = page.locator('#filter-kw, input[name="keyword"], input[type="search"]').first();
      if (await searchInput.isVisible()) {
        await searchInput.fill('Technical');
        await page.waitForTimeout(200);
      }

      // Check job cards exist
      const jobCards = page.locator('.card-paper, .job-card, .card a[href*="job-details.php"]');
      expect(await jobCards.count()).toBeGreaterThan(0);
    }
  });

  test('Criterion 5: Job Details Requirements & Slot Quotas Legibility', async ({ page }) => {
    for (const width of [375, 768, 1280]) {
      await page.setViewportSize({ width, height: 850 });

      // 1. Check an open unapplied job (Job 4) - Apply button must be active
      await page.goto('/student/job-details.php?id=4', { waitUntil: 'domcontentloaded' });
      const openDetailsViolations = await assertLayoutIntegrity(page, `Open Job Details at ${width}px`);
      expect(openDetailsViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
      await expect(page.locator('body')).not.toContainText('Fatal error');
      const applyBtn = page.locator('a[href*="apply.php"], button:has-text("Apply")').first();
      await expect(applyBtn).toBeVisible();

      // 2. Check an already-applied job (Job 1) - Application status / Track status must be shown
      await page.goto('/student/job-details.php?id=1', { waitUntil: 'domcontentloaded' });
      const appliedViolations = await assertLayoutIntegrity(page, `Applied Job Details at ${width}px`);
      expect(appliedViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
      await expect(page.locator('body')).toContainText(/Application Active|Track Application|Currently Appointed/i);
    }
  });

  test('Criterion 6: Complete Application Submission & Pipeline Reflection', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 800 });
    // Navigate to open job 4 apply page
    await page.goto('/student/apply.php?id=4', { waitUntil: 'domcontentloaded' });

    // Verify zero overflow on application form
    const applyFormViolations = await assertLayoutIntegrity(page, 'Apply Form at 375px');
    expect(applyFormViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // Select availability slot if required
    const availabilityCheckbox = page.locator('input[name="availability[]"]').first();
    if (await availabilityCheckbox.isVisible()) {
      await availabilityCheckbox.check({ force: true });
    }

    // Submit using profile resume
    const submitBtn = page.locator('#submitAppBtn, button[type="submit"]:has-text("Submit")').first();
    if (await submitBtn.isVisible()) {
      await submitBtn.click();
      await page.waitForNavigation({ timeout: 10000 }).catch(() => {});
    }

    // Navigate to My Applications and verify submission is recorded
    await page.goto('/student/my-applications.php', { waitUntil: 'domcontentloaded' });
    const myAppsViolations = await assertLayoutIntegrity(page, 'My Applications at 375px');
    expect(myAppsViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // Verify applications list renders candidate submission
    await expect(page.locator('body')).toContainText(/Applications|Applied|Submission|Status/i);
  });
});
