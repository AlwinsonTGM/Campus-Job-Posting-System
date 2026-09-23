<?php
/**
 * Campus Job Posting System - Job Opportunity Details
 * Archetype C: Detail & Action Sidebar (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$job_id = $_GET['id'] ?? ($_GET['job_id'] ?? null);
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The requested opportunity could not be found or has been closed.');
    header('Location: jobs.php');
    exit;
}

$user = get_logged_user();
$already_applied = false;
if ($user) {
    $my_apps = get_applications($user['id'] ?? 0);
    foreach ($my_apps as $a) {
        if ((int)($a['job_id'] ?? 0) === (int)$job['id']) {
            $already_applied = true;
            $app_status = $a['status'] ?? 'pending';
            break;
        }
    }
}

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

