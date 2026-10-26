<?php
/** Email verification keeps the existing OTP controller, script, and animation hooks. */
$auth_partner = ($existing_user['role'] ?? 'student') !== 'student';
$auth_story_title = 'One last step. Make it yours.';
$auth_story_description = 'Confirm your email address to finish setting up your Campus Hire account.';
$auth_story_benefits = [['shield-check', 'Single-use, 6-digit code'], ['clock-history', 'Valid for 15 minutes'], ['envelope-check', 'Confirm ownership of your email']];
$auth_story_footnote = 'Your account, one step closer.';
$verification_flash = get_flash();
require __DIR__ . '/auth-start.php';
?>
<div class="email-verification">
    <div id="otp-form-view">
        <h1 id="form-title">Check your email.</h1>
        <p class="intro verification-intro">Enter the 6-digit verification code sent to:</p>
        <div class="otp-email-chip"><i class="bi bi-envelope" aria-hidden="true"></i><span><?= htmlspecialchars($pending_email) ?></span></div>
        <?php if ($verification_flash): ?><div class="verification-notice" role="status"><?= htmlspecialchars($verification_flash['message']) ?></div><?php endif; ?>

        <?php if (!$smtp_ready): ?>
            <div class="verification-notice" role="status">
                <strong>Email delivery is not configured.</strong>
                <p>SMTP credentials (<code>MAIL_USERNAME</code> and <code>MAIL_PASSWORD</code>) are not configured in <code>.env</code>.</p>
                <?php if (!empty($_SESSION['pending_verification']['dev_code'])): ?><p>Verification code for this testing session: <strong><?= htmlspecialchars($_SESSION['pending_verification']['dev_code']) ?></strong></p><?php endif; ?>
            </div>
        <?php endif; ?>

        <div id="otp-error-alert" class="error-summary <?= $error ? '' : 'd-none' ?>" role="alert"><span id="otp-error-text"><?= htmlspecialchars($error ?? '') ?></span></div>
        <form action="verify-email.php" method="POST" id="otp-form">
            <input type="hidden" name="action" value="verify">
            <input type="hidden" name="otp_code" id="otp-code-hidden" value="">
            <div class="verification-code-field">
                <label for="otp-digit-0">6-digit verification code</label>
                <div class="otp-inputs-grid" id="otp-inputs-wrapper">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <input type="text" id="otp-digit-<?= $i ?>" name="digit[]" class="otp-digit-pod" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" data-index="<?= $i ?>" aria-label="Digit <?= $i + 1 ?> of 6" aria-describedby="otp-code-hint" required <?= $i === 0 ? 'autofocus' : '' ?>>
                    <?php endfor; ?>
                </div>
                <p class="hint" id="otp-code-hint">Type the code or paste all 6 digits into any box.</p>
            </div>
            <button type="submit" id="btn-submit-otp" class="primary"><span>Verify email &amp; continue</span><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>
        <div class="verification-actions">
            <form action="verify-email.php" method="POST" id="resend-form">
                <input type="hidden" name="action" value="resend">
                <button type="submit" id="btn-resend-otp" class="text-button" data-remaining="<?= (int)$remaining_seconds ?>" <?= $remaining_seconds > 0 ? 'disabled' : '' ?>><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> <span id="resend-text"><?= $remaining_seconds > 0 ? "Resend code in {$remaining_seconds}s" : 'Resend verification code' ?></span></button>
            </form>
            <a href="verify-email.php?action=cancel">Cancel registration</a>
        </div>
        <p class="hint verification-help">Didn't receive an email? Check your spam or junk folder, or contact <a href="mailto:support@kld.edu.ph">support@kld.edu.ph</a>.</p>
    </div>
    <div id="otp-success-view" class="otp-success-morph" role="status" aria-live="polite" style="display: none;">
        <svg class="otp-checkmark-svg" viewBox="0 0 52 52" aria-hidden="true"><circle class="otp-checkmark-circle" cx="26" cy="26" r="23"/><path class="otp-checkmark-check" d="M14 27l8 8 16-16"/></svg>
        <h2>Email verified!</h2>
        <p class="intro" id="otp-success-msg">Your email is confirmed. Taking you to the next step…</p>
        <div class="otp-progress-bar-wrap" aria-hidden="true"><div class="otp-progress-bar-fill"></div></div>
    </div>
</div>
<script src="assets/js/otp-verify.js?v=<?= filemtime(__DIR__ . '/../../assets/js/otp-verify.js') ?>"></script>
<?php require __DIR__ . '/auth-end.php'; ?>
