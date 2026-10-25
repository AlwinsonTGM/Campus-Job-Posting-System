<?php
/**
 * Campus Job Posting System - Admin User Directory & Verification Management
 * Archetype D/G: User Management & Document Verification (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
purge_expired_unverified_users();
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
    } elseif ($action === 'reject_user' || $action === 'reject_student' || $action === 'reject_employer' || $action === 'revoke_user') {
        $reject_id = (int)($_POST['id'] ?? 0);
        $target_user = get_user_by_id($reject_id);
        if ($target_user && ($target_user['role'] ?? '') === 'admin') {
            set_flash('danger', 'Unauthorized operation: Administrator accounts cannot be modified through user verification.');
            header('Location: users.php');
            exit;
        }
        $notes = trim($_POST['notes'] ?? '');
        $is_revoke = ($action === 'revoke_user' || stripos($notes, 'revok') !== false);
        if ($notes === '') {
            $notes = $is_revoke ? 'Verification revoked by admin' : 'Submitted credentials did not match or require re-submission.';
        }
        $target_status = $is_revoke ? 'revoked' : 'rejected';
        if (update_user_verification($reject_id, $target_status, $notes)) {
            $u_name = $target_user['name'] ?? 'Account';
            $role_label = ucfirst($target_user['role'] ?? 'User');
            $msg = $is_revoke 
                ? "{$role_label} '{$u_name}' verification has been officially revoked."
                : "{$role_label} '{$u_name}' registration has been marked as rejected / revision requested.";
            set_flash('warning', $msg);
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
    } elseif ($action === 'suspend_user') {
        $suspend_id = (int)($_POST['id'] ?? 0);
        $target_user = get_user_by_id($suspend_id);
        if ($target_user && ($target_user['role'] ?? '') === 'admin') {
            set_flash('danger', 'Unauthorized operation: Administrator accounts cannot be suspended.');
            header('Location: users.php');
            exit;
        }
        if (suspend_user($suspend_id)) {
            $u_name = $target_user['name'] ?? 'User';
            set_flash('warning', "Account '{$u_name}' has been archived and removed from the active directory.");
            header('Location: users.php');
            exit;
        }
    } elseif ($action === 'reactivate_user') {
        $reactivate_id = (int)($_POST['id'] ?? 0);
        $target_user = get_user_by_id($reactivate_id);
        if (reactivate_user($reactivate_id)) {
            $u_name = $target_user['name'] ?? 'User';
            set_flash('success', "Account '{$u_name}' has been reactivated successfully!");
            header('Location: users.php');
            exit;
        }
    }
}

$role_filter = $_GET['role'] ?? null;
$emp_type_filter = $_GET['emp_type'] ?? null;
$ver_filter = $_GET['ver_status'] ?? null;
$search = $_GET['q'] ?? null;
$search_field = $_GET['search_field'] ?? null;
$sort = $_GET['sort'] ?? 'newest';

// Count pending verifications & student requests across active un-archived roles
$all_users = get_all_users();
$pending_employers_count = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'employer' && ($u['verification_status'] ?? '') === 'pending_approval'));
$pending_students_count = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'student' && ($u['verification_status'] ?? '') === 'pending_approval'));
$pending_count = $pending_employers_count + $pending_students_count;
$revoked_users_count = count(array_filter($all_users, fn($u) => ($u['verification_status'] ?? '') === 'revoked' || !empty($u['is_revoked'])));

$all_profile_requests = get_profile_requests();
$pending_profile_requests = array_filter($all_profile_requests, fn($r) => ($r['status'] ?? '') === 'pending');
$pending_profile_count = count($pending_profile_requests);

// Archived accounts act as deleted on the website and are never shown in active directory views
$users = $all_users;

if ($role_filter) {
    $users = array_filter($users, fn($u) => $u['role'] === $role_filter);
}

if ($emp_type_filter) {
    $users = array_filter($users, fn($u) => ($u['employer_type'] ?? '') === $emp_type_filter);
}

if ($ver_filter) {
    $users = array_filter($users, function($u) use ($ver_filter) {
        $st = $u['verification_status'] ?? 'verified';
        if ($ver_filter === 'revoked') {
            return $st === 'revoked' || !empty($u['is_revoked']);
        }
        return $st === $ver_filter;
    });
}

if ($search) {
    $q = strtolower(trim($search));
    if ($search_field === 'org_program') {
        // Targeted search: Organization / Company / Department / Program / Course
        $users = array_filter($users, fn($u) => 
            stripos($u['organization_name'] ?? '', $q) !== false || 
            stripos($u['department'] ?? '', $q) !== false || 
            stripos($u['course'] ?? '', $q) !== false || 
            stripos($u['student_department'] ?? '', $q) !== false
        );
    } elseif ($search_field === 'id_code') {
        // Targeted search: Student ID, Accreditation / MOA Number, or System Account ID
        $users = array_filter($users, function($u) use ($q) {
            $sid = (string)($u['student_id'] ?? '');
            $acc = (string)($u['accreditation_number'] ?? '');
            $uid = (string)($u['id'] ?? '');
            $reqId = 'req-' . str_pad($uid, 4, '0', STR_PAD_LEFT);
            return stripos($sid, $q) !== false || 
                   stripos($acc, $q) !== false || 
                   $uid === $q || 
                   stripos($reqId, $q) !== false;
        });
    } else {
        // Universal search: Name, Email, Student ID, Organization, MOA Number, Course, Department
        $users = array_filter($users, fn($u) => 
            stripos($u['name'] ?? '', $q) !== false || 
            stripos($u['email'] ?? '', $q) !== false || 
            stripos($u['student_id'] ?? '', $q) !== false || 
            stripos($u['organization_name'] ?? '', $q) !== false || 
            stripos($u['accreditation_number'] ?? '', $q) !== false ||
            stripos($u['course'] ?? '', $q) !== false ||
            stripos($u['department'] ?? '', $q) !== false
        );
    }
}

// Apply user directory sorting
switch ($sort) {
    case 'name_asc':
        usort($users, fn($a, $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''));
        break;
    case 'name_desc':
        usort($users, fn($a, $b) => strcasecmp($b['name'] ?? '', $a['name'] ?? ''));
        break;
    case 'oldest':
        usort($users, function($a, $b) {
            $tA = strtotime($a['created_at'] ?? '') ?: ($a['id'] ?? 0);
            $tB = strtotime($b['created_at'] ?? '') ?: ($b['id'] ?? 0);
            return $tA <=> $tB;
        });
        break;
    case 'email_asc':
        usort($users, fn($a, $b) => strcasecmp($a['email'] ?? '', $b['email'] ?? ''));
        break;
    case 'role_student':
        $roleOrder = ['student' => 1, 'employer' => 2, 'admin' => 3];
        usort($users, function($a, $b) use ($roleOrder) {
            $rA = $roleOrder[$a['role'] ?? ''] ?? 99;
            $rB = $roleOrder[$b['role'] ?? ''] ?? 99;
            if ($rA === $rB) return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            return $rA <=> $rB;
        });
        break;
    case 'role_employer':
        $roleOrder = ['employer' => 1, 'student' => 2, 'admin' => 3];
        usort($users, function($a, $b) use ($roleOrder) {
            $rA = $roleOrder[$a['role'] ?? ''] ?? 99;
            $rB = $roleOrder[$b['role'] ?? ''] ?? 99;
            if ($rA === $rB) return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            return $rA <=> $rB;
        });
        break;
    case 'role_admin':
        $roleOrder = ['admin' => 1, 'employer' => 2, 'student' => 3];
        usort($users, function($a, $b) use ($roleOrder) {
            $rA = $roleOrder[$a['role'] ?? ''] ?? 99;
            $rB = $roleOrder[$b['role'] ?? ''] ?? 99;
            if ($rA === $rB) return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            return $rA <=> $rB;
        });
        break;
    case 'pending_first':
        usort($users, function($a, $b) {
            $isPendingA = (($a['verification_status'] ?? '') === 'pending_approval') ? 0 : 1;
            $isPendingB = (($b['verification_status'] ?? '') === 'pending_approval') ? 0 : 1;
            if ($isPendingA === $isPendingB) {
                return (strtotime($b['created_at'] ?? '') ?: ($b['id'] ?? 0)) <=> (strtotime($a['created_at'] ?? '') ?: ($a['id'] ?? 0));
            }
            return $isPendingA <=> $isPendingB;
        });
        break;
    case 'verified_first':
        usort($users, function($a, $b) {
            $isVerA = (($a['verification_status'] ?? '') === 'verified') ? 0 : 1;
            $isVerB = (($b['verification_status'] ?? '') === 'verified') ? 0 : 1;
            if ($isVerA === $isVerB) {
                return (strtotime($b['created_at'] ?? '') ?: ($b['id'] ?? 0)) <=> (strtotime($a['created_at'] ?? '') ?: ($a['id'] ?? 0));
            }
            return $isVerA <=> $isVerB;
        });
        break;
    case 'newest':
    default:
        usort($users, function($a, $b) {
            $tA = strtotime($a['created_at'] ?? '') ?: ($a['id'] ?? 0);
            $tB = strtotime($b['created_at'] ?? '') ?: ($b['id'] ?? 0);
            return $tB <=> $tA;
        });
        break;
}

$total_filtered_users = count($users);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 25;
$total_pages = max(1, (int)ceil($total_filtered_users / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$paged_users = array_slice(array_values($users), ($page - 1) * $per_page, $per_page);
$users = $paged_users;

// Last line: view template
require __DIR__ . '/../includes/templates/admin-users-view.php';

