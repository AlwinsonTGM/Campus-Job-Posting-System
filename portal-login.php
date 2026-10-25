<?php
/**
 * Campus Job Posting System - Partner & Staff Portal Sign In
 * Dedicated gateway for KLD Department Offices, Industry Partners, and Administrators
 * Archetype A: Auth Split Card Shell (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

// Test hooks (?demo) — LOCAL-ONLY. Blocked in production and for remote clients.
// E2E suite runs on 127.0.0.1 so tests keep working. Set APP_ENV=production in prod .env to hard-close.
$__app_env = strtolower(trim((string)get_env('APP_ENV', '')));
$__is_production = ($__app_env === 'production' || $__app_env === 'prod');
$__remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
$__is_loopback = in_array($__remote_addr, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true);
$__test_hooks_enabled = !$__is_production && $__is_loopback;

// Handle Quick Demo Login from URL params (local / E2E only, role-whitelisted)
if (isset($_GET['demo'])) {
    if (!$__test_hooks_enabled) {
        http_response_code(403);
        exit('Forbidden');
    }
    $role = $_GET['demo'];
    if ($role === 'student') {
        header('Location: login.php?demo=student');
        exit;
    }
    if (!in_array($role, ['employer', 'admin'], true)) {
        header('Location: portal-login.php');
        exit;
    }
    $user = quick_login($role);
    if ($user) {
        set_flash('success', "Welcome, {$user['name']}! Signed in as " . ucfirst($role) . '.');
        redirect_by_role($role);
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
    if (!empty($u['must_change_password'])) {
        header('Location: settings.php?setup_password=1#password-section');
        exit;
    }
    $role_path = ($u['role'] ?? '') . '/';
    if ($safe_next !== '' && strpos($safe_next, $role_path) === 0) {
        header('Location: ' . $safe_next);
        exit;
    }
    redirect_by_role($u['role'] ?? null);
}

$error = null;
$student_redirect_hint = false;
$email = trim((string)($_POST['email'] ?? ($_GET['email'] ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    $lockout_until = (int)($_SESSION['login_lockout_until'] ?? 0);
    if (time() < $lockout_until) {
        $remaining = $lockout_until - time();
        $error = "Too many failed login attempts. Please wait {$remaining}s before trying again.";
    } else {
        $res = login_user($email, $password);
        if ($res['success'] || !empty($res['unverified'])) {
            $role = $res['user']['role'] ?? 'student';

            // Student applicants must sign in via student login page
            if ($role === 'student') {
                unset($_SESSION['user']);
                $error = 'Student applicants must sign in using the Student Login page.';
                $student_redirect_hint = true;
            } elseif ($res['success']) {
                unset($_SESSION['login_failed_attempts'], $_SESSION['login_lockout_until']);
                $u = $res['user'];
                $ver_status = $u['verification_status'] ?? 'pending_approval';

                // Block portal access for every non-verified, non-admin state.
                if ($role !== 'admin' && in_array($ver_status, ['pending_approval', 'rejected', 'revoked'], true)) {
                    $_SESSION['pending_approval_notice'] = [
                        'user_id'             => (int)($u['id'] ?? 0),
                        'name'                => $u['name'] ?? 'User',
                        'email'               => $u['email'] ?? '',
                        'role'                => $role,
                        'verification_status' => $ver_status,
                        'rejection_reason'    => $u['rejection_reason'] ?? '',
                    ];
                    unset($_SESSION['user']);

                    if ($ver_status === 'rejected') {
                        set_flash('danger', 'Your registration was not approved. Review the administrator\'s feedback below and resubmit your credentials.');
                    } elseif ($ver_status === 'revoked') {
                        set_flash('danger', 'Your account verification has been revoked by institutional administration. Please contact the Career Services Office.');
                    } else {
                        set_flash('warning', 'Your account is awaiting administrator approval. Please check back later or contact the admin.');
                    }

                    header('Location: pending-approval.php');
                    exit;
                }

                // Temporary password security checkpoint
                if (!empty($u['must_change_password'])) {
                    set_flash('warning', 'Security Notice: You signed in with a temporary password. Please set a new personal password to continue.');
                    header('Location: settings.php?setup_password=1#password-section');
                    exit;
                }

                set_flash('success', "Welcome, {$u['name']}!");
                if ($safe_next !== '') {
                    header('Location: ' . $safe_next);
                    exit;
                }
                redirect_by_role($role);
            } else {
                // Employer with unverified email
                unset($_SESSION['login_failed_attempts'], $_SESSION['login_lockout_until']);
                SessionGuard::quarantineForVerification($res['user']);
                exit;
            }
        } else {
            $attempts = (int)($_SESSION['login_failed_attempts'] ?? 0) + 1;
            $_SESSION['login_failed_attempts'] = $attempts;
            if ($attempts >= 5) {
                $_SESSION['login_lockout_until'] = time() + 60;
                $_SESSION['login_failed_attempts'] = 0;
                $error = 'Too many failed login attempts. Account access is temporarily locked for 60 seconds.';
            } else {
                $error = $res['message'];
            }
        }
    }
}

$page_title = 'Partner & Staff Portal Sign In';
require __DIR__ . '/includes/templates/portal-login-view.php';
