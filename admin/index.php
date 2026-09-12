<?php
/**
 * Campus Job Posting System - Admin Root Router
 * Securely redirects authenticated administrators to the reports overview.
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
header('Location: reports.php');
exit;
