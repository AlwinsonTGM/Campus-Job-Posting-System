<?php
/**
 * Institutional Email Verification (6-Digit OTP) HTML Email Template
 * Campus Job Posting System - KLD Campus Hire
 */
$safe_recipient = htmlspecialchars($recipient_name ?? 'User', ENT_QUOTES, 'UTF-8');
$safe_code      = htmlspecialchars($verification_code ?? '000000', ENT_QUOTES, 'UTF-8');
$current_year   = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div style="background-color: #f1f5f9; padding: 32px 12px;">
        <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            
            <!-- Brand Header -->
            <div style="background: #0f172a; padding: 24px 28px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: -0.01em;">
                        KLD Campus Job Portal
                    </h1>
                </div>
                <p style="margin: 4px 0 0; color: #2ecc71; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                    Institutional Identity Verification
                </p>
            </div>

            <!-- Email Body Content -->
            <div style="padding: 28px 28px 20px 28px; color: #1e293b; font-size: 15px; line-height: 1.6;">
                <p style="margin-top: 0; margin-bottom: 12px;">Hello <strong><?= $safe_recipient ?></strong>,</p>
                
                <p style="margin-top: 0; margin-bottom: 20px; color: #475569;">
                    Thank you for registering with the KLD Campus Job Portal. To verify your institutional email address and complete your account setup, please enter the one-time verification code below:
                </p>

                <!-- OTP Code Display Card -->
                <div style="text-align: center; margin: 28px 0;">
                    <div style="display: inline-block; background-color: #f0fdf4; border: 2px dashed #86efac; border-radius: 12px; padding: 18px 36px;">
                        <span style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.12em; color: #166534; font-weight: 700; margin-bottom: 6px;">
                            One-Time Passcode (OTP)
                        </span>
                        <span style="font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace; font-size: 34px; font-weight: 800; letter-spacing: 0.22em; color: #15803d; line-height: 1;">
                            <?= $safe_code ?>
                        </span>
                    </div>
                </div>

                <p style="margin: 0 0 16px; font-size: 14px; text-align: center; color: #64748b;">
                    Enter this 6-digit code on the verification screen to activate your account.
                </p>

                <!-- Security Notice Box -->
                <div style="background: #f8fafc; border-left: 4px solid #15803d; padding: 14px 16px; margin: 24px 0 16px; font-size: 13px; color: #475569; border-radius: 0 8px 8px 0;">
                    <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Security Notice:</strong>
                    This code will expire in <strong>15 minutes</strong> and is strictly single-use. If you did not create an account on the KLD Campus Job Portal, please disregard this email.
                </div>
            </div>

            <!-- Footer -->
            <div style="padding: 16px 28px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; background: #fafafa;">
                &copy; <?= $current_year ?> Kolehiyo ng Lungsod ng Dasmariñas (KLD). All rights reserved.<br>
                <span>Career Services & IT Center &bull; Institutional Authentication System</span>
            </div>

        </div>
    </div>
</body>
</html>
