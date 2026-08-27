<?php
/**
 * Campus Job Posting System - Registration Page
 */
require_once __DIR__ . '/includes/data-helper.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
$preselected_role = $_GET['role'] ?? 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $employer_type = $_POST['employer_type'] ?? 'university_office';
    $organization_name = trim($_POST['organization_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year_level = $_POST['year_level'] ?? '1st Year';
    $phone = trim($_POST['phone'] ?? '');
    $office_location = trim($_POST['office_location'] ?? '');
    $accreditation_number = trim($_POST['accreditation_number'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid institutional or business email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password and Confirm Password do not match.';
    } else {
        $res = register_user([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'employer_type' => $employer_type,
            'organization_name' => $organization_name ?: ($department ?: $name),
            'student_id' => $student_id,
            'department' => $department ?: ($organization_name ?: 'General'),
            'course' => $course,
            'year_level' => $year_level,
            'phone' => $phone,
            'office_location' => $office_location,
            'accreditation_number' => $accreditation_number
        ]);

        if ($res['success']) {
            if ($role === 'employer' && $employer_type === 'approved_partner') {
                set_flash('success', 'Account registered! Your partner profile is being verified by University Career Services.');
            } else {
                set_flash('success', 'Account registered successfully! Welcome to the Campus Job Portal.');
            }
            if ($role === 'student') header('Location: student/dashboard.php');
            else header('Location: employer/dashboard.php');
            exit;
        } else {
            $error = $res['message'];
        }
    }
}

$page_title = 'Create an Account';
$extra_js = ['assets/js/password-strength.js'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 bg-surface flex-grow-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-7">
                
                <div class="card border-line shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <div class="stat-icon bg-accent-soft text-ink mx-auto mb-2">
                            <i class="bi bi-person-plus-fill fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-ink mb-1">Create Your Account</h3>
                        <p class="text-muted-custom small mb-0">Register as a Student Applicant, Campus Office, or Approved Partner Employer</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4 rounded-3">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" id="register-form" novalidate>
                        
                        <!-- Account Role Selector -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-ink text-uppercase">Account Type / Role</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role-student" value="student" <?= $preselected_role !== 'employer' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" for="role-student">
                                        <i class="bi bi-mortarboard-fill"></i> Student Applicant
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role-employer" value="employer" <?= $preselected_role === 'employer' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" for="role-employer">
                                        <i class="bi bi-building"></i> Campus Office / Partner
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Employer Classification Sub-selector (Visible when Employer is selected) -->
                        <div id="employer-classification-section" class="p-3 bg-cream rounded-3 border-line border mb-4" style="display: <?= $preselected_role === 'employer' ? 'block' : 'none' ?>;">
                            <label class="form-label small fw-bold text-ink text-uppercase mb-2">Employer Classification</label>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="employer_type" id="type-office" value="university_office" checked>
                                    <label class="btn btn-outline-secondary w-100 py-2 small d-flex align-items-center justify-content-center gap-1" for="type-office">
                                        <i class="bi bi-bank"></i> University Office / Lab
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="employer_type" id="type-partner" value="approved_partner">
                                    <label class="btn btn-outline-secondary w-100 py-2 small d-flex align-items-center justify-content-center gap-1" for="type-partner">
                                        <i class="bi bi-patch-check-fill text-accent"></i> Approved Industry Partner
                                    </label>
                                </div>
                            </div>
                            <div class="small text-muted-custom" id="employer-type-note">
                                <i class="bi bi-info-circle text-accent me-1"></i> For university academic divisions, laboratories, and student services offices.
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Full Name / Authorized Rep <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Juan Dela Cruz" value="<?= htmlspecialchars($name ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Institutional or Business Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="username@campus-hire.edu" value="<?= htmlspecialchars($email ?? '') ?>" required>
                            </div>
                        </div>

                        <!-- Student Specific Fields -->
                        <div class="row g-3 mb-3" id="student-fields">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Student ID Number</label>
                                <input type="text" name="student_id" class="form-control" placeholder="2024-00123" value="<?= htmlspecialchars($student_id ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Academic Institute</label>
                                <select name="department" class="form-select" id="department-select">
                                    <option value="">Select Institute / Office</option>
                                    <optgroup label="Academic Institutes">
                                        <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                            <option value="<?= htmlspecialchars($inst) ?>" <?= (isset($department) && $department === $inst) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Administrative Offices">
                                        <option value="Office of the University Registrar" <?= (isset($department) && $department === 'Office of the University Registrar') ? 'selected' : '' ?>>Office of the University Registrar</option>
                                        <option value="Student Affairs & Services Office (SASO)" <?= (isset($department) && $department === 'Student Affairs & Services Office (SASO)') ? 'selected' : '' ?>>Student Affairs & Services Office (SASO)</option>
                                        <option value="Management Information Systems (MIS)" <?= (isset($department) && $department === 'Management Information Systems (MIS)') ? 'selected' : '' ?>>Management Information Systems (MIS)</option>
                                        <option value="KLD University Library" <?= (isset($department) && $department === 'KLD University Library') ? 'selected' : '' ?>>KLD University Library</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Degree Program / Course</label>
                                <select name="course" class="form-select" id="course-select">
                                    <option value="">Select Degree Program</option>
                                    <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                        <optgroup label="<?= htmlspecialchars($inst) ?>">
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?= htmlspecialchars($c) ?>" <?= (isset($course) && $course === $c) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Year Level</label>
                                <select name="year_level" class="form-select">
                                    <option value="1st Year" <?= (isset($year_level) && $year_level === '1st Year') ? 'selected' : '' ?>>1st Year</option>
                                    <option value="2nd Year" <?= (!isset($year_level) || $year_level === '2nd Year') ? 'selected' : '' ?>>2nd Year</option>
                                    <option value="3rd Year" <?= (isset($year_level) && $year_level === '3rd Year') ? 'selected' : '' ?>>3rd Year</option>
                                    <option value="4th Year" <?= (isset($year_level) && $year_level === '4th Year') ? 'selected' : '' ?>>4th Year</option>
                                </select>
                            </div>
                        </div>

                        <!-- Partner Employer Specific Fields -->
                        <div class="row g-3 mb-3" id="partner-fields" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Company / Organization Name <span class="text-danger">*</span></label>
                                <input type="text" name="organization_name" class="form-control" placeholder="e.g. TechVanguard Solutions Inc." value="<?= htmlspecialchars($organization_name ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">MOA Reference / Business Permit</label>
                                <input type="text" name="accreditation_number" class="form-control" placeholder="e.g. MOA-2026-IT004" value="<?= htmlspecialchars($accreditation_number ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-ink">Office Location / Workplace Address</label>
                                <input type="text" name="office_location" class="form-control" placeholder="e.g. Dasma Tech Park (Near Campus) / Hybrid" value="<?= htmlspecialchars($office_location ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">Contact Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+63 917 000 0000">
                        </div>

                        <hr class="my-4 border-line">

                        <!-- Password & Real-Time Strength Meter Section -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">
                                Account Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock text-muted-custom"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Create a secure password" required>
                            </div>
                            
                            <!-- Dynamic Password Strength Bar -->
                            <div class="password-meter-bar">
                                <div id="password-meter-fill" class="password-meter-fill"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div id="password-strength-text" class="small text-muted-custom">
                                    Enter password to see strength
                                </div>
                                <span class="small text-muted-custom">Min. 8 characters</span>
                            </div>

                            <!-- Live Password Criteria Checklist -->
                            <div class="p-3 bg-cream rounded-3 mt-2 border-line border">
                                <span class="small fw-bold text-ink d-block mb-2">Password Requirements:</span>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div id="req-length" class="req-item unmet">
                                            <i class="bi bi-circle text-muted-custom"></i> At least 8 characters
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="req-upper" class="req-item unmet">
                                            <i class="bi bi-circle text-muted-custom"></i> Uppercase letter (A-Z)
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="req-lower" class="req-item unmet">
                                            <i class="bi bi-circle text-muted-custom"></i> Lowercase letter (a-z)
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="req-number" class="req-item unmet">
                                            <i class="bi bi-circle text-muted-custom"></i> At least one number (0-9)
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div id="req-special" class="req-item unmet">
                                            <i class="bi bi-circle text-muted-custom"></i> Special character (!@#$%^&*)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-ink">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill text-muted-custom"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-type your password" required>
                            </div>
                            <div id="confirm-feedback"></div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheck" required checked>
                            <label class="form-check-label small text-muted-custom" for="termsCheck">
                                I agree to the <a href="terms.php" target="_blank" class="text-ink fw-bold">Terms of Service</a> and have read the <a href="privacy.php" target="_blank" class="text-ink fw-bold">Data Privacy Policy (RA 10173)</a>.
                            </label>
                        </div>

                        <button type="submit" class="btn-accent-pill w-100 py-3 mb-3">
                            <i class="bi bi-check-circle"></i> REGISTER ACCOUNT
                        </button>
                    </form>

                    <div class="border-top border-line pt-3 text-center">
                        <span class="text-muted-custom small">Already have an account?</span>
                        <a href="login.php" class="text-decoration-none fw-bold text-accent small ms-1">
                            Sign In Here <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleStudent = document.getElementById('role-student');
    const roleEmployer = document.getElementById('role-employer');
    const employerClassSec = document.getElementById('employer-classification-section');
    const typeOffice = document.getElementById('type-office');
    const typePartner = document.getElementById('type-partner');
    const studentFields = document.getElementById('student-fields');
    const partnerFields = document.getElementById('partner-fields');
    const employerTypeNote = document.getElementById('employer-type-note');

    function syncRoleUI() {
        if (roleStudent.checked) {
            if (employerClassSec) employerClassSec.style.display = 'none';
            if (studentFields) studentFields.style.display = 'flex';
            if (partnerFields) partnerFields.style.display = 'none';
        } else {
            if (employerClassSec) employerClassSec.style.display = 'block';
            if (typePartner.checked) {
                if (studentFields) studentFields.style.display = 'none';
                if (partnerFields) partnerFields.style.display = 'flex';
                if (employerTypeNote) employerTypeNote.innerHTML = '<i class="bi bi-patch-check text-accent me-1"></i> For accredited industry partners, local businesses, and campus concessionaires. Account will be verified by Career Services.';
            } else {
                if (studentFields) studentFields.style.display = 'flex';
                if (partnerFields) partnerFields.style.display = 'none';
                if (employerTypeNote) employerTypeNote.innerHTML = '<i class="bi bi-info-circle text-accent me-1"></i> For university academic divisions, laboratories, and student services offices.';
            }
        }
    }

    if (roleStudent && roleEmployer) {
        roleStudent.addEventListener('change', syncRoleUI);
        roleEmployer.addEventListener('change', syncRoleUI);
    }
    if (typeOffice && typePartner) {
        typeOffice.addEventListener('change', syncRoleUI);
        typePartner.addEventListener('change', syncRoleUI);
    }
    syncRoleUI();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
