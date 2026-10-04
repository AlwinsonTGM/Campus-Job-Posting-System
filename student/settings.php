<?php
/**
 * Campus Job Posting System - Student Settings Gateway Router
 * Smoothly routes requests to the centralized system settings page.
 */
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['student', 'admin']);
header('Location: ../settings.php');
exit;
