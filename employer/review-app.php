<?php
/**
 * Campus Job Posting System - Candidate Evaluation & Decision Drawer
 * Archetype C/D: Candidate Evaluation & Availability Matrix (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();

$app_id = $_GET['id'] ?? null;
$target_app = $app_id ? get_application_by_id($app_id) : null;

if (!$target_app) {
    set_flash('danger', 'The specified student application could not be found.');
    header('Location: applicants.php');
    exit;
}

if (!can_review_application($target_app, $user)) {
    set_flash('danger', 'Unauthorized: Candidate application belongs to another department.');
    header('Location: applicants.php');
    exit;
}

$job = get_job_by_id($target_app['job_id'] ?? 0);

// Handle status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security validation failed: Invalid or expired security token. Please try again.');
        header("Location: review-app.php?id={$target_app['id']}");
        exit;
    }

    $new_status = $_POST['status'] ?? 'under_review';
    $notes = trim($_POST['supervisor_notes'] ?? '');

    $interview_data = [];
    if ($new_status === 'interview_scheduled') {
        $interview_data = [
            'date' => $_POST['interview_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'time' => $_POST['interview_time'] ?? '10:00 AM',
            'venue' => $_POST['interview_venue'] ?? ($user['office_location'] ?? 'Admin Building Room 102')
        ];
    }

    $res = update_application_status($target_app['id'], $new_status, $notes, $interview_data);
    if ($res) {
        set_flash('success', "Candidate status for {$target_app['student_name']} updated to " . ucfirst(str_replace('_', ' ', $new_status)) . ".");
    } else {
        set_flash('danger', "Unable to update candidate status. Please ensure the interview schedule date is today or in the future.");
    }
    header("Location: review-app.php?id={$target_app['id']}");
    exit;
}

$page_title = 'Evaluate: ' . $target_app['student_name'];

// Candidate shift availability summary
$schedule_summary = get_schedule_summary($target_app, $job);

// Last line: view template
require __DIR__ . '/../includes/templates/employer-review-app-view.php';


