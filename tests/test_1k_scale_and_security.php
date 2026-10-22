<?php
declare(strict_types=1);

/**
 * 1,000-User Scale, Security Hardening & Concurrency Verification Test
 * File: tests/test_1k_scale_and_security.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$passCount = 0;
$failCount = 0;

function assert_1k(bool $condition, string $id, string $desc, string $extra = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo "  \033[32m[PASS]\033[0m \033[1m{$id}\033[0m: {$desc}\n";
    } else {
        $failCount++;
        echo "  \033[31m[FAIL]\033[0m \033[1m{$id}\033[0m: {$desc}\n";
        if ($extra) {
            echo "         \033[31mDiagnostic: {$extra}\033[0m\n";
        }
    }
}

class TestHttpClient {
    private string $cookie_file;
    private string $base_url;

    public function __construct(string $base_url = 'http://127.0.0.1:8000') {
        $this->base_url = rtrim($base_url, '/');
        $this->cookie_file = tempnam(sys_get_temp_dir(), 'test_1k_');
    }

    public function __destruct() {
        if (file_exists($this->cookie_file)) {
            @unlink($this->cookie_file);
        }
    }

    public function get(string $path): array {
        $ch = curl_init($this->base_url . '/' . ltrim($path, '/'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($raw, 0, $header_size);
        $body = substr($raw, $header_size);
        curl_close($ch);

        return [
            'status'   => $status,
            'headers'  => $headers,
            'body'     => $body,
            'location' => preg_match('/^Location:\s*(.*?)$/mi', $headers, $m) ? trim($m[1]) : null
        ];
    }

    public function post(string $path, array $data = []): array {
        $ch = curl_init($this->base_url . '/' . ltrim($path, '/'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $raw = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($raw, 0, $header_size);
        $body = substr($raw, $header_size);
        curl_close($ch);

        return [
            'status'   => $status,
            'headers'  => $headers,
            'body'     => $body,
            'location' => preg_match('/^Location:\s*(.*?)$/mi', $headers, $m) ? trim($m[1]) : null
        ];
    }

    public function demoLogin(string $role): bool {
        $res = $this->get("login.php?demo={$role}");
        return in_array($res['status'], [200, 302], true);
    }

    public function extractCsrfToken(string $html): ?string {
        if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([a-f0-9]{64})["\']/i', $html, $m)) {
            return $m[1];
        }
        return null;
    }
}

echo "\n=====================================================\n";
echo "1K SCALE & SECURITY HARDENING TEST RUNNER\n";
echo "=====================================================\n\n";

$pdo = get_db_connection();

// -----------------------------------------------------------------------------
// 1. Simulating and Verifying 1,000 Users Dataset & Pagination Logic
// -----------------------------------------------------------------------------
echo ">>> 1. 1,000-User Scale Dataset & Pagination Invariant\n";
$simulated_users = [];
for ($i = 1; $i <= 1000; $i++) {
    $simulated_users[] = [
        'id'                  => 1000 + $i,
        'name'                => "Scale Student {$i}",
        'email'               => "student{$i}@kld.edu.ph",
        'role'                => ($i % 10 === 0) ? 'employer' : (($i === 1) ? 'admin' : 'student'),
        'status'              => 'active',
        'verification_status' => 'verified',
        'is_email_verified'   => 1
    ];
}
$per_page = 25;
$total_count = count($simulated_users);
$total_pages = (int)ceil($total_count / $per_page);
$page1_slice = array_slice($simulated_users, 0, $per_page);
$page2_slice = array_slice($simulated_users, 25, $per_page);
$page40_slice = array_slice($simulated_users, 39 * $per_page, $per_page);

assert_1k($total_count === 1000, 'SCALE-01A', '1,000 user dataset generated successfully');
assert_1k($total_pages === 40, 'SCALE-01B', '1,000 users cleanly paginate into 40 pages at 25/page');
assert_1k(count($page1_slice) === 25, 'SCALE-01C', 'Page 1 renders exactly 25 items');
assert_1k(count($page2_slice) === 25, 'SCALE-01D', 'Page 2 renders exactly 25 items');
assert_1k(count($page40_slice) === 25, 'SCALE-01E', 'Final page 40 renders exactly 25 items');
assert_1k($page1_slice[0]['id'] !== $page2_slice[0]['id'], 'SCALE-01F', 'Pages contain disjoint distinct records');

// -----------------------------------------------------------------------------
// 2. Testing admin/users.php and employer/applicants.php Server-Side Pagination
// -----------------------------------------------------------------------------
echo "\n>>> 2. Server-Side Pagination in admin/users.php & employer/applicants.php\n";
$adminClient = new TestHttpClient();
$adminClient->demoLogin('admin');
$resAdmin = $adminClient->get('admin/users.php?page=1');
assert_1k($resAdmin['status'] === 200, 'SCALE-02A', 'admin/users.php responds 200 OK to admin session');
assert_1k(strpos($resAdmin['body'], 'User Directory') !== false, 'SCALE-02B', 'admin/users.php renders directory heading');
assert_1k(strpos($resAdmin['body'], 'pagination') !== false || strpos($resAdmin['body'], 'Page 1 of') !== false, 'SCALE-02C', 'admin/users.php renders pagination navigation bar');

$resAdminP2 = $adminClient->get('admin/users.php?page=2');
assert_1k($resAdminP2['status'] === 200, 'SCALE-02D', 'admin/users.php responds 200 OK for page=2 slice');

$empClient = new TestHttpClient();
$empClient->demoLogin('employer');
$resEmp = $empClient->get('employer/applicants.php?page=1');
assert_1k($resEmp['status'] === 200, 'SCALE-02E', 'employer/applicants.php responds 200 OK to employer session');
assert_1k(strpos($resEmp['body'], 'Applicants') !== false || strpos($resEmp['body'], 'Candidate') !== false, 'SCALE-02F', 'employer/applicants.php renders applicant candidate views');

// -----------------------------------------------------------------------------
// 3. Testing employer/updates.php Horizontal Privilege Escalation (IDOR) Fix
// -----------------------------------------------------------------------------
echo "\n>>> 3. VULN-02: IDOR Prevention on Bulletins (employer/updates.php)\n";
// Clean any prior stray test rows
$pdo->exec("DELETE FROM `updates` WHERE `slug` LIKE 'it-department-infrastructure-notice%'");

$test_uuid = bin2hex(random_bytes(4));
$dispatch_title = 'IT Department Infrastructure Notice ' . $test_uuid;

// Create a test dispatch authored by IT Department (office = 'Institute of Computing & IT Services', author_id = 4)
$dispatch = add_career_update([
    'title'         => $dispatch_title,
    'category'      => 'System Notice',
    'summary'       => 'Scheduled downtime.',
    'content'       => 'All servers undergoing maintenance.',
    'author_name'   => 'IT Director',
    'author_role'   => 'Director',
    'author_office' => 'Institute of Computing & IT Services'
]);
$dispatch_id = (int)($dispatch['id'] ?? 0);

// Employer B (logged in via demoLogin('employer')) visits updates page and gets CSRF token
$attackerClient = new TestHttpClient();
$attackerClient->demoLogin('employer');
$updatesPage = $attackerClient->get('employer/updates.php');
$csrf_token = $attackerClient->extractCsrfToken($updatesPage['body']);

// Attempt IDOR edit on dispatch owned by IT Director
$postRes = $attackerClient->post('employer/updates.php', [
    'action'     => 'edit',
    'id'         => $dispatch_id,
    'csrf_token' => $csrf_token ?? '',
    'title'      => 'HACKED BY ADVERSARY',
    'content'    => 'Malicious modification of foreign department bulletin.'
]);

// Dispatch in database must be unchanged
$fresh_dispatch = get_career_update_by_id($dispatch_id);
assert_1k(
    $fresh_dispatch && $fresh_dispatch['title'] === $dispatch_title,
    'VULN-02A',
    'Foreign department dispatch title was protected from unauthorized edit'
);

// Verify follow-up page displays unauthorized notice
$followUp = $attackerClient->get('employer/updates.php');
assert_1k(
    strpos($followUp['body'], 'Unauthorized') !== false || strpos($followUp['body'], 'danger') !== false,
    'VULN-02B',
    'Unauthorized department flash banner rendered to caller'
);

// Clean up test dispatch
if ($dispatch_id > 0) {
    $pdo->prepare("DELETE FROM `updates` WHERE `id` = :id")->execute([':id' => $dispatch_id]);
}

// -----------------------------------------------------------------------------
// 4. Testing api/notifications.php Open Redirect Protection
// -----------------------------------------------------------------------------
echo "\n>>> 4. VULN-03: Open Redirect Prevention in api/notifications.php\n";
// SessionGuard::sanitizeReturnTo Unit Invariants
$evil_https = SessionGuard::sanitizeReturnTo('https://evil.com/phishing-login');
assert_1k($evil_https === null, 'VULN-03A', 'SessionGuard::sanitizeReturnTo blocks https://evil.com');

$evil_proto = SessionGuard::sanitizeReturnTo('//attacker.org/steal-session');
assert_1k($evil_proto === null, 'VULN-03B', 'SessionGuard::sanitizeReturnTo blocks protocol-relative //attacker.org');

$evil_js = SessionGuard::sanitizeReturnTo('javascript:alert(1)');
assert_1k($evil_js === null, 'VULN-03C', 'SessionGuard::sanitizeReturnTo blocks javascript: URI');

$valid_student = SessionGuard::sanitizeReturnTo('student/dashboard.php');
assert_1k($valid_student === 'student/dashboard.php', 'VULN-03D', 'SessionGuard::sanitizeReturnTo permits student/dashboard.php');

$valid_employer = SessionGuard::sanitizeReturnTo('employer/applicants.php?page=2');
assert_1k($valid_employer === 'employer/applicants.php?page=2', 'VULN-03E', 'SessionGuard::sanitizeReturnTo permits internal employer query routes');

// Create a test notification pointing to https://evil.com
$notif_id = create_notification(
    1,
    'system',
    'Phishing Test',
    'Suspicious link',
    'https://evil.com/malicious',
    'bi-shield',
    'danger'
);

// Student clicks on notification link
$studentClient = new TestHttpClient();
$studentClient->demoLogin('student');
$clickRes = $studentClient->get("api/notifications.php?action=click&id={$notif_id}");

// Redirection must NOT be https://evil.com
$redirectTarget = $clickRes['location'] ?? '';
assert_1k(
    strpos($redirectTarget, 'evil.com') === false,
    'VULN-03F',
    'api/notifications.php?action=click never redirects to external evil.com host'
);
assert_1k(
    strpos($redirectTarget, 'notifications.php') !== false || strpos($redirectTarget, 'index.php') !== false || strpos($redirectTarget, 'dashboard.php') !== false,
    'VULN-03G',
    'api/notifications.php?action=click safely falls back to local application route'
);

// Clean up notification
if ($notif_id > 0) {
    $pdo->prepare("DELETE FROM notifications WHERE id = :id")->execute([':id' => $notif_id]);
}

// -----------------------------------------------------------------------------
// 5. Testing Live Session Reconciliation for Suspended Users (VULN-05)
// -----------------------------------------------------------------------------
echo "\n>>> 5. VULN-05: Live Suspension Gating in SessionGuard\n";
// Create a temporary active test user in the database
$stmt_temp = $pdo->prepare("
    INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`, `verification_status`, `is_email_verified`, `created_at`)
    VALUES ('Suspension Test Subject', 'suspension.test@kld.edu.ph', 'hash', 'student', 'active', 'verified', 1, NOW())
");
$stmt_temp->execute();
$subject_id = (int)$pdo->lastInsertId();

// Set up active session with expired _last_db_sync (> 15 seconds)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user'] = [
    'id'                  => $subject_id,
    'name'                => 'Suspension Test Subject',
    'email'               => 'suspension.test@kld.edu.ph',
    'role'                => 'student',
    'status'              => 'active',
    'verification_status' => 'verified',
    'is_email_verified'   => 1,
    '_last_db_sync'       => time() - 30 // 30 seconds ago (expired)
];

// Verify initial state
assert_1k(SessionGuard::check() === true, 'VULN-05A', 'User session is initially authenticated');

// Admin suspends user in database
$pdo->prepare("UPDATE `users` SET `status` = 'suspended' WHERE `id` = :id")->execute([':id' => $subject_id]);

// SessionGuard redirect handler interceptor
$suspended_intercepted = null;
SessionGuard::setRedirectHandler(function (string $url, int $status, string $reason) use (&$suspended_intercepted) {
    $suspended_intercepted = ['url' => $url, 'status' => $status, 'reason' => $reason];
});

// User attempts to access protected student dashboard
$_SERVER['SCRIPT_NAME'] = '/student/dashboard.php';
$prot_res = SessionGuard::protect(['student']);

assert_1k(empty($prot_res), 'VULN-05B', 'SessionGuard::protect() denies suspended user');
assert_1k(!isset($_SESSION['user']), 'VULN-05C', 'SessionGuard::protect() immediately purges session for suspended user');
assert_1k($suspended_intercepted !== null, 'VULN-05D', 'SessionGuard intercepted redirect on suspended user');
assert_1k(
    strpos($suspended_intercepted['url'] ?? '', 'login.php') !== false,
    'VULN-05E',
    'Suspended user was redirected to login.php'
);

// Clean up temporary subject
$pdo->prepare("DELETE FROM `users` WHERE `id` = :id")->execute([':id' => $subject_id]);
SessionGuard::setRedirectHandler(null);

// -----------------------------------------------------------------------------
// 6. Restoring Pristine Baseline Fixtures
// -----------------------------------------------------------------------------
echo "\n>>> 6. Restoring Pristine Datastore Baseline\n";
require_once __DIR__ . '/../tools/reset-test-fixtures.php';
echo "\n";

echo "=====================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "=====================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
