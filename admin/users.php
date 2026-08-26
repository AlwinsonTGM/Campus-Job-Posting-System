<?php
/**
 * Campus Job Posting System - Admin User Management
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'User Management';

$users = $_SESSION['users'] ?? load_json_file('users.json');

$role_filter = $_GET['role'] ?? null;
$search = $_GET['q'] ?? null;

if ($role_filter) {
    $users = array_filter($users, fn($u) => $u['role'] === $role_filter);
}

if ($search) {
    $q = strtolower(trim($search));
    $users = array_filter($users, fn($u) => stripos($u['name'], $q) !== false || stripos($u['email'], $q) !== false || stripos($u['student_id'] ?? '', $q) !== false);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-light flex-grow-1">
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
                <h2 class="fw-bold text-dark mb-0">System User Directory</h2>
            </div>
            <div class="mt-2 mt-md-0 text-muted small">
                Total Registered Accounts: <strong><?= count($users) ?></strong>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="users.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search by name, email, or student ID..." value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">All Account Roles</option>
                        <option value="student" <?= ($role_filter === 'student') ? 'selected' : '' ?>>Students Only</option>
                        <option value="employer" <?= ($role_filter === 'employer') ? 'selected' : '' ?>>Campus Employers Only</option>
                        <option value="admin" <?= ($role_filter === 'admin') ? 'selected' : '' ?>>Administrators Only</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-academic w-100">Filter</button>
                    <?php if ($role_filter || $search): ?>
                        <a href="users.php" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">User Details</th>
                            <th>Role</th>
                            <th>Affiliation / Course</th>
                            <th>ID / Office Code</th>
                            <th>Contact</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-kld-soft text-kld-green rounded-circle d-flex align-items-center justify-content-center fw-bold border border-success-subtle" style="width:36px;height:36px;">
                                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                            <span class="text-muted small"><?= htmlspecialchars($u['email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'student'): ?>
                                        <span class="badge text-white text-uppercase small" style="background-color: var(--kld-green-primary);">Student</span>
                                    <?php elseif ($u['role'] === 'employer'): ?>
                                        <span class="badge bg-warning text-dark text-uppercase small">Employer</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger text-white text-uppercase small">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?= htmlspecialchars($u['department'] ?? 'General') ?></div>
                                    <span class="text-muted small"><?= htmlspecialchars($u['course'] ?? '') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small"><?= htmlspecialchars($u['student_id'] ?? 'N/A') ?></span>
                                </td>
                                <td class="small text-muted">
                                    <?= htmlspecialchars($u['phone'] ?? 'Not set') ?>
                                </td>
                                <td>
                                    <span class="badge bg-kld-soft text-kld-green border border-success-subtle small">Active</span>
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
