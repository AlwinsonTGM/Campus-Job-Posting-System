<?php
/**
 * Campus Job Posting System - Institutional Email Verification
 * Archetype A: Auth Split Card Shell
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/includes/mailer.php';

// Check for AJAX request
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

if (isset($_GET['action']) && $_GET['action'] === 'cancel') {
    unset($_SESSION['pending_verification']);
    header('Location: login.php');
    exit;
}

$pending = $_SESSION['pending_verification'] ?? null;
if (!$pending || empty($pending['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'redirect' => 'login.php', 'message' => 'Session expired. Please sign in.']);
        exit;
    }
    if (is_logged_in()) {
        $u = get_logged_user();
        redirect_by_role($u['role'] ?? null);
    }
    header('Location: login.php');
    exit;
}

$user_id = (int)$pending['user_id'];
$existing_user = get_user_by_id($user_id);
if (!$existing_user) {
    unset($_SESSION['pending_verification']);
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'redirect' => 'login.php',
            'message' => 'Your verification session has expired. Please sign in or register again.'
        ]);
        exit;
    }
    set_flash('warning', 'Your verification session has expired. Please register or sign in.');
    header('Location: login.php');
    exit;
}

// Seamlessly graduate already-verified users to their dashboard
if ((int)($existing_user['is_email_verified'] ?? 0) === 1) {
    unset($_SESSION['pending_verification']);
    $_SESSION['user'] = $existing_user;
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'redirect' => get_role_dashboard_url($existing_user['role'] ?? null),
            'message' => 'Your email is already verified!'
        ]);
        exit;
    }
    redirect_by_role($existing_user['role'] ?? null);
}

$pending_email = $existing_user['email'] ?? ($pending['email'] ?? '');
$pending_name = $existing_user['name'] ?? ($pending['name'] ?? 'User');

$error = null;
$smtp_ready = is_smtp_configured();

// Handle Resend Verification Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend') {
    $resend_status = can_resend_email_code($user_id);
    if (!$resend_status['allowed']) {
        $msg = "Please wait {$resend_status['remaining_seconds']}s before requesting another code.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'remaining_seconds' => $resend_status['remaining_seconds'], 'message' => $msg]);
            exit;
        }
        $error = $msg;
    } else {
        $code = create_email_verification_code($user_id);
        if ($code === '') {
            unset($_SESSION['pending_verification']);
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'redirect' => 'login.php', 'message' => 'Account session expired. Please sign in again.']);
                exit;
            }
            set_flash('danger', 'Account session expired. Please sign in again.');
            header('Location: login.php');
            exit;
        }
        $mail_res = send_verification_code_email($pending_email, $pending_name, $code);

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'remaining_seconds' => 60,
                'smtp_configured' => $mail_res['smtp_configured'],
                'message' => $mail_res['smtp_configured']
                    ? 'A fresh 6-digit verification code has been dispatched to your institutional inbox.'
                    : 'Notice: Outbound SMTP is not configured in .env.'
            ]);
            exit;
        }

        if (!$mail_res['smtp_configured']) {
            set_flash('warning', 'Notice: Outbound SMTP is not configured in .env. Configure MAIL_USERNAME and MAIL_PASSWORD to receive codes.');
        } else {
            set_flash('success', 'A fresh 6-digit verification code has been dispatched to your institutional inbox.');
        }
        header('Location: verify-email.php');
        exit;
    }
}

// Handle Verification Code Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'verify')) {
    $submitted_code = trim($_POST['otp_code'] ?? '');
    if (empty($submitted_code) && isset($_POST['digit']) && is_array($_POST['digit'])) {
        $submitted_code = implode('', $_POST['digit']);
    }
    $clean_code = preg_replace('/\D/', '', $submitted_code);

    if (strlen($clean_code) !== 6) {
        $err_msg = 'Please enter all 6 digits of your verification code.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $err_msg]);
            exit;
        }
        $error = $err_msg;
    } else {
        $verify_res = verify_email_code($user_id, $clean_code);
        if ($verify_res['success']) {
            $user = get_user_by_id($user_id);
            if ($user) {
                unset($user['password']);
            }
            $_SESSION['user'] = $user;
            unset($_SESSION['pending_verification']);

            $role = $user['role'] ?? 'student';
            $dest_url = get_role_dashboard_url($role);

            $role_msg = !empty($pending['role_message'])
                ? 'Email verified! ' . $pending['role_message']
                : 'Your email has been verified successfully. Welcome to your campus portal!';
            set_flash('success', $role_msg);

            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'redirect' => $dest_url,
                    'message' => 'Identity verified! Redirecting to your workspace...'
                ]);
                exit;
            }

            redirect_by_role($role);
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $verify_res['message']]);
                exit;
            }
            $error = $verify_res['message'];
        }
    }
}

$cooldown = can_resend_email_code($user_id);
$remaining_seconds = $cooldown['remaining_seconds'];

$page_title = 'Verify Your Email Address';
// Last line: view template
require __DIR__ . '/includes/templates/verify-email-view.php';

