<?php
/**
 * Campus Job Posting System - Forgot Password Page
 * Strict Requirement: Minimalist layout with only email input field.
 */
require_once __DIR__ . '/includes/data-helper.php';

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
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 bg-surface flex-grow-1 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                
                <div class="card border-line shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <div class="stat-icon bg-accent-soft text-ink mx-auto mb-3">
                            <i class="bi bi-key-fill fs-4"></i>
                        </div>
                        <h4 class="fw-bold text-ink mb-1">Forgot Password?</h4>
                        <p class="text-muted-custom small mb-0">
                            Enter your registered email address to receive password reset instructions.
                        </p>
                    </div>

                    <?php if ($submitted): ?>
                        <div class="alert alert-success d-flex align-items-start gap-2 mb-4 p-3 rounded-3">
                            <i class="bi bi-check-circle-fill text-accent fs-5 mt-1"></i>
                            <div>
                                <strong class="d-block mb-1 text-ink">Reset Instructions Dispatched!</strong>
                                <span class="small text-muted-custom">
                                    If an account is associated with <strong><?= htmlspecialchars($email) ?></strong>, password recovery instructions have been delivered to your inbox.
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="forgot-pass.php" method="POST">
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-ink">Institutional Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope text-muted-custom"></i></span>
                                <input type="email" name="email" class="form-control py-2" placeholder="username@campus-hire.edu" value="<?= htmlspecialchars($email) ?>" required autofocus>
                            </div>
                        </div>

                        <button type="submit" class="btn-accent-pill w-100 py-3 mb-3">
                            <i class="bi bi-send"></i> SEND PASSWORD RESET LINK
                        </button>
                    </form>

                    <div class="text-center border-top border-line pt-3">
                        <a href="login.php" class="text-decoration-none small fw-bold text-accent">
                            <i class="bi bi-arrow-left me-1"></i> Return to Sign In
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
