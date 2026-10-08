<?php
/**
 * Campus Job Posting System - Account Login
 * Archetype A: Auth Split Card Shell (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

// Test hooks (?reset / ?demo) — LOCAL-ONLY. Blocked in production and for remote clients.
// E2E suite runs on 127.0.0.1 so tests keep working. Set APP_ENV=production in prod .env to hard-close.
$__app_env = strtolower(trim((string)get_env('APP_ENV', '')));
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
    DatastoreManager::resetToDemo('Datastore Reset', true);
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
    $role_path = ($u['role'] ?? '') . '/';
    if ($safe_next !== '' && strpos($safe_next, $role_path) === 0) {
        header('Location: ' . $safe_next);
        exit;
    }
    redirect_by_role($u['role'] ?? null);
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $res = login_user($email, $password);
    if ($res['success']) {
        $u = $res['user'];
        set_flash('success', "Welcome back, {$u['name']}!");
        if ($safe_next !== '') {
            header('Location: ' . $safe_next);
            exit;
        }
        redirect_by_role($u['role'] ?? null);
    } elseif (!empty($res['unverified'])) {
        SessionGuard::quarantineForVerification($res['user']);
        exit;
    } else {
        $error = $res['message'];
    }
}

$page_title = 'Sign In to Campus Hire';
// Last line: view template
require __DIR__ . '/includes/templates/login-view.php';

