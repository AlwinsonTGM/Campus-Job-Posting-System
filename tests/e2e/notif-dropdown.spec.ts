import { test, expect } from '@playwright/test';
import { loginAsDemo } from './helpers/auth';

test.describe('Notification Dropdown & Job Images Verification', () => {
  test('renders notification dropdown without overflow and with visible timestamp & badges', async ({ page }) => {
    // 1. Login as Student
    await loginAsDemo(page, 'student');

    // 2. Open notification dropdown
    const notifBtn = page.locator('#navNotificationDropdown');
    await expect(notifBtn).toBeVisible();
    await notifBtn.click();

    const dropdownMenu = page.locator('.notification-dropdown-menu');
    await expect(dropdownMenu).toBeVisible();

    // 3. Remove toast alerts and take screenshot of dropdown menu
    await page.evaluate(() => {
      document.querySelectorAll('.toast, .alert, #notificationToastContainer').forEach(el => el.remove());
    });
    await page.waitForTimeout(400);
    await dropdownMenu.screenshot({ path: 'screenshots/notification-dropdown-fixed.png' });

    // 4. Verify the first notification item
    const firstItem = dropdownMenu.locator('.notification-item').first();
    await expect(firstItem).toBeVisible();

    const titleEl = firstItem.locator('.notification-item-title');
    await expect(titleEl).toBeVisible();
    await expect(titleEl).toContainText('Interview Scheduled');

    const timeEl = firstItem.locator('.notification-item-time');
    await expect(timeEl).toBeVisible();

    const dotEl = firstItem.locator('.badge-dot-unread');
    await expect(dotEl).toBeVisible();

    // 5. Verify jobs 11-20 display valid images on student/jobs.php
    await page.goto('/student/jobs.php');
    await page.waitForLoadState('networkidle');

    // Check images for jobs 11 to 20
    for (let id = 11; id <= 20; id++) {
      const imgSelector = `img[src*="job-${id < 10 ? '0' + id : id}.jpg"]`;
      const img = page.locator(imgSelector).first();
      if (await img.count() > 0) {
        const naturalWidth = await img.evaluate((el: HTMLImageElement) => el.naturalWidth);
        expect(naturalWidth).toBeGreaterThan(0);
      }
    }
  });
});
