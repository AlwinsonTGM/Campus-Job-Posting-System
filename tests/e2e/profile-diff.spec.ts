import { test, expect } from '@playwright/test';
import { loginAsDemo } from './helpers/auth';

test.describe('Admin Profile Update Request Diff Modal', () => {
  test('displays real student comparison data and valid verification proof without dashes', async ({ page }) => {
    // 1. Login as Admin
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/users.php');
    await page.waitForLoadState('networkidle');

    // 2. Locate REQ-2 Inspect Diff button
    const inspectBtn = page.locator('button[data-bs-target="#inspectProfileReqModal2"]');
    await expect(inspectBtn).toBeVisible();
    await inspectBtn.click();

    // 3. Verify modal is visible
    const modal = page.locator('#inspectProfileReqModal2');
    await expect(modal).toBeVisible();

    // 4. Verify Student Name & ID in header
    await expect(modal.locator('.modal-title')).toContainText('#REQ-0002');
    await expect(modal).toContainText('Alwinson');
    await expect(modal).toContainText('2025-2-000065');

    // 5. Verify Diff table has real data and NO empty dashes for name/course
    const nameRow = modal.locator('tr:has-text("Full Name")');
    await expect(nameRow).toBeVisible();
    await expect(nameRow).toContainText('Alwinson Bustamante');
    await expect(nameRow).toContainText('Alwinson T. Bustamante');
    await expect(nameRow).toContainText('MODIFIED');

    const courseRow = modal.locator('tr:has-text("Degree Program / Course")');
    await expect(courseRow).toBeVisible();
    await expect(courseRow).toContainText('BS Information Systems (BSIS)');
    await expect(courseRow).toContainText('BS Computer Science (BSCS)');
    await expect(courseRow).toContainText('MODIFIED');

    // 6. Verify proof document is rendered and NOT "No Valid Document Found"
    await expect(modal).not.toContainText('No Valid Document Found');
    const proofViewer = modal.locator('img[alt="Student Proof Document"], .bi-file-earmark-pdf');
    await expect(proofViewer.first()).toBeVisible();

    // 7. Take screenshot of the fixed modal
    await page.waitForTimeout(300);
    await modal.locator('.modal-content').screenshot({ path: 'screenshots/admin-profile-diff-modal-fixed.png' });
  });
});
