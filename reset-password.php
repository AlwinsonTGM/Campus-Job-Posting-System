<?php
/**
 * Campus Job Posting System - Reset Password (token link target)
 * Simplest flow: forgot-pass.php emails ?token=... -> this page sets new password.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$reset = $token !== '' ? get_password_reset_by_token($token) : null;

$error = null;
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $reset = $token !== '' ? get_password_reset_by_token($token) : null;
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $strength = validate_password_strength($new);

    if (!$reset) {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    } elseif (!$strength['valid']) {
        $error = $strength['error'];
    } elseif ($new !== $confirm) {
        $error = 'New password and confirm password do not match.';
    } else {
        if (consume_password_reset((int)$reset['id'], (int)$reset['user_id'], $new)) {
            $done = true;
        } else {
            $error = 'Could not update password. Please try again.';
        }
    }
}

$page_title = 'Set New Password';
// Last line: view template
require __DIR__ . '/includes/templates/reset-password-view.php';

