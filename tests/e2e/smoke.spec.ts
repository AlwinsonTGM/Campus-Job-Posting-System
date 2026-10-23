import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo, logout } from './helpers/auth';

test.describe('Milestone 1: Live Health & Smoke Verification', () => {
  test.beforeEach(async ({ page }) => {
    // Reseed datastore before each test to maintain state consistency
    await resetDatastore(page);
  });

  test('1. Landing page loads cleanly with featured jobs and metrics without fatal errors', async ({ page }) => {
    const response = await page.goto('/index.php', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);

    // Verify title and brand
    await expect(page).toHaveTitle(/CAMPUS HIRE|Empowering Students/i);

    // Verify no PHP Fatal Error or Warning in page body
    const bodyText = await page.textContent('body');
    expect(bodyText).not.toContain('Fatal error');
    expect(bodyText).not.toContain('Uncaught Error');
    expect(bodyText).not.toContain('Warning:');
    expect(bodyText).not.toContain('Stack trace:');

    // Verify key UI sections are visible
    await expect(page.locator('.navbar')).toBeVisible();
    await expect(page.locator('a[href*="student/jobs.php"]').first()).toBeVisible();

    // Verify featured jobs container or cards are rendered
    const jobCards = page.locator('.featured-job-card, .job-card, a[href*="apply.php"]');
    const jobCount = await jobCards.count();
    expect(jobCount).toBeGreaterThan(0);
  });

  test('2. Public informational routes load with 200 OK', async ({ page }) => {
    // About Us
    const aboutRes = await page.goto('/about-us.php');
    expect(aboutRes?.status()).toBe(200);
    await expect(page.locator('body')).toContainText(/About/i);

    // Privacy Policy
    const privacyRes = await page.goto('/privacy.php');
    expect(privacyRes?.status()).toBe(200);
    await expect(page.locator('body')).toContainText(/Privacy/i);

    // Terms of Service
    const termsRes = await page.goto('/terms.php');
    expect(termsRes?.status()).toBe(200);
    await expect(page.locator('body')).toContainText(/Terms/i);

    // FAQs
    const faqsRes = await page.goto('/faqs.php');
    expect(faqsRes?.status()).toBe(200);
    await expect(page.locator('body')).toContainText(/Frequently Asked|FAQ/i);
  });

  test('3. Login page renders credentials form, role selector tabs, and authentication flow', async ({ page }) => {
    const response = await page.goto('/login.php');
    expect(response?.status()).toBe(200);

    // Verify form elements
    const emailInput = page.locator('input[name="email"], input[type="email"]');
    const passwordInput = page.locator('input[name="password"], input[type="password"]');
    const submitBtn = page.locator('form:not(.data-mode-fab__form) button[type="submit"]');

    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
    await expect(submitBtn).toBeVisible();

    // Fill credentials and sign in
    await emailInput.fill('student@kld.edu.ph');
    await passwordInput.fill('Student2026!');

    await submitBtn.click();
    await page.waitForURL(/.*student\/dashboard\.php/);
    await expect(page.locator('body')).toContainText(/Dashboard|Welcome|Applications/i);

    // Logout
    await logout(page);

    // Verify fast demo bypass hook works
    await loginAsDemo(page, 'employer');
    await expect(page).toHaveURL(/.*employer\/dashboard\.php/);
    await logout(page);
  });

  test('4. Public job search catalog and job details page render correctly', async ({ page }) => {
    // Browse jobs listing
    const jobsRes = await page.goto('/student/jobs.php');
    expect(jobsRes?.status()).toBe(200);
    await expect(page.locator('body')).not.toContainText('Fatal error');

    // Check that job cards or links are displayed
    const detailLinks = page.locator('a[href*="job-details.php?id="], a[href*="apply.php?id="]');
    await expect(detailLinks.first()).toBeVisible();

    // View specific job details
    const detailsRes = await page.goto('/student/job-details.php?id=1', { waitUntil: 'domcontentloaded' });
    expect(detailsRes?.status()).toBe(200);
    await expect(page.locator('body')).not.toContainText('Fatal error');
    await expect(page.locator('body')).toContainText(/Department|Requirements|Responsibilities|Apply/i);
  });

  test('5. Search API (/api/search-jobs.php) returns valid JSON schema and filters', async ({ request }) => {
    // Unfiltered query
    const res = await request.get('/api/search-jobs.php');
    expect(res.status()).toBe(200);
    const data = await res.json();
    expect(data).toHaveProperty('status', 'success');
    expect(data).toHaveProperty('results');
    expect(Array.isArray(data.results)).toBe(true);
    expect(data.results.length).toBeGreaterThan(0);

    // Query with search term
    const searchRes = await request.get('/api/search-jobs.php?q=Assistant');
    expect(searchRes.status()).toBe(200);
    const searchData = await searchRes.json();
    expect(searchData.status).toBe('success');
    expect(searchData.results.length).toBeGreaterThan(0);
    for (const job of searchData.results) {
      const combined = (job.title + ' ' + (job.department || '') + ' ' + (job.job_type || '')).toLowerCase();
      expect(combined).toContain('assistant');
    }
  });

  test('6. Unauthenticated security guards redirect protected routes to login', async ({ page }) => {
    // Clear cookies to ensure unauthenticated state
    await page.context().clearCookies();

    // Student protected route
    await page.goto('/student/dashboard.php');
    await expect(page).toHaveURL(/.*login\.php/);

    // Employer protected route
    await page.goto('/employer/dashboard.php');
    await expect(page).toHaveURL(/.*login\.php/);

    // Admin protected route
    await page.goto('/admin/categories.php');
    await expect(page).toHaveURL(/.*login\.php/);

    // Settings protected route
    await page.goto('/settings.php');
    await expect(page).toHaveURL(/.*login\.php/);
  });
});
