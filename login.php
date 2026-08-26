<?php
/**
 * Campus Job Posting System - Login Page
 */
require_once __DIR__ . '/includes/data-helper.php';

// Check if already logged in
if (is_logged_in()) {
    $u = get_logged_user();
    if ($u['role'] === 'student') header('Location: student/dashboard.php');
    elseif ($u['role'] === 'employer') header('Location: employer/dashboard.php');
    elseif ($u['role'] === 'admin') header('Location: admin/reports.php');
    exit;
}

// Handle Quick Demo Login / Reset from URL params
if (isset($_GET['demo'])) {
    $role = $_GET['demo'];
    $user = quick_login($role);
    if ($user) {
        set_flash('success', "Welcome, {$user['name']}! Signed in as " . ucfirst($role) . '.');
        if ($role === 'student') header('Location: student/dashboard.php');
        elseif ($role === 'employer') header('Location: employer/dashboard.php');
        elseif ($role === 'admin') header('Location: admin/reports.php');
        exit;
    }
}

if (isset($_GET['reset'])) {
    reset_demo_data();
    header('Location: login.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = login_user($email, $password);
    if ($res['success']) {
        $u = $res['user'];
        set_flash('success', "Welcome back, {$u['name']}!");
        if ($u['role'] === 'student') header('Location: student/dashboard.php');
        elseif ($u['role'] === 'employer') header('Location: employer/dashboard.php');
        elseif ($u['role'] === 'admin') header('Location: admin/reports.php');
        exit;
    } else {
        $error = $res['message'];
    }
}

$page_title = 'Account Sign In';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 bg-light flex-grow-1 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                
                <!-- Quick Demo Switcher Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 text-white p-3 bg-kld-gradient">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-warning text-uppercase"><i class="bi bi-lightning-charge-fill"></i> Instant KLD Demo Sign-In</span>
                        <span class="badge bg-light text-dark small">1-Click Test</span>
                    </div>
                    <p class="small text-light opacity-90 mb-3">Click any button below to instantly populate credentials & test specific roles:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-light text-success fw-bold" data-demo-role="student">
                            <i class="bi bi-mortarboard me-1"></i> Student Demo
                        </button>
                        <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" data-demo-role="employer">
                            <i class="bi bi-building me-1"></i> Employer Demo
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light fw-bold" data-demo-role="admin">
                            <i class="bi bi-shield-lock me-1"></i> Admin Demo
                        </button>
                    </div>
                </div>

                <!-- Main Login Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 bg-white">
                    <div class="text-center mb-4">
                        <div class="stat-icon bg-kld-gradient text-white mx-auto mb-2">
                            <i class="bi bi-person-lock fs-4"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Sign In to Your Account</h4>
                        <p class="text-muted small mb-0">Access student applications or KLD department job postings</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">KLD Institutional Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" id="login-email" class="form-control" placeholder="username@kld.edu.ph" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-dark mb-0">Account Password</label>
                                <a href="forgot-pass.php" class="text-decoration-none small text-kld-green fw-semibold">Forgot password?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" checked>
                            <label class="form-check-label small text-muted" for="rememberMe">Remember my login session on this device</label>
                        </div>

                        <button type="submit" class="btn btn-academic w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
                        </button>
                    </form>

                    <div class="border-top pt-3 text-center">
                        <span class="text-muted small">Don't have an account yet?</span>
                        <a href="register.php" class="text-decoration-none fw-bold text-kld-green small ms-1">
                            Create an Account <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
