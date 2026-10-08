<?php
/**
 * Campus Job Posting System - Centralized Mail Dispatcher
 * Authenticated SMTP delivery via PHPMailer
 */

require_once __DIR__ . '/ai-config.php';
require_once __DIR__ . '/data-helper.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

function is_smtp_configured(): bool {
    // A QA/test server must be treated as having no mail transport, whatever
    // .env says. This is what makes the app render OTP codes on screen instead
    // of dispatching them, and it is the single switch every caller reads, so
    // gating it here keeps the whole "no mail" behaviour consistent.
    if (function_exists('registration_mail_allowed') && !registration_mail_allowed()) {
        return false;
    }

    load_env();
    $smtp_user = trim((string)get_env('MAIL_USERNAME', ''));
    $smtp_pass = trim((string)get_env('MAIL_PASSWORD', ''));
    return ($smtp_user !== '' && $smtp_pass !== '');
}

function send_campus_email(string $recipient_email, string $recipient_name, string $subject, string $html_body): bool {
    // Hard stop: never open an SMTP connection on a QA/test server, even if a
    // caller bypassed is_smtp_configured().
    if (function_exists('registration_mail_allowed') && !registration_mail_allowed()) {
        error_log("Campus Mailer: dispatch suppressed (QA_MAIL_DISABLED) for {$recipient_email}");
        return false;
    }

    if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    if (function_exists('validate_email_domain_dns')) {
        $domain_res = validate_email_domain_dns($recipient_email);
        if (!$domain_res['valid']) {
            error_log('Campus Mailer rejected invalid domain: ' . ($domain_res['error'] ?? $recipient_email));
            return false;
        }
    }

    // Synthetic test mailbox safeguard: never dispatch real SMTP to non-existent test accounts
    $lower_email = strtolower($recipient_email);
    if (
        str_contains($lower_email, 'darkmode_tester') ||
        str_contains($lower_email, 'test_') ||
        str_contains($lower_email, '@example.com') ||
        str_contains($lower_email, '@test.local') ||
        str_ends_with($lower_email, '.invalid')
    ) {
        error_log("Campus Mailer simulated delivery for synthetic test address: {$recipient_email}");
        return true;
    }

    load_env();

    $smtp_user = trim((string)get_env('MAIL_USERNAME', ''));
    $smtp_pass = trim((string)get_env('MAIL_PASSWORD', ''));
    if ($smtp_user === '' || $smtp_pass === '') {
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = (string)get_env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->Port       = (int)get_env('MAIL_PORT', 587);
        $mail->CharSet    = 'UTF-8';
        $encryption       = strtolower((string)get_env('MAIL_ENCRYPTION', 'tls'));
        $mail->SMTPSecure = ($encryption === 'ssl' || $mail->Port === 465)
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $from_address = (string)get_env('MAIL_FROM_ADDRESS', $smtp_user);
        $from_name    = (string)get_env('MAIL_FROM_NAME', 'KLD Campus Job Portal');

        $mail->setFrom($from_address, $from_name);
        $mail->addAddress($recipient_email, $recipient_name ?: $recipient_email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = strip_tags($html_body);

        return $mail->send();
    } catch (\Throwable $e) {
        error_log('Campus Mailer error: ' . $e->getMessage());
        return false;
    }
}

function render_email_template(string $template_name, array $vars = []): string {
    $file = __DIR__ . '/templates/' . $template_name . '.php';
    if (!file_exists($file)) {
        return '';
    }
    extract($vars, EXTR_SKIP);
    ob_start();
    require $file;
    return (string)ob_get_clean();
}

function send_password_reset_email(string $email, string $name, string $reset_link): bool {
    $subject = 'Reset your ' . (defined('SITE_NAME') ? SITE_NAME : 'Campus Job Portal') . ' password';
    $body = render_email_template('password-reset-email', [
        'recipient_name' => $name,
        'reset_url'      => $reset_link
    ]);
    return $body !== '' && send_campus_email($email, $name, $subject, $body);
}

function send_verification_code_email(string $email, string $name, string $code): array {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'smtp_configured' => is_smtp_configured(),
            'message' => 'Invalid recipient email address format.'
        ];
    }

    if (!is_smtp_configured()) {
        return [
            'success' => false,
            'smtp_configured' => false,
            'message' => 'SMTP credentials (MAIL_USERNAME and MAIL_PASSWORD) are not configured in your .env file.'
        ];
    }

    $site = defined('SITE_NAME') ? SITE_NAME : 'KLD Campus Job Portal';
    $subject = $code . ' is your verification code - ' . $site;
    $body = render_email_template('verification-code-email', [
        'recipient_name'    => $name,
        'verification_code' => $code
    ]);

    if ($body === '') {
        return [
            'success' => false,
            'smtp_configured' => true,
            'message' => 'Email verification template file not found.'
        ];
    }

    $sent = send_campus_email($email, $name, $subject, $body);
    return [
        'success' => $sent,
        'smtp_configured' => true,
        'message' => $sent ? 'Verification code sent to your email.' : 'Failed to dispatch verification email via SMTP server.'
    ];
}
