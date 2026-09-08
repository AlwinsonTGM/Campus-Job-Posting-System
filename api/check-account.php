<?php
/**
 * Campus Job Posting System - Account Availability & Duplicate Check API
 * Verifies whether a KLD email address or student ID is already in use
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/data-helper.php';

$raw_email = $_GET['email'] ?? $_POST['email'] ?? '';
$email = is_string($raw_email) ? strtolower(trim($raw_email)) : '';

$raw_student_id = $_GET['student_id'] ?? $_POST['student_id'] ?? '';
$student_id = is_string($raw_student_id) ? trim($raw_student_id) : '';

$response = [
    'email_exists' => false,
    'student_id_exists' => false,
    'available' => true,
    'message' => ''
];

// Check Email
if (!empty($email)) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT `id`, `name`, `role` FROM `users` WHERE LOWER(`email`) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($user = $stmt->fetch()) {
            $response['email_exists'] = true;
            $response['available'] = false;
            $response['message'] = 'This KLD account / email is already registered. Please sign in instead.';
        }
    } catch (Exception $e) {
        $users = get_users();
        foreach ($users as $u) {
            if (isset($u['email']) && strtolower(trim($u['email'])) === $email) {
                $response['email_exists'] = true;
                $response['available'] = false;
                $response['message'] = 'This KLD account / email is already registered. Please sign in instead.';
                break;
            }
        }
    }
}

// Check Student ID
if (!empty($student_id)) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT `id` FROM `users` WHERE LOWER(TRIM(`student_id`)) = LOWER(TRIM(:sid)) AND `role` = 'student' LIMIT 1");
        $stmt->execute([':sid' => $student_id]);
        if ($stmt->fetch()) {
            $response['student_id_exists'] = true;
            $response['available'] = false;
            $msg = 'This Student ID Number is already registered.';
            $response['message'] = $response['message'] ? $response['message'] . ' ' . $msg : $msg;
        }
    } catch (Exception $e) {
        $users = get_users();
        foreach ($users as $u) {
            if (isset($u['student_id']) && strtolower(trim($u['student_id'])) === strtolower($student_id) && ($u['role'] ?? '') === 'student') {
                $response['student_id_exists'] = true;
                $response['available'] = false;
                $msg = 'This Student ID Number is already registered.';
                $response['message'] = $response['message'] ? $response['message'] . ' ' . $msg : $msg;
                break;
            }
        }
    }
}

echo json_encode($response);
exit;
