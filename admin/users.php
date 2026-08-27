<?php
/**
 * Campus Job Posting System - Admin User Management
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'User Management';

$users = $_SESSION['users'] ?? load_json_file('users.json');

// Handle verification approval action
if (isset($_GET['approve_id'])) {
    $approve_id = (int)$_GET['approve_id'];
    foreach ($users as $key => $u) {
        if ($u['id'] == $approve_id) {
            $users[$key]['verification_status'] = 'verified';
            $_SESSION['users'] = $users;
            save_json_file('users.json', $users);
            set_flash('success', "Partner employer '{$u['name']}' has been officially verified!");
            header('Location: users.php');
            exit;
        }
    }
}

$role_filter = $_GET['role'] ?? null;
$emp_type_filter = $_GET['emp_type'] ?? null;
$search = $_GET['q'] ?? null;

if ($role_filter) {
    $users = array_filter($users, fn($u) => $u['role'] === $role_filter);
}

if ($emp_type_filter) {
    $users = array_filter($users, fn($u) => ($u['employer_type'] ?? '') === $emp_type_filter);
}

if ($search) {
    $q = strtolower(trim($search));
    $users = array_filter($users, fn($u) => stripos($u['name'], $q) !== false || stripos($u['email'], $q) !== false || stripos($u['student_id'] ?? '', $q) !== false || stripos($u['organization_name'] ?? '', $q) !== false || stripos($u['accreditation_number'] ?? '', $q) !== false);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="reports.php">Admin Control</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Users</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-ink mb-0">System User & Partner Directory</h2>
                <p class="text-muted-custom small mb-0">Manage student accounts, academic departments, and accredited external employers</p>
            </div>
            <div class="mt-2 mt-md-0 text-muted-custom small">
                Total Registered Accounts: <strong><?= count($users) ?></strong>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-line shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="users.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search text-muted-custom"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search by name, email, org, or MOA reference..." value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Account Roles</option>
                        <option value="student" <?= ($role_filter === 'student') ? 'selected' : '' ?>>Students Only</option>
                        <option value="employer" <?= ($role_filter === 'employer') ? 'selected' : '' ?>>Employers & Offices Only</option>
                        <option value="admin" <?= ($role_filter === 'admin') ? 'selected' : '' ?>>Administrators Only</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="emp_type" class="form-select">
                        <option value="">All Employer Types</option>
                        <option value="university_office" <?= ($emp_type_filter === 'university_office') ? 'selected' : '' ?>>University Offices</option>
                        <option value="approved_partner" <?= ($emp_type_filter === 'approved_partner') ? 'selected' : '' ?>>Approved Partners</option>
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn-accent-pill w-100 py-2" title="Filter"><i class="bi bi-funnel-fill"></i></button>
                    <?php if ($role_filter || $emp_type_filter || $search): ?>
                        <a href="users.php" class="btn-circle-icon flex-shrink-0" style="width: 38px; height: 38px;" title="Reset"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card border-line shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-cream border-bottom border-line">
                        <tr>
                            <th class="ps-4 text-ink fw-bold">User Details</th>
                            <th class="text-ink fw-bold">Role & Classification</th>
                            <th class="text-ink fw-bold">Organization / Institute</th>
                            <th class="text-ink fw-bold">Accreditation / MOA</th>
                            <th class="text-ink fw-bold">Verification Status</th>
                            <th class="text-end pe-4 text-ink fw-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): 
                            $is_partner = ($u['employer_type'] ?? '') === 'approved_partner';
                            $ver_status = $u['verification_status'] ?? 'verified';
                            $org = $u['organization_name'] ?? ($u['department'] ?? 'Campus Organization');
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-accent-soft text-ink rounded-circle d-flex align-items-center justify-content-center fw-bold border-line border flex-shrink-0" style="width:36px;height:36px;">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-ink"><?= htmlspecialchars($u['name']) ?></div>
                                            <span class="text-muted-custom small"><?= htmlspecialchars($u['email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'student'): ?>
                                        <span class="pill-badge" style="font-size: 11px;"><i class="bi bi-mortarboard me-1"></i>Student</span>
                                    <?php elseif ($u['role'] === 'employer'): ?>
                                        <?php if ($is_partner): ?>
                                            <span class="pill-badge pill-badge-ink" style="font-size: 11px;"><i class="bi bi-patch-check-fill text-accent me-1"></i>Approved Partner</span>
                                        <?php else: ?>
                                            <span class="pill-badge" style="font-size: 11px;"><i class="bi bi-bank text-accent me-1"></i>Campus Office</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="pill-badge pill-badge-ink" style="font-size: 11px;"><i class="bi bi-shield-lock me-1"></i>Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($org) ?></div>
                                    <span class="text-muted-custom small"><?= htmlspecialchars($u['course'] ?? ($u['office_location'] ?? '')) ?></span>
                                </td>
                                <td>
                                    <span class="chip-tag" style="font-size: 11px;">
                                        <?= htmlspecialchars($u['accreditation_number'] ?? ($u['student_id'] ?? 'INTERNAL')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ver_status === 'verified'): ?>
                                        <span class="badge bg-success small"><i class="bi bi-check-circle me-1"></i>Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark small"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if ($u['role'] === 'employer' && $ver_status !== 'verified'): ?>
                                        <a href="users.php?approve_id=<?= $u['id'] ?>" class="btn-accent-pill py-1 px-2" style="font-size: 11px;">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted-custom small">Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
