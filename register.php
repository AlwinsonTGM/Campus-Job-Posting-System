<?php
/**
 * Campus Job Posting System - User Registration
 * Archetype A: Auth Split Card Shell & Live Password Meter (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

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
    $sex = $_POST['sex'] ?? 'Male';
    $birthdate = trim($_POST['birthdate'] ?? '');
    $age = !empty($birthdate) ? calculate_age($birthdate) : 20;
    $phone = trim($_POST['phone'] ?? '');
    $office_location = trim($_POST['office_location'] ?? '');
    $accreditation_number = trim($_POST['accreditation_number'] ?? '');

    // Server-side validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password and Confirm Password do not match.';
    } elseif ($role === 'student' && (empty($student_id) || empty($department) || empty($course) || empty($birthdate))) {
        $error = 'Please complete all student profile fields (Student ID, Institute, Degree Program, and Date of Birth).';
    } else {
        $permit_file_path = null;
        if ($role === 'employer' && isset($_FILES['permit_photo']) && ($_FILES['permit_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $permit_file_path = save_uploaded_permit($_FILES['permit_photo']);
        }

        $proof_file_path = null;
        if ($role === 'student') {
            if (isset($_FILES['student_proof']) && ($_FILES['student_proof']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $proof_file_path = save_uploaded_proof($_FILES['student_proof']);
            }
            if (!$proof_file_path) {
                $error = 'Please upload a valid Certificate of Registration (COR) or Student ID attachment (PDF, JPG, PNG).';
            }
        }

        if (!$error) {
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
                'sex' => $sex,
                'birthdate' => $birthdate,
                'age' => $age,
                'phone' => $phone,
                'office_location' => $office_location,
                'accreditation_number' => $accreditation_number,
                'permit_file' => $permit_file_path,
                'proof_file' => $proof_file_path
            ], $permit_file_path, $proof_file_path);

            if ($res['success']) {
                if ($role === 'employer' && $employer_type === 'approved_partner') {
                    set_flash('success', 'Account registered! Your business permit and partner profile will be reviewed by Career Services.');
                } else {
                    set_flash('success', 'Account registered successfully! Welcome to Campus Hire.');
                }
                if ($role === 'student') header('Location: student/dashboard.php');
                else header('Location: employer/dashboard.php');
                exit;
            } else {
                $error = $res['message'];
            }
        }
    }
}

$page_title = 'Create an Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Archetype A: Auth Split Card Shell -->
                <div class="auth-shell">
                    
                    <!-- Left Brand Panel -->
                    <div class="auth-brand-panel">
                        <div>
                            <!-- Brand Mark -->
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-3 p-2 shadow-sm" style="width: 38px; height: 38px;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#2ECC5E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="fw-extrabold text-ink fs-5 tracking-tight"><?= htmlspecialchars(SITE_NAME) ?></span>
                            </div>

                            <span class="pill-badge mb-3">
                                <i class="bi bi-person-plus-fill text-accent"></i> New Registration
                            </span>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Join the Campus Job & Talent Network
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Create an account to access verified assistantship opportunities, submit applications, or post department vacancies.
                            </p>

                            <!-- Feature List -->
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-accent">
                                        <i class="bi bi-search"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Schedule-Friendly On-Campus Vacancies</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-accent">
                                        <i class="bi bi-building-check"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Accredited Offices & Enterprise Partners</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-accent">
                                        <i class="bi bi-file-earmark-check"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Digital DTR & Stipend Tracking</span>
                                </div>
                            </div>
                        </div>

                        <!-- Data Privacy & Legal Compliance Callout -->
                        <div class="p-3 bg-white rounded-4 border border-line mt-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-shield-check text-accent fs-5"></i>
                                <span class="small fw-bold text-ink">RA 10173 Privacy Protected</span>
                            </div>
                            <p class="small text-muted-custom mb-0" style="font-size: 11.5px;">
                                Your academic credentials and application records are encrypted and protected under Philippine Data Privacy regulations.
                            </p>
                        </div>

                    </div>

                    <!-- Right Form Panel -->
                    <div class="auth-form-panel">
                        
                        <div class="mb-4">
                            <h2 class="card-paper-title fs-4 mb-1">Create Your Account</h2>
                            <p class="text-muted-custom small mb-0">Select your account type to customize your registration form</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST" id="register-form" enctype="multipart/form-data" class="form-paper" novalidate>
                            
                            <!-- Role Switcher -->
                            <div class="mb-4">
                                <label class="form-label">Account Role</label>
                                <div class="d-flex gap-2">
                                    <input type="radio" class="d-none" name="role" id="role-student" value="student" <?= $preselected_role !== 'employer' ? 'checked' : '' ?>>
                                    <label class="chip chip-selectable flex-grow-1 text-center py-2 <?= $preselected_role !== 'employer' ? 'active' : '' ?>" for="role-student" id="lbl-role-student">
                                        <i class="bi bi-mortarboard-fill text-accent me-1"></i> Student Applicant
                                    </label>

                                    <input type="radio" class="d-none" name="role" id="role-employer" value="employer" <?= $preselected_role === 'employer' ? 'checked' : '' ?>>
                                    <label class="chip chip-selectable flex-grow-1 text-center py-2 <?= $preselected_role === 'employer' ? 'active' : '' ?>" for="role-employer" id="lbl-role-employer">
                                        <i class="bi bi-building text-accent me-1"></i> Office / Partner
                                    </label>
                                </div>
                            </div>

                            <!-- Employer Sub-classification (Shown when employer selected) -->
                            <div id="employer-classification-section" class="p-3 bg-cream rounded-4 border border-line mb-4" style="display: <?= $preselected_role === 'employer' ? 'block' : 'none' ?>;">
                                <label class="form-label mb-2">Employer Classification</label>
                                <div class="d-flex gap-2 mb-2">
                                    <input type="radio" class="d-none" name="employer_type" id="type-office" value="university_office" checked>
                                    <label class="chip chip-selectable flex-grow-1 text-center py-2 active" for="type-office" id="lbl-type-office">
                                        <i class="bi bi-bank text-accent me-1"></i> University Office / Lab
                                    </label>

                                    <input type="radio" class="d-none" name="employer_type" id="type-partner" value="approved_partner">
                                    <label class="chip chip-selectable flex-grow-1 text-center py-2" for="type-partner" id="lbl-type-partner">
                                        <i class="bi bi-patch-check-fill text-accent me-1"></i> Industry Partner
                                    </label>
                                </div>
                                <div class="small text-muted-custom" id="employer-type-note" style="font-size: 12px;">
                                    <i class="bi bi-info-circle text-accent me-1"></i> For university academic divisions, laboratories, and student services offices.
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="reg-name">Full Name / Representative <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="reg-name" class="form-control" placeholder="Juan Dela Cruz" value="<?= htmlspecialchars($name ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="reg-email">Institutional / Business Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="reg-email" class="form-control" placeholder="username@campus-hire.edu" value="<?= htmlspecialchars($email ?? '') ?>" required>
                                </div>
                            </div>

                            <!-- Student Specific Fields -->
                            <div class="row g-3 mb-3" id="student-fields">
                                <div class="col-md-6">
                                    <label class="form-label" for="student_id">Student ID Number <span class="text-danger">*</span></label>
                                    <input type="text" name="student_id" id="student_id" class="form-control" placeholder="2024-00123" value="<?= htmlspecialchars($student_id ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="department-select">Academic Institute <span class="text-danger">*</span></label>
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
                                    <label class="form-label" for="course-select">Degree Program / Course <span class="text-danger">*</span></label>
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
                                    <label class="form-label" for="year_level">Year Level / Academic Status <span class="text-danger">*</span></label>
                                    <select name="year_level" id="year_level" class="form-select">
                                        <?php foreach (get_year_levels() as $val => $label): ?>
                                            <option value="<?= htmlspecialchars($val) ?>" <?= (isset($year_level) && $year_level === $val) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="reg-sex">Biological Sex <span class="text-danger">*</span></label>
                                    <select name="sex" id="reg-sex" class="form-select">
                                        <?php foreach (get_sex_options() as $val => $label): ?>
                                            <option value="<?= htmlspecialchars($val) ?>" <?= (isset($sex) && $sex === $val) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="reg-birthdate">Date of Birth <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                        <input type="date" name="birthdate" id="reg-birthdate" class="form-control" value="<?= htmlspecialchars($birthdate ?? '') ?>" max="<?= date('Y-m-d', strtotime('-15 years')) ?>">
                                    </div>
                                    <span class="small text-muted-custom" style="font-size: 11px;">Verification for student eligibility (RA 10173 protected).</span>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="reg-student-proof">Certificate of Registration (COR) / School ID <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-check"></i></span>
                                        <input type="file" name="student_proof" id="reg-student-proof" class="form-control" accept="image/*,application/pdf">
                                    </div>
                                    <span class="small text-muted-custom mt-1 d-block" style="font-size: 11.5px;">
                                        <i class="bi bi-shield-lock text-accent me-1"></i> Upload your official COR or valid student ID. Your institutional profile will be locked and verified based on this document.
                                    </span>
                                </div>
                            </div>

                            <!-- Partner Employer Specific Fields -->
                            <div class="row g-3 mb-3" id="partner-fields" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label" for="organization_name">Company / Organization Name <span class="text-danger">*</span></label>
                                    <input type="text" name="organization_name" id="organization_name" class="form-control" placeholder="e.g. TechVanguard Solutions Inc." value="<?= htmlspecialchars($organization_name ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="accreditation_number">MOA Ref / Permit No.</label>
                                    <input type="text" name="accreditation_number" id="accreditation_number" class="form-control" placeholder="e.g. MOA-2026-IT004, SEC, or DTI" value="<?= htmlspecialchars($accreditation_number ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Workplace / Office Address</label>
                                    <input type="text" name="office_location" class="form-control" placeholder="Campus Office / Tech Park / Hybrid" value="<?= htmlspecialchars($office_location ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Business Permit / MOA Attachment</label>
                                    <input type="file" name="permit_photo" class="form-control" accept="image/*,application/pdf">
                                    <span class="small text-muted-custom mt-1 d-block" style="font-size: 11.5px;">
                                        <i class="bi bi-shield-check text-accent me-1"></i> Upload business permit, SEC/DTI registration, or university MOA.
                                    </span>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label class="form-label" for="reg-phone">Contact Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="phone" id="reg-phone" class="form-control" placeholder="+63 917 000 0000" value="<?= htmlspecialchars($phone ?? '') ?>">
                                </div>
                            </div>

                            <hr class="my-4 border-line">

                            <!-- Password & Real-Time Strength Meter -->
                            <div class="mb-3">
                                <label class="form-label" for="password">Account Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Create a secure password" required>
                                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', 'toggle-reg-icon')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="toggle-reg-icon"></i>
                                    </button>
                                </div>
                                
                                <!-- Dynamic Password Strength Bar -->
                                <div class="password-meter-bar">
                                    <div id="password-meter-fill" class="password-meter-fill"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <div id="password-strength-text" class="small text-muted-custom">
                                        Enter password to see strength
                                    </div>
                                    <span class="small text-muted-custom" style="font-size: 11px;">Min. 8 characters</span>
                                </div>

                                <!-- Live Checklist -->
                                <div class="p-3 bg-cream rounded-3 mt-2 border border-line">
                                    <span class="small fw-bold text-ink d-block mb-2">Password Requirements:</span>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div id="req-length" class="req-item unmet">
                                                <i class="bi bi-circle text-muted-custom"></i> At least 8 characters
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div id="req-upper" class="req-item unmet">
                                                <i class="bi bi-circle text-muted-custom"></i> Uppercase (A-Z)
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div id="req-lower" class="req-item unmet">
                                                <i class="bi bi-circle text-muted-custom"></i> Lowercase (a-z)
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div id="req-number" class="req-item unmet">
                                                <i class="bi bi-circle text-muted-custom"></i> Number (0-9)
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div id="req-special" class="req-item unmet">
                                                <i class="bi bi-circle text-muted-custom"></i> Special symbol (!@#$%^&*)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label class="form-label" for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-type your password" required>
                                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('confirm_password', 'toggle-conf-icon')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="toggle-conf-icon"></i>
                                    </button>
                                </div>
                                <div id="confirm-feedback" class="mt-1"></div>
                            </div>

                            <!-- Terms & Conditions Checkbox -->
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="termsCheck" required checked>
                                <label class="form-check-label small text-muted-custom" for="termsCheck">
                                    I agree to the <a href="terms.php" target="_blank" class="text-ink fw-bold text-decoration-none">Terms of Service</a> and have read the <a href="privacy.php" target="_blank" class="text-ink fw-bold text-decoration-none">Data Privacy Policy (RA 10173)</a>.
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-pill w-100 mb-3">
                                <i class="bi bi-check-circle-fill"></i> REGISTER ACCOUNT
                            </button>
                        </form>

                        <!-- Alternate Links -->
                        <div class="border-top border-line pt-3 text-center">
                            <span class="text-muted-custom small">Already have an account?</span>
                            <a href="login.php" class="text-ink fw-bold small ms-1 text-decoration-none">
                                Sign In Here <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>

<script src="assets/js/password-strength.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleStudent = document.getElementById('role-student');
    const roleEmployer = document.getElementById('role-employer');
    const lblRoleStudent = document.getElementById('lbl-role-student');
    const lblRoleEmployer = document.getElementById('lbl-role-employer');

    const employerClassSec = document.getElementById('employer-classification-section');
    const typeOffice = document.getElementById('type-office');
    const typePartner = document.getElementById('type-partner');
    const lblTypeOffice = document.getElementById('lbl-type-office');
    const lblTypePartner = document.getElementById('lbl-type-partner');

    const studentFields = document.getElementById('student-fields');
    const partnerFields = document.getElementById('partner-fields');
    const employerTypeNote = document.getElementById('employer-type-note');

    function syncRoleUI() {
        if (roleStudent.checked) {
            lblRoleStudent.classList.add('active');
            lblRoleEmployer.classList.remove('active');
            if (employerClassSec) employerClassSec.style.display = 'none';
            if (studentFields) studentFields.style.display = 'flex';
            if (partnerFields) partnerFields.style.display = 'none';
        } else {
            lblRoleEmployer.classList.add('active');
            lblRoleStudent.classList.remove('active');
            if (employerClassSec) employerClassSec.style.display = 'block';

            if (typePartner.checked) {
                lblTypePartner.classList.add('active');
                lblTypeOffice.classList.remove('active');
                if (studentFields) studentFields.style.display = 'none';
                if (partnerFields) partnerFields.style.display = 'flex';
                if (employerTypeNote) employerTypeNote.innerHTML = '<i class="bi bi-patch-check text-accent me-1"></i> For accredited industry partners and campus concessionaires. Account will be verified by Career Services.';
            } else {
                lblTypeOffice.classList.add('active');
                lblTypePartner.classList.remove('active');
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
