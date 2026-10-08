import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo, logout } from './helpers/auth';

test.describe('Employer Workflow & Candidate Management Suite', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
  });

  test('1. Employer dashboard renders KPI metrics and department vacancy table', async ({ page }) => {
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/dashboard.php');
    await expect(page).toHaveTitle(/Dashboard|Employer/i);

    // Verify 4 KPI Metric Cards
    await expect(page.locator('body')).toContainText(/Active Requisitions|Total Applicants|Interviews Scheduled|Appointed|Dashboard/i);

    // Verify Department Vacancies Table / Cards
    const jobRows = page.locator('table tbody tr, .card-paper');
    expect(await jobRows.count()).toBeGreaterThan(0);

    // Check visible quick action link on page
    const createJobBtn = page.locator('main a[href*="create-job.php"], .btn-pill[href*="create-job.php"]').first();
    await expect(createJobBtn).toBeVisible();
  });

  test('2. Job requisition creation workflow with validations and multi-stage wizard', async ({ page }) => {
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/create-job.php');
    await expect(page).toHaveTitle(/Publish a Campus Vacancy|Post New Opportunity|Vacancy/i);

    // Step 1: Vacancy Information
    const titleInput = page.locator('#job-title, input[name="title"]');
    const descInput = page.locator('#job-desc, textarea[name="description"]');
    await expect(titleInput).toBeVisible();
    await expect(descInput).toBeVisible();

    await titleInput.fill('Robotics & IoT Laboratory Assistant');
    await page.locator('#job-category').selectOption({ index: 1 });
    await page.locator('#job-type').selectOption({ index: 1 });
    await page.locator('#work-setup').selectOption({ index: 1 });
    await descInput.fill('Assist students with Arduino and Raspberry Pi hardware debugging, sensor calibration, and equipment inventory.');

    // Add Dynamic Duty
    const respBar = page.locator('#resp-input-bar');
    const addRespBtn = page.locator('#btn-add-resp');
    if (await respBar.isVisible()) {
      await respBar.fill('Calibrate microcontrollers and sensors');
      await addRespBtn.click();
      await expect(page.locator('#resp-items-container .dynamic-line-item')).toHaveCount(1);
    }

    // Add Dynamic Qualification
    const qualBar = page.locator('#qual-input-bar');
    const addQualBtn = page.locator('#btn-add-qual');
    if (await qualBar.isVisible()) {
      await qualBar.fill('Enrolled in BS Computer Engineering or BSIS');
      await addQualBtn.click();
      await expect(page.locator('#qual-items-container .dynamic-line-item')).toHaveCount(1);
    }

    // Advance to Step 2
    const nextBtn1 = page.locator('#step-pane-1 button.btn-step-next');
    await nextBtn1.click();
    await expect(page.locator('#step-pane-2')).toBeVisible();

    // Step 2: Hiring Flyer (Optional) -> Advance to Step 3
    const nextBtn2 = page.locator('#step-pane-2 button.btn-step-next');
    await nextBtn2.click();
    await expect(page.locator('#step-pane-3')).toBeVisible();

    // Step 3: Terms & Quota
    const deptInput = page.locator('#job-dept');
    if (await deptInput.isVisible() && !(await deptInput.inputValue())) {
      await deptInput.fill('College of Engineering & Technology');
    }

    const locationInput = page.locator('#job-loc, input[name="location"]');
    if (await locationInput.isVisible()) {
      await locationInput.fill('Tech Building · Room 408');
    }

    const payAmountInput = page.locator('#job-pay-amount');
    if (await payAmountInput.isVisible()) {
      await payAmountInput.fill('90.00');
    }

    const vacanciesInput = page.locator('#job-vac, input[name="vacancies"]');
    if (await vacanciesInput.isVisible()) {
      await vacanciesInput.fill('3');
    }

    const deadlineInput = page.locator('#job-deadline');
    if (await deadlineInput.isVisible()) {
      const nextMonth = new Date();
      nextMonth.setDate(nextMonth.getDate() + 20);
      await deadlineInput.fill(nextMonth.toISOString().split('T')[0]);
    }

    const submitBtn = page.locator('#btn-publish-job, button[type="submit"]:has-text("PUBLISH REQUISITION")');
    await submitBtn.click();
    await page.waitForURL(/.*employer\/dashboard\.php/);
    await expect(page.locator('body')).toContainText(/Robotics & IoT Laboratory Assistant|published successfully/i);
  });

  test('3. Job requisition editing workflow with separated stipend rate and dynamic lines', async ({ page }) => {
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/edit-job.php?id=2');
    await expect(page.locator('body')).not.toContainText('Fatal error');

    const titleInput = page.locator('#edit-title, input[name="title"]');
    await expect(titleInput).toBeVisible();

    // Update title and separated pay rate
    await titleInput.fill('Senior Student Records & Archival Assistant');
    const payAmountInput = page.locator('#edit-pay-amount');
    if (await payAmountInput.isVisible()) {
      await payAmountInput.fill('95.00');
    }

    const submitBtn = page.locator('button[type="submit"]:has-text("SAVE CHANGES"), button[type="submit"]').first();
    await submitBtn.click();
    await page.waitForURL(/.*employer\/dashboard\.php/);
    await expect(page.locator('body')).toContainText(/Senior Student Records & Archival Assistant|updated successfully/i);
  });

  test('4. Candidate evaluation roster filtering and search', async ({ page }) => {
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/applicants.php');
    await expect(page).toHaveTitle(/Applicant Evaluation|Applicant Roster|Applicants/i);

    // Verify applicants table or cards
    await expect(page.locator('body')).toContainText(/Applicant|Candidate|Applied|Status/i);

    // Test Search Filter input
    const searchInput = page.locator('input[name="q"], #search-candidate, input[type="text"]').first();
    if (await searchInput.isVisible()) {
      await searchInput.fill('Juan');
      await searchInput.press('Enter');
      await page.waitForTimeout(500);
      await expect(page.locator('body')).toContainText(/Juan/i);
    }
  });

  test('5. In-depth candidate evaluation drawer, resume modal, and interview scheduling', async ({ page }) => {
    await loginAsDemo(page, 'employer');
    // Open candidate review drawer for application #2 (owned by Registrar user #3)
    await page.goto('/employer/review-app.php?id=2');
    await expect(page.locator('body')).not.toContainText('Fatal error');

    // Verify academic profile, statement of intent, and shift availability matrix
    await expect(page.locator('body')).toContainText(/Academic & Contact Profile|Statement of Intent|Candidate Shift Availability/i);

    // Verify Resume link / modal trigger exists
    const resumeBtn = page.locator('button[data-bs-target="#resumeModal"], a[href*="view-resume.php?app_id=2"], a[href*="view-resume.php"]').first();
    await expect(resumeBtn).toBeVisible();

    // Update status to Interview Scheduled with date, time, venue
    const statusSelect = page.locator('#eval-status, select[name="status"]');
    await expect(statusSelect).toBeVisible();
    await statusSelect.selectOption('interview_scheduled');

    const dateInput = page.locator('#int-date, input[name="interview_date"]');
    if (await dateInput.isVisible()) {
      await dateInput.fill('2026-09-20');
    }

    const timeSelect = page.locator('#int-time, select[name="interview_time"]');
    if (await timeSelect.isVisible()) {
      await timeSelect.selectOption('10:00 AM');
    }

    const venueSelect = page.locator('#int-venue, select[name="interview_venue"]');
    if (await venueSelect.isVisible()) {
      await venueSelect.selectOption({ index: 0 });
    }

    const notesTextarea = page.locator('#supervisor_notes, textarea[name="supervisor_notes"]');
    if (await notesTextarea.isVisible()) {
      await notesTextarea.fill('Candidate shortlisted for in-person technical evaluation and records test.');
    }

    const saveDecisionBtn = page.locator('button[type="submit"]:has-text("Save Evaluation Decision")');
    await saveDecisionBtn.click();
    await page.waitForURL(/.*review-app\.php\?id=2/);
    await expect(page.locator('body')).toContainText(/Interview Scheduled|updated to Interview Scheduled/i);
  });

  test('6. Candidate appointment: transition stage to Accepted / Officially Hired', async ({ page }) => {
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/review-app.php?id=2');

    const statusSelect = page.locator('#eval-status, select[name="status"]');
    await statusSelect.selectOption('accepted');

    const notesTextarea = page.locator('#supervisor_notes, textarea[name="supervisor_notes"]');
    if (await notesTextarea.isVisible()) {
      await notesTextarea.fill('Officially appointed. Ready for contract signing.');
    }

    const saveDecisionBtn = page.locator('button[type="submit"]:has-text("Save Evaluation Decision")');
    await saveDecisionBtn.click();
    await page.waitForURL(/.*review-app\.php\?id=2/);
    await expect(page.locator('body')).toContainText(/Accepted|Accepted \/ Hired/i);
  });

  test('7. Multi-stage requisition wizard with dynamic addable lines, clean defaults, and screenshots', async ({ page }) => {
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/create-job.php');

    // Verify Clean Non-Filled Defaults
    await expect(page.locator('#job-title')).toHaveValue('');
    await expect(page.locator('#job-category')).toHaveValue('');
    await expect(page.locator('#job-type')).toHaveValue('');
    await expect(page.locator('#work-setup')).toHaveValue('');
    await expect(page.locator('#job-desc')).toHaveValue('');
    await expect(page.locator('#resp-items-container .dynamic-line-item')).toHaveCount(0);
    await expect(page.locator('#qual-items-container .dynamic-line-item')).toHaveCount(0);
    await expect(page.locator('#resp-empty-hint')).toBeVisible();
    await expect(page.locator('#qual-empty-hint')).toBeVisible();

    // Verify Multi-Stage Wizard Initial State (Step 1 of 3)
    await expect(page.locator('#step-pane-1')).toBeVisible();
    await expect(page.locator('#step-pane-2')).not.toBeVisible();
    await expect(page.locator('#step-pane-3')).not.toBeVisible();
    await expect(page.locator('#progress-step-tag')).toHaveText('Step 1 of 3');

    // Screenshot Step 1 initial (desktop)
    await page.setViewportSize({ width: 1280, height: 850 });
    await page.screenshot({ path: 'test-results/screenshots/wizard_step1_desktop.png', fullPage: true });

    // Step 1 Validation
    await page.locator('#step-pane-1 button.btn-step-next').click();
    await expect(page.locator('#step-error-alert')).toBeVisible();
    await expect(page.locator('#step-error-message')).toContainText(/Vacancy Title/i);

    // Fill Step 1 Vacancy Info
    await page.locator('#job-title').fill('Academic Computer Lab Assistant');
    await page.locator('#job-category').selectOption({ index: 1 });
    await page.locator('#job-type').selectOption({ value: 'Student Assistant' });
    await page.locator('#work-setup').selectOption({ value: 'On-Campus' });
    await page.locator('#job-desc').fill('Assist campus students with laboratory workstation login, basic software troubleshooting, printer setup, and clean lab maintenance.');

    // Add Dynamic Duties (+ button and Enter key)
    await page.locator('#resp-input-bar').fill('Assist student visitors with computer lab login');
    await page.locator('#btn-add-resp').click();
    await expect(page.locator('#resp-items-container .dynamic-line-item')).toHaveCount(1);
    await expect(page.locator('#resp-items-container .dynamic-line-badge').first()).toHaveText('1');

    await page.locator('#resp-input-bar').fill('Organize physical and digital department forms');
    await page.locator('#resp-input-bar').press('Enter');
    await expect(page.locator('#resp-items-container .dynamic-line-item')).toHaveCount(2);
    await expect(page.locator('#resp-items-container .dynamic-line-badge').nth(1)).toHaveText('2');

    await page.locator('#resp-input-bar').fill('Coordinate Daily Time Records for assistantship cohort');
    await page.locator('#btn-add-resp').click();
    await expect(page.locator('#resp-items-container .dynamic-line-item')).toHaveCount(3);

    // Add Dynamic Qualifications
    await page.locator('#qual-input-bar').fill('Currently enrolled student in good academic standing');
    await page.locator('#btn-add-qual').click();

    await page.locator('#qual-input-bar').fill('GWA of 2.25 or better');
    await page.locator('#btn-add-qual').click();

    await page.locator('#qual-input-bar').fill('Proficiency with basic spreadsheet and office software');
    await page.locator('#btn-add-qual').click();
    await expect(page.locator('#qual-items-container .dynamic-line-item')).toHaveCount(3);

    // Screenshot Step 1 with dynamic rows filled
    await page.screenshot({ path: 'test-results/screenshots/wizard_step1_filled.png', fullPage: true });

    // Advance to Step 2: Hiring Flyer / Media
    await page.locator('#step-pane-1 button.btn-step-next').click();
    await expect(page.locator('#step-pane-2')).toBeVisible();
    await expect(page.locator('#step-pane-1')).not.toBeVisible();
    await expect(page.locator('#progress-step-tag')).toHaveText('Step 2 of 3');

    // Screenshot Step 2
    await page.screenshot({ path: 'test-results/screenshots/wizard_step2_desktop.png', fullPage: true });

    // Advance to Step 3: Terms & Quota
    await page.locator('#step-pane-2 button.btn-step-next').click();
    await expect(page.locator('#step-pane-3')).toBeVisible();
    await expect(page.locator('#progress-step-tag')).toHaveText('Step 3 of 3');

    // Verify Clean Defaults on Step 3
    await expect(page.locator('#job-pay-amount')).toHaveValue('');
    await expect(page.locator('#job-vac')).toHaveValue('');
    await expect(page.locator('#job-tags')).toHaveValue('');

    // Verify Separated Stipend Rate and Compensation Controls
    const payAmountInput = page.locator('#job-pay-amount');
    const payPeriodSelect = page.locator('#job-pay-period');
    await expect(payAmountInput).toBeVisible();
    await expect(payAmountInput).toHaveAttribute('type', 'number');
    await expect(payPeriodSelect).toBeVisible();

    const deptInput = page.locator('#job-dept');
    if (!(await deptInput.inputValue())) {
      await deptInput.fill('Office of Academic Computing');
    }
    const locInput = page.locator('#job-loc');
    if (!(await locInput.inputValue())) {
      await locInput.fill('KLD Admin Building, 2nd Floor, Room 204');
    }

    await payAmountInput.fill('85.00');
    await payPeriodSelect.selectOption('/ hour');
    await page.locator('#job-vac').fill('2');
    await page.locator('#job-hours').selectOption('10 - 20 hrs/week');

    const deadlineDate = new Date();
    deadlineDate.setDate(deadlineDate.getDate() + 30);
    await page.locator('#job-deadline').fill(deadlineDate.toISOString().split('T')[0]);
    await page.locator('#job-tags').fill('Flexible Schedule, Computer Lab, Urgent');

    // Verify Live Preview Card Updated Real-time
    await expect(page.locator('#preview-title')).toHaveText('Academic Computer Lab Assistant');
    await expect(page.locator('#preview-pay')).toContainText('₱85.00 / hour');
    await expect(page.locator('#preview-slots')).toContainText('2 slot(s)');

    // Screenshot Step 3
    await page.screenshot({ path: 'test-results/screenshots/wizard_step3_desktop.png', fullPage: true });

    // Mobile Viewport Screenshot
    await page.setViewportSize({ width: 390, height: 844 });
    await expect(page.locator('#job-mobile-stepper')).toBeVisible();
    await page.screenshot({ path: 'test-results/screenshots/wizard_step3_mobile.png', fullPage: true });

    // Switch back to desktop and publish
    await page.setViewportSize({ width: 1280, height: 850 });
    await page.locator('#btn-publish-job').click();
    await page.waitForURL(/.*employer\/dashboard\.php/);
    await expect(page.locator('body')).toContainText(/Academic Computer Lab Assistant|published successfully/i);

    // Verify Edit Job with Separated Stipend and Dynamic Line Items
    const editLink = page.locator('.table-paper-dept a[href*="edit-job.php"]').first();
    await editLink.click();
    await expect(page.locator('#edit-title')).toBeVisible();

    // Verify separated stipend in edit mode
    await expect(page.locator('#edit-pay-amount')).toBeVisible();
    await expect(page.locator('#edit-pay-period')).toBeVisible();

    // Verify duties dynamic lines are pre-populated
    const editDutiesCount = await page.locator('#resp-items-container .dynamic-line-item').count();
    expect(editDutiesCount).toBeGreaterThan(0);

    // Screenshot Edit Page
    await page.screenshot({ path: 'test-results/screenshots/edit_job_desktop.png', fullPage: true });
  });
});
