import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo, logout } from './helpers/auth';

test.describe('Tier 4 Full Cross-Role Lifecycle E2E Regression Suite', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
  });

  test('Complete End-to-End Multi-Role Workflow: Employer Posts Job -> Student Discovers & Applies -> Employer Interviews & Hires -> Student Verifies -> Admin Audits', async ({ page }) => {
    // -------------------------------------------------------------
    // STEP 1: Employer Logs In & Creates a New Job Requisition
    // -------------------------------------------------------------
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/dashboard.php');
    await expect(page).toHaveTitle(/Dashboard|Employer/i);

    // Navigate to Create Job form
    await page.goto('/employer/create-job.php');
    await expect(page.locator('#job-title, input[name="title"]')).toBeVisible();

    const uniqueJobTitle = 'Campus Cybersecurity Student Specialist';
    await page.locator('#job-title, input[name="title"]').fill(uniqueJobTitle);
    await page.locator('#job-category, select[name="category"]').selectOption({ index: 1 });
    await page.locator('#job-type, select[name="job_type"]').selectOption('Student Assistant');
    await page.locator('#work-setup, select[name="work_setup"]').selectOption('Hybrid');
    await page.locator('#job-desc, textarea[name="description"]').fill('Protect campus lab networks, perform vulnerability scanning, and assist MIS security audits.');
    
    // Add dynamic duties if dynamic input exists, else fallback
    const respBar = page.locator('#resp-input-bar');
    if (await respBar.isVisible()) {
      await respBar.fill('Audit server access logs');
      await page.locator('#btn-add-resp').click();
    } else {
      const respInput = page.locator('#job-resp, textarea[name="responsibilities"]');
      if (await respInput.isVisible()) {
        await respInput.fill('Audit server access logs\nInspect lab network switches\nSupport cybersecurity awareness');
      }
    }

    // Add dynamic qualifications if dynamic input exists, else fallback
    const qualBar = page.locator('#qual-input-bar');
    if (await qualBar.isVisible()) {
      await qualBar.fill('Enrolled in BSIS or BSCS');
      await page.locator('#btn-add-qual').click();
    } else {
      const qualInput = page.locator('#job-qual, textarea[name="qualifications"]');
      if (await qualInput.isVisible()) {
        await qualInput.fill('Enrolled in BSIS or BSCS\nKnowledge of network security basics\nGWA 2.0 or better');
      }
    }

    // Advance Step 1 -> Step 2: Hiring Flyer / Media
    await page.locator('#step-pane-1 button.btn-step-next').click();
    await expect(page.locator('#step-pane-2')).toBeVisible();

    // Advance Step 2 -> Step 3: Terms & Quota
    await page.locator('#step-pane-2 button.btn-step-next').click();
    await expect(page.locator('#step-pane-3')).toBeVisible();

    const payAmountInput = page.locator('#job-pay-amount');
    if (await payAmountInput.isVisible()) {
      await payAmountInput.fill('95.00');
    }
    const payPeriodSelect = page.locator('#job-pay-period');
    if (await payPeriodSelect.isVisible()) {
      await payPeriodSelect.selectOption('/ hour');
    }
    const vacInput = page.locator('#job-vac');
    if (await vacInput.isVisible()) {
      await vacInput.fill('2');
    }
    const hoursSelect = page.locator('#job-hours');
    if (await hoursSelect.isVisible()) {
      await hoursSelect.selectOption('10 - 20 hrs/week');
    }

    const deptInput = page.locator('#job-dept');
    if (await deptInput.isVisible() && !(await deptInput.inputValue())) {
      await deptInput.fill('MIS Security Office');
    }
    const locInput = page.locator('#job-loc');
    if (await locInput.isVisible() && !(await locInput.inputValue())) {
      await locInput.fill('KLD Cyberlab Room 301');
    }

    const deadlineInput = page.locator('#job-deadline');
    if (await deadlineInput.isVisible()) {
      const deadlineDate = new Date();
      deadlineDate.setDate(deadlineDate.getDate() + 30);
      await deadlineInput.fill(deadlineDate.toISOString().split('T')[0]);
    }

    const submitBtn = page.locator('#btn-publish-job');
    await submitBtn.click();
    await page.waitForURL(/.*employer\/dashboard\.php/);
    await expect(page.locator('body')).toContainText(uniqueJobTitle);

    // -------------------------------------------------------------
    // STEP 2: Student Logs In, Searches Catalog & Applies for Job
    // -------------------------------------------------------------
    await logout(page);
    await loginAsDemo(page, 'student');
    await page.goto('/student/jobs.php');

    // Search for newly posted job
    const searchInput = page.locator('#filter-keyword, input[name="keyword"], input[name="q"], input[name="kw"]').first();
    if (await searchInput.isVisible()) {
      await searchInput.fill('Cybersecurity');
      await searchInput.press('Enter');
      await page.waitForTimeout(600);
    }

    await expect(page.locator('body')).toContainText(uniqueJobTitle);

    // Open Job Details
    const detailsLink = page.locator(`a[href*="job-details.php"]:has-text("${uniqueJobTitle}"), .card-paper:has-text("${uniqueJobTitle}") a[href*="job-details.php"]`).first();
    if (await detailsLink.isVisible()) {
      await detailsLink.click();
    } else {
      // Find direct link
      await page.locator('a[href*="job-details.php"]').first().click();
    }

    await expect(page.locator('body')).toContainText(/Requisition Summary|APPLY FOR THIS POSITION|Requirements/i);

    // Click Apply button
    const applyBtn = page.locator('a[href*="apply.php"]').first();
    await applyBtn.click();
    await page.waitForURL(/.*student\/apply\.php/);

    // Fill application form
    await page.locator('input[name="phone"]').fill('+63 917 555 7788');
    await page.locator('textarea[name="cover_letter"]').fill('I am very passionate about cybersecurity and network defense, and I have completed relevant laboratory coursework.');

    const complianceCheck = page.locator('#saComplianceCheck');
    if (await complianceCheck.isVisible()) {
      await complianceCheck.check();
    }

    await page.locator('#submitAppBtn').click();
    await page.waitForURL(/.*student\/my-applications\.php/);
    await expect(page.locator('body')).toContainText(/Application successfully submitted|My Assistantship Applications/i);

    // Verify initial application status is Pending
    await expect(page.locator('body')).toContainText(/Pending/i);

    // -------------------------------------------------------------
    // STEP 3: Employer Reviews Candidate & Schedules Interview
    // -------------------------------------------------------------
    await logout(page);
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/applicants.php');

    // Find applicant and click evaluate
    const evalLink = page.locator('a[href*="review-app.php"]').first();
    await expect(evalLink).toBeVisible();
    await evalLink.click();
    await page.waitForURL(/.*employer\/review-app\.php/);

    // Verify application drawer contents
    await expect(page.locator('body')).toContainText(/Academic & Contact Profile|Statement of Intent|Candidate Shift Availability/i);

    // Transition to Interview Scheduled
    const statusSelect = page.locator('#eval-status, select[name="status"]');
    await statusSelect.selectOption('interview_scheduled');

    const intDateInput = page.locator('#int-date, input[name="interview_date"]');
    if (await intDateInput.isVisible()) {
      await intDateInput.fill('2026-09-25');
    }

    const intTimeSelect = page.locator('#int-time, select[name="interview_time"]');
    if (await intTimeSelect.isVisible()) {
      await intTimeSelect.selectOption('10:00 AM');
    }

    const intVenueInput = page.locator('#int-venue');
    if (await intVenueInput.isVisible()) {
      await intVenueInput.fill('Technology Annex, Interview Room 405');
    }

    const supervisorNotes = page.locator('#supervisor_notes, textarea[name="supervisor_notes"]');
    if (await supervisorNotes.isVisible()) {
      await supervisorNotes.fill('Shortlisted candidate for in-person network defense interview.');
    }

    await page.locator('button[type="submit"]:has-text("Save Evaluation Decision")').click();
    await page.waitForURL(/.*review-app\.php/);
    await expect(page.locator('body')).toContainText(/Interview Scheduled/i);

    // -------------------------------------------------------------
    // STEP 4: Student Sees Scheduled Interview Notice
    // -------------------------------------------------------------
    await logout(page);
    await loginAsDemo(page, 'student');
    await page.goto('/student/my-applications.php');

    await expect(page.locator('body')).toContainText(/Interview Scheduled|Official Interview Schedule/i);

    // -------------------------------------------------------------
    // STEP 5: Employer Officially Hires Candidate
    // -------------------------------------------------------------
    await logout(page);
    await loginAsDemo(page, 'employer');
    await page.goto('/employer/applicants.php');
    await page.locator('a[href*="review-app.php"]').first().click();
    await page.waitForURL(/.*employer\/review-app\.php/);

    await page.locator('#eval-status, select[name="status"]').selectOption('accepted');
    const notesInput = page.locator('#supervisor_notes, textarea[name="supervisor_notes"]');
    if (await notesInput.isVisible()) {
      await notesInput.fill('Candidate officially appointed as Student Assistant.');
    }

    await page.locator('button[type="submit"]:has-text("Save Evaluation Decision")').click();
    await page.waitForURL(/.*review-app\.php/);
    await expect(page.locator('body')).toContainText(/Accepted|Accepted \/ Hired/i);

    // -------------------------------------------------------------
    // STEP 6: Student Verifies Appointment Confirmation
    // -------------------------------------------------------------
    await logout(page);
    await loginAsDemo(page, 'student');
    await page.goto('/student/my-applications.php');

    await expect(page.locator('body')).toContainText(/Accepted|Accepted \/ Hired|Congratulations! You are officially appointed/i);

    // -------------------------------------------------------------
    // STEP 7: Admin Audits Metrics & Institutional Analytics
    // -------------------------------------------------------------
    await logout(page);
    await loginAsDemo(page, 'admin');
    await page.goto('/admin/reports.php');
    await expect(page).toHaveTitle(/Reports & Institutional Analytics|Analytics/i);

    // Verify analytics KPI cards and table
    await expect(page.locator('body')).toContainText(/Total Vacancies|Applications Filed|Interviews Held|Officially Hired/i);
  });
});
