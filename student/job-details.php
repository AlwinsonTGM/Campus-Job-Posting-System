<?php
/**
 * Campus Job Posting System - Job Opportunity Details
 * Archetype C: Detail & Action Sidebar (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$job_id = query_int('id') ?? query_int('job_id');
$job = get_job_by_id($job_id);
$user = get_logged_user();

if (!$job || (!empty($job['is_archived']) && !in_array($user['role'] ?? '', ['admin', 'employer']))) {
    set_flash('danger', 'The requested opportunity could not be found or has been archived.');
    header('Location: jobs.php');
    exit;
}

$eligibility = ApplicationService::checkEligibility($job, $user);
$already_applied = ($eligibility->reason() === 'already_applied');
$already_employed = ($eligibility->reason() === 'already_employed');
$active_placement = $already_employed ? $eligibility->existingApplication() : null;
$app_status = $eligibility->applicationStatus() ?? 'pending';

// Student schedule compatibility overview (read-only, never gates the Apply CTA).
// Guests see a neutral signed-out state; closed/expired/filled jobs hide the panel in the view.
// Applicant demand is counted live so competition reflects real contention, not fill state.
$fit_student = $user ?? ['id' => null, 'availability' => []];
$job_applicant_count = ApplicationService::getApplicantCount((int)($job['id'] ?? 0));
$student_schedule_fit = get_student_schedule_fit($fit_student, $job, $job_applicant_count);

$is_partner = ($job['employer_type'] ?? '') === 'approved_partner';
$org_name = $job['organization_name'] ?? ($job['department'] ?? 'Campus Organization');
$jtype = $job['job_type'] ?? 'Student Assistant';
$wsetup = $job['work_setup'] ?? 'On-Campus';
$slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
$slots_filled = (int)($job['slots_filled'] ?? 0);
$pct = ($slots_total > 0) ? round(($slots_filled / $slots_total) * 100) : 0;

$page_title = $job['title'] . ' | Opportunity Details';
// Last line: view template
require __DIR__ . '/../includes/templates/student-job-details-view.php';

