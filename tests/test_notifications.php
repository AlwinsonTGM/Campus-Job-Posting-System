<?php
/**
 * Test script for Notification System & Lifecycle Verification
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

echo "=== STARTING NOTIFICATION SYSTEM TEST ===\n";

$pdo = get_db_connection();

// 1. Direct Notification CRUD Test
echo "\n--- 1. Direct Notification CRUD ---\n";
// Find student user and employer user
$student = $pdo->query("SELECT * FROM users WHERE role = 'student' LIMIT 1")->fetch();
$employer = $pdo->query("SELECT u.*, ep.verification_status FROM users u LEFT JOIN employer_profiles ep ON ep.user_id = u.id WHERE u.role = 'employer' LIMIT 1")->fetch();
$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();

if (!$student || !$employer || !$admin) {
    die("Error: Missing test users in database.\n");
}

echo "Found Student: {$student['name']} (ID: {$student['id']})\n";
echo "Found Employer: {$employer['name']} (ID: {$employer['id']})\n";
echo "Found Admin: {$admin['name']} (ID: {$admin['id']})\n";

$initial_unread = get_unread_notifications_count($student['id']);
echo "Initial Student Unread Count: {$initial_unread}\n";

$test_notif_id = create_notification(
    $student['id'],
    'system_test',
    'Welcome to Notification System',
    'This is a test notification to verify delivery and unread counting.',
    'student/dashboard.php',
    'bi-info-circle',
    'info'
);
echo "Created notification ID: {$test_notif_id}\n";
assert($test_notif_id > 0, "Notification ID should be > 0");

$new_unread = get_unread_notifications_count($student['id']);
echo "Unread Count after insert: {$new_unread}\n";
assert($new_unread === $initial_unread + 1, "Unread count should have increased by 1");

$recent = get_user_notifications($student['id'], 5);
echo "Fetched recent notifications count: " . count($recent) . "\n";
assert(count($recent) > 0, "Should return at least 1 notification");
assert($recent[0]['id'] === $test_notif_id, "Latest notification should match test ID");
assert($recent[0]['is_read'] === false, "Latest notification should be unread");

// Mark as read
$read_success = mark_notification_as_read($test_notif_id, $student['id']);
assert($read_success === true, "Mark as read should succeed");
$after_read = get_unread_notifications_count($student['id']);
echo "Unread Count after mark as read: {$after_read}\n";
assert($after_read === $initial_unread, "Unread count should return to initial");

// Delete test notification
delete_notification($test_notif_id, $student['id']);
echo "Cleaned up test notification.\n";

// 2. Lifecycle Trigger: Employer Verification
echo "\n--- 2. Lifecycle Trigger: User Verification Status Change ---\n";
$emp_id = $employer['id'];
$orig_ver = $employer['verification_status'];

// Simulate admin approving employer
$res_ver = update_user_verification($emp_id, 'verified');
assert($res_ver === true, "update_user_verification should return true");

$emp_notifs = get_user_notifications($emp_id, 3);
echo "Employer latest notification title: '{$emp_notifs[0]['title']}'\n";
assert(str_contains($emp_notifs[0]['title'], 'Verified'), "Employer should receive verified notification");
echo "Employer latest notification message: '{$emp_notifs[0]['message']}'\n";

// Clean up employer verification notification
delete_notification($emp_notifs[0]['id'], $emp_id);
// Restore original verification status
$pdo->prepare("UPDATE employer_profiles SET verification_status = :st WHERE user_id = :id")->execute([':st' => $orig_ver, ':id' => $emp_id]);

// 3. Lifecycle Trigger: Application Status Change
echo "\n--- 3. Lifecycle Trigger: Application Status Update ---\n";
// Find an existing application or create a dummy one
$app = $pdo->query("SELECT * FROM applications LIMIT 1")->fetch();
if ($app) {
    $target_student_id = (int)$app['student_id'];

    // Update status to interview_scheduled
    $upd_res = update_application_status(
        $app['id'],
        'interview_scheduled',
        'Please bring your official certificate of registration.',
        [
            'date' => date('Y-m-d', strtotime('+3 days')),
            'time' => '02:00 PM',
            'venue' => 'Library Office 302'
        ]
    );
    assert($upd_res === true, "update_application_status should succeed");

    $stud_notifs = get_user_notifications($target_student_id, 3);
    echo "Student latest notification title: '{$stud_notifs[0]['title']}'\n";
    assert(str_contains($stud_notifs[0]['title'], 'Interview Scheduled'), "Student should receive Interview Scheduled notification");
    echo "Student latest notification message: '{$stud_notifs[0]['message']}'\n";
    assert(str_contains($stud_notifs[0]['message'], 'Library Office 302'), "Message should contain interview venue");

    // Clean up test notification and restore app status
    delete_notification($stud_notifs[0]['id'], $target_student_id);
    $pdo->prepare("UPDATE applications SET status = :st WHERE id = :id")
        ->execute([':st' => $app['status'], ':id' => $app['id']]);
}

// 4. Admin broadcast notification test
echo "\n--- 4. Admin Broadcast Notification ---\n";
$admin_notifs_sent = notify_all_admins('test_admin', 'Admin Test Broadcast', 'This is a test notification for all admins.', 'admin/users.php');
echo "Sent notifications to {$admin_notifs_sent} admins.\n";
assert($admin_notifs_sent >= 1, "Should send to at least 1 admin");

$admin_notifs = get_user_notifications($admin['id'], 3);
echo "Admin latest notification title: '{$admin_notifs[0]['title']}'\n";
assert($admin_notifs[0]['title'] === 'Admin Test Broadcast', "Admin notification should match title");

// Clean up
delete_notification($admin_notifs[0]['id'], $admin['id']);

// 5. API Endpoint Testing
echo "\n--- 5. API Endpoint Logic Test ---\n";
// Test unread count & json format
$_SESSION['user'] = $student;
$n_id = create_notification($student['id'], 'api_test', 'API Check', 'Checking JSON endpoint', 'student/dashboard.php');

// Simulate GET
$api_unread = get_unread_notifications_count($student['id']);
$api_notifs = get_user_notifications($student['id'], 5);
assert($api_unread >= 1, "API unread should be >= 1");
assert(count($api_notifs) >= 1, "API notifs should have at least 1 item");
echo "API Data query verified: unread={$api_unread}, items=" . count($api_notifs) . "\n";

// Test mark_all_read
mark_all_notifications_as_read($student['id']);
$after_all_read = get_unread_notifications_count($student['id']);
assert($after_all_read === 0, "All notifications should be marked as read");
echo "Mark all read verified: unread={$after_all_read}\n";

// Clean up
delete_notification($n_id, $student['id']);

echo "\n=== ALL NOTIFICATION SYSTEM TESTS PASSED SUCCESSFULLY! ===\n";
