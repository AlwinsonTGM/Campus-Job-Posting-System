<?php
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = get_db_connection();
$pass = 0;
$fail = 0;

function assert_test(bool $cond, string $msg): void {
    global $pass, $fail;
    if ($cond) {
        echo "  [PASS] $msg\n";
        $pass++;
    } else {
        echo "  [FAIL] $msg\n";
        $fail++;
    }
}

echo "=== TESTING USER ARCHIVE & REVOKED IDENTIFIERS ===\n";

// 1. Column existence in users table
$cols = array_column($pdo->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_ASSOC), 'Field');
assert_test(in_array('is_archived', $cols, true), 'users table has is_archived column');
assert_test(in_array('archived_at', $cols, true), 'users table has archived_at column');
assert_test(in_array('is_revoked', $cols, true), 'users table has is_revoked column');
assert_test(in_array('revoked_at', $cols, true), 'users table has revoked_at column');
assert_test(in_array('revocation_reason', $cols, true), 'users table has revocation_reason column');
assert_test(in_array('verification_status', $cols, true), 'users table has verification_status column');

// 2. Kimberly's record
$kimberly = get_user_by_email('kimberly.ramos@kld.edu.ph');
assert_test(!empty($kimberly), 'Kimberly Ramos user record retrieved');
assert_test((int)$kimberly['is_revoked'] === 1, 'Kimberly is_revoked === 1');
assert_test($kimberly['verification_status'] === 'revoked', 'Kimberly verification_status === revoked');
assert_test(!empty($kimberly['revoked_at']), 'Kimberly revoked_at is populated');
assert_test(str_contains($kimberly['revocation_reason'] ?? '', 'revoked'), 'Kimberly revocation_reason is populated');
assert_test((int)$kimberly['is_archived'] === 0, 'Kimberly is_archived === 0');

// 3. Suspend and Reactivate cycle on a test user
// Create temporary test user
$testEmail = 'archive_test_' . time() . '@kld.edu.ph';
$pdo->exec("INSERT INTO `users` (`email`, `password`, `role`, `name`, `status`, `created_at`) VALUES ('$testEmail', 'hash', 'student', 'Archive Test', 'active', NOW())");
$testId = (int)$pdo->lastInsertId();
$pdo->exec("INSERT INTO `student_profiles` (`user_id`, `student_id`, `department`, `course`, `year_level`, `verification_status`) VALUES ($testId, 'TEST-ARCH-01', 'ICDI', 'BSIS', '1st Year', 'verified')");

// Test suspend (archive)
$suspendRes = suspend_user($testId);
assert_test($suspendRes === true, 'suspend_user executed successfully');
$afterSuspend = get_user_by_id($testId);
assert_test($afterSuspend['status'] === 'suspended', 'user status is suspended');
assert_test((int)$afterSuspend['is_archived'] === 1, 'user is_archived is 1');
assert_test(!empty($afterSuspend['archived_at']), 'user archived_at is populated');

// Test reactivate (unarchive)
$reactivateRes = reactivate_user($testId);
assert_test($reactivateRes === true, 'reactivate_user executed successfully');
$afterReactivate = get_user_by_id($testId);
assert_test($afterReactivate['status'] === 'active', 'user status is active');
assert_test((int)$afterReactivate['is_archived'] === 0, 'user is_archived is 0');
assert_test(empty($afterReactivate['archived_at']), 'user archived_at is null');

// 4. Revocation and Re-approval cycle
$revokeRes = update_user_verification($testId, 'revoked', 'Revoked for testing');
assert_test($revokeRes === true, 'update_user_verification to revoked succeeds');
$afterRevoke = get_user_by_id($testId);
assert_test((int)$afterRevoke['is_revoked'] === 1, 'user is_revoked is 1 in users table');
assert_test($afterRevoke['verification_status'] === 'revoked', 'user verification_status is revoked in users table');
assert_test(!empty($afterRevoke['revoked_at']), 'user revoked_at is populated in users table');
assert_test($afterRevoke['revocation_reason'] === 'Revoked for testing', 'user revocation_reason matches');

// Test re-approval
$approveRes = update_user_verification($testId, 'verified');
assert_test($approveRes === true, 'update_user_verification to verified succeeds');
$afterApprove = get_user_by_id($testId);
assert_test((int)$afterApprove['is_revoked'] === 0, 'user is_revoked is reset to 0 in users table');
assert_test($afterApprove['verification_status'] === 'verified', 'user verification_status is reset to verified in users table');
assert_test(empty($afterApprove['revoked_at']), 'user revoked_at is reset to null');
assert_test(empty($afterApprove['revocation_reason']), 'user revocation_reason is reset to null');

// Clean up temporary test user
$pdo->exec("DELETE FROM `users` WHERE `id` = $testId");

// 5. Test get_all_users filtering
$revokedList = get_all_users(null, null, null, 'revoked');
assert_test(count($revokedList) >= 1, 'get_all_users with ver_status=revoked returns at least 1 user (Kimberly)');
$foundKimberly = false;
foreach ($revokedList as $ru) {
    if ((int)$ru['id'] === 21) {
        $foundKimberly = true;
        break;
    }
}
assert_test($foundKimberly, 'Kimberly Ramos found in get_all_users(ver_status=revoked)');

echo "=== SUMMARY: $pass Passed, $fail Failed ===\n";
exit($fail === 0 ? 0 : 1);
