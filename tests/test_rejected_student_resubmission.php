<?php
/**
 * Test Suite: Rejected Student Account Quarantine & Resubmission
 *
 * Covers the "rejected but not fully rejected" regression:
 *  - rejection is stored on both the users supertype row and student_profiles;
 *  - resubmission returns the account to pending_approval in BOTH tables;
 *  - the administrator feedback is cleared and admins are notified;
 *  - a rejected student with no proof on file cannot resubmit nothing.
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/mailer.php';

echo "=======================================================================\n";
echo "  TEST SUITE: Rejected Student Account & Resubmission\n";
echo "=======================================================================\n\n";

function check_true(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "  [FAIL] {$message}\n");
        exit(1);
    }
    echo "  [PASS] {$message}\n";
}

$pdo = get_db_connection();
$emails = ['test_rejected_student@kld.edu.ph', 'test_rejected_noproof@kld.edu.ph'];
foreach ($emails as $email) {
    $pdo->prepare("DELETE FROM `users` WHERE `email` = :email")->execute([':email' => $email]);
}

$pass_hash = password_hash('SecretPassword123!', PASSWORD_BCRYPT);

// --- Primary fixture: rejected student with an existing (bad) proof ---------
$stmt = $pdo->prepare("
    INSERT INTO `users` (`email`, `password`, `role`, `name`, `phone`, `status`, `is_email_verified`, `verification_status`, `created_at`)
    VALUES (:email, :pass, 'student', 'Rejected Student', '09170000001', 'active', 1, 'rejected', NOW())
");
$stmt->execute([':email' => $emails[0], ':pass' => $pass_hash]);
$student_id = (int)$pdo->lastInsertId();

$pdo->prepare("
    INSERT INTO `student_profiles`
        (`user_id`, `student_id`, `department`, `course`, `year_level`, `verification_status`, `rejection_reason`, `registration_proof`, `created_at`)
    VALUES
        (:id, 'KLD-TEST-001', 'ICDI', 'BS Information Systems', '3rd Year', 'rejected', 'The uploaded COR is blurry and the student number is unreadable.', 'uploads/proofs/old_blurry_cor.pdf', NOW())
")->execute([':id' => $student_id]);

$initial = get_user_by_id($student_id);
check_true(($initial['verification_status'] ?? '') === 'rejected', 'Rejected decision is visible to the student');
check_true(!empty($initial['rejection_reason']), 'Rejection feedback is exposed for the review page');

// --- Resubmission ----------------------------------------------------------
$res = resubmit_student_registration($student_id, 'uploads/proofs/new_clear_cor.pdf', 'Uploaded a clearer COR.');

check_true($res['success'] === true, 'Rejected student can resubmit corrected proof');
$fresh = get_user_by_id($student_id);
check_true(($fresh['verification_status'] ?? '') === 'pending_approval', 'Account returns to pending_approval after resubmission');
check_true(empty($fresh['rejection_reason']), 'Administrator feedback is cleared after resubmission');
check_true(($fresh['registration_proof'] ?? '') === 'uploads/proofs/new_clear_cor.pdf', 'New proof document replaces the stale one');

$user_row = $pdo->prepare("SELECT `verification_status` FROM `users` WHERE `id` = :id");
$user_row->execute([':id' => $student_id]);
check_true(($user_row->fetchColumn() ?: '') === 'pending_approval', 'users supertype row is synchronised to pending_approval');

$notif = $pdo->prepare("SELECT * FROM `notifications` WHERE `type` = 'system' AND `title` LIKE '%Student Registration Resubmitted%' AND `user_id` IN (SELECT `id` FROM `users` WHERE `role` = 'admin') ORDER BY `id` DESC LIMIT 1");
$notif->execute();
$notif_row = $notif->fetch();
check_true(!empty($notif_row), 'Administrators are notified of the student resubmission');

// --- Guard: nothing to resubmit and no proof on file -----------------------
$stmt = $pdo->prepare("
    INSERT INTO `users` (`email`, `password`, `role`, `name`, `phone`, `status`, `is_email_verified`, `verification_status`, `created_at`)
    VALUES (:email, :pass, 'student', 'No Proof Student', '09170000002', 'active', 1, 'rejected', NOW())
");
$stmt->execute([':email' => $emails[1], ':pass' => $pass_hash]);
$no_proof_id = (int)$pdo->lastInsertId();
$pdo->prepare("
    INSERT INTO `student_profiles`
        (`user_id`, `student_id`, `department`, `course`, `year_level`, `verification_status`, `rejection_reason`, `registration_proof`, `created_at`)
    VALUES
        (:id, 'KLD-TEST-002', 'ICDI', 'BS Information Systems', '2nd Year', 'rejected', 'No proof was attached during registration.', NULL, NOW())
")->execute([':id' => $no_proof_id]);

$blocked = resubmit_student_registration($no_proof_id, null, '');
check_true($blocked['success'] === false, 'Resubmission without any proof on file is rejected');
$still_rejected = get_user_by_id($no_proof_id);
check_true(($still_rejected['verification_status'] ?? '') === 'rejected', 'Blocked resubmission leaves the account rejected');

// --- Rejection email template ---------------------------------------------
$body = render_email_template('account-rejected-email', [
    'recipient_name' => 'Rejected Student',
    'role'           => 'student',
    'reason'         => 'The uploaded COR is blurry.',
    'status'         => 'rejected',
    'action_url'     => 'http://localhost/Campus-Job-Posting-System/login.php',
]);
check_true(strpos($body, 'The uploaded COR is blurry.') !== false, 'Rejection email renders the administrator feedback');
check_true(strpos($body, 'Registration Not Approved') !== false, 'Rejection email uses the declined-registration headline');

// --- Admin rejection action fully flips the account -----------------------
$flow_email = 'test_rejected_flow@example.com';
$pdo->prepare("DELETE FROM `users` WHERE `email` = :email")->execute([':email' => $flow_email]);
$pdo->prepare("
    INSERT INTO `users` (`email`, `password`, `role`, `name`, `phone`, `status`, `is_email_verified`, `verification_status`, `created_at`)
    VALUES (:email, :pass, 'student', 'Flow Student', '09170000003', 'active', 1, 'verified', NOW())
")->execute([':email' => $flow_email, ':pass' => $pass_hash]);
$flow_id = (int)$pdo->lastInsertId();
$pdo->prepare("
    INSERT INTO `student_profiles`
        (`user_id`, `student_id`, `department`, `course`, `year_level`, `verification_status`, `created_at`)
    VALUES
        (:id, 'KLD-TEST-003', 'ICDI', 'BS Information Systems', '1st Year', 'verified', NOW())
")->execute([':id' => $flow_id]);

$decision = update_user_verification($flow_id, 'rejected', 'The COR is unreadable.');
check_true($decision === true, 'Administrator rejection action succeeds');
$flow_user = get_user_by_id($flow_id);
check_true(($flow_user['verification_status'] ?? '') === 'rejected', 'Rejection is stored on the users supertype row');
check_true(($flow_user['rejection_reason'] ?? '') === 'The COR is unreadable.', 'Rejection reason is exposed to the applicant');

$flow_notif = $pdo->prepare("SELECT * FROM `notifications` WHERE `user_id` = :id AND `title` LIKE '%Registration Not Approved%' ORDER BY `id` DESC LIMIT 1");
$flow_notif->execute([':id' => $flow_id]);
$flow_notif_row = $flow_notif->fetch();
check_true(!empty($flow_notif_row), 'Applicant receives an in-app rejection notification');
check_true(($flow_notif_row['link'] ?? '') === 'pending-approval.php', 'Rejection notification points at the review page, not the locked settings page');

// The live-session guard must also quarantine a rejected account, not just
// block the next fresh login.
$_SESSION['user'] = array_merge($flow_user, ['is_email_verified' => 1, '_last_db_sync' => 0]);
$guard_redirect = null;
SessionGuard::setRedirectHandler(function (string $url, int $status, string $reason) use (&$guard_redirect): void {
    $guard_redirect = ['url' => $url, 'reason' => $reason];
});
$guard_result = SessionGuard::protect(['student']);
SessionGuard::setRedirectHandler(null);

check_true(empty($guard_result), 'Rejected live session is denied protected-page access');
check_true(($guard_redirect['reason'] ?? '') === 'rejected', 'Live session guard redirects with the rejected reason');
check_true(strpos($guard_redirect['url'] ?? '', 'pending-approval.php') !== false, 'Live session guard lands on the review page');
check_true(!isset($_SESSION['user']), 'Rejected live session is de-authenticated');
check_true(($_SESSION['pending_approval_notice']['rejection_reason'] ?? '') === 'The COR is unreadable.', 'Live session guard carries the rejection feedback to the review page');
unset($_SESSION['pending_approval_notice']);

// --- Clean up --------------------------------------------------------------
if (!empty($notif_row['id'])) {
    $pdo->prepare("DELETE FROM `notifications` WHERE `id` = :id")->execute([':id' => $notif_row['id']]);
}
if (!empty($flow_notif_row['id'])) {
    $pdo->prepare("DELETE FROM `notifications` WHERE `id` = :id")->execute([':id' => $flow_notif_row['id']]);
}
foreach ([$student_id, $no_proof_id, $flow_id] as $id) {
    $pdo->prepare("DELETE FROM `student_profiles` WHERE `user_id` = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM `users` WHERE `id` = :id")->execute([':id' => $id]);
}

echo "\n=======================================================================\n";
echo "  RESULT: ALL REJECTED-ACCOUNT TESTS PASSED\n";
echo "=======================================================================\n";
