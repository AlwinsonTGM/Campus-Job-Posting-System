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

<main class="py-5 bg-light flex-grow-1 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <div class="stat-icon bg-warning text-dark mx-auto mb-3">
                            <i class="bi bi-key-fill fs-4"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Forgot Password?</h4>
                        <p class="text-muted small mb-0">
                            Enter your registered email address to receive password reset instructions.
                        </p>
                    </div>

                    <?php if ($submitted): ?>
                        <div class="alert alert-success d-flex align-items-start gap-2 mb-4 p-3 rounded-3">
                            <i class="bi bi-check-circle-fill text-success fs-5 mt-1"></i>
                            <div>
                                <strong class="d-block mb-1">Reset Instructions Dispatched!</strong>
                                <span class="small">
                                    If an account is associated with <strong><?= htmlspecialchars($email) ?></strong>, password recovery instructions have been delivered to your inbox.
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="forgot-pass.php" method="POST">
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Institutional Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control py-2" placeholder="name@university.edu.ph" value="<?= htmlspecialchars($email) ?>" required autofocus>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-academic w-100 py-2 fw-semibold mb-3">
                            <i class="bi bi-send me-1"></i> Send Password Reset Link
                        </button>
                    </form>

                    <div class="text-center border-top pt-3">
                        <a href="login.php" class="text-decoration-none small fw-semibold text-primary">
                            <i class="bi bi-arrow-left me-1"></i> Return to Sign In
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
