<?php
/**
 * Campus Job Posting System - Centralized Mail Dispatcher
 * Authenticated SMTP delivery via PHPMailer
 */

require_once __DIR__ . '/ai-config.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

function is_smtp_configured(): bool {
    load_env();
    $smtp_user = trim((string)getenv('MAIL_USERNAME'));
    $smtp_pass = trim((string)getenv('MAIL_PASSWORD'));
    return ($smtp_user !== '' && $smtp_pass !== '');
}

function send_campus_email(string $recipient_email, string $recipient_name, string $subject, string $html_body): bool {
    if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    load_env();

    $smtp_user = (string)getenv('MAIL_USERNAME');
    $smtp_pass = (string)getenv('MAIL_PASSWORD');
    if ($smtp_user === '' || $smtp_pass === '') {
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = (string)(getenv('MAIL_HOST') ?: 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->Port       = (int)(getenv('MAIL_PORT') ?: 587);
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPSecure = (strtolower((string)getenv('MAIL_ENCRYPTION')) === 'ssl' || $mail->Port === 465)
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $from_address = (string)(getenv('MAIL_FROM_ADDRESS') ?: $smtp_user);
        $from_name    = (string)(getenv('MAIL_FROM_NAME') ?: 'KLD Campus Job Portal');

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
