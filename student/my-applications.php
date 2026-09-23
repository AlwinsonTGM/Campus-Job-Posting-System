<?php
/**
 * Campus Job Posting System - Student Application Tracker
 * Archetype D: Status Tracker & 4-Step Stepper (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['student', 'admin']);
$user = get_logged_user();
$page_title = 'My Assistantship Applications';

// Handle withdrawal
if (isset($_POST['withdraw_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security validation failed (invalid CSRF session token). Please refresh and try again.');
        header('Location: my-applications.php');
        exit;
    }

    $withdraw_id = (int)$_POST['withdraw_id'];
    $target_app = get_application_by_id($withdraw_id);

    if (!$target_app || (int)$target_app['student_id'] !== (int)($user['id'] ?? 0)) {
        set_flash('danger', 'Unauthorized or non-existent application.');
        header('Location: my-applications.php');
        exit;
    }

    // Only allow withdrawing applications that are still pending review
    if (!in_array(strtolower($target_app['status'] ?? ''), ['pending', 'pending review'])) {
        set_flash('warning', 'Applications that are under review, scheduled for interview, or accepted cannot be withdrawn.');
        header('Location: my-applications.php');
        exit;
    }

    delete_application($withdraw_id, $user['id'] ?? null);
    set_flash('info', 'Application was successfully withdrawn.');
    header('Location: my-applications.php');
    exit;
}

// Handle withdraw-all-others (accepted student tidying up remaining pending apps)
if (isset($_POST['withdraw_others'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security validation failed (invalid CSRF session token). Please refresh and try again.');
        header('Location: my-applications.php');
        exit;
    }

    $all = get_applications($user['id'] ?? 0);
    $withdrawn = 0;
    foreach ($all as $a) {
        if (!in_array(strtolower($a['status'] ?? ''), ['pending', 'pending review'])) {
            continue;
        }
        if ((int)$a['student_id'] !== (int)($user['id'] ?? 0)) {
            continue;
        }
        delete_application((int)$a['id'], $user['id'] ?? null);
        $withdrawn++;
    }
    set_flash('success', $withdrawn > 0
        ? "Congratulations again! {$withdrawn} pending application" . ($withdrawn === 1 ? ' was' : 's were') . " withdrawn, freeing slots for fellow students."
        : 'No pending applications left to withdraw.');
    header('Location: my-applications.php');
    exit;
}

$my_apps = get_applications($user['id'] ?? 0);
$filter_status = trim($_GET['status'] ?? '');

if (!empty($filter_status)) {
    $my_apps = array_filter($my_apps, function($a) use ($filter_status) {
        $st = strtolower($a['status'] ?? '');
        $target = strtolower($filter_status);
        if ($target === 'pending') return in_array($st, ['pending', 'pending review']);
        if ($target === 'review') return in_array($st, ['under_review', 'under review', 'under evaluation']);
        if ($target === 'interview') return in_array($st, ['interview_scheduled', 'interview scheduled']);
        if ($target === 'accepted') return in_array($st, ['accepted', 'accepted / hired']);
        if ($target === 'declined') return in_array($st, ['declined', 'rejected', 'declined / position filled']);
        return true;
    });
}
// Last line: view template
require __DIR__ . '/../includes/templates/student-my-applications-view.php';

