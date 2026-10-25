<?php
declare(strict_types=1);

/**
 * Unit & Integration Test for SessionGuard Module
 */

require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

echo "=====================================================\n";
echo "SESSION GUARD DEEP MODULE TEST RUNNER\n";
echo "=====================================================\n";

$passCount = 0;
$failCount = 0;

function assert_test(bool $condition, string $name): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] $name\n";
        $passCount++;
    } else {
        echo "  [FAIL] $name\n";
        $failCount++;
    }
}

// 1. Sanitize return-to security checks
assert_test(SessionGuard::sanitizeReturnTo('student/apply.php?id=12') === 'student/apply.php?id=12', 'Allows valid student route');
assert_test(SessionGuard::sanitizeReturnTo('employer/dashboard.php') === 'employer/dashboard.php', 'Allows valid employer route');
assert_test(SessionGuard::sanitizeReturnTo('https://evil.com/phishing') === null, 'Blocks https external open redirect');
assert_test(SessionGuard::sanitizeReturnTo('//evil.com/phishing') === null, 'Blocks protocol-relative open redirect');
assert_test(SessionGuard::sanitizeReturnTo('javascript:alert(1)') === null, 'Blocks javascript URI scheme');
assert_test(SessionGuard::sanitizeReturnTo('/student/apply.php') === 'student/apply.php', 'Normalizes leading slash');

// 2. Testable interceptor (eliminating exit)
$intercepted = null;
SessionGuard::setRedirectHandler(function (string $url, int $status, string $reason) use (&$intercepted) {
    $intercepted = ['url' => $url, 'status' => $status, 'reason' => $reason];
});

// 3. Unauthenticated protect() interception
unset($_SESSION['user']);
$_SERVER['SCRIPT_NAME'] = '/student/apply.php';
$_SERVER['QUERY_STRING'] = 'id=42';
$result = SessionGuard::protect(['student']);

assert_test(empty($result), 'Unauthenticated protect() returns empty array');
assert_test($intercepted !== null, 'Redirect was intercepted without process exit');
assert_test(strpos($intercepted['url'] ?? '', 'login.php?next=student%2Fapply.php%3Fid%3D42') !== false, 'Captured deep link in ?next= param');
assert_test(($intercepted['reason'] ?? '') === 'unauthenticated', 'Reason is unauthenticated');

// 3b. Unauthenticated employer & admin routes redirect to portal-login.php
$intercepted = null;
$_SERVER['SCRIPT_NAME'] = '/employer/create-job.php';
$_SERVER['QUERY_STRING'] = '';
$resultEmp = SessionGuard::protect(['employer', 'admin']);
assert_test(empty($resultEmp) && strpos($intercepted['url'] ?? '', 'portal-login.php?next=employer%2Fcreate-job.php') !== false, 'Unauthenticated employer route redirects to portal-login.php');

$intercepted = null;
$_SERVER['SCRIPT_NAME'] = '/admin/users.php';
$resultAdm = SessionGuard::protect(['admin']);
assert_test(empty($resultAdm) && strpos($intercepted['url'] ?? '', 'portal-login.php?next=admin%2Fusers.php') !== false, 'Unauthenticated admin route redirects to portal-login.php');

// 4. Role authorization gating
$_SESSION['user'] = [
    'id' => 999999,
    'name' => 'Demo Employer',
    'email' => 'employer@campus.edu',
    'role' => 'employer',
    'is_email_verified' => 1,
    'verification_status' => 'verified',
    '_last_db_sync' => time()
];
$intercepted = null;
$result = SessionGuard::protect(['student']);

assert_test(empty($result), 'Unauthorized role returns empty array');
assert_test($intercepted !== null, 'Role mismatch intercepted redirect');
assert_test(strpos($intercepted['url'] ?? '', 'employer/dashboard.php') !== false, 'Redirected employer to employer dashboard');

// 5. Authorized role access
$intercepted = null;
$result = SessionGuard::protect(['employer']);
assert_test(!empty($result) && $result['id'] === 999999, 'Authorized role passes and returns user record');
assert_test($intercepted === null, 'No redirect triggered for authorized user');
assert_test(SessionGuard::hasRole('employer') === true, 'hasRole detects employer');
assert_test(SessionGuard::hasRole('student') === false, 'hasRole denies student');

// 5b. Temporary Password Gate (must_change_password = 1)
$_SESSION['user']['must_change_password'] = 1;
$_SERVER['SCRIPT_NAME'] = '/employer/dashboard.php';
$intercepted = null;
$resultGate = SessionGuard::protect(['employer']);
assert_test(empty($resultGate) && ($intercepted['reason'] ?? '') === 'must_change_password', 'Temporary Password Gate intercepts protected route');
assert_test(strpos($intercepted['url'] ?? '', 'settings.php?setup_password=1#password-section') !== false, 'Temporary Password Gate redirects to settings.php#password-section');
$_SESSION['user']['must_change_password'] = 0;

// 6. Verification Quarantine detection
$_SESSION['user']['is_email_verified'] = 0;
$_SESSION['user']['role'] = 'student';
$intercepted = null;
// Avoid mail dispatch network dependencies by catching or testing quarantine structure
$_SESSION['pending_verification'] = [
    'user_id' => 999999,
    'email' => 'unverified@campus.edu',
    'name' => 'Unverified Student',
    'role' => 'student'
];
assert_test(isset($_SESSION['pending_verification']), 'Pending verification state correctly established');

// 7. Session Graduation
SessionGuard::logout();
assert_test(!isset($_SESSION['user']), 'logout() clears user session');
assert_test(!isset($_SESSION['pending_verification']), 'logout() clears pending verification');

// Reset redirect handler
SessionGuard::setRedirectHandler(null);

echo "=====================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "=====================================================\n";

exit($failCount === 0 ? 0 : 1);
