<?php
/**
 * Account Approval Notification HTML Email Template
 * Campus Job Posting System - KLD Campus Hire
 */
$safe_recipient = htmlspecialchars($recipient_name ?? 'User', ENT_QUOTES, 'UTF-8');
$safe_role      = htmlspecialchars(ucfirst($role ?? 'student'), ENT_QUOTES, 'UTF-8');
$default_login_url = function_exists('get_app_base_url') ? get_app_base_url() . '/login.php' : 'http://localhost/Campus-Job-Posting-System/login.php';
$login_url      = htmlspecialchars($login_url ?? $default_login_url, ENT_QUOTES, 'UTF-8');
$site_name      = htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'KLD Campus Job Portal', ENT_QUOTES, 'UTF-8');
$current_year   = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Approved</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div style="background-color: #f1f5f9; padding: 32px 12px;">
        <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            
            <!-- Brand Header -->
            <div style="background: #0f172a; padding: 24px 28px;">
                <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: -0.01em;">
                    <?= $site_name ?>
                </h1>
                <p style="margin: 4px 0 0; color: #2ecc71; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                    Account Approved &amp; Verified
                </p>
            </div>

            <!-- Email Body Content -->
            <div style="padding: 28px 28px 20px 28px; color: #1e293b; font-size: 15px; line-height: 1.6;">
                <p style="margin-top: 0; margin-bottom: 12px;">Hello <strong><?= $safe_recipient ?></strong>,</p>
                
                <p style="margin-top: 0; margin-bottom: 20px; color: #475569;">
                    Great news! Your <strong><?= $safe_role ?></strong> account registration and credentials have been officially reviewed and <strong style="color: #15803d;">approved</strong> by the administrator.
                </p>

                <!-- Success Badge Box -->
                <div style="text-align: center; margin: 28px 0;">
                    <div style="display: inline-block; background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 20px 32px;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                        <span style="display: block; font-size: 16px; color: #166534; font-weight: 700; margin-bottom: 4px;">
                            Account Verified
                        </span>
                        <span style="font-size: 13px; color: #475569;">
                            You now have full access to campus opportunities and features.
                        </span>
                    </div>
                </div>

                <!-- Call to Action Button -->
                <div style="text-align: center; margin: 30px 0 24px 0;">
                    <a href="<?= $login_url ?>" style="display: inline-block; background: #0f172a; color: #ffffff; text-decoration: none; padding: 13px 32px; border-radius: 9999px; font-weight: 600; font-size: 14px; letter-spacing: 0.02em; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        Sign In to Your Account &rarr;
                    </a>
                </div>

                <!-- Help Desk Box -->
                <div style="background: #f8fafc; border-left: 4px solid #0f172a; padding: 14px 16px; margin: 24px 0 16px; font-size: 13px; color: #475569; border-radius: 0 8px 8px 0;">
                    <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Need Assistance?</strong>
                    If you have questions or experience issues logging in, please contact the Career Services Office or reply to this notice.
                </div>

                <p style="margin-top: 24px; margin-bottom: 0; font-size: 13px; color: #64748b;">
                    Best regards,<br>
                    <strong><?= $site_name ?> Administration</strong>
                </p>
            </div>

            <!-- Footer -->
            <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 28px; text-align: center; font-size: 12px; color: #94a3b8;">
                &copy; <?= $current_year ?> <?= $site_name ?>. All rights reserved.
            </div>

        </div>
    </div>
</body>
</html>
