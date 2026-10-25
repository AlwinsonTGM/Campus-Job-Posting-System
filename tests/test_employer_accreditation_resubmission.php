<?php
/**
 * Test Suite: Employer Accreditation Locking & Resubmission Workflow
 */
require_once __DIR__ . '/../includes/data-helper.php';

echo "=======================================================================\n";
echo "  TEST SUITE: Employer Accreditation Locking & Resubmission\n";
echo "=======================================================================\n\n";

$pdo = get_db_connection();

// 1. Setup a test employer
$test_email = 'test_accred_employer@partner.kld.edu.ph';
$pdo->prepare("DELETE FROM users WHERE email = :email")->execute([':email' => $test_email]);

$pass_hash = password_hash('SecretPassword123!', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (email, password, role, name, phone, status, is_email_verified, verification_status, created_at) VALUES (:email, :pass, 'employer', 'Original Representative', '09171234567', 'active', 1, 'rejected', NOW())");
$stmt->execute([':email' => $test_email, ':pass' => $pass_hash]);
$emp_id = (int)$pdo->lastInsertId();

$stmt_ep = $pdo->prepare("INSERT INTO employer_profiles (user_id, employer_type, organization_name, office_location, contact_person, accreditation_number, verification_status, rejection_reason, business_permit, created_at) VALUES (:id, 'approved_partner', 'Original Tech Corp', 'Building A, Room 101', 'Original Representative', 'ACC-2026-001', 'rejected', 'Missing City Business Permit / Mayor\'s Permit and BIR Form 2303 registration certificate.', 'uploads/permits/old_permit.pdf', NOW())");
$stmt_ep->execute([':id' => $emp_id]);

// Test 1: Verify initial rejected state
$emp = get_user_by_id($emp_id);
assert($emp['verification_status'] === 'rejected', 'Employer verification status should initially be rejected');
assert($emp['organization_name'] === 'Original Tech Corp', 'Initial organization name should match');
assert(!empty($emp['rejection_reason']), 'Initial rejection reason should be present');
echo "  [PASS] Test 1: Initial employer profile is in 'rejected' state with admin feedback.\n";

// Test 2: Standard profile update does NOT tamper with locked Representative Name
$update_success = update_user_profile($emp_id, 'employer', [
    'phone' => '+63 920 888 6655',
    'name' => 'Hacked Tampered Name',
    'office_location' => 'Silicon Valley Cavite Hub, Suite 402'
]);
assert($update_success === true, 'update_user_profile should succeed');
$emp_after_profile = get_user_by_id($emp_id);
assert($emp_after_profile['name'] === 'Original Representative', 'Representative Name must remain LOCKED against standard profile edits');
assert($emp_after_profile['phone'] === '+63 920 888 6655', 'Phone should be updated in standard profile');
assert($emp_after_profile['office_location'] === 'Silicon Valley Cavite Hub, Suite 402', 'Workplace location should be updated');
echo "  [PASS] Test 2: Standard profile update protects locked Representative Name from tampering.\n";

// Test 3: Resubmit employer accreditation with updated credentials and new permit
$resubmit_res = resubmit_employer_accreditation($emp_id, [
    'organization_name' => 'CodeCrafted Interactive Labs',
    'name' => 'Michelle Anne Soriano'
], 'uploads/permits/permit_2026_new.pdf', 'Submitting updated City Business Permit and BIR 2303 certificate.');

assert($resubmit_res['success'] === true, 'resubmit_employer_accreditation should succeed');
$emp_after_resubmit = get_user_by_id($emp_id);
assert($emp_after_resubmit['name'] === 'Michelle Anne Soriano', 'Representative Name should be updated via accreditation resubmission');
assert($emp_after_resubmit['organization_name'] === 'CodeCrafted Interactive Labs', 'Organization Name should be updated');
assert($emp_after_resubmit['business_permit'] === 'uploads/permits/permit_2026_new.pdf', 'Business permit path should be updated');
assert($emp_after_resubmit['verification_status'] === 'pending_approval', 'Verification status must transition to pending_approval');
assert($emp_after_resubmit['rejection_reason'] === null, 'Rejection reason must be cleared upon resubmission');
echo "  [PASS] Test 3: Accreditation resubmission atomically updates locked credentials, new permit, and resets verification to pending_approval.\n";

// Test 4: Verify Admin notification was generated
$stmt_notif = $pdo->prepare("SELECT * FROM notifications WHERE type = 'system' AND title LIKE '%Employer Accreditation Resubmitted%' ORDER BY id DESC LIMIT 1");
$stmt_notif->execute();
$notif = $stmt_notif->fetch();
assert(!empty($notif), 'Admin notification for accreditation resubmission should be created');
assert(strpos($notif['message'], 'CodeCrafted Interactive Labs') !== false, 'Admin notification should reference the employer organization name');
echo "  [PASS] Test 4: Administrator notification created with link to pending verifications.\n";

// Clean up test data
$pdo->prepare("DELETE FROM notifications WHERE id = :id")->execute([':id' => $notif['id']]);
$pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $emp_id]);

echo "\n=======================================================================\n";
echo "  RESULT: ALL 4 TESTS PASSED (100% SUCCESS)\n";
echo "=======================================================================\n";
