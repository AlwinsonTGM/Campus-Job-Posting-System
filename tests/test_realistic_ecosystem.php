<?php
declare(strict_types=1);

/**
 * Verification Test Suite for 1,000 Realistic Users & Ecosystem Integrity
 * File: tests/test_realistic_ecosystem.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/data-helper.php';

$passCount = 0;
$failCount = 0;

function assert_test(bool $cond, string $id, string $desc, string $extra = ''): void {
    global $passCount, $failCount;
    if ($cond) {
        $passCount++;
        echo "  \033[32m[PASS]\033[0m \033[1m{$id}\033[0m: {$desc}\n";
    } else {
        $failCount++;
        echo "  \033[31m[FAIL]\033[0m \033[1m{$id}\033[0m: {$desc}\n";
        if ($extra) echo "         Diagnostic: {$extra}\n";
    }
}

echo "\n======================================================\n";
echo "1,000 REALISTIC USERS & ECOSYSTEM VERIFICATION SUITE\n";
echo "======================================================\n\n";

$pdo = get_db_connection();

// 1. User & Demographic Totals
echo ">>> 1. Population & Demographics\n";
$userCount = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
assert_test($userCount >= 1000, 'POP-01', "At least 1,000 users in database (actual: {$userCount})");

$studentCount = (int)$pdo->query("SELECT COUNT(*) FROM `student_profiles`")->fetchColumn();
assert_test($studentCount >= 839, 'POP-02', "At least 839 student profiles (actual: {$studentCount})");

$employerCount = (int)$pdo->query("SELECT COUNT(*) FROM `employer_profiles`")->fetchColumn();
assert_test($employerCount >= 160, 'POP-03', "At least 160 employer profiles (actual: {$employerCount})");

$adminCount = (int)$pdo->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'admin'")->fetchColumn();
assert_test($adminCount === 1, 'POP-04', "Exactly 1 Super Administrator account (actual: {$adminCount})");

// 2. Official KLD Institutes Integrity
echo "\n>>> 2. KLD Academic Taxonomy Coverage\n";
$instRows = $pdo->query("SELECT DISTINCT `department` FROM `student_profiles`")->fetchAll(PDO::FETCH_COLUMN);
$expectedInstitutes = [
    'Institute of Computing and Digital Innovation (ICDI)',
    'Institute of Engineering (IE)',
    'Institute of Nursing (IN)',
    'Institute of Medical Laboratory Science (IMLS)',
    'Institute of Midwifery (IM)',
    'Institute of Science and Mathematics (ISM)',
    'Institute of Behavioral Sciences (IBS)',
    'Institute of Governance and Development Studies (IGDS)'
];
$missingInst = array_diff($expectedInstitutes, $instRows);
assert_test(empty($missingInst), 'TAX-01', "All 8 official KLD institutes represented", implode(', ', $missingInst));

// 3. Student ID & Year Progression Regex
echo "\n>>> 3. Student ID Regex & Chronology\n";
$invalidSids = $pdo->query("SELECT COUNT(*) FROM `student_profiles` WHERE `student_id` NOT REGEXP '^[0-9]{4}-[12]-[0-9]{6}$'")->fetchColumn();
assert_test((int)$invalidSids === 0, 'SID-01', "All 839 student IDs strictly follow YYYY-T-XXXXXX format");

// 4. Vacancy Conservation Math
echo "\n>>> 4. Mathematical Vacancy Conservation & Zero Over-Hiring\n";
$jobs = $pdo->query("SELECT `id`, `title`, `slots_total`, `slots_filled`, `vacancies`, `status` FROM `jobs`")->fetchAll(PDO::FETCH_ASSOC);
$mathViolations = 0;
$overHireViolations = 0;
$statusViolations = 0;

foreach ($jobs as $j) {
    // Exact count of accepted applications
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `applications` WHERE `job_id` = :jid AND `status` = 'accepted'");
    $stmt->execute([':jid' => $j['id']]);
    $realAccepted = (int)$stmt->fetchColumn();
    
    if ((int)$j['slots_filled'] !== $realAccepted) {
        $mathViolations++;
    }
    if ((int)$j['slots_total'] !== ((int)$j['slots_filled'] + (int)$j['vacancies'])) {
        $mathViolations++;
    }
    if ((int)$j['slots_filled'] > (int)$j['slots_total']) {
        $overHireViolations++;
    }
    if ((int)$j['vacancies'] === 0 && !in_array($j['status'], ['filled', 'closed'], true)) {
        $statusViolations++;
    }
}
assert_test($mathViolations === 0, 'MATH-01', "slots_total = slots_filled + vacancies across all jobs", "Violations: {$mathViolations}");
assert_test($overHireViolations === 0, 'MATH-02', "Zero over-hiring across all jobs (slots_filled <= slots_total)");
assert_test($statusViolations === 0, 'MATH-03', "All jobs with vacancies = 0 are marked 'filled' or 'closed'");

// 5. Authentic Document Files Integrity
echo "\n>>> 5. Physical PDF Document Files on Disk\n";
$sampleProofs = $pdo->query("SELECT `registration_proof` FROM `student_profiles` LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
$missingProofs = 0;
$badProofSize = 0;
foreach ($sampleProofs as $p) {
    $fullPath = dirname(__DIR__) . '/' . ltrim((string)$p, '/');
    if (!file_exists($fullPath)) {
        $missingProofs++;
    } elseif (filesize($fullPath) < 3000) {
        $badProofSize++;
    }
}
assert_test($missingProofs === 0 && $badProofSize === 0, 'DOC-01', "Sample COR PDFs exist on disk and exceed 3KB (high fidelity)");

$sampleResumes = $pdo->query("SELECT `resume_file` FROM `student_profiles` LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
$missingResumes = 0;
$badResumeSize = 0;
foreach ($sampleResumes as $r) {
    $fullPath = dirname(__DIR__) . '/uploads/resumes/' . basename((string)$r);
    if (!file_exists($fullPath)) {
        $missingResumes++;
    } elseif (filesize($fullPath) < 2500) {
        $badResumeSize++;
    }
}
assert_test($missingResumes === 0 && $badResumeSize === 0, 'DOC-02', "Sample Resume PDFs exist on disk and exceed 2.5KB (high fidelity)");

$samplePermits = $pdo->query("SELECT `business_permit` FROM `employer_profiles` LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);
$missingPermits = 0;
foreach ($samplePermits as $pm) {
    $fullPath = dirname(__DIR__) . '/' . ltrim((string)$pm, '/');
    if (!file_exists($fullPath) || filesize($fullPath) < 2000) {
        $missingPermits++;
    }
}
assert_test($missingPermits === 0, 'DOC-03', "Sample BIR 2303 & Mayor's Permit PDFs exist on disk and exceed 2KB");

// 6. Detailed Notes & Rejection Reasons Integrity
echo "\n>>> 6. Contextual Remarks & Rejection Reason Integrity\n";
$declinedWithoutNotes = (int)$pdo->query("SELECT COUNT(*) FROM `applications` WHERE `status` = 'declined' AND (`supervisor_notes` IS NULL OR TRIM(`supervisor_notes`) = '')")->fetchColumn();
assert_test($declinedWithoutNotes === 0, 'NOTE-01', "100% of declined applications have detailed department feedback notes");

$interviewWithoutNotes = (int)$pdo->query("SELECT COUNT(*) FROM `applications` WHERE `status` = 'interview_scheduled' AND (`supervisor_notes` IS NULL OR TRIM(`supervisor_notes`) = '')")->fetchColumn();
assert_test($interviewWithoutNotes === 0, 'NOTE-02', "100% of interview-scheduled applications have supervisor remarks / venue");

$rejectedStudentsWithoutReason = (int)$pdo->query("SELECT COUNT(*) FROM `student_profiles` WHERE `verification_status` = 'rejected' AND (`rejection_reason` IS NULL OR TRIM(`rejection_reason`) = '')")->fetchColumn();
assert_test($rejectedStudentsWithoutReason === 0, 'NOTE-03', "100% of rejected student profiles have specific rejection reason");

$rejectedEmployersWithoutReason = (int)$pdo->query("SELECT COUNT(*) FROM `employer_profiles` WHERE `verification_status` = 'rejected' AND (`rejection_reason` IS NULL OR TRIM(`rejection_reason`) = '')")->fetchColumn();
assert_test($rejectedEmployersWithoutReason === 0, 'NOTE-04', "100% of rejected employer profiles have specific rejection reason");

// 7. Admin Hierarchy & Role Clarity
echo "\n>>> 7. Administrator Role Hierarchy\n";
$superAdmin = $pdo->query("SELECT `email`, `name` FROM `users` WHERE `id` = 5 AND `role` = 'admin'")->fetch(PDO::FETCH_ASSOC);
assert_test($superAdmin && $superAdmin['email'] === 'admin@kld.edu.ph', 'ADM-01', "Super Administrator ID 5 is admin@kld.edu.ph");

$otherAdminsCount = (int)$pdo->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'admin' AND `id` != 5")->fetchColumn();
assert_test($otherAdminsCount === 0, 'ADM-02', "Only 1 Super Administrator exists (no extra/redundant admin accounts)");

// 8. Authentication & Bcrypt Verification
echo "\n>>> 8. Dynamic Authentication Verification\n";
$sampleAdmin = $pdo->query("SELECT `password` FROM `users` WHERE `email` = 'admin@kld.edu.ph'")->fetch();
assert_test(password_verify('AdminKld2026!', $sampleAdmin['password']), 'AUTH-01', "Admin password verifies with AdminKld2026!");

$sampleStudent = $pdo->query("SELECT `password` FROM `users` WHERE `role` = 'student' LIMIT 1")->fetch();
assert_test(password_verify('Student2026!', $sampleStudent['password']), 'AUTH-02', "Student password verifies with Student2026!");

$sampleEmployer = $pdo->query("SELECT `password` FROM `users` WHERE `role` = 'employer' LIMIT 1")->fetch();
assert_test(password_verify('Partner2026!', $sampleEmployer['password']), 'AUTH-03', "Employer password verifies with Partner2026!");

// 9. JSON Datastore Parity
echo "\n>>> 9. JSON Datastore Synchronization Parity\n";
$jsonUsers = json_decode(file_get_contents(dirname(__DIR__) . '/data/users.json'), true);
assert_test(is_array($jsonUsers) && count($jsonUsers) === $userCount, 'JSON-01', "data/users.json matches MySQL user count ({$userCount})");

$jsonJobs = json_decode(file_get_contents(dirname(__DIR__) . '/data/jobs.json'), true);
assert_test(is_array($jsonJobs) && count($jsonJobs) === count($jobs), 'JSON-02', "data/jobs.json matches MySQL job count (" . count($jobs) . ")");

// 10. Archiving / Soft-Delete Behavior
echo "\n>>> 10. Archiving & Soft-Delete User Count Behavior\n";
$websiteUsersBefore = count(get_all_users());
assert_test($websiteUsersBefore === $userCount, 'ARCH-01', "Website active user count before archiving matches total users ({$userCount})");

suspend_user(1000);
$websiteUsersAfter = count(get_all_users());
$dbUser1000 = $pdo->query("SELECT `is_archived`, `status` FROM `users` WHERE `id` = 1000")->fetch(PDO::FETCH_ASSOC);

assert_test($websiteUsersAfter === ($userCount - 1), 'ARCH-02', "Archived user is soft-deleted from website user count (" . ($userCount - 1) . " remaining)");
assert_test((int)$dbUser1000['is_archived'] === 1 && $dbUser1000['status'] === 'suspended', 'ARCH-03', "Archived user remains intact in MySQL database with is_archived = 1 and status = 'suspended'");

reactivate_user(1000);
$websiteUsersRestored = count(get_all_users());
assert_test($websiteUsersRestored === $userCount, 'ARCH-04', "Reactivating user restores website user count to {$userCount}");

// 11. Verification Queue Integrity
echo "\n>>> 11. Interactive Verification Queue Integrity\n";
$deckCount = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM users u
    LEFT JOIN student_profiles sp ON u.id = sp.user_id
    LEFT JOIN employer_profiles ep ON u.id = ep.user_id
    WHERE (u.verification_status = 'pending_approval' OR sp.verification_status = 'pending_approval' OR ep.verification_status = 'pending_approval')
      AND (u.is_archived = 0 OR u.is_archived IS NULL)
")->fetchColumn();
assert_test($deckCount >= 20, 'QUEUE-01', "Verification deck has pending accounts ready for testing (actual: {$deckCount})");

$pendingKldCount = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users u
    LEFT JOIN student_profiles sp ON u.id = sp.user_id
    LEFT JOIN employer_profiles ep ON u.id = ep.user_id
    WHERE (u.verification_status = 'pending_approval' OR sp.verification_status = 'pending_approval' OR ep.verification_status = 'pending_approval')
      AND u.email LIKE '%@kld.edu.ph'
")->fetchColumn();
assert_test($pendingKldCount === 0, 'PEND-01', "Zero pending accounts use @kld.edu.ph institutional email (all use personal/external domains)");

// Summary
echo "\n======================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "======================================================\n\n";

exit($failCount === 0 ? 0 : 1);
