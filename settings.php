<?php
/**
 * Campus Job Posting System - Account Settings
 * Archetype E: Settings & Availability Matrix (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

require_auth();
$user = get_logged_user();
$page_title = 'Account Settings & Preferences';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'profile') {
            $phone = trim($_POST['phone'] ?? '');
            $availability = $_POST['availability'] ?? [];

            $digits_only = preg_replace('/[^0-9]/', '', $phone);
            if (!empty($phone) && (preg_match('/[a-zA-Z]/', $phone) || !preg_match('/^[\+]?[0-9\s\-()]{7,20}$/', $phone) || strlen($digits_only) < 7 || strlen($digits_only) > 15)) {
                $error = 'Contact phone number must contain valid numbers only (e.g. 09171234567 or +63 917 123 4567). Letters and arbitrary text are not allowed.';
            } elseif (($user['role'] ?? '') === 'student' && (empty($availability) || count($availability) === 0)) {
                $error = 'Candidate Shift Availability is required and cannot be empty. Please select at least one weekly timeslot.';
            } else {
                $user_role = $user['role'] ?? 'student';
                $profile_data = [
                    'phone' => $phone,
                    'name' => trim($_POST['name'] ?? ''),
                    'availability' => $availability,
                    'office_location' => trim($_POST['office_location'] ?? '')
                ];

                if (update_user_profile((int)$user['id'], $user_role, $profile_data)) {
                    // Refresh session user
                    $fresh = get_user_by_id((int)$user['id']);
                    if ($fresh) {
                        unset($fresh['password']);
                        $_SESSION['user'] = $fresh;
                    }

                    set_flash('success', 'Contact information and weekly availability settings have been updated successfully.');
                    header('Location: settings.php');
                    exit;
                }

                $error = 'Failed to update profile settings. Please try again.';
            }
        } elseif ($action === 'request_profile_change') {
            $reason = trim($_POST['reason'] ?? '');
            $proof_path = null;
            if (isset($_FILES['proof_file']) && ($_FILES['proof_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $proof_path = save_uploaded_proof($_FILES['proof_file']);
            }

            if (!$proof_path) {
                $error = 'Please attach an official Certificate of Registration (COR), Student ID, or PSA document to verify your request.';
            } else {
                $res = create_profile_request($user['id'], $_POST, $proof_path, $reason);
                if ($res['success']) {
                    set_flash('success', 'Your official profile change request has been submitted to the University Admin / Registrar for review.');
                    header('Location: settings.php');
                    exit;
                } else {
                    $error = $res['message'];
                }
            }
        } elseif ($action === 'dismiss_notice') {
            dismiss_profile_request_notice($user['id']);
            header('Location: settings.php');
            exit;
        } elseif ($action === 'password') {
            $current_pass = $_POST['current_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';

            $fresh_user = get_user_by_id($user['id']);
            $stored_pass = $fresh_user['password'] ?? '';
            $is_current_valid = password_verify($current_pass, $stored_pass) || ($stored_pass === $current_pass);

            if (empty($current_pass)) {
                $error = 'Please enter your current password to confirm your identity.';
            } elseif (!$is_current_valid) {
                $error = 'The current password you entered is incorrect.';
            } elseif (strlen($new_pass) < 8) {
                $error = 'New password must contain at least 8 characters.';
            } elseif ($new_pass !== $confirm_pass) {
                $error = 'New password and confirm password do not match.';
            } else {
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                update_user_password($user['id'], $hashed_password);
                set_flash('success', 'Your password has been updated successfully.');
                header('Location: settings.php');
                exit;
            }
        }
    }
}

$user_availability = $user['availability'] ?? [
    'Mon - Morning (8AM–12NN)',
    'Wed - Morning (8AM–12NN)',
    'Fri - Afternoon (1PM–5PM)'
];

$pending_req = (($user['role'] ?? '') === 'student') ? get_pending_profile_request($user['id']) : null;
$recent_notice = (($user['role'] ?? '') === 'student') ? get_recent_profile_request_notice($user['id']) : null;

// Preload view data — no service/DB calls in the template
$view_get_system_data_mode = get_system_data_mode();
$view_get_kld_institutes_and_courses = get_kld_institutes_and_courses();
$view_get_year_levels = get_year_levels();
$view_get_sex_options = get_sex_options();

// Last line: view template
require __DIR__ . '/includes/templates/settings-view.php';

