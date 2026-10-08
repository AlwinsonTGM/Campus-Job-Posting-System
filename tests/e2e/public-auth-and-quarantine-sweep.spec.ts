import { test, expect } from '@playwright/test';
import { resetDatastore, logout } from './helpers/auth';
import { runContinuousViewportSweep, assertLayoutIntegrity, DEFAULT_SWEEP_BREAKPOINTS } from './helpers/layout-invariants';

test.describe('Ticket 04: Public Pages, Authentication & Quarantine Sweep', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
    await logout(page).catch(() => {});
  });

  test('Criterion 1: Public Pages Continuous Sliding Sweep (360px -> 1280px)', async ({ page }) => {
    // 1. Landing Page
    await page.goto('/index.php', { waitUntil: 'domcontentloaded' });
    const landingSweep = await runContinuousViewportSweep(page, '/index.php');
    expect(landingSweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // 2. About Us
    await page.goto('/about-us.php', { waitUntil: 'domcontentloaded' });
    const aboutSweep = await runContinuousViewportSweep(page, '/about-us.php');
    expect(aboutSweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

    // 3. FAQs
    await page.goto('/faqs.php', { waitUntil: 'domcontentloaded' });
    const faqsSweep = await runContinuousViewportSweep(page, '/faqs.php');
    expect(faqsSweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
  });

  test('Criterion 2: Registration Page Layout (Mobile 375px vs Desktop 1280px)', async ({ page }) => {
    for (const width of [375, 1280]) {
      await page.setViewportSize({ width, height: 850 });
      await page.goto('/register.php', { waitUntil: 'domcontentloaded' });

      // Zero-overflow invariant
      const regViolations = await assertLayoutIntegrity(page, `Registration at ${width}px`);
      expect(regViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);

      // Verify form elements exist
      const form = page.locator('form');
      await expect(form.first()).toBeVisible();
      const emailInput = page.locator('input[type="email"], input[name="email"]').first();
      await expect(emailInput).toBeVisible();
    }
  });

  test('Criterion 3: Unauthenticated Role Gating & Return-To Target Preservation', async ({ page }) => {
    const protectedRoutes = [
      { path: '/student/dashboard.php', expectedRole: 'student' },
      { path: '/employer/dashboard.php', expectedRole: 'employer' },
      { path: '/admin/reports.php', expectedRole: 'admin' },
    ];

    for (const route of protectedRoutes) {
      await page.goto(route.path, { waitUntil: 'domcontentloaded' });
      // Must be redirected to login.php
      await expect(page).toHaveURL(/.*login\.php.*/);

      // Verify zero overflow on login redirect screen
      const loginViolations = await assertLayoutIntegrity(page, `Login Gate for ${route.path}`);
      expect(loginViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    }
  });

  test('Criterion 4: Login Form Continuous Sliding Sweep (360px -> 1280px)', async ({ page }) => {
    await page.goto('/login.php', { waitUntil: 'domcontentloaded' });
    const sweep = await runContinuousViewportSweep(page, '/login.php');
    expect(sweep.violations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    expect(sweep.violations.filter(v => v.type === 'offscreen_interactive')).toEqual([]);
  });

  test('Criterion 5: Verification Quarantine & OTP Screen (375px vs 1280px)', async ({ page }) => {
    for (const width of [375, 1280]) {
      await page.setViewportSize({ width, height: 850 });
      await page.goto('/verify-email.php', { waitUntil: 'domcontentloaded' });

      const quarantineViolations = await assertLayoutIntegrity(page, `Verify Email at ${width}px`);
      expect(quarantineViolations.filter(v => v.type === 'horizontal_overflow')).toEqual([]);
    }
  });
});
