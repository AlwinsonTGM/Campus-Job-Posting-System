<?php
/**
 * Campus Job Posting System - Deep Link & Target Highlight Test Suite
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/services/job-service.php';

$passes = 0;
$total = 0;

function assert_test(bool $condition, string $description): void {
    global $passes, $total;
    $total++;
    if ($condition) {
        $passes++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
    }
}

echo "=====================================================\n";
echo "DEEP LINK & TARGET HIGHLIGHT VERIFICATION SUITE\n";
echo "=====================================================\n";

// 1. SessionGuard Sanitization with Hashes and Parameters
$valid_student_deeplink = SessionGuard::sanitizeReturnTo('student/my-applications.php?highlight=app-42#app-42');
assert_test($valid_student_deeplink === 'student/my-applications.php?highlight=app-42#app-42', 'SessionGuard permits student application deep link with query and hash');

$valid_emp_deeplink = SessionGuard::sanitizeReturnTo('employer/review-app.php?id=9&highlight=app-9#app-9');
assert_test($valid_emp_deeplink === 'employer/review-app.php?id=9&highlight=app-9#app-9', 'SessionGuard permits employer review deep link');

$valid_admin_deeplink = SessionGuard::sanitizeReturnTo('admin/users.php?ver_status=pending_approval&highlight=user-row-7#user-row-7');
assert_test($valid_admin_deeplink === 'admin/users.php?ver_status=pending_approval&highlight=user-row-7#user-row-7', 'SessionGuard permits admin user row deep link');

$valid_settings_deeplink = SessionGuard::sanitizeReturnTo('settings.php?highlight=profile-form#profile-form');
assert_test($valid_settings_deeplink === 'settings.php?highlight=profile-form#profile-form', 'SessionGuard permits settings profile deep link');

// 2. Open Redirect Defense with Deep Links
assert_test(SessionGuard::sanitizeReturnTo('https://malicious.org/login?highlight=app-1#app-1') === null, 'SessionGuard blocks external https URL with deep link');
assert_test(SessionGuard::sanitizeReturnTo('//malicious.org/steal#app-1') === null, 'SessionGuard blocks protocol-relative URL with deep link');
assert_test(SessionGuard::sanitizeReturnTo('javascript:alert(1)#app-1') === null, 'SessionGuard blocks javascript URI with hash');

// 3. Application Status Notification Link Builder
$pdo = get_db_connection();
$stmt = $pdo->query("SELECT `id`, `student_id`, `job_id` FROM `applications` LIMIT 1");
$sample_app = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sample_app) {
    $sid = (int)$sample_app['student_id'];
    $aid = (int)$sample_app['id'];
    
    // Test dispatch with application ID
    dispatch_application_status_notification(
        $sid,
        'Test Role',
        'under_review',
        'Under Review',
        'Reviewing test resume',
        null,
        null,
        null,
        $aid
    );

    $latest_stmt = $pdo->prepare("SELECT `link` FROM `notifications` WHERE `user_id` = :uid ORDER BY `id` DESC LIMIT 1");
    $latest_stmt->execute([':uid' => $sid]);
    $latest_link = $latest_stmt->fetchColumn();

    assert_test($latest_link === "student/my-applications.php?highlight=app-{$aid}#app-{$aid}", 'Notification links to specific application card with hash and query param');
}

echo "=====================================================\n";
echo "SUMMARY: {$passes} Passed, " . ($total - $passes) . " Failed\n";
echo "=====================================================\n";

if ($passes !== $total) {
    exit(1);
}
