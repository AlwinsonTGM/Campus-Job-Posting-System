import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';
import { runContinuousViewportSweep, assertLayoutIntegrity, DEFAULT_SWEEP_BREAKPOINTS } from './helpers/layout-invariants';

test.describe('Prototype Spike: Continuous Sliding Sweep & Responsive Invariants', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
    await loginAsDemo(page, 'student');
  });

  test('Student Dashboard: Continuous Sliding Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/student/dashboard.php', { waitUntil: 'domcontentloaded' });
    const sweep = await runContinuousViewportSweep(page, '/student/dashboard.php');

    console.log(`[Sweep Results] Student Dashboard tested across ${sweep.testedWidths.length} breakpoints.`);
    if (sweep.violations.length > 0) {
      console.warn(`[Violations Found] ${sweep.violations.length} layout violations:`, sweep.violations);
    }

    // Zero-overflow must hold on every single breakpoint
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
  });

  test('Student Jobs Directory: Continuous Sliding Sweep & Filter Responsiveness', async ({ page }) => {
    await page.goto('/student/jobs.php', { waitUntil: 'domcontentloaded' });
    const sweep = await runContinuousViewportSweep(page, '/student/jobs.php');

    console.log(`[Sweep Results] Student Jobs Directory tested across ${sweep.testedWidths.length} breakpoints.`);
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
  });

  test('Mobile (375px) Navigation Drawer & Interactive Flow', async ({ page }) => {
    // Set to canonical iPhone viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/student/dashboard.php', { waitUntil: 'domcontentloaded' });

    // Assert baseline zero-overflow
    const initialViolations = await assertLayoutIntegrity(page, 'Mobile 375px baseline');
    expect(initialViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // Check if mobile hamburger / toggle exists
    const menuToggle = page.locator('button.navbar-toggler, [data-bs-toggle="collapse"], [data-bs-toggle="offcanvas"], .sidebar-toggler, #sidebarToggle');
    if (await menuToggle.count() > 0 && await menuToggle.first().isVisible()) {
      await menuToggle.first().click();
      await page.waitForTimeout(300); // Allow drawer animation to expand

      // Assert opening the drawer did not create horizontal scrollbars on document
      const drawerViolations = await assertLayoutIntegrity(page, 'Mobile 375px with drawer opened');
      expect(drawerViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    }
  });

  test('Student Apply Flow: Mobile (375px) vs Tablet (768px) vs Desktop (1280px)', async ({ page }) => {
    for (const width of [375, 768, 1280]) {
      await page.setViewportSize({ width, height: 800 });
      await page.goto('/student/job-details.php?id=1', { waitUntil: 'domcontentloaded' });

      // Verify zero overflow
      const detailsViolations = await assertLayoutIntegrity(page, `Job Details at ${width}px`);
      expect(detailsViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // Verify apply button is visible and clickable
      const applyBtn = page.locator('a[href*="apply.php"], button:has-text("Apply")').first();
      if (await applyBtn.isVisible()) {
        await expect(applyBtn).toBeEnabled();
      }
    }
  });
});
