<?php
declare(strict_types=1);

/**
 * Unit & Integration Test for ApplicationService & RequisitionEligibility Module
 */

require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/services/application-service.php';

echo "=====================================================\n";
echo "APPLICATION SERVICE DEEP MODULE TEST RUNNER\n";
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

// 1. Requisition Status Gating
$closedJob = [
    'id' => 101,
    'title' => 'Lab Assistant',
    'status' => 'closed',
    'deadline' => date('Y-m-d', strtotime('+7 days')),
    'slots_total' => 2,
    'slots_filled' => 0
];
$e1 = ApplicationService::checkEligibility($closedJob, null);
assert_test(!$e1->isAllowed(), 'Closed job is not allowed for applications');
assert_test($e1->reason() === 'closed', 'Reason code is "closed"');
assert_test($e1->badge()['icon'] === 'bi-slash-circle', 'Badge renders closed icon');

// 2. Deadline Gating
$expiredJob = [
    'id' => 102,
    'title' => 'Tutor',
    'status' => 'active',
    'deadline' => '2020-01-01',
    'slots_total' => 2,
    'slots_filled' => 0
];
$e2 = ApplicationService::checkEligibility($expiredJob, null);
assert_test(!$e2->isAllowed(), 'Expired job is not allowed for applications');
assert_test($e2->reason() === 'deadline_passed', 'Reason code is "deadline_passed"');

// 3. Vacancy Slot Capacity Gating
$filledJob = [
    'id' => 103,
    'title' => 'Library Aide',
    'status' => 'active',
    'deadline' => date('Y-m-d', strtotime('+7 days')),
    'slots_total' => 3,
    'slots_filled' => 3
];
$e3 = ApplicationService::checkEligibility($filledJob, null);
assert_test(!$e3->isAllowed(), 'Filled job is not allowed for applications');
assert_test($e3->reason() === 'slots_filled', 'Reason code is "slots_filled"');

// 4. Role Authorization Gating
$openJob = [
    'id' => 104,
    'title' => 'Campus Guide',
    'status' => 'active',
    'deadline' => date('Y-m-d', strtotime('+7 days')),
    'slots_total' => 5,
    'slots_filled' => 1
];
$employerUser = ['id' => 201, 'role' => 'employer', 'name' => 'Dean Office'];
$e4 = ApplicationService::checkEligibility($openJob, $employerUser);
assert_test(!$e4->isAllowed(), 'Employer cannot apply for student vacancy');
assert_test($e4->reason() === 'unauthorized_role', 'Reason code is "unauthorized_role"');

// 5. Unverified Student Administrative Status Gating
$unverifiedStudent = [
    'id' => 301,
    'role' => 'student',
    'name' => 'Juan Dela Cruz',
    'verification_status' => 'pending'
];
$e5 = ApplicationService::checkEligibility($openJob, $unverifiedStudent);
assert_test(!$e5->isAllowed(), 'Unverified student cannot apply before admin approval');
assert_test($e5->reason() === 'unverified_student', 'Reason code is "unverified_student"');

// 6. Valid Eligible Student
$verifiedStudent = [
    'id' => 302,
    'role' => 'student',
    'name' => 'Maria Santos',
    'verification_status' => 'verified'
];
$e6 = ApplicationService::checkEligibility($openJob, $verifiedStudent);
assert_test($e6->isAllowed(), 'Verified student is eligible for active unfilled job');
assert_test($e6->reason() === null, 'Reason code is null when allowed');
assert_test($e6->badge()['label'] === 'Open for Applications', 'Badge renders open label');

// 7. Atomic Existence & Count Queries
$count = ApplicationService::getApplicantCount(99999);
assert_test(is_int($count) && $count >= 0, 'getApplicantCount returns integer count without row hydration');

$applied = ApplicationService::hasStudentApplied(99999, 99999);
assert_test(is_bool($applied), 'hasStudentApplied returns boolean');

// 8. Applicant Search & Filter Encapsulation
$searchResults = ApplicationService::search([
    'status' => 'pending',
    'search' => 'NonExistentCandidateNameXYZ'
]);
assert_test(is_array($searchResults), 'ApplicationService::search returns array');
assert_test(empty($searchResults), 'Search filter excludes non-matching candidates');

// 9. RequisitionEligibility Value Object Integrity
$customEligibility = new RequisitionEligibility(false, 'already_applied', 'Active application exists', 'under_review');
assert_test(!$customEligibility->isAllowed(), 'Custom eligibility reports false');
assert_test($customEligibility->applicationStatus() === 'under_review', 'Reports applicationStatus');
assert_test($customEligibility->badge()['icon'] === 'bi-check-circle-fill', 'Reports active application badge');

// 10. Already Employed Eligibility & Badge Verification
$employedEligibility = new RequisitionEligibility(false, 'already_employed', 'Candidate is currently employed', 'accepted');
assert_test(!$employedEligibility->isAllowed(), 'Already employed student is blocked from applying');
assert_test($employedEligibility->reason() === 'already_employed', 'Reason code is "already_employed"');
assert_test($employedEligibility->badge()['icon'] === 'bi-briefcase-fill', 'Reports currently employed briefcase badge');
assert_test($employedEligibility->badge()['label'] === 'Currently Employed', 'Badge label is "Currently Employed"');

// 11. Student Placement Summary Service Method
$summary = ApplicationService::getStudentPlacementSummary(99999);
assert_test(is_array($summary), 'getStudentPlacementSummary returns array structure');
assert_test(isset($summary['is_employed']) && is_bool($summary['is_employed']), 'Summary reports boolean is_employed');
assert_test(isset($summary['other_total_count']) && is_int($summary['other_total_count']), 'Summary reports integer other_total_count');

echo "=====================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "=====================================================\n";

exit($failCount === 0 ? 0 : 1);
