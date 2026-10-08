<?php
/**
 * Comprehensive Multi-Role Verification for Notification System
 * Validates Student, Employer, and Admin role workflows and UI rendering.
 * Helpers extracted to tests/helpers/notification-helpers.php
 */
require_once __DIR__ . '/helpers/notification-helpers.php';
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

echo "========================================================\n";
echo "  COMPREHENSIVE ALL-ROLES NOTIFICATIONS VERIFICATION\n";
echo "========================================================\n\n";

$pdo = get_db_connection();

// 1. Fetch one test user for each of the 3 roles
$users = fetch_role_users($pdo);
$student = $users['student'];
$employer = $users['employer'];
$admin = $users['admin'];

if (!$student || !$employer || !$admin) {
    die("FAILED: Test users missing from database.\n");
}

echo "Role 1: STUDENT  -> {$student['name']} ({$student['email']})\n";
echo "Role 2: EMPLOYER -> {$employer['name']} ({$employer['email']})\n";
echo "Role 3: ADMIN    -> {$admin['name']} ({$admin['email']})\n\n";

// -------------------------------------------------------------------------
// TEST 1: Verify UI Rendering & Theme for All 3 Roles on notifications.php
// -------------------------------------------------------------------------
echo "--- TEST 1: UI Rendering & Theme Verification Across Roles ---\n";

foreach ([$student, $employer, $admin] as $u) {
    $role = strtoupper($u['role']);
    $html = render_page_for_user($u, 'all');

    // Check that the old eyebrow breadcrumb is completely removed
    assert(!str_contains($html, 'Communications ·'), "FAILED ($role): 'Communications ·' must be removed from page");
    assert(!str_contains($html, 'Student Portal') || $u['role'] !== 'student', "FAILED ($role): 'Communications · Student Portal' must be removed");
    assert(!str_contains($html, 'Employer Portal'), "FAILED ($role): 'Communications · Employer Portal' must be removed");
    assert(!str_contains($html, 'Admin Portal'), "FAILED ($role): 'Communications · Admin Portal' must be removed");

    // Check that the theme chip selector is used instead of default nav-pills
    assert(str_contains($html, 'chip chip-selectable'), "FAILED ($role): chip chip-selectable must be used for tabs");
    assert(!str_contains($html, 'paper-nav-pills'), "FAILED ($role): unstyled paper-nav-pills should be replaced with chips");

    // Check that title and header are intact
    assert(str_contains($html, 'Notification Center'), "FAILED ($role): Page title must be present");
    assert(str_contains($html, 'navNotificationBadge'), "FAILED ($role): Notification badge element must exist");

    echo "  [PASS] {$role} notification page renders with clean typography & theme chips.\n";
}

// -------------------------------------------------------------------------
// TEST 2: Role Lifecycle - Student to Employer (Application Submission)
// -------------------------------------------------------------------------
echo "\n--- TEST 2: Lifecycle Flow: Student Applies -> Employer Alerted ---\n";

// Find an active job owned by employer
$job_stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = :eid AND status = 'active' LIMIT 1");
$job_stmt->execute([':eid' => $employer['id']]);
$job = $job_stmt->fetch();

if (!$job) {
    // If employer has no job, pick any active job and temporarily set employer_id
    $job = $pdo->query("SELECT * FROM jobs WHERE status = 'active' LIMIT 1")->fetch();
    $pdo->prepare("UPDATE jobs SET employer_id = :eid WHERE id = :jid")->execute([':eid' => $employer['id'], ':jid' => $job['id']]);
}

$emp_initial_unread = get_unread_notifications_count($employer['id']);

// Simulate student application
$_SESSION['user'] = $student;
$dummy_phone = '+63 917 555 1234';

// Remove any existing application for this student & job to avoid unique constraint
$pdo->prepare("DELETE FROM applications WHERE job_id = :jid AND student_id = :sid")->execute([':jid' => $job['id'], ':sid' => $student['id']]);

$app_res = create_application([
    'job_id' => $job['id'],
    'cover_letter' => 'Test application statement for notification testing.',
    'phone' => $dummy_phone,
    'availability' => ['Mon - Morning (8AM–12NN)']
]);

assert($app_res['success'] === true, "create_application must succeed: " . ($app_res['message'] ?? ''));
$created_app_id = $app_res['id'];

$emp_new_unread = get_unread_notifications_count($employer['id']);
assert($emp_new_unread === $emp_initial_unread + 1, "Employer unread count must increase by 1 upon student application");

$emp_notifs = get_user_notifications($employer['id'], 3);
echo "  Employer Notification Title: '{$emp_notifs[0]['title']}'\n";
echo "  Employer Notification Message: '{$emp_notifs[0]['message']}'\n";
echo "  Employer Target Link: '{$emp_notifs[0]['link']}'\n";

assert(str_contains($emp_notifs[0]['title'], 'New Candidate Application'), "Title must indicate candidate application");
assert(str_contains($emp_notifs[0]['message'], $student['name']), "Message must contain applicant name");
assert(str_contains($emp_notifs[0]['link'], "id={$created_app_id}"), "Link must target specific candidate review drawer");
echo "  [PASS] Student application correctly alerted Employer with direct candidate link.\n";

// -------------------------------------------------------------------------
// TEST 3: Role Lifecycle - Employer to Student (Application Status Decision)
// -------------------------------------------------------------------------
echo "\n--- TEST 3: Lifecycle Flow: Employer Decisions -> Student Alerted ---\n";

$stud_initial_unread = get_unread_notifications_count($student['id']);

// Employer updates candidate application to 'interview_scheduled'
$status_res = update_application_status(
    $created_app_id,
    'interview_scheduled',
    'Bring printed portfolio and class schedule.',
    [
        'date' => date('Y-m-d', strtotime('+5 days')),
        'time' => '10:30 AM',
        'venue' => 'Innovation Lab Room 204'
    ]
);
assert($status_res === true, "update_application_status must return true");

$stud_new_unread = get_unread_notifications_count($student['id']);
assert($stud_new_unread === $stud_initial_unread + 1, "Student unread count must increase by 1 upon interview scheduling");

$stud_notifs = get_user_notifications($student['id'], 3);
echo "  Student Notification Title: '{$stud_notifs[0]['title']}'\n";
echo "  Student Notification Message: '{$stud_notifs[0]['message']}'\n";
assert(str_contains($stud_notifs[0]['title'], 'Interview Scheduled'), "Title must indicate interview scheduled");
assert(str_contains($stud_notifs[0]['message'], 'Innovation Lab Room 204'), "Message must contain interview venue");
assert(str_contains($stud_notifs[0]['message'], '10:30 AM'), "Message must contain interview time");
echo "  [PASS] Employer status decision correctly alerted Student with interview details.\n";

// -------------------------------------------------------------------------
// TEST 4: Role Lifecycle - Student/Employer to Admin (Verification & Requests)
// -------------------------------------------------------------------------
echo "\n--- TEST 4: Lifecycle Flow: Profile Change -> Admin Alerted ---\n";

$admin_initial_unread = get_unread_notifications_count($admin['id']);

// Clear existing pending request for student if any
$pdo->prepare("DELETE FROM profile_requests WHERE user_id = :uid")->execute([':uid' => $student['id']]);

$req_res = create_profile_request(
    $student['id'],
    [
        'name' => $student['name'],
        'email' => $student['email'],
        'department' => 'Institute of Computing and Digital Innovation (ICDI)',
        'course' => 'BS Information Systems (BSIS)',
        'year_level' => '3rd Year'
    ],
    'proof_cor_test.pdf',
    'Updating year level to 3rd Year.'
);
assert($req_res['success'] === true, "create_profile_request must succeed");

$admin_new_unread = get_unread_notifications_count($admin['id']);
assert($admin_new_unread > $admin_initial_unread, "Admin unread count must increase when profile request submitted");

$admin_notifs = get_user_notifications($admin['id'], 3);
echo "  Admin Notification Title: '{$admin_notifs[0]['title']}'\n";
echo "  Admin Notification Message: '{$admin_notifs[0]['message']}'\n";
assert(str_contains($admin_notifs[0]['title'], 'Profile Correction Request'), "Admin should be alerted about profile correction");
echo "  [PASS] Student profile request correctly alerted Administrator.\n";

// -------------------------------------------------------------------------
// TEST 5: Role Lifecycle - Admin Approves Profile -> Student Alerted
// -------------------------------------------------------------------------
echo "\n--- TEST 5: Lifecycle Flow: Admin Approves Request -> Student Alerted ---\n";

$stud_unread_before_app = get_unread_notifications_count($student['id']);
$target_req_id = $req_res['request']['id'];

$approve_res = approve_profile_request($target_req_id, 'Verified via campus registrar master list.');
assert($approve_res === true, "approve_profile_request must succeed");

$stud_unread_after_app = get_unread_notifications_count($student['id']);
assert($stud_unread_after_app === $stud_unread_before_app + 1, "Student must receive notification when profile approved");

$stud_latest = get_user_notifications($student['id'], 1)[0];
echo "  Student Approved Notification Title: '{$stud_latest['title']}'\n";
assert(str_contains($stud_latest['title'], 'Profile Update Approved'), "Title must indicate approval");
echo "  [PASS] Admin profile approval correctly alerted Student.\n";

// -------------------------------------------------------------------------
// TEST 6: Role Lifecycle - Admin Verifies Employer -> Employer Alerted
// -------------------------------------------------------------------------
echo "\n--- TEST 6: Lifecycle Flow: Admin Verifies Partner -> Employer Alerted ---\n";

$emp_unread_before_ver = get_unread_notifications_count($employer['id']);
$ver_res = update_user_verification($employer['id'], 'verified');
assert($ver_res === true, "update_user_verification must succeed");

$emp_unread_after_ver = get_unread_notifications_count($employer['id']);
assert($emp_unread_after_ver === $emp_unread_before_ver + 1, "Employer must receive verification notification");

$emp_latest = get_user_notifications($employer['id'], 1)[0];
echo "  Employer Verified Notification Title: '{$emp_latest['title']}'\n";
assert(str_contains($emp_latest['title'], 'Verified'), "Title must indicate verified");
echo "  [PASS] Admin accreditation verification correctly alerted Employer.\n";

// -------------------------------------------------------------------------
// TEST 7: Mark All as Read & Clean Up
// -------------------------------------------------------------------------
echo "\n--- TEST 7: Mark All as Read & Final Cleanup ---\n";

foreach ([$student, $employer, $admin] as $u) {
    $role = strtoupper($u['role']);
    mark_all_notifications_as_read($u['id']);
    $final_unread = get_unread_notifications_count($u['id']);
    assert($final_unread === 0, "FAILED ($role): Final unread count must be 0");
    echo "  [PASS] {$role} notifications successfully marked all as read.\n";
}

// Clean up created test application and profile request
$pdo->prepare("DELETE FROM applications WHERE id = :id")->execute([':id' => $created_app_id]);
$pdo->prepare("DELETE FROM profile_requests WHERE id = :id")->execute([':id' => $target_req_id]);

echo "\n========================================================\n";
echo "  ALL NOTIFICATION TESTS FOR ALL ROLES PASSED (100%)\n";
echo "========================================================\n";
