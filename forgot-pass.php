<?php
/**
 * Campus Job Posting System - Forgot Password
 * Archetype A: Auth Split Card Shell (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/includes/mailer.php';

$submitted = false;
$email = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid institutional email address.';
    } else {
        $domain_check = validate_email_domain_dns($email);
        if (!$domain_check['valid']) {
            $error = $domain_check['error'];
        } else {
            $user = get_user_by_email($email);
            if ($user) {
                $token = create_password_reset((int)$user['id']);
                if ($token) {
                    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
                    $reset_link = ($https ? 'https://' : 'http://') . $host . $base . '/reset-password.php?token=' . $token;

                    $recipient_name = $user['name'] ?? ($user['organization_name'] ?? 'User');
                    send_password_reset_email($email, $recipient_name, $reset_link);
                }
            }
            $submitted = true;
        }
    }
}

$page_title = 'Reset Your Password';
// Last line: view template
require __DIR__ . '/includes/templates/forgot-pass-view.php';
