<?php
/**
 * Campus Job Posting System - Notifications API Endpoint
 * Handles live polling, mark-as-read, and navigation clicks for authenticated users.
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$user = get_logged_user();

// Safe click redirection handler (GET request that redirects to target link)
if (isset($_GET['action']) && $_GET['action'] === 'click') {
    $notif_id = (int)($_GET['id'] ?? 0);
    if ($user && $notif_id > 0) {
        $notif = get_notification_by_id($notif_id, (int)$user['id']);
        if ($notif) {
            mark_notification_as_read($notif_id, (int)$user['id']);
            $target_link = trim($notif['link'] ?? '');
            if (!empty($target_link)) {
                // Ensure target link does not contain protocol for internal navigation security
                if (!preg_match('#^https?://#i', $target_link)) {
                    $target_link = ltrim($target_link, '/');
                    header('Location: ../' . $target_link);
                    exit;
                }
                header('Location: ' . $target_link);
                exit;
            }
        }
    }
    header('Location: ../notifications.php');
    exit;
}

// JSON Responses for all remaining actions
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Please log in.', 'unread_count' => 0, 'notifications' => []]);
    exit;
}

$user_id = (int)$user['id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Handle POST actions (mark_read, mark_all_read, delete)
if ($method === 'POST') {
    // Parse JSON or form data
    $post_data = $_POST;
    $input_json = json_decode(file_get_contents('php://input'), true);
    if (is_array($input_json)) {
        $post_data = array_merge($post_data, $input_json);
    }

    $action = $post_data['action'] ?? '';

    if ($action === 'mark_read') {
        $notif_id = (int)($post_data['id'] ?? 0);
        if ($notif_id > 0) {
            mark_notification_as_read($notif_id, $user_id);
        }
        $unread_count = get_unread_notifications_count($user_id);
        echo json_encode(['success' => true, 'unread_count' => $unread_count]);
        exit;
    }

    if ($action === 'mark_all_read') {
        mark_all_notifications_as_read($user_id);
        echo json_encode(['success' => true, 'unread_count' => 0]);
        exit;
    }

    if ($action === 'delete') {
        $notif_id = (int)($post_data['id'] ?? 0);
        if ($notif_id > 0) {
            delete_notification($notif_id, $user_id);
        }
        $unread_count = get_unread_notifications_count($user_id);
        echo json_encode(['success' => true, 'unread_count' => $unread_count]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

// Handle GET: Return latest notifications & unread count for polling / UI
$limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;
$unread_only = !empty($_GET['unread_only']) && ($_GET['unread_only'] === '1' || $_GET['unread_only'] === 'true');

$notifications = get_user_notifications($user_id, $limit, $unread_only);
$unread_count = get_unread_notifications_count($user_id);

$formatted = [];
foreach ($notifications as $n) {
    $formatted[] = [
        'id'          => $n['id'],
        'type'        => $n['type'],
        'title'       => $n['title'],
        'message'     => $n['message'],
        'link'        => $n['link'],
        'icon'        => $n['icon'] ?? 'bi-bell',
        'badge_color' => $n['badge_color'] ?? 'primary',
        'is_read'     => (bool)$n['is_read'],
        'time_ago'    => time_ago_short($n['created_at']),
        'created_at'  => $n['created_at']
    ];
}

echo json_encode([
    'success'       => true,
    'unread_count'  => $unread_count,
    'notifications' => $formatted
], JSON_UNESCAPED_UNICODE);
