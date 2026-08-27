<?php
/**
 * Campus Job Posting System - Admin User Management
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'User Management';

$users = $_SESSION['users'] ?? load_json_file('users.json');

// Handle verification approval and rejection actions
if (isset($_GET['approve_id'])) {
    $approve_id = (int)$_GET['approve_id'];
    if (update_employer_verification($approve_id, 'verified')) {
        $u_name = '';
        foreach ($_SESSION['users'] as $u) {
            if ($u['id'] == $approve_id) { $u_name = $u['name']; break; }
        }
        set_flash('success', "Partner employer '{$u_name}' has been officially verified!");
        header('Location: users.php');
        exit;
    }
}

if (isset($_GET['reject_id'])) {
    $reject_id = (int)$_GET['reject_id'];
    $notes = trim($_GET['notes'] ?? 'Permit documentation did not match or requires re-submission.');
    if (update_employer_verification($reject_id, 'rejected', $notes)) {
        set_flash('warning', "Partner employer registration has been marked as rejected / revision requested.");
        header('Location: users.php');
        exit;
    }
}

$role_filter = $_GET['role'] ?? null;
$emp_type_filter = $_GET['emp_type'] ?? null;
$ver_filter = $_GET['ver_status'] ?? null;
$search = $_GET['q'] ?? null;

// Count pending verifications
$all_users = $_SESSION['users'] ?? load_json_file('users.json');
$pending_count = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'employer' && ($u['verification_status'] ?? '') === 'pending_approval'));

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
            <div class="mt-2 mt-md-0 d-flex align-items-center gap-2">
                <?php if ($pending_count > 0): ?>
                    <a href="users.php?ver_status=pending_approval" class="btn btn-sm btn-warning rounded-pill px-3 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-exclamation-circle-fill"></i> Pending Verification (<?= $pending_count ?>)
                    </a>
                <?php endif; ?>
                <div class="text-muted-custom small bg-white px-3 py-2 rounded-pill border-line border">
                    Total Accounts: <strong><?= count($users) ?></strong>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-line shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="users.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search text-muted-custom"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search by name, email, org, or MOA..." value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="student" <?= ($role_filter === 'student') ? 'selected' : '' ?>>Students</option>
                        <option value="employer" <?= ($role_filter === 'employer') ? 'selected' : '' ?>>Employers</option>
                        <option value="admin" <?= ($role_filter === 'admin') ? 'selected' : '' ?>>Admins</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="emp_type" class="form-select">
                        <option value="">All Employer Types</option>
                        <option value="university_office" <?= ($emp_type_filter === 'university_office') ? 'selected' : '' ?>>University Offices</option>
                        <option value="approved_partner" <?= ($emp_type_filter === 'approved_partner') ? 'selected' : '' ?>>Approved Partners</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="ver_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending_approval" <?= ($ver_filter === 'pending_approval') ? 'selected' : '' ?>>Pending Review</option>
                        <option value="verified" <?= ($ver_filter === 'verified') ? 'selected' : '' ?>>Verified</option>
                        <option value="rejected" <?= ($ver_filter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn-accent-pill w-100 py-2" title="Filter"><i class="bi bi-funnel-fill"></i></button>
                    <?php if ($role_filter || $emp_type_filter || $ver_filter || $search): ?>
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
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted-custom">
                                    <i class="bi bi-people fs-2 d-block mb-2 text-accent"></i>
                                    No user accounts matched the filter criteria.
                                </td>
                            </tr>
                        <?php endif; ?>

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
                                    <?php elseif ($ver_status === 'rejected'): ?>
                                        <span class="badge bg-danger small"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark small"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <?php if ($u['role'] === 'employer'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#verifyModal<?= $u['id'] ?>" style="font-size: 11px;" title="Manual Verification Review">
                                                <i class="bi bi-file-earmark-check me-1"></i> Review
                                            </button>
                                            
                                            <?php if ($ver_status !== 'verified'): ?>
                                                <a href="users.php?approve_id=<?= $u['id'] ?>" class="btn-accent-pill py-1 px-2" style="font-size: 11px;" title="1-Click Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted-custom small">Active</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Verification Review Modals for Employer Users -->
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
        <div class="modal-content rounded-4 border-line shadow">
            
            <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon bg-accent-soft text-ink p-2 rounded-circle" style="width:36px;height:36px;">
                        <i class="bi bi-patch-check-fill fs-5 text-accent"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-ink mb-0" id="verifyModalLabel<?= $u['id'] ?>">Business &amp; Permit Verification</h5>
                        <span class="text-muted-custom small"><?= htmlspecialchars($org) ?> (<?= $is_partner ? 'Approved Industry Partner' : 'University Office' ?>)</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    
                    <!-- Left: Details & Reference Code -->
                    <div class="col-md-6 border-end-md border-line">
                        <h6 class="fw-bold text-ink text-uppercase small mb-3">
                            <i class="bi bi-info-circle text-accent me-1"></i> Registration Information
                        </h6>

                        <div class="p-3 bg-surface rounded-3 border-line border mb-3">
                            <div class="text-muted-custom small" style="font-size: 11px;">ORGANIZATION / COMPANY NAME</div>
                            <div class="fw-bold text-ink fs-6"><?= htmlspecialchars($org) ?></div>
                            <div class="text-muted-custom small mt-1">
                                <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($u['name']) ?>
                            </div>
                        </div>

                        <!-- MOA / Business Permit Code Box with Copy Button -->
                        <div class="p-3 bg-cream rounded-3 border-line border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-ink text-uppercase" style="font-size: 11px;">
                                    <i class="bi bi-key-fill text-accent me-1"></i> MOA / Business Permit / Reg No.
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-2 py-0" style="font-size: 11px;" onclick="copyCodeText('code-<?= $u['id'] ?>', this)">
                                    <i class="bi bi-clipboard me-1"></i> Copy
                                </button>
                            </div>
                            <div class="p-2 bg-white rounded-2 border-line border font-monospace fw-bold text-ink fs-6" id="code-<?= $u['id'] ?>">
                                <?= htmlspecialchars($accreditation) ?>
                            </div>
                            <div class="small text-muted-custom mt-1" style="font-size: 11px;">
                                Use this code to cross-reference against institutional MOA archives or official registry records.
                            </div>
                        </div>

                        <ul class="list-unstyled small text-muted-custom mb-0 d-flex flex-column gap-2">
                            <li>
                                <strong class="text-ink">Email:</strong> <?= htmlspecialchars($u['email']) ?>
                            </li>
                            <li>
                                <strong class="text-ink">Phone:</strong> <?= htmlspecialchars($u['phone'] ?? 'Not provided') ?>
                            </li>
                            <li>
                                <strong class="text-ink">Workplace Address:</strong> <?= htmlspecialchars($u['office_location'] ?? 'Campus Main Office') ?>
                            </li>
                            <li>
                                <strong class="text-ink">Registered At:</strong> <?= htmlspecialchars($u['created_at'] ?? 'N/A') ?>
                            </li>
                            <li>
                                <strong class="text-ink">Current Status:</strong> 
                                <?php if ($ver_status === 'verified'): ?>
                                    <span class="badge bg-success small ms-1"><i class="bi bi-check-circle me-1"></i>Verified</span>
                                <?php elseif ($ver_status === 'rejected'): ?>
                                    <span class="badge bg-danger small ms-1"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark small ms-1"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Right: Uploaded Permit / Certificate Photo Preview -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-ink text-uppercase small mb-3">
                            <i class="bi bi-file-earmark-image text-accent me-1"></i> Uploaded Permit / MOA Photo
                        </h6>

                        <?php if ($has_permit_file): 
                            $ext = strtolower(pathinfo($permit_doc, PATHINFO_EXTENSION));
                        ?>
                            <div class="card border-line rounded-3 overflow-hidden bg-surface p-2 text-center">
                                <?php if ($ext === 'pdf'): ?>
                                    <div class="py-5 text-center">
                                        <i class="bi bi-file-earmark-pdf fs-1 text-danger d-block mb-2"></i>
                                        <div class="fw-bold text-ink small">PDF Document Uploaded</div>
                                        <span class="text-muted-custom" style="font-size: 11px;"><?= basename($permit_doc) ?></span>
                                    </div>
                                <?php else: ?>
                                    <img src="../<?= htmlspecialchars($permit_doc) ?>" alt="Permit Photo" class="img-fluid rounded border-line border" style="max-height: 250px; object-fit: contain; width: 100%; background: #ffffff;">
                                <?php endif; ?>

                                <a href="../<?= htmlspecialchars($permit_doc) ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill w-100 mt-2 py-1" style="font-size: 12px;">
                                    <i class="bi bi-arrows-fullscreen me-1"></i> Open Full Document in New Tab
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="p-4 bg-cream rounded-3 border-line border text-center text-muted-custom">
                                <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted-custom"></i>
                                <div class="fw-bold text-ink small">No Permit Photo Uploaded</div>
                                <p class="small text-muted-custom mb-0 mt-1" style="font-size: 11px;">
                                    The employer only provided the reference code (<strong><?= htmlspecialchars($accreditation) ?></strong>). Verify manually via code records.
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Verification Tip -->
                        <div class="mt-3 p-2 bg-surface rounded-2 border-line border small text-muted-custom" style="font-size: 11px;">
                            <i class="bi bi-shield-check text-accent me-1"></i>
                            <strong>Manual Review Protocol:</strong> Verify that the organization name, seal, and registration code align before approving.
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-cream border-top border-line py-3 px-4 d-flex justify-content-between">
                <div>
                    <?php if ($ver_status === 'verified'): ?>
                        <span class="badge bg-success py-2 px-3"><i class="bi bi-shield-check me-1"></i> Officially Verified Partner</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($ver_status !== 'verified'): ?>
                        <a href="users.php?reject_id=<?= $u['id'] ?>" class="btn btn-outline-danger rounded-pill px-3 py-2 small" onclick="return confirm('Reject this employer registration?')">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </a>
                        <a href="users.php?approve_id=<?= $u['id'] ?>" class="btn-accent-pill px-3 py-2 small">
                            <i class="bi bi-check-circle-fill me-1"></i> Approve &amp; Verify
                        </a>
                    <?php else: ?>
                        <a href="users.php?reject_id=<?= $u['id'] ?>&notes=Revoked+by+admin" class="btn btn-outline-danger rounded-pill px-3 py-1 small" onclick="return confirm('Revoke verification for this partner?')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Revoke
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary rounded-pill px-3 py-1 small" data-bs-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
</div>
<?php endforeach; ?>

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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
