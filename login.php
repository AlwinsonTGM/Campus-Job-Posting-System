<?php
/**
 * Campus Job Posting System - Account Login
 * Archetype A: Auth Split Card Shell (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

// Check if already logged in
if (is_logged_in()) {
    $u = get_logged_user();
    if ($u['role'] === 'student') header('Location: student/dashboard.php');
    elseif ($u['role'] === 'employer') header('Location: employer/dashboard.php');
    elseif ($u['role'] === 'admin') header('Location: admin/reports.php');
    exit;
}

// Handle Quick Demo Login from URL params
if (isset($_GET['demo'])) {
    $role = $_GET['demo'];
    $user = quick_login($role);
    if ($user) {
        set_flash('success', "Welcome back, {$user['name']}! Signed in as " . ucfirst($role) . '.');
        if ($role === 'student') header('Location: student/dashboard.php');
        elseif ($role === 'employer') header('Location: employer/dashboard.php');
        elseif ($role === 'admin') header('Location: admin/reports.php');
        exit;
    }
}

if (isset($_GET['reset'])) {
    reset_demo_data();
    set_flash('info', 'Demo datastore has been reset to defaults.');
    header('Location: login.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
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

$page_title = 'Sign In to Campus Hire';
require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Archetype A: Auth Split Card Shell (max-w 960) -->
                <div class="auth-shell">
                    
                    <!-- Left Brand Panel (42% on desktop) -->
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

                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle mb-3">
                                <i class="bi bi-shield-lock-fill text-accent"></i> Secure Access Portal
                            </span>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Empowering Student Talent & Campus Opportunities
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Sign in to manage your student applications, explore departmental assistantships, or evaluate student candidates.
                            </p>

                            <!-- Feature List -->
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-mortarboard-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Verified On-Campus Assistantships</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Strict 20 hrs/week Academic Safeguards</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">RA 10173 Data Privacy Compliance</span>
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click Instant Demo Login Selector -->
                        <div class="p-3 bg-white rounded-4 border border-line mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-ink text-uppercase" style="font-size: 11px;">
                                    <i class="bi bi-lightning-charge-fill text-accent"></i> 1-Click Demo Accounts
                                </span>
                                <span class="badge-status--accepted" style="font-size: 9px; padding: 2px 6px;">Ready</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="chip chip-selectable" data-demo-email="student@kld.edu.ph">
                                    <i class="bi bi-mortarboard text-accent"></i> Student
                                </button>
                                <button type="button" class="chip chip-selectable" data-demo-email="registrar@kld.edu.ph">
                                    <i class="bi bi-bank text-accent"></i> Campus Office
                                </button>
                                <button type="button" class="chip chip-selectable" data-demo-email="techvanguard@partner.kld.edu.ph">
                                    <i class="bi bi-patch-check-fill text-accent"></i> Partner
                                </button>
                                <button type="button" class="chip chip-selectable" data-demo-email="admin@kld.edu.ph">
                                    <i class="bi bi-shield-lock text-accent"></i> Admin
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Right Form Panel (58% on desktop) -->
                    <div class="auth-form-panel">
                        
                        <div class="mb-4">
                            <h2 class="card-paper-title fs-4 mb-1">Sign In to Your Account</h2>
                            <p class="text-muted-custom small mb-0">Enter your institutional credentials to proceed</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Role Switcher Chips -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted-custom text-uppercase" style="font-size: 11px;">Select Role Context</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="chip chip-selectable active" id="tab-student" onclick="selectRoleTab('student')">
                                    <i class="bi bi-mortarboard"></i> Student
                                </button>
                                <button type="button" class="chip chip-selectable" id="tab-employer" onclick="selectRoleTab('employer')">
                                    <i class="bi bi-building"></i> Employer / Office
                                </button>
                                <button type="button" class="chip chip-selectable" id="tab-admin" onclick="selectRoleTab('admin')">
                                    <i class="bi bi-shield-lock"></i> Admin
                                </button>
                            </div>
                        </div>

                        <form action="login.php" method="POST" class="form-paper">
                            
                            <!-- Email Input -->
                            <div class="mb-3">
                                <label class="form-label" for="login-email">Institutional Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="login-email" class="form-control" placeholder="username@campus-hire.edu" required autofocus>
                                </div>
                            </div>

                            <!-- Password Input with Show/Hide Toggle -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0" for="login-password">Account Password</label>
                                    <a href="forgot-pass.php" class="small fw-bold text-ink text-decoration-none">
                                        Forgot Password?
                                    </a>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('login-password', 'toggle-pw-icon')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="toggle-pw-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Remember Me Checkbox -->
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" checked>
                                <label class="form-check-label small text-muted-custom" for="rememberMe">
                                    Keep me signed in on this device
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-pill w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> SIGN IN TO PORTAL
                            </button>
                        </form>

                        <!-- Alternate Links -->
                        <div class="border-top border-line pt-3 text-center">
                            <span class="text-muted-custom small">Don't have an account yet?</span>
                            <a href="register.php" class="text-ink fw-bold small ms-1 text-decoration-none">
                                Create an Account <i class="bi bi-arrow-right"></i>
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
function selectRoleTab(role) {
    document.querySelectorAll('.chip-selectable').forEach(el => el.classList.remove('active'));
    const btn = document.getElementById('tab-' + role);
    if (btn) btn.classList.add('active');

    const emailInput = document.getElementById('login-email');
    const passInput = document.getElementById('login-password');
    if (emailInput && passInput) {
        if (role === 'student') emailInput.value = 'student@kld.edu.ph';
        else if (role === 'employer') emailInput.value = 'registrar@kld.edu.ph';
        else if (role === 'admin') emailInput.value = 'admin@kld.edu.ph';
        passInput.value = 'Password123!';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('login-email');
    const passInput = document.getElementById('login-password');
    const demoBtns = document.querySelectorAll('[data-demo-email]');

    demoBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const email = this.getAttribute('data-demo-email');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = 'Password123!';
                emailInput.focus();
            }
        });
    });
});
</script>
