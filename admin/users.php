<?php
/**
 * Campus Job Posting System - Admin User Directory & Verification Management
 * Archetype D/G: User Management & Document Verification (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'User Directory & Partner Verification';

$users = get_all_users();

// Reject any deprecated GET-based mutation attempts
if (isset($_GET['approve_id']) || isset($_GET['reject_id'])) {
    set_flash('danger', 'Invalid request method. Verification actions require a secure POST submission.');
    header('Location: users.php');
    exit;
}

// Handle POST actions with CSRF token verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash('danger', 'Security validation failed: Invalid or expired security token. Please try again.');
        header('Location: users.php');
        exit;
    }

    if ($action === 'approve_user' || $action === 'approve_student' || $action === 'approve_employer') {
        $approve_id = (int)($_POST['id'] ?? 0);
        $target_user = get_user_by_id($approve_id);
        if ($target_user && ($target_user['role'] ?? '') === 'admin') {
            set_flash('danger', 'Unauthorized operation: Administrator accounts cannot be modified through user verification.');
            header('Location: users.php');
            exit;
        }
        if (update_user_verification($approve_id, 'verified')) {
            $u_name = $target_user['name'] ?? 'Account';
            $role_label = ucfirst($target_user['role'] ?? 'User');
            set_flash('success', "{$role_label} '{$u_name}' has been officially approved & verified!");
            header('Location: users.php');
            exit;
        }
    } elseif ($action === 'reject_user' || $action === 'reject_student' || $action === 'reject_employer') {
        $reject_id = (int)($_POST['id'] ?? 0);
        $target_user = get_user_by_id($reject_id);
        if ($target_user && ($target_user['role'] ?? '') === 'admin') {
            set_flash('danger', 'Unauthorized operation: Administrator accounts cannot be modified through user verification.');
            header('Location: users.php');
            exit;
        }
        $notes = trim($_POST['notes'] ?? 'Submitted credentials did not match or require re-submission.');
        if (update_user_verification($reject_id, 'rejected', $notes)) {
            $u_name = $target_user['name'] ?? 'Account';
            $role_label = ucfirst($target_user['role'] ?? 'User');
            set_flash('warning', "{$role_label} '{$u_name}' registration has been marked as rejected / revision requested.");
            header('Location: users.php');
            exit;
        }
    } elseif ($action === 'approve_profile_req') {
        $req_id = (int)($_POST['req_id'] ?? 0);
        $notes = trim($_POST['admin_notes'] ?? 'Approved by University Registrar / Administrator');
        if (approve_profile_request($req_id, $notes)) {
            set_flash('success', "Student profile change request #{$req_id} approved. Student institutional records updated!");
            header('Location: users.php');
            exit;
        }
    } elseif ($action === 'reject_profile_req') {
        $req_id = (int)($_POST['req_id'] ?? 0);
        $notes = trim($_POST['admin_notes'] ?? 'Submitted proof is invalid, expired, or does not match institutional records.');
        if (reject_profile_request($req_id, $notes)) {
            set_flash('warning', "Student profile change request #{$req_id} declined. Student notified.");
            header('Location: users.php');
            exit;
        }
    }
}

$role_filter = $_GET['role'] ?? null;
$emp_type_filter = $_GET['emp_type'] ?? null;
$ver_filter = $_GET['ver_status'] ?? null;
$search = $_GET['q'] ?? null;

// Count pending verifications & student requests across all roles
$all_users = get_all_users();
$pending_employers_count = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'employer' && ($u['verification_status'] ?? '') === 'pending_approval'));
$pending_students_count = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'student' && ($u['verification_status'] ?? '') === 'pending_approval'));
$pending_count = $pending_employers_count + $pending_students_count;

$all_profile_requests = get_profile_requests();
$pending_profile_requests = array_filter($all_profile_requests, fn($r) => ($r['status'] ?? '') === 'pending');
$pending_profile_count = count($pending_profile_requests);

if ($role_filter) {
    $users = array_filter($users, fn($u) => $u['role'] === $role_filter);
}

if ($emp_type_filter) {
    $users = array_filter($users, fn($u) => ($u['employer_type'] ?? '') === $emp_type_filter);
}

if ($ver_filter) {
    $users = array_filter($users, fn($u) => ($u['verification_status'] ?? 'verified') === $ver_filter);
}

if ($search) {
    $q = strtolower(trim($search));
    $users = array_filter($users, fn($u) => stripos($u['name'] ?? '', $q) !== false || stripos($u['email'] ?? '', $q) !== false || stripos($u['student_id'] ?? '', $q) !== false || stripos($u['organization_name'] ?? '', $q) !== false || stripos($u['accreditation_number'] ?? '', $q) !== false);
}

// Verification queue triage (read-only completeness scoring; never
// approves or rejects — POST actions stay sole gate). Scope note: account items
// respect the current role/search filter ($users), while profile requests are
// always unfiltered (that queue has no filter in this controller).
$triage_users = array_values(array_filter($users, fn($u) => ($u['verification_status'] ?? '') === 'pending_approval'));
$verification_triage = get_verification_triage($triage_users, array_values($pending_profile_requests));
// Last line: view template
require __DIR__ . '/../includes/templates/admin-users-view.php';

