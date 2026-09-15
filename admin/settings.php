<?php
/**
 * Campus Job Posting System - Admin Settings Redirector
 * Smoothly routes requests to the centralized system settings page.
 */
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
header('Location: ../settings.php');
exit;
