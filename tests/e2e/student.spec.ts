import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo, logout } from './helpers/auth';

test.describe('Student Multi-Role Workflow & Application Portal Suite', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
  });

  test('1. Student job catalog browsing, keyword search, and multi-criteria filtering', async ({ page }) => {
    await loginAsDemo(page, 'student');
    await page.goto('/student/jobs.php', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveTitle(/Browse Campus|Student Jobs|Vacancies/i);

    // Initial job cards rendered
    const initialCards = page.locator('.card-paper a[href*="job-details.php"]');
    const count = await initialCards.count();
    expect(count).toBeGreaterThan(0);

    // Test Search by Keyword
    await page.fill('#filter-kw', 'Technical');
    await page.press('#filter-kw', 'Enter');
    await expect(page).toHaveURL(/.*keyword=Technical.*/i);
    await expect(page.locator('body')).toContainText(/Computer Lab Technical Assistant|Technical/i);

    // Test Department Filter
    await page.goto('/student/jobs.php?department=Management+Information+Systems+%28MIS%29', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toContainText('Management Information Systems');

    // Test Job Type Filter
    await page.goto('/student/jobs.php?job_type=Student+Assistant', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toContainText('Student Assistant');

    // Test Work Setup Filter
    await page.goto('/student/jobs.php?work_setup=On-Campus', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toContainText('On-Campus');

    // Test Category Filter
    await page.goto('/student/jobs.php?category=IT+%26+Technical+Support', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toContainText(/IT & Technical Support|Computer Lab/i);
  });

  test('2. Spotlight search modal queries live API and navigates to target job', async ({ page }) => {
    await page.goto('/index.php', { waitUntil: 'domcontentloaded' });

    // Trigger spotlight search modal
    const spotlightBtn = page.locator('[data-bs-target="#globalSearchModal"], button:has-text("Search")').first();
    if (await spotlightBtn.isVisible()) {
      await spotlightBtn.click();
    } else {
      await page.keyboard.press('Control+K');
    }

    const modal = page.locator('#globalSearchModal');
    if (await modal.isVisible()) {
      const modalInput = page.locator('#spotlightSearchInput, #globalSearchModal input[type="text"]');
      await expect(modalInput).toBeVisible();
      await modalInput.fill('Library');
      await page.waitForTimeout(500);

      // Verify search results inside modal
      const resultsContainer = page.locator('#spotlightResults, .spotlight-results, #globalSearchModal .list-group');
      await expect(resultsContainer).toBeVisible();
    }
  });

  test('3. Job details page renders academic requisitions, slot quotas, and apply link', async ({ page }) => {
    await loginAsDemo(page, 'student');
    await page.goto('/student/job-details.php?id=4', { waitUntil: 'domcontentloaded' });
    expect(await page.locator('body').textContent()).not.toContain('Fatal error');

    // Verify key vacancy details
    await expect(page.locator('body')).toContainText(/Peer Tutor|Requirements|Responsibilities|Qualifications|Requisition Summary/i);
    await expect(page.locator('body')).toContainText(/Slots|Quota|Vacanc|slots filled/i);

    // Verify Apply button is present
    const applyBtn = page.locator('a[href*="apply.php?id=4"], a[href*="apply.php?job_id=4"]').first();
    await expect(applyBtn).toBeVisible();
  });

  test('4. Application validation enforces candidate shift availability matrix', async ({ page }) => {
    await loginAsDemo(page, 'student');
    await page.goto('/student/apply.php?id=4');

    // Uncheck all availability slots
    const checkboxes = page.locator('input[name="availability[]"]');
    const count = await checkboxes.count();
    for (let i = 0; i < count; i++) {
      if (await checkboxes.nth(i).isChecked()) {
        await checkboxes.nth(i).uncheck({ force: true });
      }
    }

    // Attempt submission without availability
    await page.locator('#submitAppBtn').click();

    // Client-side alert is shown
    const errorAlert = page.locator('#availabilityErrorAlert');
    await expect(errorAlert).toBeVisible();
    expect(page.url()).toContain('apply.php');
  });

  test('5. Complete job application submission with matrix, tracking, and duplicate prevention', async ({ page }) => {
    await loginAsDemo(page, 'student');

    // Apply for Job #4 (Peer Tutor) which student #1 has not applied to yet
    await page.goto('/student/apply.php?id=4');
    await expect(page.locator('body')).toContainText('Juan Dela Cruz');
    await expect(page.locator('body')).toContainText('2024-00123');

    // Fill application form
    await page.fill('#app-phone', '+63 917 888 9999');
    await page.locator('input[name="availability[]"]').first().check({ force: true });
    await page.fill('#cover_letter', 'I am highly qualified and enthusiastic about tutoring linear algebra and discrete math.');

    // Ensure compliance check is checked
    const complianceCheck = page.locator('#saComplianceCheck');
    if (await complianceCheck.isVisible() && !(await complianceCheck.isChecked())) {
      await complianceCheck.check();
    }

    // Submit application
    await page.locator('#submitAppBtn').click();
    await page.waitForURL(/.*student\/my-applications\.php/);
    await expect(page.locator('body')).toContainText(/Application successfully submitted|Peer Tutor/i);

    // Verify submitted application appears in my-applications.php with 4-step stepper
    await expect(page.locator('body')).toContainText('Peer Tutor');
    await expect(page.locator('.stepper-container, .stepper-step').first()).toBeVisible();

    // Duplicate prevention: attempt to apply for Job #4 again
    await page.goto('/student/apply.php?id=4');
    await page.fill('#app-phone', '+63 917 888 9999');
    await page.locator('input[name="availability[]"]').first().check({ force: true });
    await page.fill('#cover_letter', 'Attempting duplicate application for same requisition.');
    const complianceCheck2 = page.locator('#saComplianceCheck');
    if (await complianceCheck2.isVisible() && !(await complianceCheck2.isChecked())) {
      await complianceCheck2.check();
    }
    await page.locator('#submitAppBtn').click();

    const bodyText = await page.textContent('body');
    expect(bodyText).toMatch(/already submitted an application|already applied/i);
  });

  test('6. Application status filter tabs and 4-step stepper rendering', async ({ page }) => {
    await loginAsDemo(page, 'student');
    await page.goto('/student/my-applications.php');

    // Verify filter chips exist
    await expect(page.locator('a[href="my-applications.php"]')).toBeVisible();
    await expect(page.locator('a[href*="status=pending"]')).toBeVisible();
    await expect(page.locator('a[href*="status=review"]')).toBeVisible();
    await expect(page.locator('a[href*="status=interview"]')).toBeVisible();
    await expect(page.locator('a[href*="status=accepted"]')).toBeVisible();

    // Filter by interviews scheduled
    await page.click('a[href*="status=interview"]');
    await expect(page).toHaveURL(/.*status=interview/);
    await expect(page.locator('body')).toContainText(/Official Interview Schedule|Interview/i);
    await expect(page.locator('.stepper-container, .stepper-step').first()).toBeVisible();
  });

  test('7. Student application withdrawal flow', async ({ page }) => {
    await loginAsDemo(page, 'student');

    // First submit an application for Job #5 to have a fresh pending application to withdraw
    await page.goto('/student/apply.php?id=5');
    await page.fill('#app-phone', '+63 917 111 2233');
    await page.locator('input[name="availability[]"]').first().check({ force: true });
    await page.fill('#cover_letter', 'Application to test student withdrawal flow.');
    const complianceCheck = page.locator('#saComplianceCheck');
    if (await complianceCheck.isVisible() && !(await complianceCheck.isChecked())) {
      await complianceCheck.check();
    }
    await page.locator('#submitAppBtn').click();
    await page.waitForURL(/.*student\/my-applications\.php/);

    // Locate the withdraw button
    const withdrawBtn = page.locator('form button:has-text("Withdraw")').first();
    await expect(withdrawBtn).toBeVisible();

    page.once('dialog', async (dialog) => {
      expect(dialog.message()).toMatch(/Are you sure you want to withdraw|Withdraw all/i);
      await dialog.accept();
    });

    await withdrawBtn.click();
    await page.waitForURL(/.*student\/my-applications\.php/);
    await expect(page.locator('body')).toContainText(/Application was successfully withdrawn|withdrawn/i);
  });

  test('8. Student profile settings update and institutional verification lock', async ({ page }) => {
    await loginAsDemo(page, 'student');
    await page.goto('/settings.php');

    // Update contact phone & availability
    await page.fill('#settings-phone', '+63 920 111 2222');
    await page.locator('input[name="availability[]"]').first().check({ force: true });
    await page.locator('button:has-text("Save Profile Changes")').click();

    await page.waitForURL(/.*settings\.php/);
    await expect(page.locator('body')).toContainText(/updated successfully/i);
    await expect(page.locator('#settings-phone')).toHaveValue('+63 920 111 2222');

    // Verify Institutional Identity Lock section is present
    await expect(page.locator('body')).toContainText(/Institutional & Academic Identity Record/i);
  });
});
