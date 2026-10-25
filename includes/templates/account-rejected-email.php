<?php
/**
 * Account Rejection / Revocation Notification HTML Email Template
 * Campus Job Posting System - KLD Campus Hire
 */
$safe_recipient = htmlspecialchars($recipient_name ?? 'User', ENT_QUOTES, 'UTF-8');
$safe_role      = htmlspecialchars(ucfirst($role ?? 'student'), ENT_QUOTES, 'UTF-8');
$safe_reason    = htmlspecialchars(trim((string)($reason ?? '')), ENT_QUOTES, 'UTF-8');
$is_revoked     = (($status ?? 'rejected') === 'revoked');
$headline       = $is_revoked ? 'Verification Revoked' : 'Registration Not Approved';
$badge_text     = $is_revoked ? 'Account Access Restricted' : 'Action Required';
$lead_text      = $is_revoked
    ? 'University administration has revoked the verification of your account. Portal access has been restricted.'
    : 'Your registration was reviewed by the administrator and could not be approved in its current state. Portal access remains restricted until the requested revisions are submitted and approved.';
$default_action_url = function_exists('get_app_base_url') ? get_app_base_url() . '/login.php' : 'http://localhost/Campus-Job-Posting-System/login.php';
$action_url     = htmlspecialchars($action_url ?? $default_action_url, ENT_QUOTES, 'UTF-8');
$site_name      = htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'KLD Campus Job Portal', ENT_QUOTES, 'UTF-8');
$current_year   = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $headline ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div style="background-color: #f1f5f9; padding: 32px 12px;">
        <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

            <!-- Brand Header -->
            <div style="background: #0f172a; padding: 24px 28px;">
                <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: -0.01em;">
                    <?= $site_name ?>
                </h1>
                <p style="margin: 4px 0 0; color: #f87171; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                    <?= htmlspecialchars($badge_text, ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>

            <!-- Email Body Content -->
            <div style="padding: 28px 28px 20px 28px; color: #1e293b; font-size: 15px; line-height: 1.6;">
                <p style="margin-top: 0; margin-bottom: 12px;">Hello <strong><?= $safe_recipient ?></strong>,</p>

                <p style="margin-top: 0; margin-bottom: 20px; color: #475569;">
                    We are writing about your <strong><?= $safe_role ?></strong> account registration on <?= $site_name ?>. <?= htmlspecialchars($lead_text, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Status Badge Box -->
                <div style="text-align: center; margin: 28px 0;">
                    <div style="display: inline-block; background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 12px; padding: 20px 32px;">
                        <div style="font-size: 32px; margin-bottom: 8px;">⚠️</div>
                        <span style="display: block; font-size: 16px; color: #991b1b; font-weight: 700; margin-bottom: 4px;">
                            <?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span style="font-size: 13px; color: #475569;">
                            <?= $is_revoked ? 'Please contact the Career Services Office for assistance.' : 'Sign in to review the feedback and resubmit your documents.' ?>
                        </span>
                    </div>
                </div>

                <?php if ($safe_reason !== ''): ?>
                    <!-- Administrator Feedback -->
                    <div style="background: #fff7ed; border-left: 4px solid #dc2626; padding: 14px 16px; margin: 24px 0; border-radius: 0 8px 8px 0;">
                        <strong style="color: #991b1b; display: block; margin-bottom: 6px; font-size: 13px;">Administrator Feedback</strong>
                        <span style="font-size: 13px; color: #475569;"><?= $safe_reason ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!$is_revoked): ?>
                    <!-- What To Do Next -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; margin: 24px 0; border-radius: 8px;">
                        <strong style="color: #0f172a; display: block; margin-bottom: 6px; font-size: 13px;">What happens next?</strong>
                        <ol style="margin: 0; padding-left: 18px; font-size: 13px; color: #475569;">
                            <li style="margin-bottom: 4px;">Sign in with your registered email and password.</li>
                            <li style="margin-bottom: 4px;">Review the administrator's feedback on the account review page.</li>
                            <li>Attach the corrected Certificate of Registration / Student ID or business permit and resubmit.</li>
                        </ol>
                    </div>
                <?php endif; ?>

                <!-- Call to Action Button -->
                <div style="text-align: center; margin: 30px 0 24px 0;">
                    <a href="<?= $action_url ?>" style="display: inline-block; background: #0f172a; color: #ffffff; text-decoration: none; padding: 13px 32px; border-radius: 9999px; font-weight: 600; font-size: 14px; letter-spacing: 0.02em; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        <?= $is_revoked ? 'Sign In for Details' : 'Review Status &amp; Resubmit' ?> &rarr;
                    </a>
                </div>

                <!-- Help Desk Box -->
                <div style="background: #f8fafc; border-left: 4px solid #0f172a; padding: 14px 16px; margin: 24px 0 16px; font-size: 13px; color: #475569; border-radius: 0 8px 8px 0;">
                    <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Need Assistance?</strong>
                    If you have questions about this decision, please contact the Career Services Office or reply to this notice.
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
