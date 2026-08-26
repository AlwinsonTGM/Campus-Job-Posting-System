<?php
/**
 * Campus Job Posting System - Logout Handler
 */
require_once __DIR__ . '/includes/data-helper.php';

if (isset($_SESSION['user'])) {
    unset($_SESSION['user']);
}

set_flash('info', 'You have successfully signed out of your account.');
header('Location: login.php');
exit;
