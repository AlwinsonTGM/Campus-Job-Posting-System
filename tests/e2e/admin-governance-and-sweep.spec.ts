import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo, logout } from './helpers/auth';
import { runContinuousViewportSweep, assertLayoutIntegrity, DEFAULT_SWEEP_BREAKPOINTS } from './helpers/layout-invariants';

test.describe('Phase 2: Administrative Governance & Continuous Viewport Sweep', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
    await loginAsDemo(page, 'admin');
  });

  test('Criterion 1: Institutional Reports & Analytics Dashboard Continuous Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/admin/reports.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*admin\/reports\.php/);

    // Verify Executive Scorecard Metrics
    await expect(page.locator('body')).toContainText(/Total Users/i);
    await expect(page.locator('body')).toContainText(/Total Vacancies/i);
    await expect(page.locator('body')).toContainText(/Applications Filed/i);

    // Verify Visual Analytics Canvases
    const donutChart = page.locator('#categoryDonutChart');
    await expect(donutChart).toBeVisible();

    const barChart = page.locator('#departmentBarChart');
    await expect(barChart).toBeVisible();

    // Verify Print Media Header element exists in DOM
    const printableHeader = page.locator('.d-print-block');
    expect(await printableHeader.count()).toBeGreaterThan(0);

    // Continuous 11-Breakpoint Viewport Sweep
    const sweep = await runContinuousViewportSweep(page, '/admin/reports.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);
  });

  test('Criterion 2: Campus User Directory & Role Filtering Continuous Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/admin/users.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*admin\/users\.php/);

    // Verify user table rows exist
    const userRows = page.locator('table tbody tr');
    expect(await userRows.count()).toBeGreaterThan(0);

    // Filter by Student Role
    const roleSelect = page.locator('#role-select, select[name="role"]').first();
    if (await roleSelect.isVisible()) {
      await roleSelect.selectOption('student');
      await page.waitForTimeout(300);
      await expect(page.locator('body')).toContainText(/Student/i);
    }

    // Continuous 11-Breakpoint Viewport Sweep on Directory
    const sweep = await runContinuousViewportSweep(page, '/admin/users.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);
  });

  test('Criterion 3: Partner Employer Accreditation Review & Verification Modal', async ({ page }) => {
    for (const width of [375, 1280]) {
      await page.setViewportSize({ width, height: 850 });
      await page.goto('/admin/users.php?ver_status=pending_approval', { waitUntil: 'domcontentloaded' });

      // Layout check on filtered pending queue
      const queueViolations = await assertLayoutIntegrity(page, `Pending Queue at ${width}px`);
      expect(queueViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // If there is an inspect/verify modal button, open and verify modal fit
      const verifyModalBtn = page.locator('button[data-bs-target^="#verifyModal"]').first();
      if (await verifyModalBtn.isVisible()) {
        await verifyModalBtn.click();
        const modal = page.locator('.modal.show');
        await expect(modal).toBeVisible();

        // Verify modal does not cause page horizontal overflow
        const modalViolations = await assertLayoutIntegrity(page, `Verify Modal at ${width}px`);
        expect(modalViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

        // Close modal for next iteration
        const closeBtn = modal.locator('button[data-bs-dismiss="modal"]').first();
        if (await closeBtn.isVisible()) {
          await closeBtn.click();
          await page.waitForTimeout(300);
        }
      }
    }
  });

  test('Criterion 4: Category Taxonomy Management & Creation Sweep', async ({ page }) => {
    // Continuous 11-Breakpoint Viewport Sweep on Categories
    await page.goto('/admin/categories.php', { waitUntil: 'domcontentloaded' });
    const sweep = await runContinuousViewportSweep(page, '/admin/categories.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);

    // Category Creation Modal flow at Mobile (375px)
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/admin/categories.php', { waitUntil: 'domcontentloaded' });

    const addCatBtn = page.locator('button[data-bs-target="#newCatModal"]').first();
    await expect(addCatBtn).toBeVisible();
    await addCatBtn.click();

    const modal = page.locator('#newCatModal');
    await expect(modal).toBeVisible();

    // Verify modal responsive fit
    const modalViolations = await assertLayoutIntegrity(page, 'New Category Modal 375px');
    expect(modalViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // Fill form and create category
    const catNameInput = modal.locator('#cat-name, input[name="name"]').first();
    const catIconSelect = modal.locator('#cat-icon, select[name="icon"]').first();
    const catDescInput = modal.locator('#cat-desc, textarea[name="description"]').first();
    const submitBtn = modal.locator('button[type="submit"]:has-text("Create Category"), button#btn-create-cat').first();

    const uniqueCatName = `Clinical Diagnostics ${Date.now()}`;
    await catNameInput.fill(uniqueCatName);
    if (await catIconSelect.isVisible()) {
      await catIconSelect.selectOption('bi-heart-pulse');
    }
    if (await catDescInput.isVisible()) {
      await catDescInput.fill('Clinical health research and laboratory assistance positions.');
    }

    await submitBtn.click();
    await page.waitForURL(/.*admin\/categories\.php/);
    await expect(page.locator('body')).toContainText(uniqueCatName);
  });

  test('Criterion 5: Career Updates & Bulletins Dispatches Surface Sweep', async ({ page }) => {
    // Continuous 11-Breakpoint Viewport Sweep on Updates
    await page.goto('/admin/updates.php', { waitUntil: 'domcontentloaded' });
    const sweep = await runContinuousViewportSweep(page, '/admin/updates.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);

    // Check Archived tab
    await page.goto('/admin/updates.php?tab=archived', { waitUntil: 'domcontentloaded' });
    const archiveSweep = await runContinuousViewportSweep(page, '/admin/updates.php?tab=archived');
    expect(archiveSweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
  });
});
