import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';
import { runContinuousViewportSweep, assertLayoutIntegrity, DEFAULT_SWEEP_BREAKPOINTS } from './helpers/layout-invariants';

test.describe('Phase 3: Auxiliary Features, Settings & Public Legal Continuous Sweep', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
  });

  test('Criterion 1: Central Notification Center & Dropdown Continuous Sweep (360px -> 1280px)', async ({ page }) => {
    await loginAsDemo(page, 'student');
    await page.goto('/notifications.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*notifications\.php/);

    // Verify Notification Center Header
    await expect(page.locator('body')).toContainText(/Notification Center|Notifications/i);

    // Continuous 11-Breakpoint Viewport Sweep on Notifications Center
    const sweep = await runContinuousViewportSweep(page, '/notifications.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);

    // Check Unread filter tab
    await page.goto('/notifications.php?filter=unread', { waitUntil: 'domcontentloaded' });
    const unreadViolations = await assertLayoutIntegrity(page, 'Notifications Unread Tab');
    expect(unreadViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // Desktop Notification Dropdown Menu Check
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/student/dashboard.php', { waitUntil: 'domcontentloaded' });
    const notifBtn = page.locator('#navNotificationDropdown');
    if (await notifBtn.isVisible()) {
      await notifBtn.click();
      const dropdownMenu = page.locator('.notification-dropdown-menu');
      await expect(dropdownMenu).toBeVisible();

      // Verify dropdown doesn't push the page beyond 100vw
      const dropdownViolations = await assertLayoutIntegrity(page, 'Desktop Notification Dropdown');
      expect(dropdownViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    }
  });

  test('Criterion 2: Account Settings & Availability Matrix Continuous Sweep (360px -> 1280px)', async ({ page }) => {
    await loginAsDemo(page, 'student');
    await page.goto('/settings.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*settings\.php/);

    // Verify settings panels exist
    await expect(page.locator('body')).toContainText(/Account Settings|Profile|Availability/i);

    // Continuous 11-Breakpoint Viewport Sweep on Settings Page
    const sweep = await runContinuousViewportSweep(page, '/settings.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);

    // Check Profile Change Request Modal at 375px
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/settings.php', { waitUntil: 'domcontentloaded' });
    const reqBtn = page.locator('button[data-bs-target="#requestProfileModal"]');
    if (await reqBtn.isVisible() && !(await reqBtn.isDisabled())) {
      await reqBtn.click();
      const modal = page.locator('#requestProfileModal');
      await expect(modal).toBeVisible();

      const modalViolations = await assertLayoutIntegrity(page, 'Profile Request Modal 375px');
      expect(modalViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    }
  });

  test('Criterion 3: Public FAQs & Accordions Continuous Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/faqs.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*faqs\.php/);

    // Verify FAQ accordion exists
    const accordionBtn = page.locator('.faq-accordion-btn, .accordion-button').first();
    await expect(accordionBtn).toBeVisible();

    // Continuous 11-Breakpoint Viewport Sweep on FAQs
    const sweep = await runContinuousViewportSweep(page, '/faqs.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);

    // Expand accordion item and verify layout invariant
    await accordionBtn.click();
    await page.waitForTimeout(300);
    const expandViolations = await assertLayoutIntegrity(page, 'Expanded FAQ Item');
    expect(expandViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
  });

  test('Criterion 4: Public Legal Documents (Terms & Privacy) Continuous Sweep (360px -> 1280px)', async ({ page }) => {
    // 1. Terms of Service
    await page.goto('/terms.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*terms\.php/);
    await expect(page.locator('body')).toContainText(/Terms of Service|Terms and Conditions/i);

    const termsSweep = await runContinuousViewportSweep(page, '/terms.php');
    expect(termsSweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(termsSweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);

    // 2. Privacy Policy
    await page.goto('/privacy.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*privacy\.php/);
    await expect(page.locator('body')).toContainText(/Privacy Policy|Data Protection/i);

    const privacySweep = await runContinuousViewportSweep(page, '/privacy.php');
    expect(privacySweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(privacySweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);
  });

  test('Criterion 5: About Us Institutional Page Continuous Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/about-us.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/.*about-us\.php/);
    await expect(page.locator('body')).toContainText(/About|Mission|Vision|Campus Hire/i);

    const sweep = await runContinuousViewportSweep(page, '/about-us.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);
  });
});
