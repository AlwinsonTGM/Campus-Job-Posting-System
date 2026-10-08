<?php
/**
 * Root Redirect for Applications
 * Handles legacy/AI assistant redirects pointing to applications.php
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';

if (SessionGuard::check()) {
    $user = SessionGuard::user();
    $role = $user['role'] ?? 'student';
    if ($role === 'employer') {
        header('Location: employer/applications.php' . $qs, true, 302);
        exit;
    } elseif ($role === 'admin') {
        header('Location: admin/reports.php' . $qs, true, 302);
        exit;
    }
}

// Default to student/my-applications.php (which handles student auth-gating if guest)
header('Location: student/my-applications.php' . $qs, true, 302);
exit;
