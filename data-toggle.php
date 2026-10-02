<?php
/**
 * Campus Job Posting System - Dataset Mode Switcher Controller
 * Handles toggling between Demo/Placeholder Mode and Real/Clean Live Mode.
 *
 * SECURITY MODEL
 * --------------
 * This endpoint replaces the entire dataset, so it is treated as a privileged
 * state mutation:
 *   1. Administrator session required (require_auth(['admin'])).
 *   2. POST only - a GET can be triggered by an <img>, a link, or a crawler,
 *      so it must never mutate state.
 *   3. CSRF token always verified (previously only checked on POST *and* only
 *      when a token happened to be present, so an empty token skipped the
 *      check entirely).
 *   4. Disabled outright when the environment is not a demo/test one.
 *   5. Action/mode read from POST only - never from the query string.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

// 1. Authorise first: only an administrator may switch datasets.
require_auth(['admin']);

// 2. Reject anything that is not an explicit POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

// 3. The capability can be removed entirely outside demo/test environments.
if (!DatastoreManager::isToggleAvailable()) {
    http_response_code(404);
    exit('Not Found');
}

// 4. Read the action from POST only - the query string is never trusted here.
$action = $_POST['action'] ?? '';
$mode = $_POST['mode'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';

if (!verify_csrf_token($csrf_token)) {
    set_flash('danger', 'Security verification failed. Please try switching data mode again.');
    header('Location: settings.php');
    exit;
}

$user = get_logged_user();
$userName = $user['name'] ?? 'Administrator';

// Redirect back to the admin dataset page. Only a same-origin referer is
// honoured (and never the toggle itself), so this cannot become an open
// redirect. No host/path prefix is needed: this file lives at the app root,
// so a bare relative target resolves correctly under any deployment path.
$location = 'settings.php';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if ($referer !== '') {
    $host = parse_url($referer, PHP_URL_HOST) ?: '';
    $path = parse_url($referer, PHP_URL_PATH) ?: '';
    $selfHost = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === $selfHost && $path !== '' && strpos($path, 'data-toggle.php') === false) {
        $location = $referer;
    }
}

if ($action === 'switch_mode' || !empty($mode)) {
    $target_mode = in_array(strtolower($mode), ['real', 'clean'], true) ? 'real' : 'demo';
    if (DatastoreManager::switchMode($target_mode, $userName)) {
        if ($target_mode === 'real') {
            set_flash('success', '🧪 Real / Clean Slate Mode activated! All sample placeholder jobs, applicants, and mock data have been cleared. You can now register real accounts, post vacancies, and test live in normal or private browser windows!');
        } else {
            set_flash('info', '📋 Demo / Placeholder Mode activated! Sample student accounts, campus offices, accredited partners, job requisitions, and applicant dossiers have been restored.');
        }
    } else {
        set_flash('danger', 'Failed to switch dataset mode. Seed directory not found.');
    }
} elseif ($action === 'reset_current' || $action === 'reset') {
    $current_mode = DatastoreManager::getMode();
    if (DatastoreManager::resetCurrent($userName)) {
        set_flash('info', 'Dataset for ' . ucfirst($current_mode) . ' Mode has been reset to its default starting baseline.');
    } else {
        set_flash('danger', 'Failed to reset dataset.');
    }
} elseif ($action === 'wipe_real') {
    if (DatastoreManager::wipeReal()) {
        set_flash('success', 'Real dataset wiped clean. All jobs, applications, and non-admin accounts cleared for a fresh test run.');
    } else {
        set_flash('danger', 'Failed to wipe data.');
    }
}

header("Location: $location");
exit;
