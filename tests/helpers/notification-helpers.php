<?php
declare(strict_types=1);

/**
 * Test helpers for notification lifecycle tests
 * Extracted from test_all_roles_notifications.php
 */

/** Render a page in buffer for a given user session */
function render_page_for_user(array $user, string $filter = 'all'): string {
    $uid = (int)($user['id'] ?? 0);
    $cmd = "c:\\xampp\\php\\php.exe -r \"session_start(); \$_SESSION['user'] = ['id' => {$uid}]; \$_GET['filter'] = '{$filter}'; \$_SERVER['REQUEST_METHOD'] = 'GET'; \$_SERVER['PHP_SELF'] = '/notifications.php'; \$_SERVER['SCRIPT_NAME'] = '/notifications.php'; include 'notifications.php';\"";
    $output = shell_exec($cmd);
    return $output ?: '';
}

/** Fetch one test user for each of the 3 roles */
function fetch_role_users(PDO $pdo): array {
    return [
        'student'  => $pdo->query("SELECT * FROM users WHERE role = 'student' LIMIT 1")->fetch(),
        'employer' => $pdo->query("SELECT * FROM users WHERE role = 'employer' LIMIT 1")->fetch(),
        'admin'    => $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch(),
    ];
}
