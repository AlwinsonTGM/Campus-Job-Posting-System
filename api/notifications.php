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
                $safe_link = SessionGuard::sanitizeReturnTo($target_link);
                if ($safe_link !== null) {
                    if (str_contains($safe_link, '#') || preg_match('/[?&]highlight=/', $safe_link)) {
                        $link_parts = explode('#', $safe_link, 2);
                        $link_parts[0] .= (str_contains($link_parts[0], '?') ? '&' : '?') . 'from_notification=1';
                        $safe_link = implode('#', $link_parts);
                    }
                    header('Location: ../' . ltrim($safe_link, '/'));
                    exit;
                }
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

    $csrf = $post_data['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
    $is_same_origin = false;
    if (!empty($origin) && parse_url($origin, PHP_URL_HOST) === parse_url('http://' . $host, PHP_URL_HOST)) {
        $is_same_origin = true;
    } elseif (!empty($referer) && parse_url($referer, PHP_URL_HOST) === parse_url('http://' . $host, PHP_URL_HOST)) {
        $is_same_origin = true;
    }

    if (!empty($csrf)) {
        if (!verify_csrf_token($csrf)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF verification failed']);
            exit;
        }
    } elseif (!$is_same_origin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cross-origin request blocked']);
        exit;
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

// Release session lock before DB read queries on GET polling requests
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
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
