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

// Filter jobs by this employer/department
$dept = $user['organization_name'] ?? ($user['department'] ?? 'Office of the University Registrar');
$emp_id_filter = ($user['role'] === 'admin') ? null : (int)$user['id'];
$all_dept_jobs = get_jobs(null, null, null, null, null, null, null, $emp_id_filter);
$dept_apps = get_applications(null, null, null, $emp_id_filter);

$active_jobs_count = count(array_filter($all_dept_jobs, fn($j) => in_array($j['status'] ?? '', ['active', 'Active'])));
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

