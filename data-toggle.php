<?php
/**
 * Campus Job Posting System - Dataset Mode Switcher Controller (RETIRED)
 *
 * Dataset toggling has been retired. The system operates exclusively
 * on the normalized MySQL / MariaDB relational datastore.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

// Block unauthenticated access to prevent unauthorized datastore resets or mode switches
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    exit('Forbidden: Administrative authorization required.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Forbidden: Invalid or missing CSRF token.');
}

http_response_code(404);
if (!headers_sent()) {
    header('Location: settings.php');
}
exit('Dataset mode switching has been permanently retired in favor of authoritative MySQL storage.');
