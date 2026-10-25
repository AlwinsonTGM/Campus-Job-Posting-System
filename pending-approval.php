<?php
/**
 * Campus Job Posting System - Account Review (Pending / Rejected / Revoked)
 *
 * This is the quarantine destination for every non-verified applicant:
 *   - pending_approval : registration is in the administrator's review queue.
 *   - rejected         : the administrator declined it and left feedback; the
 *                        applicant may correct and resubmit documents here.
 *   - revoked          : verification was withdrawn; no self-service path,
 *                        the applicant must contact the Career Services Office.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$pending_approval_session = $_SESSION['pending_approval_notice'] ?? null;
$logged_user = get_logged_user();

if (!$pending_approval_session && !$logged_user) {
    header('Location: login.php');
    exit;
}

// Resolve the account from the database so the decision shown here is always
// the current one (the administrator may have acted after the session started).
$account_id = (int)($pending_approval_session['user_id'] ?? ($logged_user['id'] ?? 0));
$account = $account_id > 0 ? get_user_by_id($account_id) : null;

if (!$account) {
    unset($_SESSION['pending_approval_notice']);
    set_flash('danger', 'We could not find that account. Please sign in again.');
    header('Location: login.php');
    exit;
}

$user_role = $account['role'] ?? ($pending_approval_session['role'] ?? 'student');

if ($user_role === 'admin') {
    redirect_by_role('admin');
}

if (strtolower((string)($account['status'] ?? 'active')) === 'suspended' || !empty($account['is_archived'])) {
    SessionGuard::logout();
    set_flash('danger', 'Account Suspended: Your account has been deactivated by institutional administration.');
    header('Location: login.php');
    exit;
}

$ver_status = (string)($account['verification_status'] ?? 'pending_approval');

// Already approved: there is nothing to review.
if ($ver_status === 'verified') {
    if ($logged_user) {
        redirect_by_role($user_role);
    }
    unset($_SESSION['pending_approval_notice']);
    set_flash('success', 'Your account is already verified. Please sign in to continue.');
    header('Location: login.php');
    exit;
}

// A non-verified account must never keep a fully authenticated portal session.
// The decision details live in the quarantine notice instead.
if ($logged_user) {
    unset($_SESSION['user']);
}

$notice = [
    'user_id'             => (int)($account['id'] ?? $account_id),
    'name'                => $account['name'] ?? ($pending_approval_session['name'] ?? 'User'),
    'email'               => $account['email'] ?? ($pending_approval_session['email'] ?? ''),
    'role'                => $user_role,
    'verification_status' => $ver_status,
    'rejection_reason'    => (string)($account['rejection_reason'] ?? ($pending_approval_session['rejection_reason'] ?? '')),
];
$_SESSION['pending_approval_notice'] = $notice;

$is_pending  = ($ver_status === 'pending_approval');
$is_rejected = ($ver_status === 'rejected');
$is_revoked  = ($ver_status === 'revoked');

$user_name  = $notice['name'];
$user_email = $notice['email'];
$rejection_reason = trim($notice['rejection_reason']);
$role_label = match ($user_role) {
    'employer' => 'Employer / Partner',
    'student'  => 'Student',
    default    => 'User'
};

$existing_proof  = (string)($account['registration_proof'] ?? '');
$existing_permit = (string)($account['business_permit'] ?? '');

// ---------------------------------------------------------------------------
// Rejected applicants can submit corrected documents without re-registering.
// ---------------------------------------------------------------------------
$resubmit_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_rejected) {
        set_flash('danger', 'Only a rejected registration can be resubmitted for review.');
        header('Location: pending-approval.php');
        exit;
    }

    if (!verify_csrf_token((string)($_POST['csrf_token'] ?? ''))) {
        $resubmit_error = 'Security validation failed: your session token expired. Please refresh the page and try again.';
    } else {
        $note = trim((string)($_POST['note'] ?? $_POST['reason'] ?? ''));

        if ($user_role === 'student') {
            $proof_path = null;
            $proofRes = AttachmentStore::storeProof($_FILES['proof_file'] ?? null);
            if ($proofRes->isOk()) {
                $proof_path = $proofRes->path();
            } elseif ($proofRes->errorCode() !== 'no_file') {
                $resubmit_error = $proofRes->errorMessage();
            }

            if ($resubmit_error === null) {
                $res = resubmit_student_registration((int)$account['id'], $proof_path, $note);
                if (!$res['success']) {
                    $resubmit_error = $res['message'];
                }
            }
        } else {
            $org_name = trim((string)($_POST['organization_name'] ?? ''));
            $rep_name = trim((string)($_POST['name'] ?? ''));

            if ($org_name === '' || $rep_name === '') {
                $resubmit_error = 'Organization name and authorized representative name are required.';
            }

            $permit_path = null;
            if ($resubmit_error === null) {
                $permitRes = AttachmentStore::storePermit($_FILES['permit_file'] ?? null);
                if ($permitRes->isOk()) {
                    $permit_path = $permitRes->path();
                } elseif ($permitRes->errorCode() !== 'no_file') {
                    $resubmit_error = $permitRes->errorMessage();
                }
            }

            if ($resubmit_error === null) {
                $res = resubmit_employer_accreditation((int)$account['id'], [
                    'organization_name' => $org_name,
                    'name'              => $rep_name,
                ], $permit_path, $note);

                if ($res['success']) {
                    $_SESSION['pending_approval_notice']['name'] = $rep_name;
                } else {
                    $resubmit_error = $res['message'];
                }
            }
        }

        if ($resubmit_error === null && isset($res) && $res['success']) {
            $_SESSION['pending_approval_notice']['verification_status'] = 'pending_approval';
            $_SESSION['pending_approval_notice']['rejection_reason'] = '';
            set_flash('success', $res['message'] . ' Your account is back in the administrative review queue.');
            header('Location: pending-approval.php');
            exit;
        }
    }
}

$page_title = $is_rejected
    ? 'Registration Not Approved'
    : ($is_revoked ? 'Verification Revoked' : 'Account Pending Approval');
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

                            <?php if ($is_rejected): ?>
                                <h2 class="h3 fw-bold text-ink mb-3">
                                    Registration Not Approved
                                </h2>
                                <p class="text-muted-custom small mb-4">
                                    The institutional administrator reviewed your <?= htmlspecialchars($role_label) ?> registration and requested revisions before portal access can be granted.
                                </p>
                            <?php elseif ($is_revoked): ?>
                                <h2 class="h3 fw-bold text-ink mb-3">
                                    Verification Revoked
                                </h2>
                                <p class="text-muted-custom small mb-4">
                                    Your <?= htmlspecialchars($role_label) ?> account verification has been withdrawn by the institutional administrator. Portal access is currently restricted.
                                </p>
                            <?php else: ?>
                                <h2 class="h3 fw-bold text-ink mb-3">
                                    Waiting for Admin Approval
                                </h2>
                                <p class="text-muted-custom small mb-4">
                                    Your email has been verified successfully. Your <?= htmlspecialchars($role_label) ?> account is currently under review by the institutional administrator. You will be notified once your account is approved.
                                </p>
                            <?php endif; ?>

                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-envelope-check-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Email Verified ✓</span>
                                </div>
                                <?php if ($is_pending): ?>
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
                                <?php elseif ($is_rejected): ?>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-circle icon-circle-sm icon-circle-danger">
                                            <i class="bi bi-x-octagon-fill"></i>
                                        </div>
                                        <span class="small fw-semibold text-ink">Review Completed — Revision Required</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-circle icon-circle-sm" style="background: #e9ecef; color: #6c757d;">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </div>
                                        <span class="small text-muted-custom">Resubmit Corrected Documents to Re-enter the Queue</span>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-circle icon-circle-sm icon-circle-danger">
                                            <i class="bi bi-shield-x"></i>
                                        </div>
                                        <span class="small fw-semibold text-ink">Verification Withdrawn</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-circle icon-circle-sm" style="background: #e9ecef; color: #6c757d;">
                                            <i class="bi bi-lock-fill"></i>
                                        </div>
                                        <span class="small text-muted-custom">Portal Access Restricted</span>
                                    </div>
                                <?php endif; ?>
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

                        <?php if ($is_pending): ?>

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

                        <?php elseif ($is_revoked): ?>

                            <!-- Revoked Notice Card -->
                            <div class="text-center mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                     style="width: 72px; height: 72px; background: #fee2e2;">
                                    <i class="bi bi-shield-x fs-2" style="color: #b91c1c;"></i>
                                </div>
                                <h2 class="card-paper-title fs-4 mb-1">Verification Revoked</h2>
                                <p class="text-muted-custom small">
                                    Hi <strong><?= htmlspecialchars($user_name) ?></strong>, your account verification is no longer active.
                                </p>
                            </div>

                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5 mt-1"></i>
                                    <div>
                                        <strong class="d-block text-ink small mb-1">Portal access has been restricted</strong>
                                        <span class="small text-muted-custom">
                                            University administration has withdrawn the verification on <strong><?= htmlspecialchars($user_email) ?></strong>.
                                            Unlike a rejection, a revoked verification cannot be resubmitted through this page.
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($rejection_reason !== ''): ?>
                                <div class="card-paper p-3 mb-4" style="border-radius: 12px;">
                                    <p class="small fw-bold text-ink mb-2">
                                        <i class="bi bi-chat-left-quote me-1 text-danger"></i>Administrator Feedback
                                    </p>
                                    <p class="small text-muted-custom mb-0"><?= nl2br(htmlspecialchars($rejection_reason)) ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="alert-paper mb-4" style="background: #f8fafc;">
                                <div class="small text-muted-custom">
                                    <strong class="text-ink d-block mb-1">What should I do?</strong>
                                    Please contact the Career Services Office at <code>support@kld.edu.ph</code> or visit the Office of Student Affairs so an administrator can review your account.
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <a href="login.php" class="btn-pill w-100 text-center">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Back to Sign In
                                </a>
                                <a href="index.php" class="btn btn-sm btn-link text-muted-custom text-decoration-none text-center">
                                    <i class="bi bi-house me-1"></i> Return to Home
                                </a>
                            </div>

                        <?php else: ?>

                            <!-- Rejected Notice Card -->
                            <div class="text-center mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                                     style="width: 72px; height: 72px; background: #fee2e2;">
                                    <i class="bi bi-exclamation-octagon-fill fs-2" style="color: #b91c1c;"></i>
                                </div>
                                <h2 class="card-paper-title fs-4 mb-1">Registration Not Approved</h2>
                                <p class="text-muted-custom small">
                                    Hi <strong><?= htmlspecialchars($user_name) ?></strong>, the administrator needs a few corrections before your account can be activated.
                                </p>
                            </div>

                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5 mt-1"></i>
                                    <div>
                                        <strong class="d-block text-ink small mb-1">Action required: your registration was declined</strong>
                                        <span class="small text-muted-custom">
                                            Your portal access remains restricted until the requested revisions are reviewed and approved.
                                            Sign-in is allowed only so you can read this decision and resubmit your documents.
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($rejection_reason !== ''): ?>
                                <div class="card-paper p-3 mb-4" style="border-radius: 12px;">
                                    <p class="small fw-bold text-ink mb-2">
                                        <i class="bi bi-chat-left-quote me-1 text-danger"></i>Administrator Feedback
                                    </p>
                                    <p class="small text-muted-custom mb-0"><?= nl2br(htmlspecialchars($rejection_reason)) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($resubmit_error !== null): ?>
                                <div class="alert-paper alert-paper--danger mb-4" role="alert">
                                    <div class="small text-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($resubmit_error) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Resubmission Form -->
                            <div class="card-paper p-3 mb-4" style="border-radius: 12px;">
                                <p class="small fw-bold text-ink mb-1">
                                    <i class="bi bi-arrow-repeat text-accent me-1"></i>Resubmit for Verification
                                </p>
                                <p class="small text-muted-custom mb-3" style="font-size: 11.5px;">
                                    <?php if ($user_role === 'employer'): ?>
                                        Update your organization credentials if needed and attach a valid Business Permit / Mayor's Permit or BIR Form 2303.
                                    <?php else: ?>
                                        Attach a clear copy of your Certificate of Registration (COR) or Student ID. Make sure the document is readable and matches your registered details.
                                    <?php endif; ?>
                                </p>

                                <form action="pending-approval.php" method="POST" enctype="multipart/form-data" class="text-start">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="resubmit">

                                    <?php if ($user_role === 'employer'): ?>
                                        <div class="mb-3">
                                            <label class="form-label small mb-1" for="pa-org">Organization / Office Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" id="pa-org" name="organization_name"
                                                   value="<?= htmlspecialchars($account['organization_name'] ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small mb-1" for="pa-rep">Authorized Representative Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" id="pa-rep" name="name"
                                                   value="<?= htmlspecialchars($account['name'] ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small mb-1" for="pa-permit">
                                                Business Permit / Mayor's Permit / BIR 2303
                                                <?= $existing_permit === '' ? '<span class="text-danger">*</span>' : '<span class="text-muted-custom fw-normal">(Optional if unchanged)</span>' ?>
                                            </label>
                                            <input type="file" class="form-control form-control-sm" id="pa-permit" name="permit_file"
                                                   accept="image/*,application/pdf" <?= $existing_permit === '' ? 'required' : '' ?>>
                                            <span class="small text-muted-custom mt-1 d-block" style="font-size: 10.5px;">PDF, JPG, or PNG — maximum 5MB.</span>
                                            <?php if ($existing_permit !== ''): ?>
                                                <a href="<?= htmlspecialchars($existing_permit) ?>" target="_blank" rel="noopener noreferrer" class="small text-accent d-inline-block mt-1">
                                                    <i class="bi bi-file-earmark-text me-1"></i>View document on file
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label class="form-label small mb-1" for="pa-proof">
                                                Updated COR / Student ID
                                                <?= $existing_proof === '' ? '<span class="text-danger">*</span>' : '<span class="text-muted-custom fw-normal">(Optional if unchanged)</span>' ?>
                                            </label>
                                            <input type="file" class="form-control form-control-sm" id="pa-proof" name="proof_file"
                                                   accept="image/*,application/pdf" <?= $existing_proof === '' ? 'required' : '' ?>>
                                            <span class="small text-muted-custom mt-1 d-block" style="font-size: 10.5px;">PDF, JPG, or PNG — maximum 5MB.</span>
                                            <?php if ($existing_proof !== ''): ?>
                                                <a href="<?= htmlspecialchars($existing_proof) ?>" target="_blank" rel="noopener noreferrer" class="small text-accent d-inline-block mt-1">
                                                    <i class="bi bi-file-earmark-text me-1"></i>View document on file
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label class="form-label small mb-1" for="pa-note">What did you change?</label>
                                        <textarea class="form-control form-control-sm" id="pa-note" name="note" rows="3"
                                                  placeholder="<?= $user_role === 'employer' ? 'e.g. Attached the requested Mayor\'s Permit and BIR 2303 certificate.' : 'e.g. Uploaded a clearer copy of my current Certificate of Registration.' ?>"></textarea>
                                        <span class="small text-muted-custom mt-1 d-block" style="font-size: 10.5px;">Optional note for the reviewing administrator.</span>
                                    </div>

                                    <button type="submit" class="btn-pill w-100">
                                        <i class="bi bi-send-fill me-1"></i> Submit for Review
                                    </button>
                                </form>

                                <div class="p-2 px-3 bg-cream rounded-3 border border-line small text-muted-custom mt-3" style="font-size: 11px;">
                                    <i class="bi bi-shield-lock-fill text-accent me-1"></i>
                                    Submitting places your account back into the administrative verification queue. You will be notified by email once it is reviewed.
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <a href="login.php" class="btn btn-sm btn-link text-muted-custom text-decoration-none text-center">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Back to Sign In
                                </a>
                                <a href="index.php" class="btn btn-sm btn-link text-muted-custom text-decoration-none text-center">
                                    <i class="bi bi-house me-1"></i> Return to Home
                                </a>
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
