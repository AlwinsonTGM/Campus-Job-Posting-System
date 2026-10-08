import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo } from './helpers/auth';

test.describe('Dataset Mode Toggle & Private Browser Real-Time Synchronization', () => {
  test.beforeEach(async ({ page }) => {
    await resetDatastore(page);
  });

  test('1. System Dataset Switcher Modal opens and allows toggling to Real Mode and back to Demo Mode in Admin Settings', async ({ page }) => {
    // Guest visitor on index should NOT see dataset mode button in navbar
    await page.goto('/index.php', { waitUntil: 'domcontentloaded' });
    const guestNavbarModeBtn = page.locator('.paper-nav-actions .btn-data-mode-toggle');
    await expect(guestNavbarModeBtn).toHaveCount(0);

    // Login as administrator and open settings
    await loginAsDemo(page, 'admin');
    await page.goto('/settings.php', { waitUntil: 'domcontentloaded' });

    // Admin Settings should have dataset mode card and single toggle button
    const modeCard = page.locator('#admin-dataset-settings');
    await expect(modeCard).toBeVisible();
    await expect(modeCard).toContainText(/DEMO DATA/i);

    const toggleBtn = modeCard.locator('button[type="submit"], .btn-data-mode-toggle').first();
    await expect(toggleBtn).toBeVisible();
    await expect(toggleBtn).toContainText(/Switch to Real Mode/i);

    // Click single toggle button to switch to Real Mode
    await toggleBtn.click();

    // Verify redirected to settings and mode changed to Real Mode
    await expect(page).toHaveURL(/.*settings\.php/);
    await expect(page.locator('#admin-dataset-settings')).toContainText(/REAL DATA/i);
    await expect(modeCard.locator('button[type="submit"]').first()).toContainText(/Switch to Demo Data/i);

    // Verify featured jobs empty state on index in Real Mode
    await page.goto('/index.php', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('text=No Active Requisitions Published Yet')).toBeVisible();

    // Open Admin Settings again and switch back to Demo Mode
    await page.goto('/settings.php', { waitUntil: 'domcontentloaded' });
    const switchBackBtn = modeCard.locator('button[type="submit"]').first();
    await expect(switchBackBtn).toContainText(/Switch to Demo Data/i);
    await switchBackBtn.click();

    // Verify Demo Mode restored and toggle button changed back
    await expect(page.locator('#admin-dataset-settings')).toContainText(/DEMO DATA/i);
    await expect(modeCard.locator('button[type="submit"]').first()).toContainText(/Switch to Real Mode/i);
    await page.goto('/index.php', { waitUntil: 'domcontentloaded' });
    const jobCards = page.locator('.featured-job-card');
    await expect(jobCards.first()).toBeVisible();
  });

  test('2. Real-Time Multi-Browser Synchronization (Incognito / Dual Context Flow)', async ({ browser }) => {
    // 1. Activate Real Clean Mode in initial session
    const setupContext = await browser.newContext();
    const setupPage = await setupContext.newPage();
    await setupPage.goto('/data-toggle.php?mode=real', { waitUntil: 'domcontentloaded' });
    await setupContext.close();

    // 2. Browser Context 1 (Window 1 - Employer)
    const employerContext = await browser.newContext();
    const pageEmp = await employerContext.newPage();

    // Register real employer (University Office)
    await pageEmp.goto('/register.php', { waitUntil: 'domcontentloaded' });
    await pageEmp.click('#lbl-role-employer');
    await pageEmp.fill('#reg-name', 'Dr. Elena Rostova');
    await pageEmp.fill('#reg-email', 'elena.rostova@kld.edu.ph');
    await pageEmp.fill('#reg-phone', '+63 917 123 4567');
    await pageEmp.locator('#step-pane-1 button.btn-step-next').click();
    await expect(pageEmp.locator('#step-pane-2')).toBeVisible();

    await pageEmp.selectOption('#office_name_select', 'Management Information Systems (MIS)');
    await pageEmp.locator('#step-pane-2 button.btn-step-next').click();
    await expect(pageEmp.locator('#step-pane-3')).toBeVisible();

    await pageEmp.fill('input[name=password]', 'Password123!');
    await pageEmp.fill('input[name=confirm_password]', 'Password123!');
    await pageEmp.click('#register-form button[type=submit]');

    // Verify employer registered and logged in
    await expect(pageEmp).toHaveURL(/.*employer\/dashboard\.php/);

    // Employer posts a new job
    await pageEmp.goto('/employer/create-job.php', { waitUntil: 'domcontentloaded' });
    await pageEmp.fill('input[name=title]', 'Quantum Computing Student Aide');
    await pageEmp.selectOption('#job-category', 'IT & Technical Support');
    await pageEmp.selectOption('#job-type', 'Student Assistant');
    await pageEmp.selectOption('#work-setup', 'Hybrid');
    await pageEmp.fill('#job-desc', 'Assist faculty researchers with quantum simulation testing and dataset logging.');

    // Advance wizard steps: Step 1 -> Step 2 (Flyer)
    await pageEmp.locator('#step-pane-1 button.btn-step-next').click();
    await expect(pageEmp.locator('#step-pane-2')).toBeVisible();

    // Advance wizard steps: Step 2 -> Step 3 (Terms & Quota)
    await pageEmp.locator('#step-pane-2 button.btn-step-next').click();
    await expect(pageEmp.locator('#step-pane-3')).toBeVisible();

    await pageEmp.fill('#job-loc', 'Tech Innovation Wing 402');
    await pageEmp.locator('#job-pay-amount').fill('120.00');
    await pageEmp.fill('#job-vac', '2');
    const deadlineInput = pageEmp.locator('#job-deadline');
    if (await deadlineInput.isVisible()) {
      const nextMonth = new Date();
      nextMonth.setDate(nextMonth.getDate() + 20);
      await deadlineInput.fill(nextMonth.toISOString().split('T')[0]);
    }
    await pageEmp.getByRole('button', { name: /PUBLISH REQUISITION/i }).click();

    // Verify job created and shown in employer dashboard
    await expect(pageEmp).toHaveURL(/.*employer\/dashboard\.php/);
    await expect(pageEmp.getByRole('link', { name: /Quantum Computing Student Aide/i }).first()).toBeVisible();

    // 3. Browser Context 2 (Window 2 - Separate Incognito Session / Student)
    const studentContext = await browser.newContext();
    const pageStu = await studentContext.newPage();

    // Student visits jobs directory and immediately sees the newly posted job in real-time
    await pageStu.goto('/student/jobs.php', { waitUntil: 'domcontentloaded' });
    await expect(pageStu.getByRole('link', { name: /Quantum Computing Student Aide/i }).first()).toBeVisible();

    // Register real student account in Window 2
    await pageStu.goto('/register.php', { waitUntil: 'domcontentloaded' });
    await pageStu.fill('#reg-first-name', 'Carlos');
    await pageStu.fill('#reg-last-name', 'Mendoza');
    await pageStu.fill('#reg-email', 'carlos.mendoza@kld.edu.ph');
    await pageStu.fill('#reg-phone', '+63 918 765 4321');
    await pageStu.locator('#step-pane-1 button.btn-step-next').click();
    await expect(pageStu.locator('#step-pane-2')).toBeVisible();

    await pageStu.fill('#student_id', '2025-99881');
    await pageStu.selectOption('#department-select', 'Institute of Computing and Digital Innovation (ICDI)');
    await pageStu.selectOption('#course-select', 'BS Information Systems (BSIS)');
    await pageStu.selectOption('#year_level', '3rd Year');
    await pageStu.selectOption('#reg-sex', 'Male');
    await pageStu.fill('#reg-birthdate', '2003-05-15');
    await pageStu.locator('#step-pane-2 button.btn-step-next').click();
    await expect(pageStu.locator('#step-pane-3')).toBeVisible();

    await pageStu.setInputFiles('#reg-student-proof', {
      name: 'COR_CarlosMendoza.pdf',
      mimeType: 'application/pdf',
      buffer: Buffer.from('%PDF-1.4 dummy student proof COR')
    });
    await pageStu.fill('input[name=password]', 'Password123!');
    await pageStu.fill('input[name=confirm_password]', 'Password123!');
    await pageStu.click('#register-form button[type=submit]');

    // Verify student logged in
    await expect(pageStu).toHaveURL(/.*student\/dashboard\.php/);

    // Student applies for the new job
    await pageStu.goto('/student/jobs.php');
    await pageStu.locator('a.btn-pill', { hasText: 'Apply' }).first().click();
    await expect(pageStu).toHaveURL(/.*student\/apply\.php/);
    
    // Check at least one shift availability slot
    const matrixBox = pageStu.locator('#matrixContainer input[type=checkbox]').first();
    await matrixBox.check();

    await pageStu.click('#submitAppBtn');

    // Verify application submitted
    await expect(pageStu).toHaveURL(/.*student\/my-applications\.php/);
    await expect(pageStu.getByRole('link', { name: /Quantum Computing Student Aide/i }).first()).toBeVisible();
    await expect(pageStu.locator('text=Pending Review').first()).toBeVisible();

    // 4. Back to Window 1 (Employer): Check applicants in real-time
    await pageEmp.goto('/employer/applicants.php', { waitUntil: 'domcontentloaded' });
    await expect(pageEmp.locator('main').getByText('Carlos Mendoza').first()).toBeVisible();

    // Employer opens candidate evaluation and schedules interview
    await pageEmp.locator('main a.btn-pill', { hasText: 'Evaluate' }).first().click();
    await expect(pageEmp).toHaveURL(/.*employer\/review-app\.php/);
    await pageEmp.selectOption('select[name=status]', 'interview_scheduled');
    await pageEmp.fill('textarea[name=supervisor_notes]', 'Strong candidate credentials. Scheduled initial technical briefing.');
    await pageEmp.locator('main button[type=submit]').click();

    // Verify employer sees updated status
    await expect(pageEmp.locator('main').locator('text=Interview Scheduled').first()).toBeVisible();

    // 5. Back to Window 2 (Student): Refresh tracker and verify real-time status update
    await pageStu.goto('/student/my-applications.php', { waitUntil: 'domcontentloaded' });
    await expect(pageStu.locator('main').locator('text=Interview Scheduled').first()).toBeVisible();

    // Cleanup contexts
    await employerContext.close();
    await studentContext.close();
  });
});
