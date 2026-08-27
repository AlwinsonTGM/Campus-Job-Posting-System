<?php
/**
 * Campus Job Posting System - Forgot Password
 * Archetype A: Auth Split Card Shell (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$submitted = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $submitted = true;
    }
}

$page_title = 'Reset Your Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Archetype A: Auth Split Card Shell -->
                <div class="auth-shell">
                    
                    <!-- Left Brand Panel -->
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

                            <span class="pill-badge mb-3">
                                <i class="bi bi-key-fill text-accent"></i> Account Recovery
                            </span>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Regain Access to Your Portal
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Lost your password? Enter your registered campus email to receive an instant verification link and restore account access.
                            </p>

                            <!-- Feature List -->
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-accent">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Encrypted Single-Use Reset Token</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-accent">
                                        <i class="bi bi-envelope-check-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Delivered Direct to Institutional Inbox</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-accent">
                                        <i class="bi bi-headset"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Campus IT Helpdesk Support</span>
                                </div>
                            </div>
                        </div>

                        <!-- Helpdesk Info -->
                        <div class="p-3 bg-white rounded-4 border border-line mt-3">
                            <span class="small fw-bold text-ink d-block mb-1">Need Immediate Assistance?</span>
                            <p class="small text-muted-custom mb-0" style="font-size: 11.5px;">
                                Contact Career Services at <code>support@campus-hire.edu</code> or visit Room 201 Admin Building.
                            </p>
                        </div>

                    </div>

                    <!-- Right Form Panel -->
                    <div class="auth-form-panel">
                        
                        <div class="mb-4">
                            <h2 class="card-paper-title fs-4 mb-1">Forgot Password?</h2>
                            <p class="text-muted-custom small mb-0">Enter your registered email address to receive password reset instructions</p>
                        </div>

                        <?php if ($submitted): ?>
                            <div class="alert-paper alert-paper--success mb-4">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-circle-fill text-accent fs-5 mt-1"></i>
                                    <div>
                                        <strong class="d-block mb-1 text-ink">Reset Instructions Dispatched!</strong>
                                        <span class="small text-muted-custom">
                                            If an account is associated with <strong><?= htmlspecialchars($email) ?></strong>, password recovery instructions have been delivered to your inbox.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="forgot-pass.php" method="POST" class="form-paper">
                            
                            <!-- Email Input -->
                            <div class="mb-4">
                                <label class="form-label" for="reset-email">Institutional Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="reset-email" class="form-control" placeholder="username@campus-hire.edu" value="<?= htmlspecialchars($email) ?>" required autofocus>
                                </div>
                                <div class="form-text small text-muted-custom mt-1" style="font-size: 12px;">
                                    We will dispatch a secure password reset link to this address.
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-pill w-100 mb-3">
                                <i class="bi bi-send-fill"></i> SEND PASSWORD RESET LINK
                            </button>
                        </form>

                        <!-- Alternate Links -->
                        <div class="border-top border-line pt-3 text-center">
                            <a href="login.php" class="text-ink fw-bold small text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Return to Sign In
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
