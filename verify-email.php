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
    $pending = $_SESSION['pending_verification'] ?? null;
    if (!empty($pending['user_id'])) {
        purge_unverified_user((int)$pending['user_id']);
    }
    unset($_SESSION['pending_verification']);
    set_flash('info', 'Registration was cancelled. Your temporary unverified details and documents have been cleared.');
    header('Location: register.php');
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
            'redirect' => 'register.php',
            'message' => 'Your verification session has expired. Please register again.'
        ]);
        exit;
    }
    set_flash('warning', 'Your verification session has expired. Please register again.');
    header('Location: register.php');
    exit;
}

// Seamlessly graduate already-verified users to their dashboard
if ((int)($existing_user['is_email_verified'] ?? 0) === 1) {
    SessionGuard::graduateVerification($user_id);
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'redirect' => SessionGuard::getRoleDashboardUrl($existing_user['role'] ?? null),
            'message' => 'Your email is already verified!'
        ]);
        exit;
    }
    SessionGuard::redirectByRole($existing_user['role'] ?? null);
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
        unset($_SESSION['otp_failed_attempts']);
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
        if (!$mail_res['smtp_configured']) {
            $_SESSION['pending_verification']['dev_code'] = $code;
        }

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'remaining_seconds' => 60,
                'smtp_configured' => $mail_res['smtp_configured'],
                'message' => $mail_res['smtp_configured']
                    ? 'A fresh 6-digit verification code has been dispatched to your institutional inbox.'
                    : 'Notice: Outbound SMTP is not configured in .env. Use the verification passcode shown on screen.'
            ]);
            exit;
        }

        if (!$mail_res['smtp_configured']) {
            set_flash('warning', 'Notice: Outbound SMTP is not configured in .env. Enter the verification passcode displayed on screen.');
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
            unset($_SESSION['otp_failed_attempts']);
            $user = SessionGuard::graduateVerification($user_id);
            $role = $user['role'] ?? 'student';

            // Check if admin approval is still required before granting portal access
            $ver_status = $user['verification_status'] ?? 'pending_approval';
            if ($ver_status === 'pending_approval' && $role !== 'admin') {
                $_SESSION['pending_approval_notice'] = [
                    'user_id' => (int)($user['id'] ?? 0),
                    'name'    => $user['name'] ?? 'User',
                    'email'   => $user['email'] ?? '',
                    'role'    => $role,
                ];
                unset($_SESSION['user']);

                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success'  => true,
                        'redirect' => 'pending-approval.php',
                        'message'  => 'Email verified! Your account is awaiting administrator approval.',
                    ]);
                    exit;
                }

                set_flash('success', 'Email verified! Your account is now pending administrator approval. You will be notified once it is reviewed.');
                header('Location: pending-approval.php');
                exit;
            }

            $dest_url = SessionGuard::getRoleDashboardUrl($role);

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

            SessionGuard::redirectByRole($role);
        } else {
            $_SESSION['otp_failed_attempts'] = ($_SESSION['otp_failed_attempts'] ?? 0) + 1;
            if ($_SESSION['otp_failed_attempts'] >= 5) {
                $_SESSION['otp_failed_attempts'] = 0;
                try {
                    $pdo = get_db_connection();
                    $pdo->prepare("DELETE FROM `email_verifications` WHERE `user_id` = :u")->execute([':u' => $user_id]);
                } catch (Exception $e) { /* non-fatal */ }
                $msg = 'Maximum verification attempts exceeded. Your verification code has been invalidated. Please request a new code.';
            } else {
                $msg = $verify_res['message'];
            }
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $error = $msg;
        }
    }
}

$cooldown = can_resend_email_code($user_id);
$remaining_seconds = $cooldown['remaining_seconds'];

$page_title = 'Verify Your Email Address';
// Last line: view template
require __DIR__ . '/includes/templates/verify-email-view.php';

