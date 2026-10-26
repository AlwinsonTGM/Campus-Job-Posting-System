<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/data-helper.php';

$pdo = get_db_connection();
get_user_base_query(); // Complete schema initialization before the rollback-only fixture.
$failures = 0;
$check = static function (bool $ok, string $message) use (&$failures): void {
    echo ($ok ? 'PASS' : 'FAIL') . ': ' . $message . PHP_EOL;
    if (!$ok) $failures++;
};

$pdo->beginTransaction();
try {
    $email = 'archive-' . bin2hex(random_bytes(6)) . '@example.invalid';
    $password = 'Archive-test-123!';
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role, name, status, is_email_verified, verification_status) VALUES (?, ?, 'student', 'Archive regression', 'active', 1, 'verified')");
    $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
    $id = (int)$pdo->lastInsertId();
    $check(login_user($email, $password)['success'], 'Active account can sign in');
    $session = $_SESSION['user'];
    $check(suspend_user($id), 'Archive succeeds');
    $archived = get_user_by_id($id);
    $check(!empty($archived['is_archived']) && $archived['status'] === 'suspended', 'Archive is persisted');
    $check(!in_array($id, array_column(get_all_users(), 'id')), 'Archived account disappears from active directory');
    $check(!login_user($email, $password)['success'], 'Archived account cannot sign in');
    $check(quick_login('student', $id) === null, 'Archived account cannot use quick login');
    $_SESSION['user'] = $session;
    $check(!is_logged_in(), 'Existing session loses access immediately after archive');
    $check(get_logged_user() === null, 'Archived account is not returned as the current user');
    $_SESSION['user'] = $session;
    $_SERVER['SCRIPT_NAME'] = '/student/dashboard.php';
    $redirect = null;
    SessionGuard::setRedirectHandler(static function ($url) use (&$redirect): void {
        $redirect = $url;
    });
    $check(SessionGuard::protect(['student']) === [] && str_contains($redirect ?? '', 'login.php'), 'Archived session is denied a protected page');
    SessionGuard::setRedirectHandler(null);

    // The archive flag must block access even for legacy rows with active status.
    $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
    $_SESSION['user'] = $session;
    $check(quick_login('student', $id) === null, 'Archive flag alone blocks quick login');
    $_SESSION['user'] = $session;
    $check(get_logged_user() === null, 'Archive flag alone invalidates an existing session');
    $check(reactivate_user($id), 'Account can be restored');
    $check(login_user($email, $password)['success'], 'Restored account can sign in');
} finally {
    $pdo->rollBack();
    unset($_SESSION['user']);
}
exit($failures ? 1 : 0);
