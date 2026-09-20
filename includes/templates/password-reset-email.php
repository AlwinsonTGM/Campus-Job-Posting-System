<?php
/**
 * Institutional Password Reset HTML Email Template
 */
$safe_recipient = htmlspecialchars($recipient_name ?? 'Student', ENT_QUOTES, 'UTF-8');
$safe_reset_url = htmlspecialchars($reset_url ?? '#', ENT_QUOTES, 'UTF-8');
$current_year   = date('Y');
?>
<div style="background-color: #f1f5f9; padding: 32px 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="background: #0f172a; padding: 24px 28px;">
            <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700;">KLD Campus Job Portal</h1>
            <p style="margin: 4px 0 0; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Institutional Account Recovery</p>
        </div>
        <div style="padding: 28px; color: #1e293b; font-size: 15px; line-height: 1.6;">
            <p style="margin-top: 0;">Hello <strong><?= $safe_recipient ?></strong>,</p>
            <p>We received a password reset request for your account. Click the button below to choose a new password:</p>
            <div style="margin: 24px 0; text-align: center;">
                <a href="<?= $safe_reset_url ?>" style="background: #15803d; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 14px; padding: 12px 28px; border-radius: 9999px; display: inline-block;">Reset Password</a>
            </div>
            <div style="background: #f8fafc; border-left: 4px solid #15803d; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #475569;">
                <strong>Security Notice:</strong> This link expires in 60 minutes and is strictly single-use. If you did not make this request, you can safely ignore this email.
            </div>
            <p style="font-size: 12px; color: #94a3b8; word-break: break-all;"><?= $safe_reset_url ?></p>
        </div>
        <div style="padding: 16px 28px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9;">
            &copy; <?= $current_year ?> Kolehiyo ng Lungsod ng Dasmariñas (KLD). All rights reserved.
        </div>
    </div>
</div>
