<?php
/**
 * Campus Job Posting System - Career Center Updates & Editorial Dispatch
 * Impeccable Design Standard & Information Architecture (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Career Center Updates & Announcements';
$updates = get_career_updates();

// Separate featured top story from the rest of the feed
$featured_story = !empty($updates) ? $updates[0] : null;
$secondary_updates = !empty($updates) ? array_slice($updates, 1) : [];

// Preload view data — no service/DB calls in the template
$view_get_current_auth_user = get_logged_user();

// Last line: view template
require __DIR__ . '/includes/templates/updates-view.php';

