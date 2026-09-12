<?php
/**
 * Campus Job Posting System - Dataset Mode Switcher Controller
 * Handles toggling between Demo/Placeholder Mode and Real/Clean Live Mode.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// Verify CSRF token for security if POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($csrf_token)) {
    if (!verify_csrf_token($csrf_token)) {
        set_flash('danger', 'Security verification failed. Please try switching data mode again.');
        header("Location: $referer");
        exit;
    }
}

if ($action === 'switch_mode' || !empty($mode)) {
    $target_mode = in_array(strtolower($mode), ['real', 'clean']) ? 'real' : 'demo';
    if (switch_system_data_mode($target_mode, is_logged_in() ? get_logged_user()['name'] : 'User')) {
        if ($target_mode === 'real') {
            set_flash('success', '🧪 Real / Clean Slate Mode activated! All sample placeholder jobs, applicants, and mock data have been cleared. You can now register real accounts, post vacancies, and test live in normal or private browser windows!');
        } else {
            set_flash('info', '📋 Demo / Placeholder Mode activated! Sample student accounts, campus offices, accredited partners, job requisitions, and applicant dossiers have been restored.');
        }
    } else {
        set_flash('danger', 'Failed to switch dataset mode. Seed directory not found.');
    }
} elseif ($action === 'reset_current' || $action === 'reset') {
    $current_mode = get_system_data_mode();
    if (reset_current_data_mode(is_logged_in() ? get_logged_user()['name'] : 'User')) {
        set_flash('info', 'Dataset for ' . ucfirst($current_mode) . ' Mode has been reset to its default starting baseline.');
    } else {
        set_flash('danger', 'Failed to reset dataset.');
    }
} elseif ($action === 'wipe_real') {
    if (wipe_real_data_fresh()) {
        set_flash('success', 'Real dataset wiped clean. All jobs, applications, and non-admin accounts cleared for a fresh test run.');
    } else {
        set_flash('danger', 'Failed to wipe data.');
    }
}

header("Location: $referer");
exit;
