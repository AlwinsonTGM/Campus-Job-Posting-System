<?php
/**
 * Campus Job Posting System - Employer Settings Gateway Router
 * Smoothly routes requests to the centralized system settings page.
 */
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
header('Location: ../settings.php');
exit;
