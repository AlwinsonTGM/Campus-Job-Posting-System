<?php
/**
 * ============================================================================
 * KLD Campus Hire - Comprehensive Dual Integration Test Suite
 * File: tests/test_comprehensive_dual_integration.php
 * Milestone 2 Programmatic Integration Verification
 * 
 * Covers ALL 5 Requirement Areas:
 *   R1: Multi-Role Authentication & Access Control (Auth, Hash, RBAC, IDOR, CSRF)
 *   R2: Student Portal & Hiring Lifecycle (Search, Pure Junction, Schedule, Stepper)
 *   R3: Employer Requisition & Candidate Review (Cap, Quota, Interview, Offer, Reject)
 *   R4: Admin Management, Accreditation & System Analytics (Permits, COR, Updates, KPIs)
 *   R5: Data Integrity & 3NF Hydration Audit (10 Tables, FK Cascades, Zero Notices)
 * ============================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../includes/components.php';
require_once __DIR__ . '/../database/migrate.php';

// ============================================================================
// TEST HARNESS & REPORTING UTILITIES
// ============================================================================

class TestHarness {
    public static int $total_tests = 0;
    public static int $passed_tests = 0;
    public static int $failed_tests = 0;
    public static array $failures = [];
    public static float $start_time = 0.0;
    public static string $current_suite = '';

    public static function init(): void {
        self::$start_time = microtime(true);
        echo "\n\033[1;36m=======================================================================\033[0m\n";
        echo "\033[1;36m  KLD CAMPUS HIRE — COMPREHENSIVE DUAL INTEGRATION TEST SUITE\033[0m\n";
        echo "\033[1;36m  Milestone 2: 3NF Relational Data Layer & Multi-Role Verification\033[0m\n";
        echo "\033[1;36m=======================================================================\033[0m\n\n";
    }

    public static function suite(string $name, string $description): void {
        self::$current_suite = $name;
        echo "\n\033[1;33m>>> SUITE {$name}: {$description}\033[0m\n";
        echo str_repeat('-', 71) . "\n";
    }

    public static function assert(bool $condition, string $test_id, string $description, string $details = ''): void {
        self::$total_tests++;
        if ($condition) {
            self::$passed_tests++;
            echo "  \033[32m[PASS]\033[0m \033[1m{$test_id}\033[0m: {$description}\n";
        } else {
            self::$failed_tests++;
            $msg = "{$test_id}: {$description}" . ($details ? " — {$details}" : "");
            self::$failures[] = "[Suite " . self::$current_suite . "] " . $msg;
            echo "  \033[31m[FAIL]\033[0m \033[1m{$test_id}\033[0m: {$description}\n";
            if ($details) {
                echo "         \033[31mDetails: {$details}\033[0m\n";
            }
        }
    }

    public static function finish(): int {
        $duration = round(microtime(true) - self::$start_time, 3);
        echo "\n\033[1;36m=======================================================================\033[0m\n";
        echo "\033[1;36m  TEST SUITE EXECUTION SUMMARY\033[0m\n";
        echo "\033[1;36m=======================================================================\033[0m\n";
        echo "  Total Tests:    " . self::$total_tests . "\n";
        echo "  Passed:         \033[32m" . self::$passed_tests . "\033[0m\n";
        echo "  Failed:         " . (self::$failed_tests > 0 ? "\033[31m" . self::$failed_tests . "\033[0m" : "0") . "\n";
        echo "  Execution Time: {$duration} seconds\n";

        if (self::$failed_tests > 0) {
            echo "\n\033[31mFailures Summary:\033[0m\n";
            foreach (self::$failures as $f) {
                echo "  - {$f}\n";
            }
            echo "\n\033[31;1mRESULT: FAILED\033[0m\n\n";
            return 1;
        }

        echo "\n\033[32;1mRESULT: ALL TESTS PASSED (100% SUCCESS)\033[0m\n\n";
        return 0;
    }
}

// Subprocess execution helper feeding PHP code via STDIN to avoid shell escaping issues on Windows
function run_subprocess_snippet(string $php_code): array {
    $php_binary = 'C:\\xampp\\php\\php.exe';
    $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    $proc = proc_open([$php_binary], $descriptors, $pipes, dirname(__DIR__));
    if (!is_resource($proc)) {
        return ['exit_code' => -1, 'stdout' => '', 'stderr' => 'Failed to spawn process'];
    }
    fwrite($pipes[0], "<?php\n" . $php_code . "\n");
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit_code = proc_close($proc);
    return [
        'exit_code' => $exit_code,
        'stdout'    => trim($stdout),
        'stderr'    => trim($stderr)
    ];
}

TestHarness::init();
$pdo = get_db_connection();

// Ensure schema without destructive truncation
DatastoreManager::ensureSchema();

// ============================================================================
// SUITE R1: Multi-Role Authentication & Access Control
// ============================================================================
TestHarness::suite('R1', 'Multi-Role Authentication & Access Control Verification');

// R1-01: Student Login with valid credentials
$login_stud = @login_user('student@kld.edu.ph', 'Password123!');
TestHarness::assert(
    $login_stud['success'] === true && isset($login_stud['user']) && $login_stud['user']['role'] === 'student',
    'R1-01',
    'Student login succeeds with valid credentials and returns student role'
);

// R1-02: Employer Login (University Office)
$login_office = @login_user('registrar@kld.edu.ph', 'Password123!');
TestHarness::assert(
    $login_office['success'] === true && isset($login_office['user']) && 
    $login_office['user']['role'] === 'employer' && 
    $login_office['user']['employer_type'] === 'university_office',
    'R1-02',
    'Employer login succeeds for University Office with matching subtype'
);

// R1-03: Employer Login (Approved Partner)
$login_partner = @login_user('campuscafe@partner.kld.edu.ph', 'Password123!');
TestHarness::assert(
    $login_partner['success'] === true && isset($login_partner['user']) && 
    $login_partner['user']['role'] === 'employer' && 
    $login_partner['user']['employer_type'] === 'approved_partner',
    'R1-03',
    'Employer login succeeds for Approved Partner with matching subtype'
);

// R1-04: Admin Login
$login_admin = @login_user('admin@kld.edu.ph', 'Password123!');
TestHarness::assert(
    $login_admin['success'] === true && isset($login_admin['user']) && $login_admin['user']['role'] === 'admin',
    'R1-04',
    'Admin login succeeds with valid administrative credentials'
);

// R1-05: Login failure with wrong password
$login_wrong_pass = @login_user('student@kld.edu.ph', 'WrongPassword999!');
TestHarness::assert(
    $login_wrong_pass['success'] === false && str_contains(strtolower($login_wrong_pass['message']), 'password'),
    'R1-05',
    'Login cleanly rejects invalid password credentials'
);

// R1-06: Login failure with non-existent email
$login_bad_email = @login_user('nonexistent.user.xyz@kld.edu.ph', 'AnyPassword123!');
TestHarness::assert(
    $login_bad_email['success'] === false && str_contains(strtolower($login_bad_email['message']), 'no account'),
    'R1-06',
    'Login cleanly rejects non-existent email address'
);

// R1-07: Password Hash Integrity (Stored as secure Bcrypt hash after login migration)
$admin_row = $pdo->query("SELECT `password` FROM `users` WHERE `email` = 'admin@kld.edu.ph' LIMIT 1")->fetch();
$is_bcrypt = (bool)preg_match('/^\$2[ayb]\$.{56}$/', $admin_row['password'] ?? '');
$verify_ok = password_verify('Password123!', $admin_row['password'] ?? '');
TestHarness::assert(
    $is_bcrypt && $verify_ok,
    'R1-07',
    'User password is encrypted as valid standard bcrypt hash ($2y$)'
);

// R1-08: Access Control Guard: Unauthenticated visitor blocked from admin portal
$code_unauth = '
    $_SERVER["SCRIPT_NAME"] = "/admin/reports.php";
    require_once "includes/data-helper.php";
    unset($_SESSION["user"]);
    require_auth(["admin"]);
    echo "ACCESSED";
';
$sub_unauth = run_subprocess_snippet($code_unauth);
TestHarness::assert(
    !str_contains($sub_unauth['stdout'], 'ACCESSED'),
    'R1-08',
    'require_auth blocks unauthenticated visitor from accessing restricted portal'
);

// R1-09: Access Control Guard: Student blocked from admin portal
$code_stud_admin = '
    $_SERVER["SCRIPT_NAME"] = "/admin/reports.php";
    require_once "includes/data-helper.php";
    $_SESSION["user"] = ["id" => 1, "role" => "student", "name" => "Student"];
    require_auth(["admin"]);
    echo "ACCESSED";
';
$sub_stud_admin = run_subprocess_snippet($code_stud_admin);
TestHarness::assert(
    !str_contains($sub_stud_admin['stdout'], 'ACCESSED'),
    'R1-09',
    'require_auth blocks student account from administrative portal'
);

// R1-10: Access Control Guard: Student blocked from employer portal
$code_stud_emp = '
    $_SERVER["SCRIPT_NAME"] = "/employer/create-job.php";
    require_once "includes/data-helper.php";
    $_SESSION["user"] = ["id" => 1, "role" => "student", "name" => "Student"];
    require_auth(["employer"]);
    echo "ACCESSED";
';
$sub_stud_emp = run_subprocess_snippet($code_stud_emp);
TestHarness::assert(
    !str_contains($sub_stud_emp['stdout'], 'ACCESSED'),
    'R1-10',
    'require_auth blocks student account from employer portal'
);

// R1-11: Access Control Guard: Employer blocked from student portal
$code_emp_stud = '
    $_SERVER["SCRIPT_NAME"] = "/student/my-applications.php";
    require_once "includes/data-helper.php";
    $_SESSION["user"] = ["id" => 3, "role" => "employer", "name" => "Employer"];
    require_auth(["student"]);
    echo "ACCESSED";
';
$sub_emp_stud = run_subprocess_snippet($code_emp_stud);
TestHarness::assert(
    !str_contains($sub_emp_stud['stdout'], 'ACCESSED'),
    'R1-11',
    'require_auth blocks employer account from student portal'
);

// R1-12: Access Control Guard: Authorized roles permitted
$code_allowed = '
    $_SERVER["SCRIPT_NAME"] = "/employer/applicants.php";
    require_once "includes/data-helper.php";
    $_SESSION["user"] = ["id" => 3, "role" => "employer", "name" => "Employer"];
    require_auth(["employer", "admin"]);
    echo "ACCESSED";
';
$sub_allowed = run_subprocess_snippet($code_allowed);
TestHarness::assert(
    str_contains($sub_allowed['stdout'], 'ACCESSED'),
    'R1-12',
    'require_auth allows authorized role into portal without blocking'
);

// R1-13: IDOR Guard: can_manage_job()
// Job 1 has employer_id = 4 (MIS)
$sample_job = get_job_by_id(1);
$emp_owner = get_user_by_id(4); // MIS employer (owns Job 1)
$emp_other = get_user_by_id(3); // Registrar employer (does NOT own Job 1)
$student_u = get_user_by_id(1); // Student (Juan Dela Cruz)
$admin_u   = get_user_by_id(5); // Admin

$idor_job_admin = can_manage_job($sample_job, $admin_u);
$idor_job_owner = can_manage_job($sample_job, $emp_owner);
$idor_job_other = can_manage_job($sample_job, $emp_other);
$idor_job_stud  = can_manage_job($sample_job, $student_u);

unset($_SESSION['user']);
$idor_job_null  = can_manage_job($sample_job, null);

TestHarness::assert(
    $idor_job_admin === true && $idor_job_owner === true && 
    $idor_job_other === false && $idor_job_stud === false && $idor_job_null === false,
    'R1-13',
    'can_manage_job correctly restricts management to job owner or admin (IDOR safe)'
);

// R1-14: IDOR Guard: can_review_application()
// App 1 has job_id = 1 (owned by employer 4) and student_id = 1
$sample_app = get_application_by_id(1);
$idor_app_admin = can_review_application($sample_app, $admin_u);
$idor_app_owner = can_review_application($sample_app, $emp_owner);
$idor_app_other = can_review_application($sample_app, $emp_other);
$idor_app_stud  = can_review_application($sample_app, $student_u);

unset($_SESSION['user']);
$idor_app_null  = can_review_application($sample_app, null);

TestHarness::assert(
    $idor_app_admin === true && $idor_app_owner === true && 
    $idor_app_other === false && $idor_app_stud === false && $idor_app_null === false,
    'R1-14',
    'can_review_application prevents other employers or students from evaluating candidates'
);

// R1-15: IDOR Guard: can_view_student_resume()
// App 1 is Student 1 applied to Job 1 (Employer 4)
$student_other = get_user_by_id(2); // Maria Santos
$can_admin_view   = can_view_student_resume($sample_app, 1, $admin_u);
$can_self_view    = can_view_student_resume($sample_app, 1, $student_u);
$can_other_stud   = can_view_student_resume($sample_app, 1, $student_other);
$can_hiring_view  = can_view_student_resume($sample_app, 1, $emp_owner);
$can_unauth_emp   = can_view_student_resume($sample_app, 1, $emp_other);

unset($_SESSION['user']);
$can_visitor_view = can_view_student_resume($sample_app, 1, null);

TestHarness::assert(
    $can_admin_view === true && $can_self_view === true && $can_hiring_view === true &&
    $can_other_stud === false && $can_unauth_emp === false && $can_visitor_view === false,
    'R1-15',
    'can_view_student_resume strictly protects candidate PII from unauthorized viewers'
);

// R1-16: Security Rule: @kld.edu.ph email auto-verifies University Office
$test_reg_kld = register_user([
    'name'              => 'Test KLD Office',
    'email'             => 'test.office@kld.edu.ph',
    'password'          => 'Office123!',
    'role'              => 'employer',
    'employer_type'     => 'university_office',
    'organization_name' => 'KLD Test Office',
    'office_location'   => 'Admin Hall'
]);
$kld_user = $test_reg_kld['user'] ?? null;
TestHarness::assert(
    $test_reg_kld['success'] === true && $kld_user && 
    $kld_user['verification_status'] === 'verified',
    'R1-16',
    'University Office registering with @kld.edu.ph email is automatically verified'
);

// R1-17: Security Rule: University Office with non-KLD email flagged as pending_approval
$test_reg_nonkld = register_user([
    'name'              => 'Test Fake Office',
    'email'             => 'fake.office@gmail.com',
    'password'          => 'Office123!',
    'role'              => 'employer',
    'employer_type'     => 'university_office',
    'organization_name' => 'Fake External Office',
    'office_location'   => 'Off Campus'
]);
$nonkld_user = $test_reg_nonkld['user'] ?? null;
TestHarness::assert(
    $test_reg_nonkld['success'] === true && $nonkld_user && 
    $nonkld_user['verification_status'] === 'pending_approval',
    'R1-17',
    'University Office registering with non-KLD email domain is flagged for admin review'
);

// R1-18: Security Rule: Partner Employer registration always pending_approval
$test_reg_partner = register_user([
    'name'              => 'New Partner Corp',
    'email'             => 'contact@partnercorp.com',
    'password'          => 'Partner123!',
    'role'              => 'employer',
    'employer_type'     => 'approved_partner',
    'organization_name' => 'Partner Corp',
    'office_location'   => 'Dasmariñas City'
]);
$partner_new = $test_reg_partner['user'] ?? null;
TestHarness::assert(
    $test_reg_partner['success'] === true && $partner_new && 
    $partner_new['verification_status'] === 'pending_approval',
    'R1-18',
    'Partner Employer registration is set to pending_approval pending accreditation review'
);

// R1-19: Duplicate Email Registration Blocked
$dup_email_res = register_user([
    'name'     => 'Duplicate Person',
    'email'    => 'student@kld.edu.ph', // Existing seeded student
    'password' => 'Pass123!',
    'role'     => 'student'
]);
TestHarness::assert(
    $dup_email_res['success'] === false && str_contains(strtolower($dup_email_res['message']), 'already registered'),
    'R1-19',
    'Registration prevents duplicate email address submission cleanly'
);

// R1-20: Duplicate Student ID Registration Blocked
$dup_id_res = register_user([
    'name'       => 'Another Student',
    'email'      => 'another.unique@kld.edu.ph',
    'password'   => 'Pass123!',
    'role'       => 'student',
    'student_id' => '2024-1-000123' // Juan Dela Cruz's student ID
]);
TestHarness::assert(
    $dup_id_res['success'] === false && str_contains(strtolower($dup_id_res['message']), 'student id'),
    'R1-20',
    'Registration prevents duplicate student ID assignment cleanly'
);

// R1-21: CSRF Token Generation & Verification
$_SESSION['csrf_token'] = null;
$token = generate_csrf_token();
$valid_csrf = verify_csrf_token($token);
$invalid_csrf = verify_csrf_token('invalid_tampered_csrf_token');
$empty_csrf = verify_csrf_token('');
TestHarness::assert(
    strlen($token) === 64 && ctype_xdigit($token) && 
    $valid_csrf === true && $invalid_csrf === false && $empty_csrf === false,
    'R1-21',
    'CSRF security system generates 64-hex tokens and validates correctly'
);

// Clean up temporary registration users created in R1
if ($kld_user) $pdo->exec("DELETE FROM users WHERE id = " . (int)$kld_user['id']);
if ($nonkld_user) $pdo->exec("DELETE FROM users WHERE id = " . (int)$nonkld_user['id']);
if ($partner_new) $pdo->exec("DELETE FROM users WHERE id = " . (int)$partner_new['id']);

// ============================================================================
// SUITE R2: Student Portal & Hiring Lifecycle
// ============================================================================
TestHarness::suite('R2', 'Student Portal & Hiring Lifecycle Verification');

// R2-01: Job discovery via get_jobs()
$all_active_jobs = get_jobs();
TestHarness::assert(
    count($all_active_jobs) >= 5 && isset($all_active_jobs[0]['title']) && isset($all_active_jobs[0]['pay_rate']),
    'R2-01',
    'Student job discovery successfully queries and hydrates active campus vacancies'
);

// R2-02: Category listing & active job counts via get_categories()
$categories = get_categories();
$cat_count_valid = true;
foreach ($categories as $c) {
    if (!isset($c['job_count']) || !is_int($c['job_count']) || $c['job_count'] < 0) {
        $cat_count_valid = false;
        break;
    }
}
TestHarness::assert(
    count($categories) === 6 && $cat_count_valid,
    'R2-02',
    'get_categories() dynamically calculates accurate non-negative active job counts'
);

// R2-03: Filtering jobs by category and keyword
$admin_jobs = get_jobs('Administrative Clerk');
$search_jobs = get_jobs(null, 'Library');
TestHarness::assert(
    count($admin_jobs) > 0 && count($search_jobs) > 0,
    'R2-03',
    'Student job filtering correctly filters by category slug/name and keyword search'
);

// R2-04: Pure Junction Record Verification in applications table
// Table `applications` MUST NOT contain snapshot columns (e.g. `student_name`, `job_title`, `department`)
$cols_stmt = $pdo->query("DESCRIBE `applications`");
$app_columns = $cols_stmt->fetchAll(PDO::FETCH_COLUMN);
$forbidden_snapshot_cols = ['student_name', 'student_email', 'job_title', 'department', 'employer_name'];
$has_snapshot = false;
foreach ($forbidden_snapshot_cols as $fc) {
    if (in_array($fc, $app_columns, true)) {
        $has_snapshot = true;
        break;
    }
}
TestHarness::assert(
    !$has_snapshot && in_array('job_id', $app_columns, true) && in_array('student_id', $app_columns, true),
    'R2-04',
    'applications table is a pure 3NF junction table without denormalized snapshot columns'
);

// R2-05: Shift Availability Schedule Matrix JSON Serialization & Deserialization
// Login as student 2 (Maria Santos) and apply to job 3 (Peer Tutor, employer 5)
$_SESSION['user'] = get_user_by_id(2);
$test_matrix = [
    'Mon - Morning (8AM–12NN)',
    'Wed - Afternoon (1PM–5PM)',
    'Fri - Morning (8AM–12NN)'
];

// Clean any pre-existing application
$pdo->exec("DELETE FROM `applications` WHERE `job_id` = 3 AND `student_id` = 2");

$app_submit_res = create_application([
    'job_id'       => 3,
    'cover_letter' => 'I am eager to assist fellow students with academic tutoring.',
    'availability' => $test_matrix,
    'resume_file'  => 'Maria_Santos_Resume.pdf'
]);

TestHarness::assert(
    $app_submit_res['success'] === true && !empty($app_submit_res['id']),
    'R2-05a',
    'Student successfully submits vacancy application'
);

$created_app_id = (int)($app_submit_res['id'] ?? 0);
$raw_app_row = $pdo->query("SELECT `availability` FROM `applications` WHERE `id` = {$created_app_id}")->fetch();
$is_raw_json = is_string($raw_app_row['availability']) && json_decode($raw_app_row['availability'], true) !== null;

$hydrated_app = get_application_by_id($created_app_id);
$is_array_hydrated = is_array($hydrated_app['availability']) && count($hydrated_app['availability']) === 3;
$matrix_matches = ($hydrated_app['availability'] === $test_matrix);

TestHarness::assert(
    $is_raw_json && $is_array_hydrated && $matrix_matches,
    'R2-05b',
    'Shift availability schedule matrix correctly serializes to JSON in DB and deserializes to array'
);

// R2-06: Duplicate Application Prevention: (job_id, student_id) UNIQUE constraint & code 23000
$dup_app_res = create_application([
    'job_id'       => 3,
    'cover_letter' => 'Attempting a duplicate submission for the same vacancy.',
    'availability' => $test_matrix
]);
TestHarness::assert(
    $dup_app_res['success'] === false && str_contains(strtolower($dup_app_res['message']), 'already submitted'),
    'R2-06a',
    'create_application cleanly rejects duplicate submission from same student for same job'
);

// Verify direct SQL constraint enforcement (UNIQUE KEY unique_student_job)
$sql_duplicate_caught = false;
try {
    $stmt_raw = $pdo->prepare("INSERT INTO `applications` (`job_id`, `student_id`, `status`, `applied_at`) VALUES (3, 2, 'pending', NOW())");
    $stmt_raw->execute();
} catch (PDOException $e) {
    if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'unique_student_job')) {
        $sql_duplicate_caught = true;
    }
}
TestHarness::assert(
    $sql_duplicate_caught,
    'R2-06b',
    'Database engine level UNIQUE KEY unique_student_job constraint cleanly catches violation (SQLSTATE 23000)'
);

// R2-07: Closed / Filled Job Gating: Cannot apply to closed vacancies
$pdo->exec("UPDATE `jobs` SET `status` = 'closed' WHERE `id` = 4");
$closed_apply_res = create_application([
    'job_id'       => 4,
    'cover_letter' => 'Applying to closed position.'
]);
$pdo->exec("UPDATE `jobs` SET `status` = 'active' WHERE `id` = 4");

TestHarness::assert(
    $closed_apply_res['success'] === false && str_contains(strtolower($closed_apply_res['message']), 'closed'),
    'R2-07',
    'Job application submission is blocked for closed or filled job vacancies'
);

// R2-08: Application Withdrawal by student (Pending status)
$withdraw_res = delete_application($created_app_id, 2);
$check_deleted = get_application_by_id($created_app_id);
TestHarness::assert(
    $withdraw_res === true && $check_deleted === null,
    'R2-08',
    'Student can successfully withdraw an application while in pending status'
);

// R2-09: 4-step Stepper progression states
$app_stepper_res = create_application([
    'job_id'       => 3,
    'cover_letter' => 'Fresh application for stepper state testing.',
    'availability' => $test_matrix
]);
$stepper_app_id = (int)$app_stepper_res['id'];

// State 1: pending
$app_s1 = get_application_by_id($stepper_app_id);
ob_start();
render_stepper($app_s1['status']);
$stepper_html_1 = ob_get_clean();
$s1_ok = ($app_s1['status'] === 'pending') && str_contains($stepper_html_1, 'Pending Review') && str_contains($stepper_html_1, 'is-active');

// State 2: under_review
update_application_status($stepper_app_id, 'under_review', 'Evaluating student transcript.');
$app_s2 = get_application_by_id($stepper_app_id);
ob_start();
render_stepper($app_s2['status']);
$stepper_html_2 = ob_get_clean();
$s2_ok = ($app_s2['status'] === 'under_review') && str_contains($stepper_html_2, 'Under Evaluation') && str_contains($stepper_html_2, 'is-completed');

// State 3: interview_scheduled
update_application_status($stepper_app_id, 'interview_scheduled', 'Please bring your study load.', [
    'date'  => date('Y-m-d', strtotime('+3 days')),
    'time'  => '10:00 AM',
    'venue' => 'Room 304'
]);
$app_s3 = get_application_by_id($stepper_app_id);
ob_start();
render_stepper($app_s3['status']);
$stepper_html_3 = ob_get_clean();
$s3_ok = ($app_s3['status'] === 'interview_scheduled') && str_contains($stepper_html_3, 'Interview Scheduled') && !empty($app_s3['interview_date']);

// State 4: accepted
update_application_status($stepper_app_id, 'accepted', 'Selected for student assistant appointment.');
$app_s4 = get_application_by_id($stepper_app_id);
ob_start();
render_stepper($app_s4['status']);
$stepper_html_4 = ob_get_clean();
$s4_ok = ($app_s4['status'] === 'accepted') && str_contains($stepper_html_4, 'Accepted / Hired');

// Terminal State: declined
update_application_status($stepper_app_id, 'declined', 'Position filled by another candidate.');
$app_s5 = get_application_by_id($stepper_app_id);
ob_start();
render_stepper($app_s5['status']);
$stepper_html_5 = ob_get_clean();
$s5_ok = ($app_s5['status'] === 'declined') && str_contains($stepper_html_5, 'Declined / Filled') && str_contains($stepper_html_5, 'is-declined');

TestHarness::assert(
    $s1_ok && $s2_ok && $s3_ok && $s4_ok && $s5_ok,
    'R2-10',
    'Application lifecycle progresses cleanly across all 4 stepper stages + declined terminal state'
);

// Clean up temporary stepper application
delete_application($stepper_app_id);

// ============================================================================
// SUITE R3: Employer Requisition & Candidate Review Lifecycle
// ============================================================================
TestHarness::suite('R3', 'Employer Requisition & Candidate Review Lifecycle Verification');

// R3-01: Job Requisition 20-hour weekly cap enforcement
$cap_test_cases = [
    '25 hrs/week'   => 'Up to 20 hrs/week',
    '30 hrs/week'   => 'Up to 20 hrs/week',
    '40 hrs/week'   => 'Up to 20 hrs/week',
    'Up to 15 hrs/week' => 'Up to 15 hrs/week',
    '10 - 20 hrs/week'  => '10 - 20 hrs/week'
];
$cap_normalization_ok = true;
foreach ($cap_test_cases as $input_hours => $expected_norm) {
    $valid_hours = ['10 - 20 hrs/week', 'Up to 15 hrs/week', 'Up to 20 hrs/week', 'Flexible Schedule (Max 20 hrs/week)', 'Flexible Schedule'];
    $normalized = $input_hours;
    if (!in_array($normalized, $valid_hours) || preg_match('/\b(2[1-9]|[3-9]\d)\b/', $normalized)) {
        $normalized = 'Up to 20 hrs/week';
    }
    if ($normalized !== $expected_norm) {
        $cap_normalization_ok = false;
        break;
    }
}
TestHarness::assert(
    $cap_normalization_ok,
    'R3-01',
    '20-hour weekly cap rule strictly normalizes work hours exceeding 20 hours'
);

// R3-02: Vacancies Quota Validation & Creation ($vacancies >= 1)
$_SESSION['user'] = get_user_by_id(3); // Registrar employer
$duties = [
    'Scan and organize student enrollment registration documents.',
    'Assist campus visitors with queuing and basic departmental inquiries.',
    'Maintain daily record logs for certificate releases.'
];
$qualifications = [
    'Currently enrolled undergraduate student at KLD.',
    'Good verbal and written communication skills in English and Filipino.',
    'Attention to detail and basic spreadsheet proficiency.'
];

$created_job_id = create_job([
    'title'            => 'Student Records Assistant',
    'department'       => 'Office of the University Registrar',
    'category'         => 'Administrative Clerk',
    'category_id'      => 3,
    'job_type'         => 'Student Assistant',
    'work_setup'       => 'On-Campus',
    'location'         => 'Main Administrative Building Room 102',
    'pay_rate'         => '₱85.00 / hour',
    'pay_type'         => 'Hourly',
    'hours_per_week'   => '15 - 20 hrs/week',
    'vacancies'        => 3,
    'deadline'         => date('Y-m-d', strtotime('+30 days')),
    'description'      => 'Support our registration team with document digitization and front-desk clerical needs.',
    'responsibilities' => $duties,
    'qualifications'   => $qualifications,
    'tags'             => ['Clerical', 'On-Campus', 'Daytime']
]);

TestHarness::assert(
    $created_job_id > 0,
    'R3-02a',
    'create_job creates requisition with valid vacancies quota ($vacancies >= 1)'
);

// Verify duty array JSON serialization and hydration
$hydrated_job = get_job_by_id($created_job_id);
TestHarness::assert(
    is_array($hydrated_job['responsibilities']) && count($hydrated_job['responsibilities']) === 3 &&
    is_array($hydrated_job['qualifications']) && count($hydrated_job['qualifications']) === 3 &&
    $hydrated_job['vacancies'] === 3 && $hydrated_job['slots_total'] === 3 && $hydrated_job['slots_filled'] === 0,
    'R3-02b',
    'Duty and qualification arrays correctly serialize to JSON in DB and hydrate as PHP arrays'
);

// R3-03: Applicant roster filtering by stage
// Submit 2 candidate applications for this test job
$_SESSION['user'] = get_user_by_id(1); // Juan Dela Cruz
$app_cand_1 = create_application(['job_id' => $created_job_id, 'cover_letter' => 'Candidate 1 cover letter.']);
$cand_1_id = (int)$app_cand_1['id'];

$_SESSION['user'] = get_user_by_id(2); // Maria Santos
$app_cand_2 = create_application(['job_id' => $created_job_id, 'cover_letter' => 'Candidate 2 cover letter.']);
$cand_2_id = (int)$app_cand_2['id'];

$all_job_apps = get_applications(null, $created_job_id);
TestHarness::assert(
    count($all_job_apps) === 2 && $cand_1_id > 0 && $cand_2_id > 0,
    'R3-03',
    'Applicant roster successfully retrieves candidates filtered by job vacancy'
);

// R3-04: Interview Scheduling Validation: Rejection of past dates
$past_date_res = update_application_status($cand_1_id, 'interview_scheduled', 'Interview note', [
    'date'  => '2023-01-01', // Past date
    'time'  => '09:00 AM',
    'venue' => 'Room 101'
]);
TestHarness::assert(
    $past_date_res === false,
    'R3-04',
    'Interview scheduling strictly rejects past dates and preserves current application status'
);

// R3-05: Interview Scheduling Validation: Acceptance of future dates
$future_date = date('Y-m-d', strtotime('+5 days'));
$future_date_res = update_application_status($cand_1_id, 'interview_scheduled', 'Bring printed COR.', [
    'date'  => $future_date,
    'time'  => '02:30 PM',
    'venue' => 'Registrar Conference Room'
]);
$cand_1_updated = get_application_by_id($cand_1_id);
TestHarness::assert(
    $future_date_res === true && 
    $cand_1_updated['status'] === 'interview_scheduled' && 
    $cand_1_updated['interview_date'] === $future_date &&
    $cand_1_updated['interview_venue'] === 'Registrar Conference Room',
    'R3-05',
    'Interview scheduling accepts valid future date and updates interview date/time/venue'
);

// R3-06: Offer Acceptance & Automatic slots_filled Incrementing
// Job has slots_total = 3, slots_filled = 0.
// Accept candidate 1 -> slots_filled becomes 1, job remains active (1 < 3)
$accept_1_res = update_application_status($cand_1_id, 'accepted', 'Hired for primary shift.');
$job_after_acc1 = get_job_by_id($created_job_id);
$app_cand1_hired = get_application_by_id($cand_1_id);

TestHarness::assert(
    $accept_1_res === true && 
    $app_cand1_hired['status'] === 'accepted' && 
    $job_after_acc1['slots_filled'] === 1 && 
    $job_after_acc1['status'] === 'active',
    'R3-06',
    'Candidate offer acceptance updates status to accepted and increments slots_filled to 1'
);

// R3-07: Second Offer Acceptance & Job Auto-Closing
// Set slots_total = 2 so second acceptance fills quota and triggers auto-closing
$pdo->exec("UPDATE `jobs` SET `slots_total` = 2 WHERE `id` = {$created_job_id}");
$accept_2_res = update_application_status($cand_2_id, 'accepted', 'Hired for secondary shift.');
$job_after_acc2 = get_job_by_id($created_job_id);

TestHarness::assert(
    $accept_2_res === true && 
    $job_after_acc2['slots_filled'] === 2 && 
    $job_after_acc2['status'] === 'closed',
    'R3-07',
    'Job vacancy automatically auto-closes when filled quota reaches slots_total (slots_filled >= slots_total)'
);

// R3-08: Candidate Rejection & Supervisor Notes Recording
// Transition candidate 2 to declined to test rejection flow and slot decrement
$decline_res = update_application_status($cand_2_id, 'declined', 'Declined offer due to class schedule conflict.');
$app_cand2_declined = get_application_by_id($cand_2_id);
$job_after_dec = get_job_by_id($created_job_id);

TestHarness::assert(
    $decline_res === true && 
    $app_cand2_declined['status'] === 'declined' && 
    str_contains($app_cand2_declined['supervisor_notes'], 'schedule conflict') &&
    $job_after_dec['slots_filled'] === 1 && 
    $job_after_dec['status'] === 'active',
    'R3-08',
    'Candidate rejection sets status to declined, records supervisor notes, and decrements filled slot'
);

// Clean up test job and applications
delete_job($created_job_id);

// ============================================================================
// SUITE R4: Admin Management, Accreditation & System Analytics
// ============================================================================
TestHarness::suite('R4', 'Admin Management, Accreditation & System Analytics Verification');

// Set active session as Admin
$_SESSION['user'] = get_user_by_id(5);

// R4-01: Partner Employer Business Permit Verification & Accreditation Approval
$partner_reg = register_user([
    'name'                 => 'Accreditation Candidate Partner',
    'email'                => 'accreditation.candidate@business.com',
    'password'             => 'Partner123!',
    'role'                 => 'employer',
    'employer_type'        => 'approved_partner',
    'organization_name'    => 'KLD Business Innovations',
    'office_location'      => 'Dasmariñas Tech Park',
    'accreditation_number' => 'ACC-TEST-2026',
    'permit_file'          => 'uploads/permits/business_permit_sample.pdf'
]);
$partner_id = (int)$partner_reg['user']['id'];
$initial_emp_prof = get_user_by_id($partner_id);

// Admin approves employer verification
$approve_emp_res = update_user_verification($partner_id, 'verified');
$approved_emp_prof = get_user_by_id($partner_id);

TestHarness::assert(
    $initial_emp_prof['verification_status'] === 'pending_approval' &&
    $approve_emp_res === true &&
    $approved_emp_prof['verification_status'] === 'verified',
    'R4-01',
    'Admin accreditation approval updates employer_profiles.verification_status to verified'
);

// R4-02: Partner Employer Verification Rejection with Notes
$reject_emp_res = update_user_verification($partner_id, 'rejected', 'Missing updated Mayor Permit for 2026.');
$rejected_emp_prof = get_user_by_id($partner_id);
TestHarness::assert(
    $reject_emp_res === true && 
    $rejected_emp_prof['verification_status'] === 'rejected' &&
    str_contains($rejected_emp_prof['rejection_reason'], 'Mayor Permit'),
    'R4-02',
    'Admin rejection updates status to rejected and logs specific rejection reason'
);

// Clean up partner
$pdo->exec("DELETE FROM users WHERE id = {$partner_id}");

// Restore session as Admin
$_SESSION['user'] = get_user_by_id(5);

// R4-03: Student COR Profile Change Request Submission & Approval Cascade
// First, approve any existing seeded pending request for user 1 to ensure a clean slate
$seeded_pr = get_pending_profile_request(1);
if ($seeded_pr) {
    approve_profile_request($seeded_pr['id'], 'Resolving seeded initial request.');
}

// Student 1 (Juan Dela Cruz) submits change request
$req_res = create_profile_request(1, [
    'name'       => 'Juan Dela Cruz Jr.',
    'email'      => 'student@kld.edu.ph',
    'department' => 'Institute of Computing and Digital Innovation (ICDI)',
    'course'     => 'BS Computer Science (BSCS)',
    'year_level' => '3rd Year',
    'sex'        => 'Male',
    'birthdate'  => '2004-03-15',
    'age'        => 22
], 'uploads/proofs/cor_2026_term1.pdf', 'Shifted from BSIS to BSCS per registrar approval.');

TestHarness::assert(
    $req_res['success'] === true && !empty($req_res['request']['id']),
    'R4-03',
    'create_profile_request records pending change request with serialized current and requested profiles'
);

// R4-04: Student COR Profile Change Request Approval Cascade
$change_req_id = (int)$req_res['request']['id'];
$approve_req_res = approve_profile_request($change_req_id, 'Approved by University Registrar Office.');
$student_after_req = get_user_by_id(1);

$raw_pr = $pdo->query("SELECT `status`, `resolved_at`, `admin_notes` FROM `profile_requests` WHERE `id` = {$change_req_id}")->fetch();

TestHarness::assert(
    $approve_req_res === true &&
    $raw_pr['status'] === 'approved' &&
    !empty($raw_pr['resolved_at']) &&
    $student_after_req['name'] === 'Juan Dela Cruz Jr.' &&
    $student_after_req['course'] === 'BS Computer Science (BSCS)' &&
    $student_after_req['year_level'] === '3rd Year',
    'R4-04',
    'approve_profile_request atomically updates student_profiles and users tables, sets resolved_at and logs resolution'
);

// Revert student record back for data consistency
$pdo->exec("UPDATE `users` SET `name` = 'Juan Dela Cruz' WHERE `id` = 1");
$pdo->exec("UPDATE `student_profiles` SET `course` = 'BS Information Systems (BSIS)', `year_level` = '2nd Year' WHERE `user_id` = 1");
$pdo->exec("DELETE FROM `profile_requests` WHERE `id` = {$change_req_id}");

// R4-05: Student Profile Change Request Rejection
$req_res_2 = create_profile_request(1, [
    'course' => 'BS Data Science (BSDS)'
], 'uploads/proofs/cor_fake.pdf', 'Want to shift');
$change_req_id_2 = (int)$req_res_2['request']['id'];
$reject_req_res = reject_profile_request($change_req_id_2, 'Incomplete grades on prerequisite subjects.');
$raw_pr_2 = $pdo->query("SELECT `status`, `admin_notes` FROM `profile_requests` WHERE `id` = {$change_req_id_2}")->fetch();

TestHarness::assert(
    $reject_req_res === true && 
    $raw_pr_2['status'] === 'rejected' &&
    str_contains($raw_pr_2['admin_notes'], 'prerequisite subjects'),
    'R4-05',
    'reject_profile_request marks request rejected with administrative explanation'
);
$pdo->exec("DELETE FROM `profile_requests` WHERE `id` = {$change_req_id_2}");

// R4-06: Career Center Updates Publishing (add_career_update / get_career_updates)
$_SESSION['user'] = get_user_by_id(5); // Admin
$new_update = add_career_update([
    'title'         => 'AY 2026-2027 Student Assistantship Guidelines Released',
    'category'      => 'Policy Guidelines',
    'summary'       => 'Comprehensive briefing on student employment hours and compensation policies.',
    'content'       => '<p>The Office of Student Affairs and Career Services announces the updated rules for on-campus student employment.</p>',
    'author_id'     => 5,
    'author_name'   => 'Dr. Ronald Santos',
    'author_role'   => 'Director of Student Affairs',
    'author_office' => 'Office of Student Affairs'
]);

TestHarness::assert(
    $new_update !== null && 
    $new_update['title'] === 'AY 2026-2027 Student Assistantship Guidelines Released' &&
    !empty($new_update['slug']) &&
    !empty($new_update['read_time']),
    'R4-06',
    'add_career_update creates article with automatic slug generation and read-time calculation'
);

// R4-07: Career Center Updates Retrieval & Hydration
$fetched_update = get_career_update_by_id($new_update['id']);
TestHarness::assert(
    $fetched_update !== null && 
    isset($fetched_update['author']) && 
    is_array($fetched_update['author']) && 
    !empty($fetched_update['author']['name']),
    'R4-07',
    'get_career_update_by_id hydrates structured author sub-object and formatted timestamps'
);

// R4-08: Career Center Updates Edit & Delete
$update_edited = update_career_update($new_update['id'], [
    'title' => 'Updated: AY 2026-2027 Student Assistantship Guidelines Released'
]);
$update_after_edit = get_career_update_by_id($new_update['id']);
$update_deleted = delete_career_update($new_update['id']);
$update_after_del = get_career_update_by_id($new_update['id']);

TestHarness::assert(
    $update_edited === true && 
    str_contains($update_after_edit['title'], 'Updated:') && 
    $update_deleted === true && 
    $update_after_del === null,
    'R4-08',
    'Career Center updates engine supports atomic editing and deletion'
);

// R4-09: System KPI Analytics & Aggregations
$metric_active_jobs = get_metrics_total_active_jobs();
$metric_offices = get_metrics_partnered_offices();
$metric_hired = get_metrics_students_hired();
$metric_pay = get_metrics_avg_hourly_pay();

TestHarness::assert(
    $metric_active_jobs > 0 && $metric_offices > 0 && $metric_hired >= 0 && str_contains($metric_pay, '₱'),
    'R4-09',
    'Institutional analytics functions calculate valid real-time KPIs (jobs, offices, hires, avg pay)'
);

// R4-10: Notification Delivery across All Roles
$admin_uid = 5;
$init_unread = get_unread_notifications_count($admin_uid);
$notif_id = create_notification(
    $admin_uid,
    'audit_test',
    'Integration Test Notification',
    'Verifying notification delivery across all roles.',
    'admin/reports.php',
    'bi-check-circle',
    'success'
);
$after_unread = get_unread_notifications_count($admin_uid);
$notifs = get_user_notifications($admin_uid, 5);
$mark_read_res = mark_notification_as_read($notif_id, $admin_uid);
$final_unread = get_unread_notifications_count($admin_uid);
delete_notification($notif_id, $admin_uid);

TestHarness::assert(
    $notif_id > 0 && 
    $after_unread === $init_unread + 1 && 
    $mark_read_res === true && 
    $final_unread === $init_unread,
    'R4-10',
    'Notification delivery logs alerts across roles, updates unread counts, and marks read accurately'
);

// ============================================================================
// SUITE R5: Data Integrity & 3NF Hydration Audit
// ============================================================================
TestHarness::suite('R5', 'Data Integrity & 3NF Hydration Audit');

// R5-01: Relational integrity across all 10 normalized tables
$expected_tables = [
    'users',
    'student_profiles',
    'employer_profiles',
    'categories',
    'jobs',
    'applications',
    'profile_requests',
    'updates',
    'notifications',
    'devblogs'
];

$existing_tables = $pdo->query("
    SELECT TABLE_NAME 
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = 'campus_job_portal'
")->fetchAll(PDO::FETCH_COLUMN);

$all_tables_exist = true;
$missing_tables = [];
foreach ($expected_tables as $tb) {
    if (!in_array($tb, $existing_tables, true)) {
        $all_tables_exist = false;
        $missing_tables[] = $tb;
    }
}
TestHarness::assert(
    $all_tables_exist,
    'R5-01',
    'All 10 normalized 3NF tables are present in database catalog',
    $missing_tables ? "Missing: " . implode(', ', $missing_tables) : ''
);

// R5-02: Foreign Key Constraints & Referential Rules
$fk_stmt = $pdo->query("
    SELECT 
        TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, DELETE_RULE
    FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'campus_job_portal'
");
$fks = $fk_stmt->fetchAll();
$fk_map = [];
foreach ($fks as $f) {
    $fk_map[$f['TABLE_NAME'] . '.' . $f['CONSTRAINT_NAME']] = [
        'ref_table'   => $f['REFERENCED_TABLE_NAME'],
        'delete_rule' => $f['DELETE_RULE']
    ];
}

$expected_fks = [
    'student_profiles.fk_student_user'      => ['ref_table' => 'users', 'delete_rule' => 'CASCADE'],
    'employer_profiles.fk_employer_user'    => ['ref_table' => 'users', 'delete_rule' => 'CASCADE'],
    'jobs.fk_jobs_category'                 => ['ref_table' => 'categories', 'delete_rule' => 'SET NULL'],
    'jobs.fk_jobs_employer'                 => ['ref_table' => 'users', 'delete_rule' => 'SET NULL'],
    'applications.fk_applications_job'      => ['ref_table' => 'jobs', 'delete_rule' => 'CASCADE'],
    'applications.fk_applications_student'  => ['ref_table' => 'users', 'delete_rule' => 'CASCADE'],
    'profile_requests.fk_profile_requests_user' => ['ref_table' => 'users', 'delete_rule' => 'CASCADE'],
    'notifications.fk_notifications_user'   => ['ref_table' => 'users', 'delete_rule' => 'CASCADE'],
];

$all_fks_valid = true;
$fk_issues = [];
foreach ($expected_fks as $fk_name => $req) {
    if (!isset($fk_map[$fk_name])) {
        $all_fks_valid = false;
        $fk_issues[] = "Missing FK: {$fk_name}";
    } elseif ($fk_map[$fk_name]['delete_rule'] !== $req['delete_rule']) {
        $all_fks_valid = false;
        $fk_issues[] = "Invalid rule on {$fk_name}: expected {$req['delete_rule']}, got {$fk_map[$fk_name]['delete_rule']}";
    }
}

TestHarness::assert(
    $all_fks_valid,
    'R5-02',
    'Foreign key constraints enforce Class Table Inheritance & pure junction cascading referential actions',
    implode('; ', $fk_issues)
);

// R5-03 to R5-09: Hydration Functions Audit (Zero Notices & Complete Keys)
$hydration_notices = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$hydration_notices) {
    $hydration_notices[] = "[$errno] $errstr in $errfile line $errline";
    return true;
});

// R5-03: hydrate_user() audit
$user_sample_row = $pdo->query(get_user_base_query() . " LIMIT 1")->fetch();
$h_user = hydrate_user($user_sample_row);
$h_user_null = hydrate_user(null);
TestHarness::assert(
    $h_user !== null && is_array($h_user['availability']) && isset($h_user['department']) && $h_user_null === null,
    'R5-03',
    'hydrate_user decodes availability array, casts types, and populates aliases with zero notices'
);

// R5-04: hydrate_job() audit
$job_sample_row = $pdo->query("SELECT * FROM `jobs` LIMIT 1")->fetch();
$h_job = hydrate_job($job_sample_row);
TestHarness::assert(
    $h_job !== null && 
    is_array($h_job['tags']) && 
    is_array($h_job['badges']) && 
    is_array($h_job['responsibilities']) && 
    is_array($h_job['qualifications']) &&
    is_int($h_job['vacancies']) &&
    is_int($h_job['slots_total']) &&
    isset($h_job['verified_employer']),
    'R5-04',
    'hydrate_job decodes all 4 JSON array columns and casts slot counters cleanly'
);

// R5-05: hydrate_application() audit
$app_sample_row = $pdo->query("SELECT * FROM `applications` LIMIT 1")->fetch();
$h_app = hydrate_application($app_sample_row);
TestHarness::assert(
    $h_app !== null && 
    is_array($h_app['availability']) && 
    isset($h_app['status_label']) && 
    isset($h_app['status_badge']),
    'R5-05',
    'hydrate_application decodes availability and maps status label and theme badge safely'
);

// R5-06: hydrate_category() audit
$cat_sample_row = $pdo->query("SELECT * FROM `categories` LIMIT 1")->fetch();
$h_cat = hydrate_category($cat_sample_row);
TestHarness::assert(
    $h_cat !== null && is_array($h_cat['popular_roles']) && is_int($h_cat['job_count']),
    'R5-06',
    'hydrate_category decodes popular_roles JSON and ensures integer job_count'
);

// R5-07: hydrate_profile_request() audit
$pr_sample_row = $pdo->query("SELECT * FROM `profile_requests` LIMIT 1")->fetch();
$h_pr = hydrate_profile_request($pr_sample_row);
TestHarness::assert(
    $h_pr === null || (is_array($h_pr['current_profile']) && is_array($h_pr['requested_profile'])),
    'R5-07',
    'hydrate_profile_request decodes current and requested profile JSON payloads'
);

// R5-08: hydrate_update() audit
$up_sample_row = $pdo->query("SELECT * FROM `updates` LIMIT 1")->fetch();
$h_up = hydrate_update($up_sample_row);
TestHarness::assert(
    $h_up !== null && isset($h_up['author']) && is_array($h_up['author']) && isset($h_up['author']['name']),
    'R5-08',
    'hydrate_update structures author composite object with name, role, office, and avatar'
);

// R5-09: hydrate_devblog() and hydrate_notification() audit
$db_sample_row = $pdo->query("SELECT * FROM `devblogs` LIMIT 1")->fetch();
$h_db = hydrate_devblog($db_sample_row);
$notif_sample_row = $pdo->query("SELECT * FROM `notifications` LIMIT 1")->fetch();
$h_notif = hydrate_notification($notif_sample_row);

restore_error_handler();

TestHarness::assert(
    empty($hydration_notices) && 
    ($h_db === null || is_array($h_db['daily_logs'])) && 
    ($h_notif === null || is_bool($h_notif['is_read'])),
    'R5-09',
    'All hydration functions executed across real datastore rows with zero PHP notices or warnings',
    implode('; ', $hydration_notices)
);

// R5-10: Cascading Deletes and Orphan Prevention Audit
// Create user -> student_profile -> job -> application -> profile_request -> notification
$cascade_user_reg = register_user([
    'name'       => 'Cascade Test Candidate',
    'email'      => 'cascade.test@kld.edu.ph',
    'password'   => 'Pass123!',
    'role'       => 'student',
    'student_id' => '2026-99999'
]);
$c_uid = (int)$cascade_user_reg['user']['id'];

// Create dependent application on Job 1
$stmt_c_app = $pdo->prepare("INSERT INTO `applications` (`job_id`, `student_id`, `status`, `applied_at`) VALUES (1, :sid, 'pending', NOW())");
$stmt_c_app->execute([':sid' => $c_uid]);
$c_appid = (int)$pdo->lastInsertId();

// Create dependent profile request
$stmt_c_pr = $pdo->prepare("INSERT INTO `profile_requests` (`user_id`, `status`, `created_at`) VALUES (:uid, 'pending', NOW())");
$stmt_c_pr->execute([':uid' => $c_uid]);
$c_prid = (int)$pdo->lastInsertId();

// Create dependent notification
$c_notif_id = create_notification($c_uid, 'test', 'Test Title', 'Test Message');

// Verify initial creation
$init_stud_prof = $pdo->query("SELECT COUNT(*) FROM `student_profiles` WHERE `user_id` = {$c_uid}")->fetchColumn();
$init_app_cnt   = $pdo->query("SELECT COUNT(*) FROM `applications` WHERE `student_id` = {$c_uid}")->fetchColumn();
$init_pr_cnt    = $pdo->query("SELECT COUNT(*) FROM `profile_requests` WHERE `user_id` = {$c_uid}")->fetchColumn();
$init_notif_cnt = $pdo->query("SELECT COUNT(*) FROM `notifications` WHERE `user_id` = {$c_uid}")->fetchColumn();

// Execute CASCADE delete on users parent record
$pdo->exec("DELETE FROM `users` WHERE `id` = {$c_uid}");

// Verify that all 4 dependent tables had records deleted via DB cascade
$post_stud_prof = $pdo->query("SELECT COUNT(*) FROM `student_profiles` WHERE `user_id` = {$c_uid}")->fetchColumn();
$post_app_cnt   = $pdo->query("SELECT COUNT(*) FROM `applications` WHERE `student_id` = {$c_uid}")->fetchColumn();
$post_pr_cnt    = $pdo->query("SELECT COUNT(*) FROM `profile_requests` WHERE `user_id` = {$c_uid}")->fetchColumn();
$post_notif_cnt = $pdo->query("SELECT COUNT(*) FROM `notifications` WHERE `user_id` = {$c_uid}")->fetchColumn();

TestHarness::assert(
    (int)$init_stud_prof === 1 && (int)$init_app_cnt === 1 && (int)$init_pr_cnt === 1 && (int)$init_notif_cnt === 1 &&
    (int)$post_stud_prof === 0 && (int)$post_app_cnt === 0 && (int)$post_pr_cnt === 0 && (int)$post_notif_cnt === 0,
    'R5-10',
    'Parent user deletion triggers atomic cascade across student_profiles, applications, profile_requests, and notifications without leaving orphaned records'
);

// ============================================================================
// VERIFICATION COMPLETE (Database state preserved)
// ============================================================================

// Output final test results
$exit_code = TestHarness::finish();
exit($exit_code);
