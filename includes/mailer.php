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

function get_app_base_url(): string {
    $app_url = function_exists('get_env') ? trim((string)get_env('APP_URL', '')) : '';
    if ($app_url !== '') {
        return rtrim($app_url, '/');
    }

    if (!empty($_SERVER['HTTP_HOST'])) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $scheme = $https ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];

        $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = dirname($script_name);
        $base = preg_replace('#/(admin|employer|student|api|includes|tools)(/.*)?$#i', '', $dir);
        $base = ($base === '/' || $base === '.') ? '' : rtrim($base, '/');

        return $scheme . $host . $base;
    }

    return 'http://localhost/Campus-Job-Posting-System';
}

function send_account_approved_email(string $email, string $name, string $role = 'student'): bool {
    $site = defined('SITE_NAME') ? SITE_NAME : 'KLD Campus Job Portal';
    $subject = 'Account Approved & Verified - ' . $site;
    
    // Resolve absolute login URL for email clients
    $login_url = get_app_base_url() . '/login.php';

    $body = render_email_template('account-approved-email', [
        'recipient_name' => $name,
        'role'           => $role,
        'login_url'      => $login_url
    ]);

    if ($body === '') {
        return false;
    }

    return send_campus_email($email, $name, $subject, $body);
}

/**
 * Notify an applicant that their registration was rejected or their
 * verification was revoked, including the administrator's feedback.
 *
 * @param string $status 'rejected' (revision possible) or 'revoked' (contact admin).
 */
function send_account_rejected_email(string $email, string $name, string $role = 'student', string $reason = '', string $status = 'rejected'): bool {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $site = defined('SITE_NAME') ? SITE_NAME : 'KLD Campus Job Portal';
    $is_revoked = ($status === 'revoked');
    $subject = ($is_revoked ? 'Account Verification Revoked' : 'Action Required: Registration Not Approved') . ' - ' . $site;

    // Rejected applicants are still allowed to sign in to read the decision and
    // resubmit, so the CTA always points at the sign-in gateway.
    $action_url = get_app_base_url() . '/login.php';

    $body = render_email_template('account-rejected-email', [
        'recipient_name' => $name,
        'role'           => $role,
        'reason'         => trim($reason),
        'status'         => $is_revoked ? 'revoked' : 'rejected',
        'action_url'     => $action_url
    ]);

    if ($body === '') {
        return false;
    }

    return send_campus_email($email, $name, $subject, $body);
}


/**
 * Dispatch welcome email with login credentials for administratively provisioned employer accounts.
 */
function send_provisioned_account_email(
    string $email,
    string $name,
    string $org_name,
    string $employer_type,
    string $temp_password
): bool {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $site_name = defined('SITE_NAME') ? SITE_NAME : 'KLD Campus Job Portal';
    $type_label = ($employer_type === 'university_office') ? 'University Office' : 'Accredited Industry Partner';
    $subject = 'Your ' . $site_name . ' Employer Account Has Been Provisioned';

    $base_url = function_exists('get_app_base_url') ? get_app_base_url() : 'http://localhost/Final-Campus-Job-Posting-System';
    $login_url = rtrim($base_url, '/') . '/portal-login.php';

    $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safe_org = htmlspecialchars($org_name, ENT_QUOTES, 'UTF-8');
    $safe_type = htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8');
    $safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safe_pass = htmlspecialchars($temp_password, ENT_QUOTES, 'UTF-8');
    $safe_site = htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8');
    $safe_login_url = htmlspecialchars($login_url, ENT_QUOTES, 'UTF-8');
    $current_year = date('Y');

    $html_body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Account Provisioned</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div style="background-color: #f1f5f9; padding: 32px 12px;">
        <div style="max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            
            <!-- Brand Header -->
            <div style="background: #0f172a; padding: 24px 28px;">
                <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: -0.01em;">
                    {$safe_site}
                </h1>
                <p style="margin: 4px 0 0; color: #38bdf8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                    Employer Account Provisioned &amp; Verified
                </p>
            </div>

            <!-- Email Body Content -->
            <div style="padding: 28px 28px 20px 28px; color: #1e293b; font-size: 15px; line-height: 1.6;">
                <p style="margin-top: 0; margin-bottom: 12px;">Hello <strong>{$safe_name}</strong>,</p>
                
                <p style="margin-top: 0; margin-bottom: 20px; color: #475569;">
                    An official employer account has been provisioned for <strong>{$safe_org}</strong> ({$safe_type}) by the Career Services Administrator. Your organization has been verified and granted posting access on the campus hiring platform.
                </p>

                <!-- Credentials Card -->
                <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; margin: 24px 0;">
                    <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 12px; letter-spacing: 0.05em;">
                        Your Sign-in Credentials
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <tr>
                            <td style="padding: 6px 0; color: #64748b; width: 140px; font-weight: 600;">Account Type:</td>
                            <td style="padding: 6px 0; color: #0f172a; font-weight: 600;">{$safe_type}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Organization:</td>
                            <td style="padding: 6px 0; color: #0f172a;">{$safe_org}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Login Email:</td>
                            <td style="padding: 6px 0; color: #0f172a; font-family: monospace; font-size: 14px;">{$safe_email}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Temporary Password:</td>
                            <td style="padding: 6px 0;">
                                <code style="background-color: #e2e8f0; color: #0f172a; padding: 4px 8px; border-radius: 6px; font-size: 15px; font-weight: 700; letter-spacing: 0.05em;">{$safe_pass}</code>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Security Notice -->
                <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; border-radius: 4px;">
                    <strong style="color: #92400e; font-size: 13px; display: block; margin-bottom: 4px;">Important Security Requirement:</strong>
                    <span style="color: #78350f; font-size: 13px;">
                        You are required to replace your temporary password with a new personal password immediately upon your first sign-in before accessing portal features.
                    </span>
                </div>

                <!-- Call to Action -->
                <div style="text-align: center; margin: 28px 0 20px 0;">
                    <a href="{$safe_login_url}" style="display: inline-block; background-color: #0284c7; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
                        Sign In via Portal Gateway &rarr;
                    </a>
                </div>

                <p style="margin-top: 24px; margin-bottom: 0; font-size: 12px; color: #94a3b8; text-align: center;">
                    Direct Portal URL: <a href="{$safe_login_url}" style="color: #0284c7; word-break: break-all;">{$safe_login_url}</a>
                </p>
            </div>

            <!-- Footer -->
            <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 28px; text-align: center; font-size: 12px; color: #64748b;">
                &copy; {$current_year} {$safe_site}. All rights reserved.<br>
                Colegio de Montalban / KLD Career Placement Services
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    return send_campus_email($email, $name, $subject, $html_body);
}
