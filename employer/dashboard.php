<?php
/**
 * Campus Job Posting System - Employer / Department Dashboard
 * Archetype B: Employer Portal & Requisitions Hub (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$page_title = 'Employer & Department Dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security validation failed: Invalid or expired security token.');
        header('Location: dashboard.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $job_id = (int)($_POST['job_id'] ?? 0);

    if ($action === 'archive_job') {
        if ($job_id > 0 && archive_job($job_id, $user)) {
            set_flash('warning', "Job requisition #{$job_id} has been moved to Archive. All applicant records and evaluations have been safely preserved.");
        } else {
            set_flash('danger', 'Failed to archive requisition or unauthorized access.');
        }
        header('Location: dashboard.php');
        exit;
    } elseif ($action === 'restore_job') {
        // Enforce: Admin ONLY has restore privileges
        if (($user['role'] ?? '') !== 'admin') {
            set_flash('danger', 'Access Restricted: Only University Administrators are authorized to restore archived requisitions.');
            header('Location: dashboard.php');
            exit;
        }

        if ($job_id > 0 && restore_job($job_id, $user)) {
            set_flash('success', "Job requisition #{$job_id} has been restored to active status!");
        } else {
            set_flash('danger', 'Failed to restore requisition.');
        }
        header('Location: dashboard.php?tab=archived');
        exit;
    }
}

// Filter jobs by this employer/department
$dept = $user['organization_name'] ?? ($user['department'] ?? 'Office of the University Registrar');
$emp_id_filter = ($user['role'] === 'admin') ? null : (int)$user['id'];

$tab = $_GET['tab'] ?? 'active';
$is_archived_view = ($tab === 'archived');
$active_dept_jobs = get_jobs(null, null, null, null, null, null, null, $emp_id_filter, false, false);
$archived_dept_jobs = get_jobs(null, null, null, null, null, null, null, $emp_id_filter, true, true);
$all_dept_jobs = $is_archived_view ? $archived_dept_jobs : $active_dept_jobs;
$dept_apps = get_applications(null, null, null, $emp_id_filter);

$active_jobs_count = count(array_filter($active_dept_jobs, fn($j) => in_array($j['status'] ?? '', ['active', 'Active'])));
$archived_jobs_count = count($archived_dept_jobs);
$total_applicants_count = count($dept_apps);
$interview_count = count(array_filter($dept_apps, fn($a) => in_array($a['status'] ?? '', ['interview_scheduled', 'Interview Scheduled'])));
$hired_count = count(array_filter($dept_apps, fn($a) => in_array($a['status'] ?? '', ['accepted', 'Accepted / Hired'])));

// Pre-calculate applicant count per vacancy to display on action buttons
$job_applicant_counts = [];
foreach ($dept_apps as $a) {
    $jid = (int)($a['job_id'] ?? 0);
    $job_applicant_counts[$jid] = ($job_applicant_counts[$jid] ?? 0) + 1;
}

$is_partner = ($user['employer_type'] ?? '') === 'approved_partner';


$org_name = $user['organization_name'] ?? ($user['department'] ?? 'Campus Organization');
$accreditation = $user['accreditation_number'] ?? ($is_partner ? 'MOA-VERIFIED' : 'INTERNAL-UNIV');
// Last line: view template
require __DIR__ . '/../includes/templates/employer-dashboard-view.php';

