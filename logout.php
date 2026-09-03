<?php
/**
 * Campus Job Posting System - Logout Handler
 */
require_once __DIR__ . '/includes/data-helper.php';

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
session_start();
session_regenerate_id(true);

set_flash('info', 'You have successfully signed out of your account.');
header('Location: login.php');
exit;
