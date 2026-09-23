<?php
/**
 * View template for verify-email.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <div class="auth-shell">
                    
                    <!-- Left Brand Identity Panel -->
                    <div class="auth-brand-panel">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-3 p-2 shadow-sm" style="width: 38px; height: 38px;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#2ECC5E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="fw-extrabold text-ink fs-5 tracking-tight"><?= htmlspecialchars(SITE_NAME) ?></span>
                            </div>

                            <span class="eyebrow-badge text-muted-custom">Identity Verification</span>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Verify Your Campus Email
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                To protect your institutional account and confirm ownership of your email address, please enter the single-use 6-digit confirmation code.
                            </p>

                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Single-Use 6-Digit Passcode</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">15-Minute Expiration Window</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-envelope-at-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Direct Institutional Delivery</span>
                                </div>
                            </div>
                        </div>

                        <!-- Theme-Aware Helpdesk Card -->
                        <div class="otp-helpdesk-card">
                            <span class="small fw-bold text-ink d-block mb-1">Didn't Receive the Email?</span>
                            <p class="small text-muted-custom mb-0" style="font-size: 11.5px;">
                                Check your spam/junk folder or contact Campus IT at <code>support@kld.edu.ph</code> for assistance.
                            </p>
                        </div>
                    </div>

                    <!-- Right Verification Panel -->
                    <div class="auth-form-panel position-relative">
                        
                        <!-- Main Interactive Form View -->
                        <div id="otp-form-view">
                            <div class="mb-4">
                                <h2 class="card-paper-title fs-4 mb-1">Enter Verification Code</h2>
                                <p class="text-muted-custom small mb-2">We dispatched a 6-digit security code to:</p>
                                <div class="otp-email-chip">
                                    <i class="bi bi-envelope text-accent"></i>
                                    <span><?= htmlspecialchars($pending_email) ?></span>
                                </div>
                            </div>

                            <?php if (!$smtp_ready): ?>
                                <div class="alert-paper alert-paper--warning mb-4">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                        <div>
                                            <strong class="d-block text-ink small mb-1">Notice: Outbound Mail Server Unconfigured</strong>
                                            <span class="small text-muted-custom">
                                                SMTP credentials (<code>MAIL_USERNAME</code> and <code>MAIL_PASSWORD</code>) are not configured in <code>.env</code>.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Dynamic Error Notification -->
                            <div id="otp-error-alert" class="alert-paper alert-paper--danger mb-4 <?= $error ? '' : 'd-none' ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                                    <span class="small fw-semibold text-ink" id="otp-error-text"><?= htmlspecialchars($error ?? '') ?></span>
                                </div>
                            </div>

                            <form action="verify-email.php" method="POST" id="otp-form" class="form-paper">
                                <input type="hidden" name="action" value="verify">
                                <input type="hidden" name="otp_code" id="otp-code-hidden" value="">

                                <div class="mb-4 text-center">
                                    <label class="form-label d-block text-muted-custom small text-uppercase tracking-wider mb-3 fw-semibold" style="font-size: 11px;">
                                        Type or paste your 6-digit confirmation code:
                                    </label>

                                    <!-- Fixed-Dimension 6-Digit Monospace Pods -->
                                    <div class="otp-inputs-grid mb-2" id="otp-inputs-wrapper">
                                        <?php for ($i = 0; $i < 6; $i++): ?>
                                            <input type="text"
                                                   name="digit[]"
                                                   class="otp-digit-pod"
                                                   maxlength="1"
                                                   inputmode="numeric"
                                                   pattern="[0-9]"
                                                   autocomplete="one-time-code"
                                                   data-index="<?= $i ?>"
                                                   required
                                                   <?= $i === 0 ? 'autofocus' : '' ?>>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="form-text text-muted-custom small mt-2" style="font-size: 11.5px;">
                                        You can paste the entire 6-digit code directly into any box.
                                    </div>
                                </div>

                                <button type="submit" id="btn-submit-otp" class="btn-pill w-100 mb-3">
                                    <i class="bi bi-shield-check me-1"></i> VERIFY EMAIL & CONTINUE
                                </button>
                            </form>

                            <!-- Cooldown Resend & Clean Cancel -->
                            <div class="d-flex align-items-center justify-content-between border-top border-line pt-3 mt-3">
                                <form action="verify-email.php" method="POST" id="resend-form" class="d-inline">
                                    <input type="hidden" name="action" value="resend">
                                    <button type="submit"
                                            id="btn-resend-otp"
                                            class="btn btn-sm btn-link text-decoration-none p-0 text-muted-custom fw-semibold"
                                            data-remaining="<?= (int)$remaining_seconds ?>"
                                            <?= $remaining_seconds > 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        <span id="resend-text">
                                            <?= $remaining_seconds > 0 ? "Resend code in {$remaining_seconds}s" : "Resend Verification Code" ?>
                                        </span>
                                    </button>
                                </form>

                                <a href="verify-email.php?action=cancel" class="text-danger small fw-semibold text-decoration-none">
                                    <i class="bi bi-box-arrow-left me-1"></i> Cancel & Sign In
                                </a>
                            </div>
                        </div>

                        <!-- 1.2-Second Seamless In-Card Success Celebration Morph -->
                        <div id="otp-success-view" class="otp-success-morph" style="display: none;">
                            <svg class="otp-checkmark-svg" viewBox="0 0 52 52">
                                <circle class="otp-checkmark-circle" cx="26" cy="26" r="23"/>
                                <path class="otp-checkmark-check" d="M14 27l8 8 16-16"/>
                            </svg>

                            <h3 class="h4 fw-bold text-ink mb-1">Identity Verified!</h3>
                            <p class="text-muted-custom small mb-3" id="otp-success-msg">
                                Welcome to your campus workspace. Redirecting...
                            </p>

                            <div class="otp-progress-bar-wrap">
                                <div class="otp-progress-bar-fill"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

<script src="assets/js/otp-verify.js"></script>

