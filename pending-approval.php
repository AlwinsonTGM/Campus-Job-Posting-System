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
require __DIR__ . '/includes/templates/pending-approval-view.php';
