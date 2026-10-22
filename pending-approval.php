<?php
/**
 * Campus Job Posting System - Pending Admin Approval
 * Shown after email OTP verification when account is awaiting admin review.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

// Allow access if:
//   (a) There is a pending_approval session flag (just finished OTP), OR
//   (b) The logged-in user has verification_status = 'pending_approval'
$pending_approval_session = $_SESSION['pending_approval_notice'] ?? null;
$logged_user = get_logged_user();

if (!$pending_approval_session && !$logged_user) {
    header('Location: login.php');
    exit;
}

if ($logged_user) {
    // Admin should never land here
    if (($logged_user['role'] ?? '') === 'admin') {
        redirect_by_role('admin');
    }
    // If already verified, send to dashboard
    if (($logged_user['verification_status'] ?? '') === 'verified') {
        redirect_by_role($logged_user['role'] ?? null);
    }
}

// Use session data if available, otherwise fall back to logged user
$user_name   = $pending_approval_session['name']  ?? ($logged_user['name']  ?? 'User');
$user_email  = $pending_approval_session['email'] ?? ($logged_user['email'] ?? '');
$user_role   = $pending_approval_session['role']  ?? ($logged_user['role']  ?? 'student');
$role_label  = match($user_role) {
    'employer' => 'Employer / Partner',
    'student'  => 'Student',
    default    => 'User'
};

$page_title = 'Account Pending Approval';
require __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <div class="auth-shell">

                    <!-- Left Brand Panel -->
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

                            <span class="eyebrow-badge text-muted-custom">Account Review</span>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Waiting for Admin Approval
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Your email has been verified successfully. Your <?= htmlspecialchars($role_label) ?> account is currently under review by the institutional administrator. You will be notified once your account is approved.
                            </p>

                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-envelope-check-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Email Verified ✓</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm" style="background: #fff3cd; color: #856404;">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Awaiting Admin Approval</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm" style="background: #e9ecef; color: #6c757d;">
                                        <i class="bi bi-door-open"></i>
                                    </div>
                                    <span class="small text-muted-custom">Portal Access Granted After Approval</span>
                                </div>
                            </div>
                        </div>

                        <div class="otp-helpdesk-card">
                            <span class="small fw-bold text-ink d-block mb-1">Need Help?</span>
                            <p class="small text-muted-custom mb-0" style="font-size: 11.5px;">
                                Contact the Career Services Office at <code>support@kld.edu.ph</code> or visit the Office of Student Affairs to expedite your verification.
                            </p>
                        </div>
                    </div>

                    <!-- Right Content Panel -->
                    <div class="auth-form-panel position-relative">

                        <!-- Pending Notice Card -->
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                 style="width: 72px; height: 72px; background: #fff3cd;">
                                <i class="bi bi-clock-history fs-2" style="color: #856404;"></i>
                            </div>
                            <h2 class="card-paper-title fs-4 mb-1">Registration Submitted!</h2>
                            <p class="text-muted-custom small">
                                Hi <strong><?= htmlspecialchars($user_name) ?></strong>, your account registration has been received.
                            </p>
                        </div>

                        <!-- Status Timeline -->
                        <div class="alert-paper alert-paper--warning mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-info-circle-fill text-warning fs-5 mt-1"></i>
                                <div>
                                    <strong class="d-block text-ink small mb-1">Your account is pending administrator approval</strong>
                                    <span class="small text-muted-custom">
                                        Your credentials and submitted documents are currently being reviewed by the institutional administrator.
                                        This process typically takes <strong>1–2 business days</strong>.
                                        You will receive an email notification at <strong><?= htmlspecialchars($user_email) ?></strong> once your account is approved or if further information is required.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- What happens next -->
                        <div class="card-paper p-3 mb-4" style="border-radius: 12px;">
                            <p class="small fw-bold text-ink mb-3">What happens next?</p>
                            <ol class="list-unstyled d-flex flex-column gap-2 mb-0">
                                <li class="d-flex align-items-center gap-2 small text-muted-custom">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span>Your email address has been verified</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 small text-muted-custom">
                                    <i class="bi bi-hourglass-split" style="color: #856404;"></i>
                                    <span>The administrator will review your registration details and uploaded documents</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 small text-muted-custom">
                                    <i class="bi bi-bell" style="color: #6c757d;"></i>
                                    <span>You'll receive a notification email when your account is approved or if action is needed</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 small text-muted-custom">
                                    <i class="bi bi-door-open" style="color: #6c757d;"></i>
                                    <span>Once approved, you can sign in and access the full campus portal</span>
                                </li>
                            </ol>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column gap-2">
                            <a href="login.php" class="btn-pill w-100 text-center">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Back to Sign In
                            </a>
                            <a href="index.php" class="btn btn-sm btn-link text-muted-custom text-decoration-none text-center">
                                <i class="bi bi-house me-1"></i> Return to Home
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
