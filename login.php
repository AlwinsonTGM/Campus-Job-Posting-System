<?php
/**
 * Campus Job Posting System - Account Login
 * Archetype A: Auth Split Card Shell (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

// Test hooks (?reset / ?demo) — LOCAL-ONLY. Blocked in production and for remote clients.
// E2E suite runs on 127.0.0.1 so tests keep working. Set APP_ENV=production in prod .env to hard-close.
$__app_env = strtolower(trim((string)(getenv('APP_ENV') ?: '')));
$__is_production = ($__app_env === 'production' || $__app_env === 'prod');
$__remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
$__is_loopback = in_array($__remote_addr, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true);
$__test_hooks_enabled = !$__is_production && $__is_loopback;

// Handle Datastore Reset from URL params (E2E / local demo fixtures only)
if (isset($_GET['reset'])) {
    if (!$__test_hooks_enabled) {
        http_response_code(403);
        exit('Forbidden');
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        session_start();
    }
    require_once __DIR__ . '/database/migrate.php';
    execute_migration_and_seed(false, null, false, true);
    set_flash('success', 'Datastore reset to pristine baseline.');
    header('Location: login.php');
    exit;
}

// Handle Quick Demo Login from URL params (local / E2E only, role-whitelisted)
if (isset($_GET['demo'])) {
    if (!$__test_hooks_enabled) {
        http_response_code(403);
        exit('Forbidden');
    }
    $role = $_GET['demo'];
    if (!in_array($role, ['student', 'employer', 'admin'], true)) {
        header('Location: login.php');
        exit;
    }
    $user = quick_login($role);
    if ($user) {
        set_flash('success', "Welcome back, {$user['name']}! Signed in as " . ucfirst($role) . '.');
        if ($role === 'student') header('Location: student/dashboard.php');
        elseif ($role === 'employer') header('Location: employer/dashboard.php');
        elseif ($role === 'admin') header('Location: admin/reports.php');
        exit;
    }
}

// Safe return-to target after manual sign-in (relative portal paths only — no open redirects).
$next_raw = (string)($_POST['next'] ?? ($_GET['next'] ?? ''));
$safe_next = '';
if ($next_raw !== '' && strpos($next_raw, '..') === false && strpos($next_raw, ':') === false && strpos($next_raw, '//') === false) {
    if (preg_match('#^(student|employer|admin)/[A-Za-z0-9/_\-.?=&%]+$#', $next_raw)) {
        $safe_next = $next_raw;
    }
}

// Check if already logged in
if (is_logged_in()) {
    $u = get_logged_user();
    $role_path = ($u['role'] ?? '') . '/';
    if ($safe_next !== '' && strpos($safe_next, $role_path) === 0) header('Location: ' . $safe_next);
    elseif ($u['role'] === 'student') header('Location: student/dashboard.php');
    elseif ($u['role'] === 'employer') header('Location: employer/dashboard.php');
    elseif ($u['role'] === 'admin') header('Location: admin/reports.php');
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
        if ($safe_next !== '') header('Location: ' . $safe_next);
        elseif ($u['role'] === 'student') header('Location: student/dashboard.php');
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
                                <span class="faq-help-icon-box m-0 flex-shrink-0" style="width: 42px; height: 42px; min-width: 42px; min-height: 42px; aspect-ratio: 1 / 1; font-size: 1.15rem;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="var(--ink)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 17L12 22L22 17" stroke="var(--ink)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 12L12 17L22 12" stroke="var(--ink)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="fw-extrabold text-ink fs-5 tracking-tight"><?= htmlspecialchars(SITE_NAME) ?></span>
                            </div>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Empowering Student Talent &amp; Campus Opportunities
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Sign in to manage your student applications, explore departmental assistantships, or evaluate student candidates.
                            </p>

                            <!-- Feature List -->
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; aspect-ratio: 1 / 1; font-size: 0.95rem;">
                                        <i class="bi bi-mortarboard-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Verified On-Campus Assistantships</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; aspect-ratio: 1 / 1; font-size: 0.95rem;">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Strict 20 hrs/week Academic Safeguards</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; aspect-ratio: 1 / 1; font-size: 0.95rem;">
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
                                    <i class="bi bi-lightning-charge-fill text-accent"></i> 1-Click Evaluation Accounts
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" id="tab-student" class="chip chip-selectable" data-demo-email="student@kld.edu.ph">
                                    <i class="bi bi-mortarboard text-accent"></i> Student
                                </button>
                                <button type="button" id="tab-employer" class="chip chip-selectable" data-demo-email="registrar@kld.edu.ph">
                                    <i class="bi bi-bank text-accent"></i> Campus Office
                                </button>
                                <button type="button" id="tab-partner" class="chip chip-selectable" data-demo-email="techvanguard@partner.kld.edu.ph">
                                    <i class="bi bi-patch-check-fill text-accent"></i> Partner
                                </button>
                                <button type="button" id="tab-admin" class="chip chip-selectable" data-demo-email="admin@kld.edu.ph">
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



                        <form action="login.php" method="POST" class="form-paper">
                            <?php if ($safe_next !== ''): ?>
                                <input type="hidden" name="next" value="<?= htmlspecialchars($safe_next) ?>">
                            <?php endif; ?>
                            
                            <!-- Email Input -->
                            <div class="mb-3">
                                <label class="form-label" for="login-email">Institutional Email Address</label>
                                <div class="input-group input-group-integrated">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="login-email" class="form-control" placeholder="username@kld.edu.ph" required autofocus>
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
                                <div class="input-group input-group-integrated">
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
