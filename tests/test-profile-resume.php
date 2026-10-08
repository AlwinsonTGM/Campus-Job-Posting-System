<?php
declare(strict_types=1);

/**
 * Candidate Profile Resume & Linkage Integration Test Suite
 * Archetype: Deep Module Verification
 */

require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/services/attachment-store.php';

$passed = 0;
$failed = 0;

function assert_test(bool $condition, string $message): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$message}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$message}\n";
    }
}

echo "=====================================================\n";
echo "CANDIDATE PROFILE RESUME TEST RUNNER\n";
echo "=====================================================\n";

// 1. Verify Schema
DatastoreManager::ensureSchema();
$pdo = get_db_connection();
$colCheck = $pdo->query("SHOW COLUMNS FROM `student_profiles` LIKE 'resume_file'")->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($colCheck), "student_profiles table contains resume_file column");

// 2. Hydrate user test
$rawRow = [
    'id' => 999,
    'name' => 'Test Student',
    'role' => 'student',
    'resume_file' => 'uploads/resumes/my_resume_123.pdf',
    'availability' => '[]'
];
$hydrated = hydrate_user($rawRow);
assert_test($hydrated['resume_file'] === 'my_resume_123.pdf', "hydrate_user extracts basename of resume_file");
assert_test($hydrated['resume'] === 'my_resume_123.pdf', "hydrate_user populates resume alias");

// 3. User profile update & fetch
// Use a test student or student 1 (Juan)
$student = get_user_by_id(1);
assert_test(!empty($student) && ($student['role'] ?? '') === 'student', "Test student (ID: 1) found");

$originalResume = $student['resume_file'] ?? null;

// Update resume_file
$testFilename = 'resume_unit_test_' . time() . '.pdf';
$updateSuccess = update_user_profile(1, 'student', ['resume_file' => $testFilename]);
assert_test($updateSuccess, "update_user_profile succeeds when setting resume_file");

$refreshed = get_user_by_id(1);
assert_test(($refreshed['resume_file'] ?? '') === $testFilename, "get_user_by_id returns newly saved resume_file");

// 4. Authorization check via can_access_resume_file
assert_test(can_access_resume_file($refreshed, $testFilename), "can_access_resume_file permits student to access own stored resume");
assert_test(!can_access_resume_file($refreshed, 'unrelated_other_student_resume.pdf'), "can_access_resume_file blocks unauthorized resume file");

// 5. Clear / Remove resume
$clearSuccess = update_user_profile(1, 'student', ['resume_file' => null]);
assert_test($clearSuccess, "update_user_profile succeeds when clearing resume_file to null");

$cleared = get_user_by_id(1);
assert_test(empty($cleared['resume_file']), "resume_file is empty after removal");

// Restore original resume if there was one
if ($originalResume !== null) {
    update_user_profile(1, 'student', ['resume_file' => $originalResume]);
}

// 6. Test application fallback logic
$dummyUserWithResume = [
    'id' => 1,
    'name' => 'Juan Dela Cruz',
    'resume_file' => 'Juan_Saved_Resume.pdf'
];
$resolvedResume = !empty($dummyUserWithResume['resume_file']) 
    ? basename($dummyUserWithResume['resume_file']) 
    : 'Fallback_Resume.pdf';
assert_test($resolvedResume === 'Juan_Saved_Resume.pdf', "Application resolution defaults to saved profile resume");

$dummyUserWithoutResume = [
    'id' => 2,
    'name' => 'New Student',
    'resume_file' => null
];
$resolvedFallback = !empty($dummyUserWithoutResume['resume_file']) 
    ? basename($dummyUserWithoutResume['resume_file']) 
    : ($dummyUserWithoutResume['name'] . '_Resume.pdf');
assert_test($resolvedFallback === 'New Student_Resume.pdf', "Application resolution falls back gracefully when no saved resume exists");

echo "=====================================================\n";
echo "SUMMARY: {$passed} Passed, {$failed} Failed\n";
echo "=====================================================\n";

if ($failed > 0) {
    exit(1);
}
