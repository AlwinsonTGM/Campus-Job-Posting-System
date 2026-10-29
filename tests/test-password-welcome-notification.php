<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/services/user-service.php';
require_once __DIR__ . '/../includes/services/system-service.php';

$pdo = get_db_connection();
// Connection-local tables keep real accounts and notifications untouched.
$pdo->exec('CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, password VARCHAR(255), must_change_password TINYINT DEFAULT 0, updated_at DATETIME)');
$pdo->exec('CREATE TEMPORARY TABLE notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, type VARCHAR(50), title VARCHAR(255), message TEXT, link VARCHAR(255), icon VARCHAR(100), badge_color VARCHAR(50), is_read TINYINT, created_at DATETIME)');
$pdo->exec("INSERT INTO users (id, password) VALUES (1, 'old'), (2, 'old')");

function check(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    echo "PASS: {$message}\n";
}

$hash = password_hash('Regression123!', PASSWORD_DEFAULT);
check(update_user_password(1, $hash), 'Routine admin password update succeeds');
check((int)$pdo->query('SELECT COUNT(*) FROM notifications WHERE user_id = 1')->fetchColumn() === 0,
    'Routine admin password update does not create a welcome notification');
check(password_verify('Regression123!', (string)$pdo->query('SELECT password FROM users WHERE id = 1')->fetchColumn()),
    'Password is still updated');

check(create_notification(2, 'account_security', 'Temporary Password Active', 'Set your password', 'settings.php?setup_password=1') > 0,
    'Temporary-password prompt is created');
check(create_notification(1, 'system', 'Unrelated notification', 'Keep this') > 0, 'Unrelated notification is created');
check(update_user_password(2, $hash), 'Temporary-password setup succeeds');
check((int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE user_id = 2 AND type = 'account_security'")->fetchColumn() === 0,
    'Temporary-password prompt is removed');
check((int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE user_id = 2 AND type = 'welcome'")->fetchColumn() === 1,
    'Temporary-password setup creates one welcome notification');
check(update_user_password(2, $hash), 'Subsequent password update succeeds');
check(resolve_temporary_password_notification(1), 'Password-reset notification cleanup succeeds without a temporary prompt');
check((int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE type = 'welcome'")->fetchColumn() === 1,
    'Subsequent updates and cleanup do not create extra welcome notifications');
check((int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE user_id = 1 AND type = 'system'")->fetchColumn() === 1,
    'Unrelated notifications are preserved');
