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
    $student_id = trim($_POST['student_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year_level = $_POST['year_level'] ?? '1st Year';
    $phone = trim($_POST['phone'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid institutional email address.';
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
            'student_id' => $student_id,
            'department' => $department,
            'course' => $course,
            'year_level' => $year_level,
            'phone' => $phone
        ]);

        if ($res['success']) {
            set_flash('success', 'Account registered successfully! Welcome to the Campus Job Portal.');
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

<main class="py-5 bg-light flex-grow-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-7">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <div class="stat-icon bg-warning text-dark mx-auto mb-2">
                            <i class="bi bi-person-plus-fill fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Create Your Account</h3>
                        <p class="text-muted small mb-0">Register as a Student Applicant or Campus Department Employer</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" id="register-form" novalidate>
                        
                        <!-- Account Role Selector -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark text-uppercase">Account Type / Role</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role-student" value="student" <?= $preselected_role !== 'employer' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" for="role-student">
                                        <i class="bi bi-mortarboard-fill"></i> Student Applicant
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role-employer" value="employer" <?= $preselected_role === 'employer' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-warning text-dark w-100 py-2 d-flex align-items-center justify-content-center gap-2" for="role-employer">
                                        <i class="bi bi-building"></i> Campus Office / Employer
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Juan Dela Cruz" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Institutional Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="name@university.edu.ph" required>
                            </div>
                        </div>

                        <!-- Student / Department Specific Fields -->
                        <div class="row g-3 mb-3" id="student-fields">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Student ID Number / Office Code</label>
                                <input type="text" name="student_id" class="form-control" placeholder="2024-00123">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">College / Department</label>
                                <input type="text" name="department" class="form-control" placeholder="e.g. College of Computer Studies">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Degree Program / Course</label>
                                <input type="text" name="course" class="form-control" placeholder="e.g. BS Information Systems">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Year Level</label>
                                <select name="year_level" class="form-select">
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year" selected>2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                    <option value="Graduate / Post-Grad">Graduate / Post-Grad</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Contact Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+63 917 000 0000">
                        </div>

                        <hr class="my-4">

                        <!-- Password & Real-Time Strength Meter Section -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">
                                Account Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Create a secure password" required>
                            </div>
                            
                            <!-- Dynamic Password Strength Bar -->
                            <div class="password-meter-bar">
                                <div id="password-meter-fill" class="password-meter-fill"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div id="password-strength-text" class="small text-muted">
                                    Enter password to see strength
                                </div>
                                <span class="small text-muted">Min. 8 characters</span>
                            </div>

                            <!-- Live Password Criteria Checklist -->
                            <div class="p-3 bg-light rounded-3 mt-2 border">
                                <span class="small fw-bold text-dark d-block mb-2">Password Requirements:</span>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div id="req-length" class="req-item unmet">
                                            <i class="bi bi-circle text-muted"></i> At least 8 characters
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="req-upper" class="req-item unmet">
                                            <i class="bi bi-circle text-muted"></i> Uppercase letter (A-Z)
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="req-lower" class="req-item unmet">
                                            <i class="bi bi-circle text-muted"></i> Lowercase letter (a-z)
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div id="req-number" class="req-item unmet">
                                            <i class="bi bi-circle text-muted"></i> At least one number (0-9)
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div id="req-special" class="req-item unmet">
                                            <i class="bi bi-circle text-muted"></i> Special character (!@#$%^&*)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-type your password" required>
                            </div>
                            <div id="confirm-feedback"></div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheck" required checked>
                            <label class="form-check-label small text-muted" for="termsCheck">
                                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and have read the <a href="privacy.php" target="_blank">Data Privacy Policy (RA 10173)</a>.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-academic w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-check-circle me-1"></i> Register Account
                        </button>
                    </form>

                    <div class="border-top pt-3 text-center">
                        <span class="text-muted small">Already have an account?</span>
                        <a href="login.php" class="text-decoration-none fw-bold text-primary small ms-1">
                            Sign In Here <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
