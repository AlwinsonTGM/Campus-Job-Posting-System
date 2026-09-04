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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                $head_actions = '
                    <a href="reports.php" class="btn-pill">
                        <i class="bi bi-bar-chart-fill"></i> System Reports
                    </a>
                    <a href="categories.php" class="btn-pill-outline">
                        <i class="bi bi-grid-fill"></i> Categories
                    </a>
                ';
                render_page_head(
                    '',
                    'Campus User Directory & Roles',
                    'Manage student accounts, academic departments, and approve accredited external partner organizations.',
                    $head_actions
                );
                ?>

                <!-- Pending Verification Banners (if any) -->
                <?php if ($pending_profile_count > 0): ?>
                    <div class="card-paper bg-cream p-3 mb-4 border border-line d-flex justify-content-between align-items-center flex-wrap gap-3 reveal-fade-rise">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle icon-circle-sm icon-circle-warning">
                                <i class="bi bi-person-badge-fill fs-5"></i>
                            </div>
                            <div>
                                <strong class="text-ink"><?= $pending_profile_count ?> Student Profile Update Request(s) Awaiting Review:</strong>
                                <span class="small text-muted-custom d-block">Students have submitted COR / Student ID documents requesting updates to their verified academic records.</span>
                            </div>
                        </div>
                        <a href="#student-requests-section" class="btn-pill btn-pill-sm">
                            Review Student Requests (<?= $pending_profile_count ?>)
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($pending_count > 0): ?>
                    <div class="card-paper bg-cream p-3 mb-4 border border-line d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div>
                                <strong class="text-ink"><?= $pending_count ?> Account Registration(s) Awaiting Review:</strong>
                                <span class="small text-muted-custom d-block">
                                    <?php if ($pending_employers_count > 0 && $pending_students_count > 0): ?>
                                        <?= $pending_employers_count ?> Partner Employer(s) &bull; <?= $pending_students_count ?> Student Registration(s) requiring verification.
                                    <?php elseif ($pending_employers_count > 0): ?>
                                        <?= $pending_employers_count ?> Partner Employer(s) awaiting business permit / MOA verification.
                                    <?php else: ?>
                                        <?= $pending_students_count ?> Student Registration(s) awaiting Certificate of Registration (COR) verification.
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <a href="users.php?ver_status=pending_approval" class="btn-pill btn-pill-sm">
                            Inspect Pending (<?= $pending_count ?>)
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Student Profile Verification Requests Queue -->
                <?php if (!empty($all_profile_requests)): ?>
                    <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise" id="student-requests-section">
                        <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center bg-surface flex-wrap gap-2">
                            <div>
                                <h3 class="card-paper-title mb-1">
                                    <i class="bi bi-shield-check text-accent me-2"></i> Student Profile Change Requests
                                </h3>
                                <span class="small text-muted-custom">
                                    Official requests submitted by students to update locked academic programs, standing, or identity demographics.
                                </span>
                            </div>
                            <?php if ($pending_profile_count > 0): ?>
                                <span class="badge-status--pending"><?= $pending_profile_count ?> Pending Review</span>
                            <?php endif; ?>
                        </div>

                        <div class="table-responsive">
                            <table class="table-paper table-paper-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 140px;">Request Ref</th>
                                        <th style="max-width: 240px;">Student Details</th>
                                        <th style="max-width: 260px;">Target Program</th>
                                        <th style="width: 130px;">Status</th>
                                        <th class="text-end pe-4" style="width: 140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_profile_requests as $req): 
                                        $req_status = $req['status'] ?? 'pending';
                                        $proof = $req['proof_file'] ?? '';
                                    ?>
                                        <tr>
                                            <td class="ps-4 text-nowrap" data-label="Request Ref">
                                                <span class="font-monospace fw-bold text-ink small">#REQ-<?= str_pad((string)$req['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                                <div class="small text-muted-custom" style="font-size: 11px;"><?= date('M d, Y', strtotime($req['created_at'])) ?></div>
                                            </td>
                                            <td data-label="Student Details" style="max-width: 240px;">
                                                <div class="fw-bold text-ink text-truncate" title="<?= htmlspecialchars($req['user_name']) ?>"><?= htmlspecialchars($req['user_name']) ?></div>
                                                <div class="small text-muted-custom text-truncate" style="font-size: 11px;" title="<?= htmlspecialchars($req['user_email']) ?>">
                                                    <span><?= htmlspecialchars($req['student_id'] ?? '') ?></span> &bull; 
                                                    <span><?= htmlspecialchars($req['user_email']) ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Target Program" style="max-width: 260px;">
                                                <div class="small fw-semibold text-ink text-truncate" title="<?= htmlspecialchars(($req['requested_profile']['course'] ?? '') . ' • ' . ($req['requested_profile']['year_level'] ?? '')) ?>">
                                                    <?= htmlspecialchars($req['requested_profile']['course'] ?? '') ?>
                                                </div>
                                                <div class="small text-muted-custom" style="font-size: 11px;">
                                                    <span class="fw-semibold text-ink"><?= htmlspecialchars($req['requested_profile']['year_level'] ?? '') ?></span> &bull; <?= htmlspecialchars($req['requested_profile']['sex'] ?? 'Male') ?>, <?= htmlspecialchars((string)($req['requested_profile']['age'] ?? 20)) ?> yrs
                                                </div>
                                            </td>
                                            <td data-label="Status" class="text-nowrap">
                                                <?php if ($req_status === 'approved'): ?>
                                                    <span class="badge-status--accepted"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                                <?php elseif ($req_status === 'rejected'): ?>
                                                    <span class="badge-status--declined"><i class="bi bi-x-circle me-1"></i>Declined</span>
                                                <?php else: ?>
                                                    <span class="badge-status--pending"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4 text-nowrap" data-label="Actions">
                                                <button type="button" class="btn-pill-outline btn-pill-sm py-1 px-2" style="font-size: 11.5px;" data-bs-toggle="modal" data-bs-target="#inspectProfileReqModal<?= $req['id'] ?>">
                                                    <i class="bi bi-eye"></i> Inspect Diff
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filter & Search Bar -->
                <div class="card-paper p-4 mb-4">
                    <form action="users.php" method="GET" class="form-paper auto-filter-form">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-xl-4 col-lg-3 col-md-12">
                                <label class="form-label" for="search-user">Search User Directory</label>
                                <div class="search-input-wrap">
                                    <i class="bi bi-search text-muted-custom"></i>
                                    <input type="text" name="q" id="search-user" class="form-control" placeholder="Search name, email, MOA, ID..." value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-6 col-xl-2 col-lg-2 col-md-4">
                                <label class="form-label" for="role-select">Role</label>
                                <select name="role" id="role-select" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="student" <?= ($role_filter === 'student') ? 'selected' : '' ?>>Students</option>
                                    <option value="employer" <?= ($role_filter === 'employer') ? 'selected' : '' ?>>Employers / Offices</option>
                                    <option value="admin" <?= ($role_filter === 'admin') ? 'selected' : '' ?>>Administrators</option>
                                </select>
                            </div>

                            <div class="col-6 col-xl-3 col-lg-3 col-md-4">
                                <label class="form-label" for="emp-select">Employer Type</label>
                                <select name="emp_type" id="emp-select" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="university_office" <?= ($emp_type_filter === 'university_office') ? 'selected' : '' ?>>University Offices</option>
                                    <option value="approved_partner" <?= ($emp_type_filter === 'approved_partner') ? 'selected' : '' ?>>Approved Partners</option>
                                </select>
                            </div>

                            <div class="col-8 col-xl-2 col-lg-3 col-md-3">
                                <label class="form-label" for="ver-select">Accreditation</label>
                                <select name="ver_status" id="ver-select" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending_approval" <?= ($ver_filter === 'pending_approval') ? 'selected' : '' ?>>Pending Review</option>
                                    <option value="verified" <?= ($ver_filter === 'verified') ? 'selected' : '' ?>>Verified</option>
                                    <option value="rejected" <?= ($ver_filter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>

                            <div class="col-4 col-xl-1 col-lg-1 col-md-1 d-flex justify-content-end">
                                <div>
                                    <label class="form-label d-none d-md-block" style="visibility: hidden;">Reset</label>
                                    <a href="users.php" class="btn-filter-reset" title="Reset all filters" aria-label="Reset all filters">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Users Directory Table -->
                <div id="filter-results-container" class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
                    <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center bg-surface">
                        <div>
                            <h3 class="card-paper-title mb-1">Registered Accounts</h3>
                            <span class="small text-muted-custom">Displaying <strong><?= count($users) ?></strong> matching accounts</span>
                        </div>
                    </div>

                    <?php if (empty($users)): ?>
                        <div class="p-4">
                            <?php
                            render_empty_state(
                                'bi-people',
                                'No Users Found',
                                'No registered user accounts match the current filter criteria.',
                                'users.php',
                                'Reset User Directory'
                            );
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-paper table-paper-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="max-width: 220px;">User Details</th>
                                        <th style="width: 140px;">Role</th>
                                        <th style="max-width: 240px;">Organization / Program</th>
                                        <th style="width: 150px;">ID / Code</th>
                                        <th style="width: 130px;">Status</th>
                                        <th class="text-end pe-4" style="width: 140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): 
                                        $is_partner = ($u['employer_type'] ?? '') === 'approved_partner';
                                        $ver_status = $u['verification_status'] ?? 'verified';
                                        $org = $u['organization_name'] ?? ($u['department'] ?? 'Campus Organization');
                                    ?>
                                        <tr>
                                            <td class="ps-4" data-label="User Details" style="max-width: 220px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="icon-circle icon-circle-sm icon-circle-success flex-shrink-0" style="width: 32px; height: 32px; font-size: 13px;">
                                                        <?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <div class="min-w-0 flex-grow-1">
                                                        <div class="fw-bold text-ink text-truncate" title="<?= htmlspecialchars($u['name']) ?>"><?= htmlspecialchars($u['name']) ?></div>
                                                        <div class="small text-muted-custom text-truncate" style="font-size: 11px;" title="<?= htmlspecialchars($u['email']) ?>">
                                                            <span><?= htmlspecialchars($u['email']) ?></span>
                                                            <?php if ($u['role'] === 'student' && (!empty($u['sex']) || !empty($u['age']))): ?>
                                                                &bull; <span><?= htmlspecialchars($u['sex'] ?? 'Male') ?>, <?= htmlspecialchars((string)($u['age'] ?? 20)) ?> yrs</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Role" class="text-nowrap">
                                                <?php if ($u['role'] === 'student'): ?>
                                                    <span class="chip" style="font-size: 11px;"><i class="bi bi-mortarboard me-1"></i>Student</span>
                                                <?php elseif ($u['role'] === 'employer'): ?>
                                                    <?php if ($is_partner): ?>
                                                        <span class="chip active" style="font-size: 11px;"><i class="bi bi-patch-check-fill text-accent me-1"></i>Partner</span>
                                                    <?php else: ?>
                                                        <span class="chip" style="font-size: 11px;"><i class="bi bi-bank text-accent me-1"></i>Office</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle" style="font-size: 10px;"><i class="bi bi-shield-lock me-1"></i>Admin</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Organization / Program" style="max-width: 240px;">
                                                <div class="small fw-semibold text-ink text-truncate" title="<?= htmlspecialchars($org) ?>"><?= htmlspecialchars($org) ?></div>
                                                <div class="small text-muted-custom text-truncate" style="font-size: 11px;" title="<?= htmlspecialchars($u['course'] ?? ($u['office_location'] ?? '')) ?>">
                                                    <?= htmlspecialchars($u['course'] ?? ($u['office_location'] ?? '')) ?>
                                                    <?php if ($u['role'] === 'student' && !empty($u['year_level'])): ?>
                                                        &bull; <span class="fw-semibold text-ink"><?= htmlspecialchars($u['year_level']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td data-label="ID / Code" class="text-nowrap">
                                                <span class="font-monospace small text-ink fw-semibold" style="font-size: 11.5px;">
                                                    <?= htmlspecialchars($u['accreditation_number'] ?? ($u['student_id'] ?? 'INTERNAL')) ?>
                                                </span>
                                            </td>
                                            <td data-label="Status" class="text-nowrap">
                                                <?php if ($ver_status === 'verified'): ?>
                                                    <span class="badge-status--accepted"><i class="bi bi-check-circle me-1"></i>Verified</span>
                                                <?php elseif ($ver_status === 'rejected'): ?>
                                                    <span class="badge-status--declined"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                                <?php else: ?>
                                                    <span class="badge-status--pending"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4 text-nowrap" data-label="Actions">
                                                <div class="d-flex align-items-center justify-content-end gap-1">
                                                    <?php if ($u['role'] === 'employer'): ?>
                                                        <button type="button" class="btn-pill-outline btn-pill-sm py-1 px-2 <?= ($ver_status === 'pending_approval') ? 'border-warning text-dark fw-bold bg-warning-subtle' : '' ?>" style="font-size: 11.5px;" data-bs-toggle="modal" data-bs-target="#verifyModal<?= $u['id'] ?>">
                                                            <i class="bi bi-file-earmark-check"></i> Inspect
                                                        </button>
                                                    <?php elseif ($u['role'] === 'student'): ?>
                                                        <button type="button" class="btn-pill-outline btn-pill-sm py-1 px-2 <?= ($ver_status === 'pending_approval') ? 'border-warning text-dark fw-bold bg-warning-subtle' : '' ?>" style="font-size: 11.5px;" data-bs-toggle="modal" data-bs-target="#verifyStudentModal<?= $u['id'] ?>">
                                                            <i class="bi bi-person-check"></i> Inspect
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="small text-muted-custom">Admin Active</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <!-- Modals for Student Profile Change Requests -->
        <?php foreach ($all_profile_requests as $req): 
            $req_status = $req['status'] ?? 'pending';
            $curr = $req['current_profile'] ?? [];
            $next = $req['requested_profile'] ?? [];
            $proof = $req['proof_file'] ?? '';
            $has_proof = !empty($proof) && file_exists(__DIR__ . '/../' . $proof);
        ?>
        <div class="modal fade" id="inspectProfileReqModal<?= $req['id'] ?>" tabindex="-1" aria-labelledby="inspectProfileReqModalLabel<?= $req['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle icon-circle-sm icon-circle-warning">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0" id="inspectProfileReqModalLabel<?= $req['id'] ?>">
                                    Student Profile Update Request #REQ-<?= str_pad((string)$req['id'], 4, '0', STR_PAD_LEFT) ?>
                                </h5>
                                <span class="small text-muted-custom">
                                    <?= htmlspecialchars($req['user_name']) ?> (<?= htmlspecialchars($req['student_id'] ?? '') ?>) &bull; <?= date('M d, Y h:i A', strtotime($req['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left 7-col: Side-by-Side Diff Table -->
                            <div class="col-lg-7 border-end-lg border-line">
                                <h4 class="card-paper-title fs-6 mb-3">
                                    <i class="bi bi-arrow-left-right text-accent me-2"></i> Current vs. Requested Changes (Diff Comparison)
                                </h4>

                                <div class="table-responsive mb-3">
                                    <table class="table-paper mb-0 small">
                                        <thead>
                                            <tr>
                                                <th>Profile Field</th>
                                                <th>Current Record</th>
                                                <th>Requested Update</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $fields_to_compare = [
                                                'name' => 'Full Name',
                                                'department' => 'Academic Institute',
                                                'course' => 'Degree Program / Course',
                                                'year_level' => 'Year Level / Standing',
                                                'sex' => 'Biological Sex',
                                                'birthdate' => 'Date of Birth',
                                                'age' => 'Derived Age'
                                            ];
                                            foreach ($fields_to_compare as $f_key => $f_label):
                                                $curr_val = (string)($curr[$f_key] ?? '');
                                                $next_val = (string)($next[$f_key] ?? '');
                                                $is_changed = ($curr_val !== $next_val && !empty($next_val));
                                            ?>
                                                <tr class="<?= $is_changed ? 'bg-cream' : '' ?>">
                                                    <td class="fw-bold text-ink">
                                                        <?= $f_label ?>
                                                        <?php if ($is_changed): ?>
                                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">MODIFIED</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-muted-custom">
                                                        <?= htmlspecialchars($curr_val ?: '—') ?>
                                                    </td>
                                                    <td class="<?= $is_changed ? 'fw-bold text-accent' : 'text-ink' ?>">
                                                        <?= htmlspecialchars($next_val ?: '—') ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if (!empty($req['reason'])): ?>
                                    <div class="p-3 bg-surface rounded-3 border border-line small mb-2">
                                        <strong class="text-ink d-block mb-1"><i class="bi bi-chat-left-quote text-accent me-1"></i> Student Remarks / Justification:</strong>
                                        <span class="text-muted-custom">"<?= htmlspecialchars($req['reason']) ?>"</span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($req['admin_notes'])): ?>
                                    <div class="p-3 bg-cream rounded-3 border border-line small">
                                        <strong class="text-ink d-block mb-1"><i class="bi bi-pencil-square text-accent me-1"></i> Admin Resolution Notes:</strong>
                                        <span class="text-muted-custom"><?= htmlspecialchars($req['admin_notes']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right 5-col: Proof Document Viewer & Actions -->
                            <div class="col-lg-5">
                                <h4 class="card-paper-title fs-6 mb-3">
                                    <i class="bi bi-file-earmark-check text-accent me-2"></i> Supporting Verification Proof
                                </h4>

                                <?php if ($has_proof): 
                                    $ext = strtolower(pathinfo($proof, PATHINFO_EXTENSION));
                                ?>
                                    <div class="card-paper p-2 text-center bg-surface border border-line mb-3">
                                        <?php if ($ext === 'pdf'): ?>
                                            <div class="py-4 text-center">
                                                <i class="bi bi-file-earmark-pdf fs-1 text-danger d-block mb-2"></i>
                                                <div class="fw-bold text-ink small">Certificate of Registration (PDF)</div>
                                                <span class="small text-muted-custom"><?= htmlspecialchars(basename($proof)) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <img src="../<?= htmlspecialchars($proof) ?>" alt="Student Proof Document" class="img-fluid rounded-3 border border-line" style="max-height: 240px; object-fit: contain; width: 100%; background: #ffffff;">
                                        <?php endif; ?>

                                        <a href="../<?= htmlspecialchars($proof) ?>" target="_blank" class="btn-pill-outline btn-pill-sm w-100 mt-2">
                                            <i class="bi bi-arrows-fullscreen"></i> View Document in Full Tab
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 bg-cream rounded-4 border border-line text-center text-muted-custom mb-3">
                                        <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted-custom"></i>
                                        <strong class="text-ink small">No Valid Document Found</strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($req_status === 'pending'): ?>
                                    <div class="p-3 bg-surface rounded-4 border border-line">
                                        <strong class="text-ink small d-block mb-2"><i class="bi bi-sliders text-accent me-1"></i> Resolve Verification:</strong>
                                        
                                        <!-- Approve Form -->
                                        <form action="users.php" method="POST" class="mb-3">
                                            <input type="hidden" name="action" value="approve_profile_req">
                                            <input type="hidden" name="req_id" value="<?= $req['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <div class="mb-2">
                                                <label class="form-label small text-muted-custom" style="font-size: 11px;">Approval Supervisor Notes (Optional)</label>
                                                <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="e.g. Verified against KLD Enrollment Database 1st Sem 2026-2027">
                                            </div>
                                            <button type="submit" class="btn-pill btn-pill-sm w-100" onclick="return confirm('Approve this student profile change? Institutional records will be updated immediately.')">
                                                <i class="bi bi-check-circle-fill"></i> Approve &amp; Update Official Record
                                            </button>
                                        </form>

                                        <!-- Reject Form -->
                                        <form action="users.php" method="POST">
                                            <input type="hidden" name="action" value="reject_profile_req">
                                            <input type="hidden" name="req_id" value="<?= $req['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <div class="mb-2">
                                                <label class="form-label small text-muted-custom" style="font-size: 11px;">Rejection / Revision Reason</label>
                                                <input type="text" name="admin_notes" class="form-control form-control-sm" placeholder="e.g. Unclear COR photo, please re-upload clear copy." required>
                                            </div>
                                            <button type="submit" class="btn-pill-outline btn-pill-sm w-100 text-danger border-danger">
                                                <i class="bi bi-x-circle"></i> Decline Request
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 bg-cream rounded-4 border border-line text-center">
                                        <?php if ($req_status === 'approved'): ?>
                                            <span class="badge-status--accepted fs-6 d-inline-block py-2 px-3"><i class="bi bi-check-circle me-1"></i> Request Approved &amp; Record Updated</span>
                                        <?php else: ?>
                                            <span class="badge-status--declined fs-6 d-inline-block py-2 px-3"><i class="bi bi-x-circle me-1"></i> Request Declined</span>
                                        <?php endif; ?>
                                        <div class="small text-muted-custom mt-2">Resolved on <?= date('M d, Y h:i A', strtotime($req['resolved_at'] ?? $req['created_at'])) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-cream border-top border-line py-3 px-4">
                        <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Document Verification Modals for Employer Partners -->
        <?php foreach ($users as $u): 
            if ($u['role'] !== 'employer') continue;
            $is_partner = ($u['employer_type'] ?? '') === 'approved_partner';
            $ver_status = $u['verification_status'] ?? 'verified';
            $org = $u['organization_name'] ?? ($u['department'] ?? 'Campus Organization');
            $accreditation = $u['accreditation_number'] ?? 'PENDING-VERIFICATION';
            $permit_doc = $u['permit_file'] ?? null;
            $has_permit_file = !empty($permit_doc) && file_exists(__DIR__ . '/../' . $permit_doc);
        ?>
        <div class="modal fade" id="verifyModal<?= $u['id'] ?>" tabindex="-1" aria-labelledby="verifyModalLabel<?= $u['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0" id="verifyModalLabel<?= $u['id'] ?>">Permit &amp; MOA Verification</h5>
                                <span class="small text-muted-custom"><?= htmlspecialchars($org) ?> &bull; <?= $is_partner ? 'Approved Industry Partner' : 'University Office' ?></span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            
                            <!-- Left: Registration Information -->
                            <div class="col-md-6 border-end-md border-line">
                                <h4 class="card-paper-title fs-6 mb-3">
                                    <i class="bi bi-info-circle text-accent me-1"></i> Registration Details
                                </h4>

                                <div class="p-3 bg-surface rounded-4 border border-line mb-3">
                                    <span class="small text-muted-custom d-block mb-1">Company / Organization</span>
                                    <strong class="text-ink fs-6"><?= htmlspecialchars($org) ?></strong>
                                    <div class="small text-muted-custom mt-1">
                                        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($u['name']) ?>
                                    </div>
                                </div>

                                <div class="p-3 bg-cream rounded-4 border border-line mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold text-ink text-uppercase" style="font-size: 11px;">
                                            <i class="bi bi-key-fill text-accent me-1"></i> MOA / Business Permit Code
                                        </span>
                                        <button type="button" class="btn-pill-outline btn-pill-sm py-0 px-2" style="font-size: 11px;" onclick="copyCodeText('code-<?= $u['id'] ?>', this)">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                    <div class="p-2 bg-white rounded-3 border border-line font-monospace fw-bold text-ink fs-6" id="code-<?= $u['id'] ?>">
                                        <?= htmlspecialchars($accreditation) ?>
                                    </div>
                                </div>

                                <ul class="list-unstyled small text-muted-custom mb-0 d-flex flex-column gap-2">
                                    <li><strong class="text-ink">Email:</strong> <?= htmlspecialchars($u['email']) ?></li>
                                    <li><strong class="text-ink">Contact Phone:</strong> <?= htmlspecialchars($u['phone'] ?? 'Not provided') ?></li>
                                    <li><strong class="text-ink">Workplace Address:</strong> <?= htmlspecialchars($u['office_location'] ?? 'Campus Main Office') ?></li>
                                </ul>
                            </div>

                            <!-- Right: Document Preview -->
                            <div class="col-md-6">
                                <h4 class="card-paper-title fs-6 mb-3">
                                    <i class="bi bi-file-earmark-image text-accent me-1"></i> Uploaded Document
                                </h4>

                                <?php if ($has_permit_file): 
                                    $ext = strtolower(pathinfo($permit_doc, PATHINFO_EXTENSION));
                                ?>
                                    <div class="card-paper p-2 text-center bg-surface border border-line">
                                        <?php if ($ext === 'pdf'): ?>
                                            <div class="py-4 text-center">
                                                <i class="bi bi-file-earmark-pdf fs-1 text-danger d-block mb-2"></i>
                                                <div class="fw-bold text-ink small">PDF Document Attached</div>
                                                <span class="small text-muted-custom"><?= basename($permit_doc) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <img src="../<?= htmlspecialchars($permit_doc) ?>" alt="Permit Document" class="img-fluid rounded-3 border border-line" style="max-height: 220px; object-fit: contain; width: 100%; background: #ffffff;">
                                        <?php endif; ?>

                                        <a href="../<?= htmlspecialchars($permit_doc) ?>" target="_blank" class="btn-pill-outline btn-pill-sm w-100 mt-2">
                                            <i class="bi bi-arrows-fullscreen"></i> View Document in Full Tab
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 bg-cream rounded-4 border border-line text-center text-muted-custom">
                                        <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted-custom"></i>
                                        <strong class="text-ink small">No File Uploaded</strong>
                                        <p class="small text-muted-custom mb-0 mt-1" style="font-size: 11px;">
                                            Organization registered with accreditation code: <strong><?= htmlspecialchars($accreditation) ?></strong>.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-cream border-top border-line py-3 px-4 d-flex justify-content-between">
                        <div>
                            <?php if ($ver_status === 'verified'): ?>
                                <span class="badge-status--accepted"><i class="bi bi-shield-check me-1"></i> Officially Verified</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($ver_status !== 'verified'): ?>
                                <form action="users.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="reject_employer">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn-pill-outline btn-pill-sm text-danger border-danger" onclick="return confirm('Reject this employer registration?')">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </form>
                                <form action="users.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="approve_employer">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn-pill btn-pill-sm">
                                        <i class="bi bi-check-circle-fill"></i> Approve &amp; Verify
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="users.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="reject_employer">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="notes" value="Revoked by admin">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn-pill-outline btn-pill-sm text-danger border-danger" onclick="return confirm('Revoke verification for this partner?')">
                                        <i class="bi bi-arrow-counterclockwise"></i> Revoke
                                    </button>
                                </form>
                            <?php endif; ?>
                            <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Document Verification Modals for Student Accounts -->
        <?php foreach ($users as $u): 
            if ($u['role'] !== 'student') continue;
            $ver_status = $u['verification_status'] ?? 'verified';
            $proof_doc = $u['registration_proof'] ?? ($u['proof_file'] ?? null);
            $has_proof_file = !empty($proof_doc) && file_exists(__DIR__ . '/../' . $proof_doc);
            $is_pdf = $has_proof_file && (strtolower(pathinfo($proof_doc, PATHINFO_EXTENSION)) === 'pdf');
        ?>
        <div class="modal fade" id="verifyStudentModal<?= $u['id'] ?>" tabindex="-1" aria-labelledby="verifyStudentModalLabel<?= $u['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0" id="verifyStudentModalLabel<?= $u['id'] ?>">Student Credential Verification</h5>
                                <span class="small text-muted-custom"><?= htmlspecialchars($u['name']) ?> &bull; <?= htmlspecialchars($u['student_id'] ?? 'Student') ?></span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left 7-col: Student Dossier Details -->
                            <div class="col-lg-7">
                                <h6 class="fw-bold text-ink mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-person-lines-fill text-accent"></i> Academic Registration Record
                                </h6>

                                <div class="bg-surface p-3 rounded-3 border border-line mb-3">
                                    <div class="row g-2 small">
                                        <div class="col-5 text-muted-custom">Full Legal Name:</div>
                                        <div class="col-7 fw-bold text-ink"><?= htmlspecialchars($u['name']) ?></div>

                                        <div class="col-5 text-muted-custom">Student ID:</div>
                                        <div class="col-7 font-monospace fw-bold text-ink"><?= htmlspecialchars($u['student_id'] ?? 'N/A') ?></div>

                                        <div class="col-5 text-muted-custom">Institutional Email:</div>
                                        <div class="col-7 font-monospace text-ink text-break"><?= htmlspecialchars($u['email']) ?></div>

                                        <div class="col-5 text-muted-custom">Academic Institute:</div>
                                        <div class="col-7 fw-semibold text-ink"><?= htmlspecialchars($u['department'] ?? 'N/A') ?></div>

                                        <div class="col-5 text-muted-custom">Degree Program:</div>
                                        <div class="col-7 fw-semibold text-ink"><?= htmlspecialchars($u['course'] ?? 'N/A') ?></div>

                                        <div class="col-5 text-muted-custom">Year Level:</div>
                                        <div class="col-7 text-ink"><?= htmlspecialchars($u['year_level'] ?? 'N/A') ?></div>

                                        <div class="col-5 text-muted-custom">Sex &amp; Age:</div>
                                        <div class="col-7 text-ink"><?= htmlspecialchars($u['sex'] ?? 'N/A') ?>, <?= htmlspecialchars((string)($u['age'] ?? 'N/A')) ?> yrs</div>

                                        <div class="col-5 text-muted-custom">Phone Contact:</div>
                                        <div class="col-7 text-ink"><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></div>

                                        <div class="col-5 text-muted-custom">Registered Date:</div>
                                        <div class="col-7 text-ink"><?= date('M d, Y h:i A', strtotime($u['created_at'])) ?></div>

                                        <div class="col-5 text-muted-custom">Verification Status:</div>
                                        <div class="col-7">
                                            <?php if ($ver_status === 'verified'): ?>
                                                <span class="badge-status--accepted"><i class="bi bi-check-circle me-1"></i>Verified Active</span>
                                            <?php elseif ($ver_status === 'rejected'): ?>
                                                <span class="badge-status--declined"><i class="bi bi-x-circle me-1"></i>Rejected / Revoked</span>
                                            <?php else: ?>
                                                <span class="badge-status--pending"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($u['rejection_reason'])): ?>
                                    <div class="p-3 bg-danger-subtle text-danger-emphasis rounded-3 border border-danger-subtle small mb-3">
                                        <strong>Admin Notes / Feedback:</strong>
                                        <p class="mb-0 mt-1"><?= htmlspecialchars($u['rejection_reason']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right 5-col: Proof of Registration Document -->
                            <div class="col-lg-5">
                                <h6 class="fw-bold text-ink mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-check text-accent"></i> Certificate of Registration / ID
                                </h6>

                                <?php if ($has_proof_file): ?>
                                    <div class="card-paper p-2 text-center bg-surface border border-line">
                                        <?php if ($is_pdf): ?>
                                            <div class="py-4">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-1 d-block mb-2"></i>
                                                <strong class="text-ink small d-block mb-2">COR Document (PDF)</strong>
                                                <a href="../<?= htmlspecialchars($proof_doc) ?>" target="_blank" class="btn-pill btn-pill-sm d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-box-arrow-up-right"></i> Open PDF Document
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <a href="../<?= htmlspecialchars($proof_doc) ?>" target="_blank" title="Click to view full size">
                                                <img src="../<?= htmlspecialchars($proof_doc) ?>" alt="Student Proof" class="img-fluid rounded border border-line" style="max-height: 220px; object-fit: contain;">
                                            </a>
                                            <div class="mt-2">
                                                <a href="../<?= htmlspecialchars($proof_doc) ?>" target="_blank" class="small fw-semibold text-ink text-decoration-none">
                                                    <i class="bi bi-zoom-in me-1"></i> View Full Attachment
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif (!empty($proof_doc)): ?>
                                    <div class="card-paper p-3 text-center bg-surface border border-line">
                                        <i class="bi bi-file-earmark-text text-accent fs-1 d-block mb-2"></i>
                                        <strong class="text-ink small d-block mb-1">Document Attachment</strong>
                                        <a href="../<?= htmlspecialchars($proof_doc) ?>" target="_blank" class="btn-pill btn-pill-sm d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-box-arrow-up-right"></i> View Document
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 bg-cream rounded-4 border border-line text-center text-muted-custom">
                                        <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted-custom"></i>
                                        <strong class="text-ink small">No Proof Uploaded</strong>
                                        <p class="small text-muted-custom mb-0 mt-1" style="font-size: 11px;">
                                            Account registered via campus direct registration.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-cream border-top border-line py-3 px-4 d-flex justify-content-between">
                        <div>
                            <?php if ($ver_status === 'verified'): ?>
                                <span class="badge-status--accepted"><i class="bi bi-shield-check me-1"></i> Verified Student</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($ver_status !== 'verified'): ?>
                                <form action="users.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="reject_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn-pill-outline btn-pill-sm text-danger border-danger" onclick="return confirm('Decline this student registration?')">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </form>
                                <form action="users.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="approve_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn-pill btn-pill-sm">
                                        <i class="bi bi-check-circle-fill"></i> Approve &amp; Verify Student
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="users.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="reject_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="notes" value="Verification revoked by admin">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn-pill-outline btn-pill-sm text-danger border-danger" onclick="return confirm('Revoke verification for this student?')">
                                        <i class="bi bi-arrow-counterclockwise"></i> Revoke
                                    </button>
                                </form>
                            <?php endif; ?>
                            <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<script>
function copyCodeText(elementId, btnElement) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="bi bi-check2 text-success"></i> Copied!';
        setTimeout(() => {
            btnElement.innerHTML = originalHtml;
        }, 1500);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[action="users.php"][method="POST"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';
                }, 10);
            }
        });
    });
});
</script>
