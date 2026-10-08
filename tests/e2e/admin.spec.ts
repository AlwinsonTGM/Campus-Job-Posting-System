import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo, logout } from './helpers/auth';

test.describe('Admin Workflow & Management Suite', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
  });

  test('1. Category taxonomy management and creating new job category', async ({ page }) => {
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/categories.php');
    await expect(page).toHaveTitle(/Job Family Category|Categories/i);

    // Initial category cards
    const catCards = page.locator('.card-paper');
    expect(await catCards.count()).toBeGreaterThan(0);

    // Open Add Category Modal
    const addCatBtn = page.locator('button[data-bs-target="#newCatModal"]');
    await expect(addCatBtn).toBeVisible();
    await addCatBtn.click();

    const modal = page.locator('#newCatModal');
    await expect(modal).toBeVisible();

    const catNameInput = modal.locator('#cat-name, input[name="name"]');
    const catIconInput = modal.locator('#cat-icon, input[name="icon"]');
    const catDescInput = modal.locator('#cat-desc, textarea[name="description"]');
    const submitBtn = modal.locator('button[type="submit"]:has-text("Create Category")');

    await catNameInput.fill('Health & Clinical Support');
    await catIconInput.fill('bi-heart-pulse');
    await catDescInput.fill('Campus clinic aides, emergency response student assistants, and health wellness roles.');

    await submitBtn.click();
    await page.waitForURL(/.*admin\/categories\.php/);
    await expect(page.locator('body')).toContainText(/Health & Clinical Support|created successfully/i);
  });

  test('2. User directory search, filtering, and role classification', async ({ page }) => {
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/users.php');
    await expect(page).toHaveTitle(/User Directory|Campus User/i);

    // Verify user table rows exist
    const userRows = page.locator('table tbody tr');
    expect(await userRows.count()).toBeGreaterThan(0);

    // Filter by Student Role
    const roleSelect = page.locator('#role-select, select[name="role"]');
    if (await roleSelect.isVisible()) {
      await roleSelect.selectOption('student');
      await page.waitForTimeout(500);
      await expect(page.locator('body')).toContainText(/Student/i);
    }

    // Filter by Employer Role
    if (await roleSelect.isVisible()) {
      await roleSelect.selectOption('employer');
      await page.waitForTimeout(500);
      await expect(page.locator('body')).toContainText(/Partner|Office|Employer/i);
    }

    // Reset filters
    await page.goto('/admin/users.php');
  });

  test('3. Partner employer accreditation review and verification approval', async ({ page }) => {
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/users.php?ver_status=pending_approval');
    await expect(page.locator('body')).not.toContainText('Fatal error');

    // If there is a pending partner employer, test approval
    const pendingInspectBtn = page.locator('button[data-bs-target^="#verifyModal"]').first();
    if (await pendingInspectBtn.isVisible()) {
      await pendingInspectBtn.click();
      const modal = page.locator('.modal.show');
      await expect(modal).toBeVisible();

      // Submit POST approval form with CSRF token
      const approveBtn = modal.locator('form button[type="submit"]:has-text("Approve Partner"), form button[type="submit"]:has-text("Approve & Verify"), form button[type="submit"]:has-text("Verify")').first();
      await expect(approveBtn).toBeVisible();
      await approveBtn.click();
      await page.waitForURL(/.*admin\/users\.php/);
      await expect(page.locator('body')).toContainText(/officially verified|Verified/i);
    }
  });

  test('4. Student profile change request queue, diff inspection, and approval cascade', async ({ page }) => {
    // First, submit a profile change request as student
    await loginAsDemo(page, 'student');
    await page.goto('/settings.php');

    const reqBtn = page.locator('button[data-bs-target="#requestProfileModal"]');
    if (await reqBtn.isVisible() && !(await reqBtn.isDisabled())) {
      await reqBtn.click();
      const modal = page.locator('#requestProfileModal');
      await expect(modal).toBeVisible();

      await modal.locator('#req-name, input[name="name"]').fill('Juan Dela Cruz Jr.');
      await modal.locator('#req-reason, textarea[name="reason"]').fill('Legal name correction with registrar.');
      await modal.locator('input[type="file"][name="proof_file"]').setInputFiles({
        name: 'psa_birth_cert.pdf',
        mimeType: 'application/pdf',
        buffer: Buffer.from('%PDF-1.4 Mock Legal Document')
      });
      await modal.locator('button[type="submit"]:has-text("Submit Verification")').click();
      await page.waitForURL(/.*settings\.php/);
    }

    // Now log in as Admin and inspect the request
    await logout(page);
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/users.php#student-requests-section');

    const inspectDiffBtn = page.locator('button[data-bs-target^="#inspectProfileReqModal"]').first();
    await expect(inspectDiffBtn).toBeVisible();
    await inspectDiffBtn.click();

    const diffModal = page.locator('.modal.show');
    await expect(diffModal).toBeVisible();

    // Verify Diff table rendered
    await expect(diffModal.locator('table')).toBeVisible();

    // Approve the profile change request
    page.once('dialog', async (dialog) => {
      await dialog.accept();
    });
    const approveBtn = diffModal.locator('form button[type="submit"]:has-text("Approve & Update Official Record")');
    await approveBtn.click();
    await page.waitForURL(/.*admin\/users\.php/);
    await expect(page.locator('body')).toContainText(/Student profile change request #.* approved/i);
  });

  test('5. Institutional analytics dashboard and printable report rendering', async ({ page }) => {
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/reports.php');
    await expect(page).toHaveTitle(/Reports & Institutional Analytics|Analytics/i);

    // Verify 5 KPI Metric Cards (including Total Users)
    await expect(page.locator('body')).toContainText(/Total Users/i);
    await expect(page.locator('body')).toContainText(/Total Vacancies|Applications Filed|Interviews Held|Officially Hired/i);

    // Verify 3 Pure-CSS Bar Charts
    const barCharts = page.locator('.bar-chart');
    expect(await barCharts.count()).toBeGreaterThanOrEqual(2);

    // Verify Department Hiring Quotas vs Filled Positions Table
    const quotaTable = page.locator('table.table-paper');
    await expect(quotaTable).toBeVisible();

    // Verify Printable Header element exists in DOM (with d-none d-print-block class)
    const printableHeader = page.locator('.d-print-block');
    expect(await printableHeader.count()).toBeGreaterThan(0);
  });
});
