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
                    <div class="auth-brand-panel">
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
                        <h2 class="h3 fw-bold text-ink mb-3">Choose a new password</h2>
                        <p class="text-muted-custom small mb-4">Your reset link expires in 1 hour and can only be used once.</p>
                    </div>

                    <div class="auth-form-panel">
                        <div class="mb-4">
                            <h2 class="card-paper-title fs-4 mb-1">Set New Password</h2>
                            <p class="text-muted-custom small mb-0">Enter and confirm your new password below</p>
                        </div>

                        <?php if ($done): ?>
                            <div class="alert-paper alert-paper--success mb-4">
                                <strong class="d-block mb-1 text-ink">Password updated!</strong>
                                <span class="small text-muted-custom">You can now sign in with your new password.</span>
                            </div>
                            <a href="login.php" class="btn-pill w-100 text-center text-decoration-none">Go to Sign In</a>
                        <?php elseif (!$reset): ?>
                            <div class="alert-paper alert-paper--danger mb-4">
                                <strong class="d-block mb-1 text-ink">Link invalid or expired</strong>
                                <span class="small text-muted-custom">Reset links expire after 1 hour and are single-use.</span>
                            </div>
                            <a href="forgot-pass.php" class="btn-pill w-100 text-center text-decoration-none">Request a new link</a>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert-paper alert-paper--danger mb-4">
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                </div>
                            <?php endif; ?>

                            <form action="reset-password.php?token=<?= htmlspecialchars($token) ?>" method="POST" id="reset-password-form" class="form-paper">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                <div class="mb-3">
                                    <label class="form-label" for="new-password">New Password</label>
                                    <input type="password" name="new_password" id="new-password" class="form-control" placeholder="Minimum 8 characters" required>
                                    <div class="password-meter-bar"><div id="password-meter-fill" class="password-meter-fill"></div></div>
                                    <div id="password-strength-text" class="small text-muted-custom mt-1">Enter password to see strength</div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="confirm-password">Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirm-password" class="form-control" placeholder="Re-type new password" required>
                                </div>
                                <button type="submit" class="btn-pill w-100 mb-3">Update Password</button>
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

