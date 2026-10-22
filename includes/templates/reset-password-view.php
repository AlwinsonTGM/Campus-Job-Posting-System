<?php
/**
 * View template for reset-password.php
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
                            <!-- Brand Mark -->
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

                            <span class="eyebrow-badge text-muted-custom">Account Recovery</span>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Choose a new password
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Create a strong, unique password to secure your institutional account and restore access.
                            </p>

                            <!-- Feature / Security Checklist -->
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Encrypted Single-Use Reset Token</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Cannot Reuse Previous Password</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Strict Institutional Security Safeguards</span>
                                </div>
                            </div>
                        </div>

                        <!-- Reset Link Security Card -->
                        <div class="p-3 bg-white rounded-4 border border-line mt-3" style="background: var(--surface) !important;">
                            <span class="small fw-bold text-ink d-block mb-1"><i class="bi bi-info-circle text-accent me-1"></i> Reset Link Notice</span>
                            <p class="small text-muted-custom mb-0" style="font-size: 11.5px;">
                                Your reset link expires in 1 hour and can only be used once. For your account security, never share your credentials with anyone.
                            </p>
                        </div>
                    </div>

                    <!-- Right Form Panel -->
                    <div class="auth-form-panel">
                        <div class="mb-4">
                            <h2 class="card-paper-title fs-4 mb-1">Set New Password</h2>
                            <p class="text-muted-custom small mb-0">Enter and confirm your new password below</p>
                        </div>

                        <?php if ($done): ?>
                            <div class="alert-paper alert-paper--success mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-accent fs-5"></i>
                                    <div>
                                        <strong class="d-block mb-1 text-ink">Password updated!</strong>
                                        <span class="small text-muted-custom">You can now sign in with your new password.</span>
                                    </div>
                                </div>
                            </div>
                            <a href="login.php" class="btn-pill w-100 text-center text-decoration-none">Go to Sign In</a>
                        <?php elseif (!$reset): ?>
                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                    <div>
                                        <strong class="d-block mb-1 text-ink">Link invalid or expired</strong>
                                        <span class="small text-muted-custom">Reset links expire after 1 hour and are single-use.</span>
                                    </div>
                                </div>
                            </div>
                            <a href="forgot-pass.php" class="btn-pill w-100 text-center text-decoration-none">Request a new link</a>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert-paper alert-paper--danger mb-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                        <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <form action="reset-password.php?token=<?= htmlspecialchars($token) ?>" method="POST" id="reset-password-form" class="form-paper">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                
                                <!-- New Password Field -->
                                <div class="mb-3">
                                    <label class="form-label" for="new-password">New Password</label>
                                    <div class="input-group input-group-integrated">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" name="new_password" id="new-password" class="form-control" placeholder="Minimum 8 characters" required autocomplete="new-password">
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('new-password', 'toggle-new-pw-icon')" aria-label="Toggle new password visibility">
                                            <i class="bi bi-eye" id="toggle-new-pw-icon"></i>
                                        </button>
                                    </div>
                                    <div class="password-meter-bar"><div id="password-meter-fill" class="password-meter-fill"></div></div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <div id="password-strength-text" class="small text-muted-custom">Enter password to see strength</div>
                                        <span class="small text-muted-custom" style="font-size: 11px;">Min. 8 characters</span>
                                    </div>
                                    <div id="old-password-warning" class="invalid-feedback d-block small mt-1 d-none">
                                        <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> New password cannot be the same as your previous password.
                                    </div>
                                </div>

                                <!-- Confirm New Password Field -->
                                <div class="mb-4">
                                    <label class="form-label" for="confirm-password">Confirm New Password</label>
                                    <div class="input-group input-group-integrated">
                                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                        <input type="password" name="confirm_password" id="confirm-password" class="form-control" placeholder="Re-type new password" required autocomplete="new-password">
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('confirm-password', 'toggle-conf-pw-icon')" aria-label="Toggle confirm password visibility">
                                            <i class="bi bi-eye" id="toggle-conf-pw-icon"></i>
                                        </button>
                                    </div>
                                    <div id="confirm-feedback" class="mt-1"></div>
                                </div>

                                <button type="submit" id="submit-btn" class="btn-pill w-100 mb-3">Update Password</button>
                            </form>
                        <?php endif; ?>

                        <div class="border-top border-line pt-3 text-center">
                            <a href="login.php" class="text-ink fw-bold small text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Return to Sign In</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

<script src="assets/js/password-strength.js"></script>
