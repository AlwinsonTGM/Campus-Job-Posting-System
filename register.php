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
$initial_step = 1;
$selected_persona = 'student';
if ($preselected_role === 'employer') {
    $emp_t_get = $_GET['type'] ?? ($_GET['employer_type'] ?? 'university_office');
    $selected_persona = ($emp_t_get === 'approved_partner') ? 'approved_partner' : 'university_office';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $raw_name = trim($_POST['name'] ?? '');

    if (!empty($raw_name) && empty($first_name)) {
        $parts = explode(' ', $raw_name);
        $first_name = array_shift($parts);
        $last_name = !empty($parts) ? implode(' ', $parts) : $first_name;
        $name = $raw_name;
    } else {
        $name = trim($first_name . ($middle_name !== '' ? ' ' . $middle_name : '') . ' ' . $last_name);
    }
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $raw_role = $_POST['role'] ?? 'student';
    $role = in_array($raw_role, ['student', 'employer'], true) ? $raw_role : 'student';
    $employer_type = $_POST['employer_type'] ?? 'university_office';
    $preselected_role = $role;
    if ($role === 'employer') {
        $selected_persona = ($employer_type === 'approved_partner') ? 'approved_partner' : 'university_office';
    } else {
        $selected_persona = 'student';
    }
    $organization_name = trim($_POST['organization_name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    
    if ($role === 'student') {
        if ($department === 'Other Institute / Outsider' && !empty($_POST['other_institute'])) {
            $department = trim($_POST['other_institute']);
        }
    } elseif ($role === 'employer') {
        if ($employer_type === 'university_office') {
            $office_dept = trim($_POST['office_department'] ?? '');
            if (!empty($office_dept)) {
                $department = $office_dept;
                $organization_name = $office_dept;
            }
            $office_location = trim($_POST['office_location'] ?? '');
            $accreditation_number = trim($_POST['office_accreditation_number'] ?? '');
        }
    }
    
    $course = trim($_POST['course'] ?? '');
    $year_level = $_POST['year_level'] ?? '1st Year';
    $sex = trim($_POST['sex'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $age = !empty($birthdate) ? calculate_age($birthdate) : 20;
    $phone = trim($_POST['phone'] ?? '');
    $office_location = trim($_POST['office_location'] ?? '');
    $accreditation_number = trim($_POST['accreditation_number'] ?? '');

    // Server-side validation
    if ((empty($first_name) && empty($raw_name)) || empty($email) || empty($password)) {
        $error = 'Please fill in all mandatory fields (Name, Email, Password).';
        $initial_step = empty($email) || (empty($first_name) && empty($raw_name)) ? 1 : 3;
    } elseif (empty($phone)) {
        $error = 'Please provide a valid contact phone number.';
        $initial_step = 1;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
        $initial_step = 1;
    } elseif ($role === 'employer' && $employer_type === 'university_office' && !preg_match('/@kld\.edu\.ph$/i', $email)) {
        $error = 'University Office accounts must use an official @kld.edu.ph institutional email address. External partners must select "Industry Partner".';
        $initial_step = 1;
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
        $initial_step = 3;
    } elseif ($password !== $confirm_password) {
        $error = 'Password and Confirm Password do not match.';
        $initial_step = 3;
    } elseif ($role === 'student' && (empty($student_id) || empty($department) || empty($course) || empty($sex) || empty($birthdate))) {
        $error = 'Please complete all student profile fields (Student ID, Academic Institute, Degree Program, Biological Sex, and Date of Birth).';
        $initial_step = 2;
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
                $initial_step = 3;
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
                if ($role === 'employer') {
                    set_flash('success', 'Account registered! Your organization credentials have been submitted for administrative verification.');
                    header('Location: employer/dashboard.php');
                } else {
                    set_flash('success', 'Account registered successfully! Your student registration and attached credentials are under review by the Administrator.');
                    header('Location: student/dashboard.php');
                }
                exit;
            } else {
                $error = $res['message'];
                if (stripos($error, 'email') !== false) {
                    $initial_step = 1;
                }
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
                
                <!-- Archetype A: Auth Split Card Shell & Stepper Wizard -->
                <div class="auth-shell">
                    
                    <!-- Left Brand & Stepper Panel -->
                    <div class="auth-brand-panel">
                        <div>
                            <!-- Brand Mark -->
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-3 p-2 shadow-sm" style="width: 38px; height: 38px;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#2ECC5E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <div class="fw-extrabold text-ink fs-6 tracking-tight leading-tight"><?= htmlspecialchars(SITE_NAME) ?></div>
                                    <div class="small text-muted-custom" style="font-size: 11px;">Talent & Placement Portal</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="auth-role-badge" id="brand-role-badge">
                                    <?php if ($selected_persona === 'university_office'): ?>
                                        <i class="bi bi-bank text-accent"></i>
                                        <span>University Office Registration</span>
                                    <?php elseif ($selected_persona === 'approved_partner'): ?>
                                        <i class="bi bi-patch-check-fill text-accent"></i>
                                        <span>Industry Partner Registration</span>
                                    <?php else: ?>
                                        <i class="bi bi-mortarboard-fill text-accent"></i>
                                        <span>Student Applicant Registration</span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <h2 class="h4 fw-bold text-ink mb-1">
                                Join the Campus Talent Network
                            </h2>
                            <p class="text-muted-custom small mb-3" style="font-size: 12.5px;">
                                Complete our 3 quick steps to set up and verify your campus credentials.
                            </p>

                            <!-- Vertical Stepper (Balances the panel height meaningfully) -->
                            <div class="reg-stepper" id="reg-desktop-stepper">
                                <div class="reg-stepper-track"></div>

                                <!-- Step Item 1 -->
                                <div class="reg-step-item is-active is-clickable" id="step-nav-1" onclick="handleStepNavClick(1)">
                                    <div class="reg-step-badge" id="step-badge-1">1</div>
                                    <div class="reg-step-text">
                                        <div class="reg-step-title">
                                            <span>Account & Identity</span>
                                            <i class="bi bi-check-circle-fill text-accent d-none" id="step-check-1"></i>
                                        </div>
                                        <div class="reg-step-desc">Role, legal name & institutional email</div>
                                    </div>
                                </div>

                                <!-- Step Item 2 -->
                                <div class="reg-step-item" id="step-nav-2" onclick="handleStepNavClick(2)">
                                    <div class="reg-step-badge" id="step-badge-2">2</div>
                                    <div class="reg-step-text">
                                        <div class="reg-step-title">
                                            <span id="step-title-text-2">Academic Profile</span>
                                            <i class="bi bi-check-circle-fill text-accent d-none" id="step-check-2"></i>
                                        </div>
                                        <div class="reg-step-desc" id="step-desc-text-2">Institute, program & student ID</div>
                                    </div>
                                </div>

                                <!-- Step Item 3 -->
                                <div class="reg-step-item" id="step-nav-3" onclick="handleStepNavClick(3)">
                                    <div class="reg-step-badge" id="step-badge-3">3</div>
                                    <div class="reg-step-text">
                                        <div class="reg-step-title">
                                            <span>Security & Verification</span>
                                            <i class="bi bi-check-circle-fill text-accent d-none" id="step-check-3"></i>
                                        </div>
                                        <div class="reg-step-desc">COR / permit upload & secure password</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Contextual Hint Box (Changes with active step & role) -->
                            <div class="reg-hint-box mt-3" id="reg-hint-box">
                                <div class="reg-hint-badge" id="reg-hint-badge">
                                    <i class="bi bi-lightbulb-fill"></i> <span id="reg-hint-step-label">Step 1 Tip</span>
                                </div>
                                <p class="reg-hint-text" id="reg-hint-text">
                                    Use your official <strong>@kld.edu.ph</strong> email address for rapid institutional status verification and automatic departmental routing.
                                </p>
                            </div>
                        </div>

                        <!-- Data Privacy & Legal Compliance Callout -->
                        <div class="p-3 bg-white rounded-4 border border-line mt-4">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-shield-check text-accent fs-5"></i>
                                <span class="small fw-bold text-ink">RA 10173 Privacy Protected</span>
                            </div>
                            <p class="small text-muted-custom mb-0" style="font-size: 11.5px; line-height: 1.4;">
                                Academic credentials, government IDs, and application records are encrypted and protected under Philippine Data Privacy regulations.
                            </p>
                        </div>

                    </div>

                    <!-- Right Form Panel -->
                    <div class="auth-form-panel">
                        
                        <!-- Mobile Mini Stepper (shown only on screens < 992px) -->
                        <div class="reg-mobile-stepper" id="reg-mobile-stepper">
                            <div class="reg-mobile-step is-active" id="mob-step-1" onclick="handleStepNavClick(1)">
                                <div class="reg-mobile-dot" id="mob-dot-1">1</div>
                                <span>Identity</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted-custom small"></i>
                            <div class="reg-mobile-step" id="mob-step-2" onclick="handleStepNavClick(2)">
                                <div class="reg-mobile-dot" id="mob-dot-2">2</div>
                                <span id="mob-label-2">Profile</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted-custom small"></i>
                            <div class="reg-mobile-step" id="mob-step-3" onclick="handleStepNavClick(3)">
                                <div class="reg-mobile-dot" id="mob-dot-3">3</div>
                                <span>Verification</span>
                            </div>
                        </div>

                        <!-- Progress Meta Header -->
                        <div class="reg-progress-wrap">
                            <div class="reg-progress-meta">
                                <div>
                                    <span class="reg-progress-step-tag" id="progress-step-tag">Step 1 of 3</span>
                                    <h2 class="reg-progress-title" id="progress-step-title">Account & Basic Details</h2>
                                </div>
                                <div>
                                    <span class="auth-role-badge" id="form-role-badge">
                                        <?php if ($selected_persona === 'university_office'): ?>
                                            <i class="bi bi-bank text-accent"></i>
                                            <span>University Office</span>
                                        <?php elseif ($selected_persona === 'approved_partner'): ?>
                                            <i class="bi bi-patch-check-fill text-accent"></i>
                                            <span>Industry Partner</span>
                                        <?php else: ?>
                                            <i class="bi bi-mortarboard-fill text-accent"></i>
                                            <span>Student Applicant</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="reg-progress-track">
                                <div class="reg-progress-fill" id="reg-progress-fill" style="width: 33.33%;"></div>
                            </div>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-paper alert-paper--danger mb-4" id="server-error-alert">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Client-Side Step Validation Alert Box -->
                        <div class="alert-paper alert-paper--danger mb-4 d-none" id="step-error-alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                                <div class="small fw-semibold text-ink" id="step-error-message">Please fill out all required fields to proceed.</div>
                            </div>
                        </div>

                        <form action="register.php" method="POST" id="register-form" enctype="multipart/form-data" class="form-paper" data-initial-step="<?= $initial_step ?>" novalidate>
                            
                            <!-- ==========================================
                                 STEP PANE 1: ROLE & BASIC IDENTITY
                                 ========================================== -->
                            <div class="reg-step-pane is-visible" id="step-pane-1">
                                
                                <!-- Unified 3-Persona Selection Grid (Eliminates repetitive 2-tier toggles) -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-bold mb-0">Select Registration Persona <span class="text-danger">*</span></label>
                                        <span class="small text-muted-custom" style="font-size: 11.5px;">1-Click Role Selection</span>
                                    </div>

                                    <!-- Hidden Inputs synchronized with persona choice -->
                                    <input type="hidden" name="role" id="reg-role-input" value="<?= htmlspecialchars($preselected_role === 'employer' ? 'employer' : 'student') ?>">
                                    <input type="hidden" name="employer_type" id="reg-employer-type-input" value="<?= htmlspecialchars($employer_type ?? ($selected_persona === 'approved_partner' ? 'approved_partner' : 'university_office')) ?>">

                                    <div class="reg-persona-grid" id="reg-persona-grid" role="radiogroup" aria-label="Registration Persona">
                                        <!-- Persona 1: Student Applicant -->
                                        <div class="reg-persona-card <?= $selected_persona === 'student' ? 'is-selected' : '' ?>" 
                                             id="persona-card-student" 
                                             onclick="selectPersona('student')"
                                             role="radio" 
                                             aria-checked="<?= $selected_persona === 'student' ? 'true' : 'false' ?>"
                                             tabindex="0">
                                            <div class="reg-persona-card-header">
                                                <div class="reg-persona-icon">
                                                    <i class="bi bi-mortarboard-fill"></i>
                                                </div>
                                                <span class="reg-persona-tag">Enrolled</span>
                                            </div>
                                            <div>
                                                <div class="reg-persona-title">Student Applicant</div>
                                                <div class="reg-persona-desc">Apply for student assistantships & campus jobs</div>
                                            </div>
                                        </div>

                                        <!-- Persona 2: University Office / Lab -->
                                        <div class="reg-persona-card <?= $selected_persona === 'university_office' ? 'is-selected' : '' ?>" 
                                             id="persona-card-office" 
                                             onclick="selectPersona('university_office')"
                                             role="radio" 
                                             aria-checked="<?= $selected_persona === 'university_office' ? 'true' : 'false' ?>"
                                             tabindex="0">
                                            <div class="reg-persona-card-header">
                                                <div class="reg-persona-icon">
                                                    <i class="bi bi-bank"></i>
                                                </div>
                                                <span class="reg-persona-tag">Campus Unit</span>
                                            </div>
                                            <div>
                                                <div class="reg-persona-title">University Office</div>
                                                <div class="reg-persona-desc">Academic divisions, labs & student services</div>
                                            </div>
                                        </div>

                                        <!-- Persona 3: Industry Partner -->
                                        <div class="reg-persona-card <?= $selected_persona === 'approved_partner' ? 'is-selected' : '' ?>" 
                                             id="persona-card-partner" 
                                             onclick="selectPersona('approved_partner')"
                                             role="radio" 
                                             aria-checked="<?= $selected_persona === 'approved_partner' ? 'true' : 'false' ?>"
                                             tabindex="0">
                                            <div class="reg-persona-card-header">
                                                <div class="reg-persona-icon">
                                                    <i class="bi bi-patch-check-fill"></i>
                                                </div>
                                                <span class="reg-persona-tag">Enterprise</span>
                                            </div>
                                            <div>
                                                <div class="reg-persona-title">Industry Partner</div>
                                                <div class="reg-persona-desc">Corporate recruiters & accredited MOA firms</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Student Name Fields (First, Middle, Last) -->
                                <div class="row g-3 mb-3" id="student-name-fields" style="display: <?= $preselected_role !== 'employer' ? 'flex' : 'none' ?>;">
                                    <div class="col-md-4">
                                        <label class="form-label" for="reg-first-name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" id="reg-first-name" class="form-control" placeholder="Juan" value="<?= htmlspecialchars($first_name ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="reg-middle-name">Middle Name / Initial</label>
                                        <input type="text" name="middle_name" id="reg-middle-name" class="form-control" placeholder="Santos (Optional)" value="<?= htmlspecialchars($middle_name ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="reg-last-name">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" id="reg-last-name" class="form-control" placeholder="Dela Cruz" value="<?= htmlspecialchars($last_name ?? '') ?>">
                                    </div>
                                </div>

                                <!-- Employer Representative Name -->
                                <div class="row g-3 mb-3" id="employer-name-fields" style="display: <?= $preselected_role === 'employer' ? 'flex' : 'none' ?>;">
                                    <div class="col-12">
                                        <label class="form-label" for="reg-name">Authorized Representative / Contact Person <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="reg-name" class="form-control" placeholder="e.g. Prof. Maria Santos" value="<?= htmlspecialchars($name ?? '') ?>">
                                    </div>
                                </div>

                                <!-- Email & Phone -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-7">
                                        <label class="form-label" for="reg-email" id="lbl-reg-email">Institutional Email Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                                            <input type="email" name="email" id="reg-email" class="form-control" placeholder="username@kld.edu.ph" value="<?= htmlspecialchars($email ?? '') ?>" required>
                                        </div>
                                        <span class="small text-muted-custom mt-1 d-block" id="email-hint" style="font-size: 11px;">
                                            Use your official <strong>@kld.edu.ph</strong> email for automatic verification.
                                        </span>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label" for="reg-phone">Contact Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" name="phone" id="reg-phone" class="form-control" placeholder="+63 917 000 0000" value="<?= htmlspecialchars($phone ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 1 Actions -->
                                <div class="reg-step-actions">
                                    <div class="text-muted-custom small">
                                        <i class="bi bi-check2-circle text-accent"></i> Step 1 of 3
                                    </div>
                                    <button type="button" class="btn-step-next" onclick="nextStep()">
                                        Continue to Profile <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- ==========================================
                                 STEP PANE 2: ACADEMIC / ORG PROFILE
                                 ========================================== -->
                            <div class="reg-step-pane" id="step-pane-2">
                                
                                <!-- Student Specific Fields -->
                                <div class="row g-3 mb-3" id="student-fields">
                                    <div class="col-md-6">
                                        <label class="form-label" for="student_id">Student ID Number <span class="text-danger">*</span></label>
                                        <input type="text" name="student_id" id="student_id" class="form-control" placeholder="2024-00123" value="<?= htmlspecialchars($student_id ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="department-select">Academic Institute <span class="text-danger">*</span></label>
                                        <select name="department" class="form-select" id="department-select" onchange="checkOtherInstitute(this.value)">
                                            <option value="">Select Academic Institute</option>
                                            <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                                <option value="<?= htmlspecialchars($inst) ?>" <?= (isset($department) && $department === $inst) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                                            <?php endforeach; ?>
                                            <option value="Other Institute / Outsider" <?= (isset($department) && ($department === 'Other Institute / Outsider' || (!empty($department) && !array_key_exists($department, get_kld_institutes_and_courses())))) ? 'selected' : '' ?>>Other Institute / Outsider</option>
                                        </select>
                                    </div>
                                    <div class="col-12" id="other-institute-wrap" style="display: <?= (isset($department) && ($department === 'Other Institute / Outsider' || (!empty($department) && !array_key_exists($department, get_kld_institutes_and_courses())))) ? 'block' : 'none' ?>;">
                                        <label class="form-label" for="other_institute">Specify Institute / University Name <span class="text-danger">*</span></label>
                                        <input type="text" name="other_institute" id="other_institute" class="form-control" placeholder="e.g. Cavite State University, De La Salle..." value="<?= htmlspecialchars($_POST['other_institute'] ?? (isset($department) && !array_key_exists($department, get_kld_institutes_and_courses()) ? $department : '')) ?>">
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
                                            <option value="Other Degree Program" <?= (isset($course) && $course === 'Other Degree Program') ? 'selected' : '' ?>>Other Degree Program / Outsider</option>
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
                                        <label class="form-label" for="reg-sex">Biological Sex / Gender <span class="text-danger">*</span></label>
                                        <select name="sex" id="reg-sex" class="form-select">
                                            <option value="" disabled <?= empty($sex) ? 'selected' : '' ?>>Select Gender</option>
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
                                </div>

                                <!-- University Office Specific Fields -->
                                <div class="row g-3 mb-3" id="office-fields" style="display: none;">
                                    <div class="col-12">
                                        <label class="form-label" for="office_name_select">University Department / Administrative Office <span class="text-danger">*</span></label>
                                        <select name="office_department" id="office_name_select" class="form-select">
                                            <option value="">Select Administrative Office</option>
                                            <option value="Office of the University Registrar" <?= (isset($department) && $department === 'Office of the University Registrar') ? 'selected' : '' ?>>Office of the University Registrar</option>
                                            <option value="Student Affairs & Services Office (SASO)" <?= (isset($department) && $department === 'Student Affairs & Services Office (SASO)') ? 'selected' : '' ?>>Student Affairs & Services Office (SASO)</option>
                                            <option value="Management Information Systems (MIS)" <?= (isset($department) && $department === 'Management Information Systems (MIS)') ? 'selected' : '' ?>>Management Information Systems (MIS)</option>
                                            <option value="KLD University Library" <?= (isset($department) && $department === 'KLD University Library') ? 'selected' : '' ?>>KLD University Library</option>
                                            <option value="Accounting & Finance Office" <?= (isset($department) && $department === 'Accounting & Finance Office') ? 'selected' : '' ?>>Accounting & Finance Office</option>
                                            <option value="Human Resource Management Office" <?= (isset($department) && $department === 'Human Resource Management Office') ? 'selected' : '' ?>>Human Resource Management Office</option>
                                            <option value="Guidance & Counseling Office" <?= (isset($department) && $department === 'Guidance & Counseling Office') ? 'selected' : '' ?>>Guidance & Counseling Office</option>
                                            <option value="Campus Clinic / Medical Health Unit" <?= (isset($department) && $department === 'Campus Clinic / Medical Health Unit') ? 'selected' : '' ?>>Campus Clinic / Medical Health Unit</option>
                                            <option value="Other Campus Office" <?= (isset($department) && $department === 'Other Campus Office') ? 'selected' : '' ?>>Other Campus Office / Unit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="office_location">Campus Office Location</label>
                                        <input type="text" name="office_location" id="office_location" class="form-control" placeholder="e.g. KLD Admin Building, 2nd Floor, Room 204" value="<?= htmlspecialchars($office_location ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="office_accreditation_number">Office Code / Department Ref</label>
                                        <input type="text" name="office_accreditation_number" id="office_accreditation_number" class="form-control" placeholder="e.g. INTERNAL-UNIV-REG01" value="<?= htmlspecialchars($accreditation_number ?? '') ?>">
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
                                        <label class="form-label" for="partner_office_location">Workplace / Office Address</label>
                                        <input type="text" name="office_location" id="partner_office_location" class="form-control" placeholder="Campus Office / Tech Park / Hybrid" value="<?= htmlspecialchars($office_location ?? '') ?>">
                                    </div>
                                </div>

                                <!-- Step 2 Actions -->
                                <div class="reg-step-actions">
                                    <button type="button" class="btn-step-prev" onclick="prevStep()">
                                        <i class="bi bi-arrow-left"></i> Previous Step
                                    </button>
                                    <button type="button" class="btn-step-next" onclick="nextStep()">
                                        Continue to Verification <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- ==========================================
                                 STEP PANE 3: SECURITY & VERIFICATION
                                 ========================================== -->
                            <div class="reg-step-pane" id="step-pane-3">
                                
                                <!-- Document Upload Dropzones -->
                                <div class="mb-4" id="student-upload-section">
                                    <label class="form-label fw-bold" for="reg-student-proof">
                                        Certificate of Registration (COR) or Student ID <span class="text-danger">*</span>
                                    </label>
                                    <div class="reg-upload-dropzone" id="student-dropzone" onclick="document.getElementById('reg-student-proof').click()">
                                        <i class="bi bi-file-earmark-arrow-up text-accent fs-2 mb-1 d-block"></i>
                                        <div class="fw-bold text-ink small mb-1" id="student-proof-title">Click to select official COR or School ID file</div>
                                        <div class="small text-muted-custom" id="student-file-chosen" style="font-size: 11.5px;">
                                            Supports PDF, JPG, PNG &bull; Maximum file size: 5MB
                                        </div>
                                    </div>
                                    <input type="file" name="student_proof" id="reg-student-proof" class="d-none" accept="image/*,application/pdf" onchange="handleFileSelected(this, 'student-proof-title', 'student-dropzone')">
                                    <span class="small text-muted-custom mt-1 d-block" style="font-size: 11px;">
                                        <i class="bi bi-shield-lock text-accent me-1"></i> Your official institutional profile will be verified against this attached document.
                                    </span>
                                </div>

                                <div class="mb-4" id="employer-upload-section" style="display: none;">
                                    <label class="form-label fw-bold" for="reg-permit-photo" id="employer-upload-label">
                                        Department Memo / MOA Attachment
                                    </label>
                                    <div class="reg-upload-dropzone" id="employer-dropzone" onclick="document.getElementById('reg-permit-photo').click()">
                                        <i class="bi bi-file-earmark-check text-accent fs-2 mb-1 d-block"></i>
                                        <div class="fw-bold text-ink small mb-1" id="employer-proof-title">Click to upload verification document</div>
                                        <div class="small text-muted-custom" id="employer-upload-desc" style="font-size: 11.5px;">
                                            Supports PDF, JPG, PNG &bull; Maximum file size: 5MB
                                        </div>
                                    </div>
                                    <input type="file" name="permit_photo" id="reg-permit-photo" class="d-none" accept="image/*,application/pdf" onchange="handleFileSelected(this, 'employer-proof-title', 'employer-dropzone')">
                                </div>

                                <hr class="my-3 border-line">

                                <!-- Password & Live Strength Meter -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="password">Account Password <span class="text-danger">*</span></label>
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
                                    <label class="form-label fw-bold" for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
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

                                <!-- Step 3 Actions -->
                                <div class="reg-step-actions">
                                    <button type="button" class="btn-step-prev" onclick="prevStep()">
                                        <i class="bi bi-arrow-left"></i> Previous Step
                                    </button>
                                    <button type="submit" class="btn-pill px-4" id="btn-submit-registration">
                                        <i class="bi bi-check-circle-fill"></i> REGISTER ACCOUNT
                                    </button>
                                </div>
                            </div>

                        </form>

                        <!-- Alternate Links -->
                        <div class="border-top border-line pt-3 mt-4 text-center">
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
// Global state
let currentStep = 1;
let maxVisitedStep = 1;
let currentPersona = '<?= $selected_persona ?>';
let currentRole = (currentPersona === 'student') ? 'student' : 'employer';
let currentEmployerType = (currentPersona === 'approved_partner') ? 'approved_partner' : (currentPersona === 'university_office' ? 'university_office' : '');

const stepHints = {
    1: {
        student: {
            label: "Step 1 Tip",
            text: "Use your official <strong>@kld.edu.ph</strong> student email address for rapid institutional status verification and automatic departmental routing."
        },
        university_office: {
            label: "Step 1 Tip",
            text: "University offices must register with an official <strong>@kld.edu.ph</strong> division address for verified campus administrative access."
        },
        approved_partner: {
            label: "Step 1 Tip",
            text: "Enterprise and corporate partners should use official business domain emails. Accounts are reviewed by Career Services."
        }
    },
    2: {
        student: {
            label: "Step 2 Tip",
            text: "Double-check your <strong>Student ID</strong> and <strong>Degree Program</strong>. Campus job postings automatically match with your enrolled academic institute."
        },
        university_office: {
            label: "Step 2 Tip",
            text: "Specify your physical campus office location (building, room) so student applicants know where interviews and duties will occur."
        },
        approved_partner: {
            label: "Step 2 Tip",
            text: "Provide your registered company name and workplace address or hybrid interview location."
        }
    },
    3: {
        student: {
            label: "Step 3 Tip",
            text: "Upload a legible scan or photo of your official <strong>Certificate of Registration (COR)</strong> or valid School ID. Files are encrypted under RA 10173."
        },
        university_office: {
            label: "Step 3 Tip",
            text: "Upload official departmental request memorandum or appointment endorsement for campus verification."
        },
        approved_partner: {
            label: "Step 3 Tip",
            text: "Provide your company SEC/DTI registration, business permit, or KLD MOA accreditation document."
        }
    }
};

const stepTitles = {
    1: "Account & Basic Details",
    2: {
        student: "Academic & Personal Profile",
        university_office: "Department & Office Details",
        approved_partner: "Company & Partnership Details"
    },
    3: "Security & Verification Uploads"
};

function checkOtherInstitute(val) {
    const wrap = document.getElementById('other-institute-wrap');
    if (wrap) {
        wrap.style.display = (val === 'Other Institute / Outsider') ? 'block' : 'none';
        const input = document.getElementById('other_institute');
        if (input && val === 'Other Institute / Outsider') {
            input.focus();
        }
    }
}

function handleFileSelected(input, labelId, dropzoneId) {
    const label = document.getElementById(labelId);
    const dropzone = document.getElementById(dropzoneId);
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        if (label) {
            label.innerHTML = `<i class="bi bi-file-earmark-check-fill text-accent"></i> ${escapeHtml(file.name)} (${sizeMb} MB)`;
        }
        if (dropzone) {
            dropzone.classList.add('has-file');
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showStepError(msg) {
    const alertBox = document.getElementById('step-error-alert');
    const msgBox = document.getElementById('step-error-message');
    if (alertBox && msgBox) {
        msgBox.textContent = msg;
        alertBox.classList.remove('d-none');
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function hideStepError() {
    const alertBox = document.getElementById('step-error-alert');
    if (alertBox) {
        alertBox.classList.add('d-none');
    }
}

function selectPersona(personaKey) {
    currentPersona = personaKey;
    if (personaKey === 'student') {
        currentRole = 'student';
        currentEmployerType = '';
    } else if (personaKey === 'university_office') {
        currentRole = 'employer';
        currentEmployerType = 'university_office';
    } else if (personaKey === 'approved_partner') {
        currentRole = 'employer';
        currentEmployerType = 'approved_partner';
    }

    // Sync hidden inputs
    const roleInput = document.getElementById('reg-role-input');
    const empTypeInput = document.getElementById('reg-employer-type-input');
    if (roleInput) roleInput.value = currentRole;
    if (empTypeInput) empTypeInput.value = currentEmployerType;

    // Update active classes on persona cards
    const cardStudent = document.getElementById('persona-card-student');
    const cardOffice = document.getElementById('persona-card-office');
    const cardPartner = document.getElementById('persona-card-partner');

    [cardStudent, cardOffice, cardPartner].forEach(c => {
        if (c) {
            c.classList.remove('is-selected');
            c.setAttribute('aria-checked', 'false');
        }
    });

    if (personaKey === 'student' && cardStudent) {
        cardStudent.classList.add('is-selected');
        cardStudent.setAttribute('aria-checked', 'true');
    } else if (personaKey === 'university_office' && cardOffice) {
        cardOffice.classList.add('is-selected');
        cardOffice.setAttribute('aria-checked', 'true');
    } else if (personaKey === 'approved_partner' && cardPartner) {
        cardPartner.classList.add('is-selected');
        cardPartner.setAttribute('aria-checked', 'true');
    }

    // Brand and Form role badges & labels
    const brandRoleBadge = document.getElementById('brand-role-badge');
    const formRoleBadge = document.getElementById('form-role-badge');
    const stepTitleText2 = document.getElementById('step-title-text-2');
    const stepDescText2 = document.getElementById('step-desc-text-2');
    const mobLabel2 = document.getElementById('mob-label-2');

    const studentNameFields = document.getElementById('student-name-fields');
    const employerNameFields = document.getElementById('employer-name-fields');
    const studentFields = document.getElementById('student-fields');
    const officeFields = document.getElementById('office-fields');
    const partnerFields = document.getElementById('partner-fields');

    const studentUploadSec = document.getElementById('student-upload-section');
    const employerUploadSec = document.getElementById('employer-upload-section');
    const lblRegEmail = document.getElementById('lbl-reg-email');
    const emailHint = document.getElementById('email-hint');
    const employerUploadLabel = document.getElementById('employer-upload-label');
    const employerUploadDesc = document.getElementById('employer-upload-desc');

    if (personaKey === 'student') {
        if (brandRoleBadge) brandRoleBadge.innerHTML = '<i class="bi bi-mortarboard-fill text-accent"></i> <span>Student Applicant Registration</span>';
        if (formRoleBadge) formRoleBadge.innerHTML = '<i class="bi bi-mortarboard-fill text-accent"></i> <span>Student Applicant</span>';
        if (stepTitleText2) stepTitleText2.textContent = 'Academic Profile';
        if (stepDescText2) stepDescText2.textContent = 'Institute, program & student ID';
        if (mobLabel2) mobLabel2.textContent = 'Academic';

        if (lblRegEmail) lblRegEmail.innerHTML = 'Institutional Email Address <span class="text-danger">*</span>';
        if (emailHint) emailHint.innerHTML = 'Use your official <strong>@kld.edu.ph</strong> email for automatic verification.';

        if (studentNameFields) studentNameFields.style.display = 'flex';
        if (employerNameFields) employerNameFields.style.display = 'none';
        if (studentFields) studentFields.style.display = 'flex';
        if (officeFields) officeFields.style.display = 'none';
        if (partnerFields) partnerFields.style.display = 'none';
        if (studentUploadSec) studentUploadSec.style.display = 'block';
        if (employerUploadSec) employerUploadSec.style.display = 'none';
    } else if (personaKey === 'university_office') {
        if (brandRoleBadge) brandRoleBadge.innerHTML = '<i class="bi bi-bank text-accent"></i> <span>University Office Registration</span>';
        if (formRoleBadge) formRoleBadge.innerHTML = '<i class="bi bi-bank text-accent"></i> <span>University Office</span>';
        if (stepTitleText2) stepTitleText2.textContent = 'Department & Office Details';
        if (stepDescText2) stepDescText2.textContent = 'Campus office location & unit code';
        if (mobLabel2) mobLabel2.textContent = 'Department';

        if (lblRegEmail) lblRegEmail.innerHTML = 'Official University Office Email <span class="text-danger">*</span>';
        if (emailHint) emailHint.innerHTML = 'Must be an official <strong>@kld.edu.ph</strong> university division address.';

        if (studentNameFields) studentNameFields.style.display = 'none';
        if (employerNameFields) employerNameFields.style.display = 'flex';
        if (studentFields) studentFields.style.display = 'none';
        if (officeFields) officeFields.style.display = 'flex';
        if (partnerFields) partnerFields.style.display = 'none';
        if (studentUploadSec) studentUploadSec.style.display = 'none';
        if (employerUploadSec) employerUploadSec.style.display = 'block';

        if (employerUploadLabel) employerUploadLabel.innerHTML = 'Department Endorsement / Request Memo <span class="text-danger">*</span>';
        if (employerUploadDesc) employerUploadDesc.innerHTML = 'Official university letterhead or administrative authorization document.';
    } else if (personaKey === 'approved_partner') {
        if (brandRoleBadge) brandRoleBadge.innerHTML = '<i class="bi bi-patch-check-fill text-accent"></i> <span>Industry Partner Registration</span>';
        if (formRoleBadge) formRoleBadge.innerHTML = '<i class="bi bi-patch-check-fill text-accent"></i> <span>Industry Partner</span>';
        if (stepTitleText2) stepTitleText2.textContent = 'Company & Partnership Details';
        if (stepDescText2) stepDescText2.textContent = 'Workplace location & MOA reference';
        if (mobLabel2) mobLabel2.textContent = 'Enterprise';

        if (lblRegEmail) lblRegEmail.innerHTML = 'Official Company / Business Email <span class="text-danger">*</span>';
        if (emailHint) emailHint.innerHTML = 'Corporate or registered business domain email address.';

        if (studentNameFields) studentNameFields.style.display = 'none';
        if (employerNameFields) employerNameFields.style.display = 'flex';
        if (studentFields) studentFields.style.display = 'none';
        if (officeFields) officeFields.style.display = 'none';
        if (partnerFields) partnerFields.style.display = 'flex';
        if (studentUploadSec) studentUploadSec.style.display = 'none';
        if (employerUploadSec) employerUploadSec.style.display = 'block';

        if (employerUploadLabel) employerUploadLabel.innerHTML = 'Company Registration / MOA Document <span class="text-danger">*</span>';
        if (employerUploadDesc) employerUploadDesc.innerHTML = 'SEC/DTI Registration, Mayor\'s Permit, or KLD MOA Partnership document.';
    }

    updateWizardUI();
}

function validateCurrentStep() {
    hideStepError();

    if (currentStep === 1) {
        if (currentPersona === 'student') {
            const firstName = document.getElementById('reg-first-name');
            const lastName = document.getElementById('reg-last-name');
            if (!firstName || !firstName.value.trim()) {
                showStepError("Please provide your First Name.");
                if (firstName) firstName.focus();
                return false;
            }
            if (!lastName || !lastName.value.trim()) {
                showStepError("Please provide your Last Name.");
                if (lastName) lastName.focus();
                return false;
            }
        } else {
            const repName = document.getElementById('reg-name');
            if (!repName || !repName.value.trim()) {
                showStepError("Please provide the Authorized Representative / Contact Person name.");
                if (repName) repName.focus();
                return false;
            }
        }

        const email = document.getElementById('reg-email');
        if (!email || !email.value.trim()) {
            showStepError("Please provide an email address.");
            if (email) email.focus();
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value.trim())) {
            showStepError("Please enter a valid email address format.");
            email.focus();
            return false;
        }

        if (currentPersona === 'university_office') {
            if (!/@kld\.edu\.ph$/i.test(email.value.trim())) {
                showStepError("University Office accounts must use an official @kld.edu.ph institutional email address. External enterprise recruiters must select 'Industry Partner'.");
                email.focus();
                return false;
            }
        }

        const phone = document.getElementById('reg-phone');
        if (!phone || !phone.value.trim()) {
            showStepError("Please provide your contact phone number.");
            if (phone) phone.focus();
            return false;
        }

        const cleanedPhone = phone.value.replace(/[^0-9]/g, '');
        if (cleanedPhone.length < 7) {
            showStepError("Please enter a valid contact phone number (at least 7 digits).");
            phone.focus();
            return false;
        }

        return true;
    }

    if (currentStep === 2) {
        if (currentPersona === 'student') {
            const studentId = document.getElementById('student_id');
            const deptSelect = document.getElementById('department-select');
            const otherInst = document.getElementById('other_institute');
            const courseSelect = document.getElementById('course-select');
            const sexSelect = document.getElementById('reg-sex');
            const birthdate = document.getElementById('reg-birthdate');

            if (!studentId || !studentId.value.trim()) {
                showStepError("Please provide your Student ID Number.");
                if (studentId) studentId.focus();
                return false;
            }

            if (!deptSelect || !deptSelect.value) {
                showStepError("Please select your Academic Institute.");
                if (deptSelect) deptSelect.focus();
                return false;
            }

            if (deptSelect.value === 'Other Institute / Outsider' && (!otherInst || !otherInst.value.trim())) {
                showStepError("Please specify the name of your academic institute / university.");
                if (otherInst) otherInst.focus();
                return false;
            }

            if (!courseSelect || !courseSelect.value) {
                showStepError("Please select your Degree Program / Course.");
                if (courseSelect) courseSelect.focus();
                return false;
            }

            if (!sexSelect || !sexSelect.value) {
                showStepError("Please select your Biological Sex / Gender.");
                if (sexSelect) sexSelect.focus();
                return false;
            }

            if (!birthdate || !birthdate.value) {
                showStepError("Please enter your Date of Birth.");
                if (birthdate) birthdate.focus();
                return false;
            }
        } else if (currentPersona === 'university_office') {
            const officeSelect = document.getElementById('office_name_select');
            if (!officeSelect || !officeSelect.value) {
                showStepError("Please select your University Department / Administrative Office.");
                if (officeSelect) officeSelect.focus();
                return false;
            }
        } else if (currentPersona === 'approved_partner') {
            const orgName = document.getElementById('organization_name');
            if (!orgName || !orgName.value.trim()) {
                showStepError("Please provide your Company / Organization Name.");
                if (orgName) orgName.focus();
                return false;
            }
        }

        return true;
    }

    return true;
}

function goToStep(targetStep) {
    if (targetStep < 1 || targetStep > 3) return;
    currentStep = targetStep;
    if (currentStep > maxVisitedStep) {
        maxVisitedStep = currentStep;
    }
    updateWizardUI();
    hideStepError();
}

function nextStep() {
    if (validateCurrentStep()) {
        goToStep(currentStep + 1);
    }
}

function prevStep() {
    hideStepError();
    goToStep(currentStep - 1);
}

function handleStepNavClick(step) {
    if (step <= maxVisitedStep || step === currentStep) {
        goToStep(step);
    } else if (step === currentStep + 1) {
        nextStep();
    }
}

function updateWizardUI() {
    // 1. Update Panes
    for (let s = 1; s <= 3; s++) {
        const pane = document.getElementById(`step-pane-${s}`);
        if (pane) {
            if (s === currentStep) {
                pane.classList.add('is-visible');
            } else {
                pane.classList.remove('is-visible');
            }
        }
    }

    // 2. Update Left Stepper Items
    for (let s = 1; s <= 3; s++) {
        const navItem = document.getElementById(`step-nav-${s}`);
        const badge = document.getElementById(`step-badge-${s}`);
        const checkIcon = document.getElementById(`step-check-${s}`);

        if (navItem && badge) {
            navItem.classList.remove('is-active', 'is-completed', 'is-clickable');
            
            if (s === currentStep) {
                navItem.classList.add('is-active', 'is-clickable');
                badge.innerHTML = s;
                if (checkIcon) checkIcon.classList.add('d-none');
            } else if (s < currentStep) {
                navItem.classList.add('is-completed', 'is-clickable');
                badge.innerHTML = '<i class="bi bi-check-lg"></i>';
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                if (s <= maxVisitedStep) {
                    navItem.classList.add('is-clickable');
                }
                badge.innerHTML = s;
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        }
    }

    // 3. Update Mobile Stepper Dots
    for (let s = 1; s <= 3; s++) {
        const mobStep = document.getElementById(`mob-step-${s}`);
        const mobDot = document.getElementById(`mob-dot-${s}`);

        if (mobStep && mobDot) {
            mobStep.classList.remove('is-active', 'is-completed');
            if (s === currentStep) {
                mobStep.classList.add('is-active');
                mobDot.innerHTML = s;
            } else if (s < currentStep) {
                mobStep.classList.add('is-completed');
                mobDot.innerHTML = '<i class="bi bi-check"></i>';
            } else {
                mobDot.innerHTML = s;
            }
        }
    }

    // 4. Update Header & Progress Bar
    const progTag = document.getElementById('progress-step-tag');
    const progTitle = document.getElementById('progress-step-title');
    const progFill = document.getElementById('reg-progress-fill');

    if (progTag) progTag.textContent = `Step ${currentStep} of 3`;
    if (progTitle) {
        const t = stepTitles[currentStep];
        progTitle.textContent = typeof t === 'string' ? t : (t[currentPersona] || t[currentRole]);
    }
    if (progFill) {
        const pct = currentStep === 1 ? '33.33%' : (currentStep === 2 ? '66.66%' : '100%');
        progFill.style.width = pct;
    }

    // 5. Update Dynamic Hint Box
    const hint = (stepHints[currentStep] && (stepHints[currentStep][currentPersona] || stepHints[currentStep][currentRole])) || { label: `Step ${currentStep} Tip`, text: '' };
    const hintLabel = document.getElementById('reg-hint-step-label');
    const hintText = document.getElementById('reg-hint-text');
    if (hintLabel) hintLabel.textContent = hint.label;
    if (hintText) hintText.innerHTML = hint.text;
}

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');

    // Persona card keyboard navigation for accessibility
    document.querySelectorAll('.reg-persona-card').forEach(card => {
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Check if server initialized at a specific step
    const initialStepAttr = registerForm ? parseInt(registerForm.getAttribute('data-initial-step') || '1', 10) : 1;
    currentStep = initialStepAttr;
    maxVisitedStep = Math.max(initialStepAttr, 1);

    selectPersona(currentPersona);

    // Client-side validation before form submission on Step 3
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (currentStep < 3) {
                e.preventDefault();
                return false;
            }

            if (currentRole === 'student') {
                const proofInput = document.getElementById('reg-student-proof');
                if (!proofInput || !proofInput.files || proofInput.files.length === 0) {
                    e.preventDefault();
                    showStepError("Please attach your Certificate of Registration (COR) or Student ID document.");
                    return false;
                }
            }

            const termsCheck = document.getElementById('termsCheck');
            if (termsCheck && !termsCheck.checked) {
                e.preventDefault();
                showStepError("You must agree to the Terms of Service and Data Privacy Policy to register.");
                termsCheck.focus();
                return false;
            }
        });
    }
});
</script>
