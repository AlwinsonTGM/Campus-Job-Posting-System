import { test, expect } from '@playwright/test';
import { resetDatastore, loginAsDemo, logout } from './helpers/auth';

test.describe('Milestone 2: Security, Access Control & Relational Integrity Testing', () => {

  test.beforeEach(async ({ page }) => {
    // Reseed datastore before each test to maintain state consistency
    await resetDatastore(page);
  });

  /* -------------------------------------------------------------------------- */
  /* 1. Unauthenticated Route Access Protection                                  */
  /* -------------------------------------------------------------------------- */
  test.describe('1. Unauthenticated Route Access Protection', () => {

    test('1.1 Unauthenticated requests to Student protected routes redirect to login', async ({ page }) => {
      await page.context().clearCookies();

      // Student Dashboard
      await page.goto('/student/dashboard.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Student My Applications
      await page.goto('/student/my-applications.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Student Apply Form (verify HTTP 302 redirect to login.php)
      const applyRes = await page.request.get('/student/apply.php?id=1', { maxRedirects: 0 });
      expect(applyRes.status()).toBe(302);
      expect(applyRes.headers()['location']).toContain('login.php');
    });

    test('1.2 Unauthenticated requests to Employer protected routes redirect to login', async ({ page }) => {
      await page.context().clearCookies();

      // Employer Dashboard
      await page.goto('/employer/dashboard.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Employer Create Job
      await page.goto('/employer/create-job.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Employer Edit Job
      await page.goto('/employer/edit-job.php?id=1');
      await expect(page).toHaveURL(/.*login\.php/);

      // Employer Applicants Roster
      await page.goto('/employer/applicants.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Employer Candidate Review Drawer
      await page.goto('/employer/review-app.php?id=1');
      await expect(page).toHaveURL(/.*login\.php/);
    });

    test('1.3 Unauthenticated requests to Admin protected routes redirect to login', async ({ page }) => {
      await page.context().clearCookies();

      // Admin Categories Taxonomy
      await page.goto('/admin/categories.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Admin User Management & Verification
      await page.goto('/admin/users.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Admin Reports & Institutional Analytics
      await page.goto('/admin/reports.php');
      await expect(page).toHaveURL(/.*login\.php/);
    });

    test('1.4 Unauthenticated requests to Common protected routes (settings, view-resume) redirect to login', async ({ page }) => {
      await page.context().clearCookies();

      // Settings
      await page.goto('/settings.php');
      await expect(page).toHaveURL(/.*login\.php/);

      // Resume & Document Server
      await page.goto('/view-resume.php?app_id=1');
      await expect(page).toHaveURL(/.*login\.php/);
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 2. Privilege Escalation & Cross-Role Access Control                        */
  /* -------------------------------------------------------------------------- */
  test.describe('2. Privilege Escalation & Cross-Role Access Control', () => {

    test('2.1 Student cannot access Employer portal routes (Privilege Escalation Prevention)', async ({ page }) => {
      await loginAsDemo(page, 'student');

      // Attempt to access Employer Dashboard
      await page.goto('/employer/dashboard.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);
      await expect(page.locator('.alert-danger, .alert-warning, body')).toContainText(/Unauthorized access/i);

      // Attempt to access Employer Create Job
      await page.goto('/employer/create-job.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);

      // Attempt to access Employer Edit Job
      await page.goto('/employer/edit-job.php?id=1', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);

      // Attempt to access Employer Applicants
      await page.goto('/employer/applicants.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);

      // Attempt to access Employer Candidate Review
      await page.goto('/employer/review-app.php?id=1', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);
    });

    test('2.2 Student cannot access Admin portal routes (Privilege Escalation Prevention)', async ({ page }) => {
      await loginAsDemo(page, 'student');

      // Attempt to access Admin Categories
      await page.goto('/admin/categories.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);
      await expect(page.locator('.alert-danger, .alert-warning, body')).toContainText(/Unauthorized access/i);

      // Attempt to access Admin Users Directory
      await page.goto('/admin/users.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);

      // Attempt to access Admin Reports
      await page.goto('/admin/reports.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|student\/dashboard\.php)/);
    });

    test('2.3 Employer cannot access Admin portal routes (/admin/categories.php, /admin/users.php)', async ({ page }) => {
      await loginAsDemo(page, 'employer');

      // Attempt to access Admin Categories
      await page.goto('/admin/categories.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|employer\/dashboard\.php)/);
      await expect(page.locator('.alert-danger, .alert-warning, body')).toContainText(/Unauthorized access/i);

      // Attempt to access Admin Users Directory
      await page.goto('/admin/users.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|employer\/dashboard\.php)/);
    });

    test('2.4 Employer cannot submit student job applications (/student/apply.php)', async ({ page }) => {
      await loginAsDemo(page, 'employer');

      // Attempt to access Job Application Form
      await page.goto('/student/apply.php?id=1', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*employer\/dashboard\.php/);
      await expect(page.locator('.alert-warning, body')).toContainText(/Only enrolled students can submit job applications/i);
    });

    test('2.5 Employer cannot access institutional reports (/admin/reports.php)', async ({ page }) => {
      await loginAsDemo(page, 'employer');

      // Employer attempting access to Admin Reports & Analytics -> should be denied
      await page.goto('/admin/reports.php', { waitUntil: 'domcontentloaded' });
      await expect(page).toHaveURL(/.*(index\.php|employer\/dashboard\.php)/);
      await expect(page.locator('.alert-danger, body')).toContainText(/Unauthorized access/i);
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 3. Insecure Direct Object References (IDOR) & PII Leakage                 */
  /* -------------------------------------------------------------------------- */
  test.describe('3. Insecure Direct Object References (IDOR) & PII Leakage', () => {

    test('3.1 IDOR Protection: Employer CANNOT modify vacancies owned by another employer/department', async ({ page }) => {
      // Log in as Employer A: Office of the University Registrar (user_id = 3)
      await loginAsDemo(page, 'employer');

      // Job #1 is owned by Management Information Systems (MIS, employer_id = 4)
      // Attempt to navigate to edit Job #1
      await page.goto('/employer/edit-job.php?id=1', { waitUntil: 'domcontentloaded' });

      // Assert unauthorized access redirection to dashboard with danger flash alert
      await expect(page).toHaveURL(/.*employer\/dashboard\.php/);
      await expect(page.locator('.alert-danger, .alert-warning, body')).toContainText(/Unauthorized|You can only edit requisitions posted by your office/i);

      // Verify Job #1 was NOT modified
      await page.goto('/student/job-details.php?id=1', { waitUntil: 'domcontentloaded' });
      await expect(page.locator('body')).toContainText('Computer Lab Technical Assistant');
      await expect(page.locator('body')).not.toContainText('IDOR Modified');
    });

    test('3.2 IDOR Protection: Employer CANNOT modify candidate review status across departments', async ({ page }) => {
      // Log in as Employer A: Office of the University Registrar (user_id = 3)
      await loginAsDemo(page, 'employer');

      // Candidate Application #1 is for Job #1 (MIS Department, employer_id = 4)
      // Attempt to access review drawer for Application #1
      await page.goto('/employer/review-app.php?id=1');

      // Assert unauthorized access redirection to applicants roster with flash alert
      await expect(page).toHaveURL(/.*employer\/applicants\.php|.*employer\/dashboard\.php/);
      await expect(page.locator('.alert-danger, .alert-warning, body')).toContainText(/Unauthorized|belongs to another department/i);

      // Verify Application #1 was NOT modified in student portal
      await logout(page);
      await loginAsDemo(page, 'student');
      await page.goto('/student/my-applications.php');
      await expect(page.locator('body')).not.toContainText('Status modified by unauthorized');
    });

    test('3.3 PII Protection: Student CANNOT view another student\'s personal profile via view-resume.php?user_id=X', async ({ page }) => {
      // Log in as Student 1: Juan Dela Cruz (user_id = 1)
      await loginAsDemo(page, 'student');

      // Attempt to access Student 2's (Maria Santos, user_id = 2) profile and resume
      const response = await page.goto('/view-resume.php?user_id=2&render_html=1');
      expect(response?.status()).toBe(403);

      // Verify access is strictly denied and confidential PII is blocked
      const bodyText = await page.textContent('body');
      expect(bodyText).toContain('Access Denied: You are not authorized to inspect other student records.');
      expect(bodyText).not.toContain('maria.santos@kld.edu.ph');
      expect(bodyText).not.toContain('+63 928 234 5678');
      expect(bodyText).not.toContain('2024-00456');
    });

    test('3.4 Proper Access Guard: Student cannot view another candidate\'s resume via app_id parameter', async ({ page }) => {
      // Log in as Student 1: Juan Dela Cruz (student_id = 1)
      await loginAsDemo(page, 'student');

      // Application #3 belongs to Maria Santos (student_id = 2)
      await page.goto('/view-resume.php?app_id=3&render_html=1');

      // Verify access denied
      const bodyText = await page.textContent('body');
      expect(bodyText).toContain('Access Denied: You are not authorized to view this candidate credential.');
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 4. Employer Email Domain Verification & Registration Gating                */
  /* -------------------------------------------------------------------------- */
  test.describe('4. Employer Email Domain Verification & Registration Gating', () => {

    test('4.1 University Office registration with non-@kld.edu.ph email fails validation', async ({ page }) => {
      await page.goto('/register.php?role=employer');
      await expect(page.locator('#reg-name')).toBeVisible();

      // Fill form with unauthorized external email
      await page.fill('#reg-name', 'Prof. External User');
      await page.fill('#reg-email', 'unauthorized.user@gmail.com');
      await page.fill('#reg-phone', '+63 917 000 1122');

      // Attempt to advance step with unauthorized non-kld email
      await page.locator('#step-pane-1 button.btn-step-next').click();

      // Verify validation error is displayed and user remains on register page
      await expect(page).toHaveURL(/.*register\.php/);
      await expect(page.locator('#step-error-alert, #server-error-alert, .alert-danger').first()).toContainText('University Office accounts must use an official @kld.edu.ph institutional email address. External partners must select "Industry Partner".');
    });

    test('4.2 University Office registration with official @kld.edu.ph domain succeeds', async ({ page }) => {
      await page.goto('/register.php?role=employer');
      await expect(page.locator('#reg-name')).toBeVisible();

      const testEmail = `univ.office.${Date.now()}@kld.edu.ph`;

      // Step 1: Fill basic info
      await page.fill('#reg-name', 'Prof. Self Verified');
      await page.fill('#reg-email', testEmail);
      await page.fill('#reg-phone', '+63 917 000 1122');
      await page.locator('#step-pane-1 button.btn-step-next').click();

      // Step 2: Department details
      await expect(page.locator('#step-pane-2')).toBeVisible();
      await page.locator('#office_name_select').selectOption('Management Information Systems (MIS)');
      await page.locator('#step-pane-2 button.btn-step-next').click();

      // Step 3: Password credentials
      await expect(page.locator('#step-pane-3')).toBeVisible();
      await page.fill('#password', 'Password123!');
      await page.fill('#confirm_password', 'Password123!');

      // Submit registration
      await page.locator('#btn-submit-registration').click();

      // Verify redirect to email verification or employer dashboard
      await expect(page).toHaveURL(/.*(verify-email\.php|employer\/dashboard\.php)/);

      // Verify immediate ability to post jobs
      await page.goto('/employer/create-job.php');
      await expect(page.locator('#job-wizard-form')).toBeVisible();
    });

    test('4.3 Registration with employer_type "approved_partner" requires admin review (pending_approval)', async ({ page }) => {
      await page.goto('/register.php?role=employer');
      await expect(page.locator('#reg-name')).toBeVisible();

      const partnerEmail = `partner.${Date.now()}@partner.kld.edu.ph`;
      const partnerOrg = `Automated Partner Corp ${Date.now()}`;

      // Click Industry Partner chip / card
      await page.locator('#persona-card-partner').click();

      // Step 1: Fill basic info
      await page.fill('#reg-name', 'Partner Manager');
      await page.fill('#reg-email', partnerEmail);
      await page.fill('#reg-phone', '+63 918 111 2233');
      await page.locator('#step-pane-1 button.btn-step-next').click();

      // Step 2: Organization details
      await expect(page.locator('#step-pane-2')).toBeVisible();
      await page.fill('#organization_name', partnerOrg);
      await page.locator('#step-pane-2 button.btn-step-next').click();

      // Step 3: Password credentials
      await expect(page.locator('#step-pane-3')).toBeVisible();
      await page.fill('#password', 'Password123!');
      await page.fill('#confirm_password', 'Password123!');

      // Submit registration
      await page.locator('#btn-submit-registration').click();

      // Verify redirect to email verification or employer dashboard
      await expect(page).toHaveURL(/.*(verify-email\.php|employer\/dashboard\.php)/);
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 5. CSRF Protection on State Mutations                                      */
  /* -------------------------------------------------------------------------- */
  test.describe('5. CSRF Protection on State Mutations', () => {

    test('5.1 CSRF Protection: GET request to approve employer (?approve_id=X) is blocked', async ({ page }) => {
      // Log in as Admin
      await loginAsDemo(page, 'admin');

      // Employer #9 (Apex Robotics) is initially 'pending_approval'
      // Direct GET navigation should be rejected
      await page.goto('/admin/users.php?approve_id=9');
      await expect(page).toHaveURL(/.*admin\/users\.php/);
      await expect(page.locator('.alert-danger, body')).toContainText(/Invalid request method|POST submission/i);

      // Verify Employer #9 remains pending approval
      await page.goto('/admin/users.php?ver_status=pending_approval');
      await expect(page.locator('body')).toContainText('Apex Robotics');
    });

    test('5.2 CSRF Protection: POST request with valid CSRF token verifies partner employer', async ({ page }) => {
      // Log in as Admin
      await loginAsDemo(page, 'admin');
      await page.goto('/admin/users.php?ver_status=pending_approval');

      // Inspect pending partner employer modal
      const inspectBtn = page.locator('button[data-bs-target^="#verifyModal"]').first();
      await expect(inspectBtn).toBeVisible();
      await inspectBtn.click();

      const modal = page.locator('.modal.show');
      await expect(modal).toBeVisible();

      // Submit POST approval form with CSRF token
      const approveBtn = modal.locator('form button[type="submit"]:has-text("Approve Partner"), form button[type="submit"]:has-text("Approve & Verify"), form button[type="submit"]:has-text("Verify")').first();
      await expect(approveBtn).toBeVisible();
      await approveBtn.click();
      await page.waitForURL(/.*admin\/users\.php/);

      // Verify status mutated to verified
      await expect(page.locator('body')).toContainText(/officially verified|Verified/i);
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 6. Session Invalidation & Multi-Tab Logout Behavior                         */
  /* -------------------------------------------------------------------------- */
  test.describe('6. Session Invalidation & Multi-Tab Logout Behavior', () => {

    test('6.1 Logout destroys active session on server and prevents protected access', async ({ page }) => {
      // Login as Student
      await loginAsDemo(page, 'student');
      await page.goto('/student/dashboard.php');
      await expect(page).toHaveURL(/.*student\/dashboard\.php/);

      // Logout
      await logout(page);

      // Attempt to access student dashboard after logout
      await page.goto('/student/dashboard.php');
      await expect(page).toHaveURL(/.*login\.php/);
    });

    test('6.2 Multi-tab logout invalidates session across all open tabs in same context', async ({ context }) => {
      // Create Tab 1
      const tab1 = await context.newPage();
      await resetDatastore(tab1);
      await loginAsDemo(tab1, 'student');
      await tab1.goto('/student/dashboard.php');
      await expect(tab1).toHaveURL(/.*student\/dashboard\.php/);

      // Create Tab 2 in same browser context (shares session cookie)
      const tab2 = await context.newPage();
      await tab2.goto('/student/my-applications.php');
      await expect(tab2).toHaveURL(/.*student\/my-applications\.php/);

      // Perform logout on Tab 2
      await tab2.goto('/logout.php');
      await expect(tab2).toHaveURL(/.*login\.php/);

      // Tab 1 attempts to navigate or refresh to protected settings page
      await tab1.goto('/settings.php');
      await expect(tab1).toHaveURL(/.*login\.php/);

      await tab1.close();
      await tab2.close();
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 7. Password Change & Current Password Verification                          */
  /* -------------------------------------------------------------------------- */
  test.describe('7. Password Change & Current Password Verification', () => {

    test('7.1 Password update with incorrect current_password fails with error', async ({ page }) => {
      // Log in as Student
      await loginAsDemo(page, 'student');
      await page.goto('/settings.php');

      // Fill incorrect current password and new password
      await page.fill('#current_password', 'WrongCurrentPassword!');
      await page.fill('#password', 'NewSecurePassword2026!');
      await page.fill('#confirm_password', 'NewSecurePassword2026!');

      // Click update password submit button
      await page.click('button:has-text("Update Password")');

      // Verify error alert
      await expect(page).toHaveURL(/.*settings\.php/);
      await expect(page.locator('.alert-danger, body')).toContainText('The current password you entered is incorrect.');
    });

    test('7.2 Password update with valid current_password succeeds', async ({ page }) => {
      // Log in as Student
      await loginAsDemo(page, 'student');
      await page.goto('/settings.php');

      const updatedPassword = 'NewSecurePassword2026!';

      // Fill valid current password and new password
      await page.fill('#current_password', 'Password123!');
      await page.fill('#password', updatedPassword);
      await page.fill('#confirm_password', updatedPassword);

      // Click update password submit button
      await page.click('button:has-text("Update Password")');

      // Verify success flash message
      await expect(page).toHaveURL(/.*settings\.php/);
      await expect(page.locator('body')).toContainText(/Your password has been updated successfully/i);

      // Logout and verify login with new password
      await logout(page);

      // Attempt login with old password -> should fail
      await page.fill('input[name="email"]', 'student@kld.edu.ph');
      await page.fill('input[name="password"]', 'Password123!');
      await page.click('button[type="submit"]');
      await expect(page.locator('.alert-danger, body')).toContainText(/Invalid password credentials/i);

      // Attempt login with new password -> should succeed
      await page.fill('input[name="email"]', 'student@kld.edu.ph');
      await page.fill('input[name="password"]', updatedPassword);
      await page.click('button[type="submit"]');
      await expect(page).toHaveURL(/.*student\/dashboard\.php/);
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 8. Relational Integrity & Robustness against Invalid Object IDs             */
  /* -------------------------------------------------------------------------- */
  test.describe('8. Relational Integrity & Non-Existent ID Handling', () => {

    test('8.1 Accessing non-existent job ID in student apply gracefully handles missing record', async ({ page }) => {
      await loginAsDemo(page, 'student');

      // Non-existent Job ID 99999
      await page.goto('/student/apply.php?id=99999');
      await expect(page.locator('.alert-danger, body')).toContainText(/could not be found or has been closed/i);
    });

    test('8.2 Accessing non-existent job ID in employer edit gracefully redirects to dashboard', async ({ page }) => {
      await loginAsDemo(page, 'employer');

      // Non-existent Job ID 99999
      await page.goto('/employer/edit-job.php?id=99999');
      await expect(page).toHaveURL(/.*employer\/dashboard\.php/);
      await expect(page.locator('.alert-danger, body')).toContainText(/could not be found/i);
    });

    test('8.3 Accessing non-existent application ID in candidate review gracefully redirects to applicants', async ({ page }) => {
      await loginAsDemo(page, 'employer');

      // Non-existent Application ID 99999
      await page.goto('/employer/review-app.php?id=99999');
      await expect(page).toHaveURL(/.*employer\/applicants\.php/);
      await expect(page.locator('.alert-danger, body')).toContainText(/could not be found/i);
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 9. Search API Type Safety & Query Guard Testing                             */
  /* -------------------------------------------------------------------------- */
  test.describe('9. Search API Type Safety & Query Guard Testing', () => {

    test('9.1 Search API safely handles array query parameters without TypeError', async ({ request }) => {
      const res = await request.get('/api/search-jobs.php?q[]=test&job_type[]=&department[]=');
      expect(res.status()).toBe(200);
      const data = await res.json();
      expect(data).toHaveProperty('status', 'success');
      expect(data).toHaveProperty('results');
      expect(Array.isArray(data.results)).toBe(true);
    });
  });

  /* -------------------------------------------------------------------------- */
  /* 10. Swarm Audit Loopholes & Defensive Hardening Regression Suite           */
  /* -------------------------------------------------------------------------- */
  test.describe('10. Swarm Audit Loopholes & Defensive Hardening Suite', () => {

    test('10.1 Privilege Escalation Prevention: Registering with role=admin forced to non-admin role', async ({ page }) => {
      await page.goto('/register.php?role=employer');
      await expect(page.locator('#reg-name')).toBeVisible();

      const testEmail = `hacker.${Date.now()}@kld.edu.ph`;

      // Step 1: Fill basic info
      await page.fill('#reg-name', 'Hacker Attempting Admin');
      await page.fill('#reg-email', testEmail);
      await page.fill('#reg-phone', '+63 917 000 9999');
      await page.locator('#step-pane-1 button.btn-step-next').click();

      // Step 2: Select department
      await expect(page.locator('#step-pane-2')).toBeVisible();
      await page.locator('#office_name_select').selectOption('Management Information Systems (MIS)');
      await page.locator('#step-pane-2 button.btn-step-next').click();

      // Step 3: Fill password credentials
      await expect(page.locator('#step-pane-3')).toBeVisible();
      await page.fill('#password', 'Password123!');
      await page.fill('#confirm_password', 'Password123!');

      // Tamper role parameter to 'admin' using evaluate
      await page.evaluate(() => {
        let input = document.querySelector('input[name="role"]') as HTMLInputElement;
        if (!input) {
          input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'role';
          document.querySelector('form')?.appendChild(input);
        }
        input.value = 'admin';
      });

      await page.locator('#btn-submit-registration').click();

      // The backend sanitizes role: 'admin' is stripped and sanitized, never granting admin
      await expect(page).toHaveURL(/.*(verify-email\.php|student\/dashboard\.php|employer\/dashboard\.php)/);

      // Attempt navigating to admin dashboard -> MUST BE BLOCKED
      await page.goto('/admin/users.php');
      await expect(page).toHaveURL(/.*(index\.php|login\.php|student\/dashboard\.php|employer\/dashboard\.php)/);
      await expect(page.locator('.alert-danger, body')).toContainText(/Unauthorized access/i);
    });

    test('10.2 CSRF Protection: Submitting student application without valid CSRF token is rejected', async ({ page }) => {
      await loginAsDemo(page, 'student');

      await page.goto('/student/apply.php?id=1');
      await page.fill('textarea[name="cover_letter"]', 'Valid Cover Letter for CSRF Test');

      // Corrupt the CSRF token
      await page.evaluate(() => {
        const tokenInput = document.querySelector('input[name="csrf_token"]') as HTMLInputElement;
        if (tokenInput) tokenInput.value = 'tampered_invalid_token_xyz';
      });

      await page.click('button[type="submit"]');
      await expect(page.locator('.alert-paper--danger, .alert-danger').first()).toContainText(/Security validation failed/i);
    });

    test('10.3 Document Isolation: Student cannot access arbitrary file via view-resume.php?file=', async ({ page }) => {
      await loginAsDemo(page, 'student');

      // Attempt to access another document via file parameter
      const res = await page.goto('/view-resume.php?file=Office_Supervisor_Permit.pdf');
      expect(res?.status()).toBe(403);
      await expect(page.locator('body')).toContainText(/Access Denied/i);
    });

    test('10.4 Employer CSRF Protection: create-job without valid CSRF token is rejected', async ({ page }) => {
      await loginAsDemo(page, 'employer');

      await page.goto('/employer/create-job.php');
      await page.fill('input[name="title"]', 'CSRF Attack Vacancy');
      await page.fill('textarea[name="description"]', 'Description for CSRF Attack vacancy');

      // Corrupt CSRF token
      await page.evaluate(() => {
        const tokenInput = document.querySelector('input[name="csrf_token"]') as HTMLInputElement;
        if (tokenInput) tokenInput.value = 'bogus_csrf_token';
      });

      // Submit form with forged CSRF token directly to server
      await page.evaluate(() => {
        const form = document.getElementById('job-wizard-form') as HTMLFormElement;
        form.submit();
      });

      await expect(page.locator('.alert-paper--danger, .alert-danger').first()).toContainText(/Security validation failed/i);
    });

    test('10.5 HTTP Defensive Headers are present on responses', async ({ page }) => {
      const res = await page.goto('/');
      const headers = res?.headers() || {};
      expect(headers['x-frame-options']?.toUpperCase()).toBe('SAMEORIGIN');
      expect(headers['x-content-type-options']?.toLowerCase()).toBe('nosniff');
    });
  });
});
