<?php
/**
 * Campus Job Posting System - Reset Password (token link target)
 * Simplest flow: forgot-pass.php emails ?token=... -> this page sets new password.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$reset = $token !== '' ? get_password_reset_by_token($token) : null;

// AJAX endpoint for real-time duplicate password check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'check_old_password') {
    header('Content-Type: application/json; charset=utf-8');
    $pwd = $_POST['password'] ?? '';
    if (!$reset) {
        echo json_encode(['valid' => false, 'same_as_old' => false, 'error' => 'Invalid or expired token']);
        exit;
    }
    $target_user = get_user_by_id((int)$reset['user_id']);
    $stored_pass = $target_user['password'] ?? '';
    $is_same = false;
    if (!empty($stored_pass) && !empty($pwd)) {
        $is_same = password_verify($pwd, $stored_pass) || ($stored_pass === $pwd);
    }
    echo json_encode(['valid' => true, 'same_as_old' => $is_same]);
    exit;
}

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
        // Enforce that new password cannot be the same as the old password
        $target_user = get_user_by_id((int)$reset['user_id']);
        $stored_pass = $target_user['password'] ?? '';
        $is_same_as_old = false;
        if (!empty($stored_pass)) {
            if (password_verify($new, $stored_pass) || ($stored_pass === $new)) {
                $is_same_as_old = true;
            }
        }

        if ($is_same_as_old) {
            $error = 'New password cannot be the same as your previous password. Please choose a different password.';
        } elseif (consume_password_reset((int)$reset['id'], (int)$reset['user_id'], $new)) {
            $done = true;
        } else {
            $error = 'Could not update password. Please try again.';
        }
    }
}

$page_title = 'Set New Password';
// Last line: view template
require __DIR__ . '/includes/templates/reset-password-view.php';
