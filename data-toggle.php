<?php
/**
 * Campus Job Posting System - Dataset Mode Controller (Retired / Unified)
 * Demo data and real user registrations now permanently coexist in a unified database.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
set_flash('info', 'Unified Data Mode is active. Demo listings and registered accounts coexist permanently.');
header("Location: $referer");
exit;
