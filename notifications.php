<?php
/**
 * Campus Job Posting System - Central Notification Center
 * Complete in-app notification dashboard with All/Unread filters, batch actions, and direct item redirection.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

require_auth();
$user = get_logged_user();
$user_id = (int)$user['id'];

// Handle POST actions (mark_all_read, mark_read, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        set_flash('danger', 'Security validation failed (invalid CSRF session token). Please refresh and try again.');
        header('Location: notifications.php');
        exit;
    }

    if ($action === 'mark_all_read') {
        mark_all_notifications_as_read($user_id);
        set_flash('success', 'All notifications have been marked as read.');
        header('Location: notifications.php');
        exit;
    }

    if ($action === 'mark_read') {
        $notif_id = (int)($_POST['id'] ?? 0);
        if ($notif_id > 0) {
            mark_notification_as_read($notif_id, $user_id);
            set_flash('info', 'Notification marked as read.');
        }
        $filter_param = isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : '';
        header('Location: notifications.php' . $filter_param);
        exit;
    }

    if ($action === 'delete') {
        $notif_id = (int)($_POST['id'] ?? 0);
        if ($notif_id > 0) {
            delete_notification($notif_id, $user_id);
            set_flash('info', 'Notification deleted.');
        }
        $filter_param = isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : '';
        header('Location: notifications.php' . $filter_param);
        exit;
    }
}

// Read parameters & filters
$filter = trim($_GET['filter'] ?? 'all');
$unread_only = ($filter === 'unread');

$all_notifications = get_user_notifications($user_id, 100, false);
$unread_count = count(array_filter($all_notifications, fn($n) => empty($n['is_read'])));
$total_count = count($all_notifications);

$display_notifications = $unread_only 
    ? array_filter($all_notifications, fn($n) => empty($n['is_read']))
    : $all_notifications;

$page_title = 'Notification Center';
// Last line: view template
require __DIR__ . '/includes/templates/notifications-view.php';

