<?php
/**
 * Campus Job Posting System - Employer Applicant Roster
 * Archetype D/G: Applicant Management Table (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$page_title = 'Applicant Evaluation Roster';

$dept = $user['organization_name'] ?? ($user['department'] ?? 'Office of the University Registrar');
$job_filter = $_GET['job_id'] ?? null;
$status_filter = $_GET['status'] ?? null;
$search = trim($_GET['q'] ?? '');

// Handle decision submissions via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security validation failed: Invalid or expired security token. Please try again.');
        header('Location: applicants.php' . ($job_filter ? "?job_id={$job_filter}" : ''));
        exit;
    }

    $app_id = $_POST['app_id'];
    $action_type = $_POST['action_type'];
    $notes = trim($_POST['supervisor_notes'] ?? '');

    $target_app = get_application_by_id($app_id);
    if (!$target_app || !can_review_application($target_app, $user)) {
        set_flash('danger', 'Unauthorized: Cannot modify candidate applications from another department.');
        header('Location: applicants.php' . ($job_filter ? "?job_id={$job_filter}" : ''));
        exit;
    }

    $interview_data = [];
    if ($action_type === 'interview_scheduled') {
        $interview_data = [
            'date' => $_POST['interview_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'time' => $_POST['interview_time'] ?? '10:00 AM',
            'venue' => $_POST['interview_venue'] ?? ($user['office_location'] ?? 'Admin Building Room 102')
        ];
    }

    $res = update_application_status($app_id, $action_type, $notes, $interview_data);
    if ($res) {
        set_flash('success', 'Applicant status has been updated successfully!');
    } else {
        set_flash('danger', 'Unable to update applicant status. Please ensure the interview date is today or in the future.');
    }
    header('Location: applicants.php' . ($job_filter ? "?job_id={$job_filter}" : ''));
    exit;
}

$emp_id_filter = ($user['role'] === 'admin') ? null : (int)$user['id'];
$all_dept_apps = get_applications(null, $job_filter, null, $emp_id_filter);

if ($status_filter) {
    $all_dept_apps = array_filter($all_dept_apps, function($a) use ($status_filter) {
        $st = strtolower($a['status'] ?? '');
        $target = strtolower($status_filter);
        if ($target === 'pending') return in_array($st, ['pending', 'pending review']);
        if ($target === 'review') return in_array($st, ['under_review', 'under review', 'under evaluation']);
        if ($target === 'interview') return in_array($st, ['interview_scheduled', 'interview scheduled']);
        if ($target === 'accepted') return in_array($st, ['accepted', 'accepted / hired']);
        if ($target === 'declined') return in_array($st, ['declined', 'rejected', 'declined / position filled']);
        return $st === $target;
    });
}

if (!empty($search)) {
    $all_dept_apps = array_filter($all_dept_apps, function($a) use ($search) {
        return stripos($a['student_name'] ?? '', $search) !== false
            || stripos($a['student_email'] ?? '', $search) !== false
            || stripos($a['course'] ?? '', $search) !== false
            || stripos($a['job_title'] ?? '', $search) !== false;
    });
}

$dept_jobs = get_jobs(null, null, null, null, null, null, null, $emp_id_filter);
// Last line: view template
require __DIR__ . '/../includes/templates/employer-applicants-view.php';

