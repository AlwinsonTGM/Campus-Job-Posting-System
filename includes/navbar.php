<?php
/**
 * Campus Job Posting System - Shared Navbar Partial
 * Paper Sheet Aesthetic & Auth-Aware (COAL101 Blueprint)
 */
require_once __DIR__ . '/auth-check.php';

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}

$current_user = get_logged_user();
$current_script = basename($_SERVER['PHP_SELF'] ?? '');
$dashboard_link = $base_url . get_role_dashboard_url($current_user['role'] ?? 'student');
$user_unread_notifs_count = ($current_user && function_exists('get_unread_notifications_count')) ? get_unread_notifications_count($current_user['id']) : 0;
$user_recent_notifs = ($current_user && function_exists('get_user_notifications')) ? get_user_notifications($current_user['id'], 5) : [];

require __DIR__ . '/templates/includes-navbar-view.php';

