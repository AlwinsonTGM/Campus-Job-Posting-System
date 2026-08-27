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
                    '<i class="bi bi-people-fill text-accent me-1"></i> User Directory &amp; Compliance',
                    'Campus User Directory & Roles',
                    'Manage student accounts, academic departments, and approve accredited external partner organizations.',
                    $head_actions
                );
                ?>

                <!-- Pending Verification Banner (if any) -->
                <?php if ($pending_count > 0): ?>
                    <div class="card-paper bg-cream p-3 mb-4 border border-line d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle icon-circle-sm icon-circle-accent">
                                <i class="bi bi-exclamation-circle-fill text-accent"></i>
                            </div>
                            <div>
                                <strong class="text-ink"><?= $pending_count ?> Partner Registration(s) Awaiting Review:</strong>
                                <span class="small text-muted-custom">Business permits and MOA references require manual verification.</span>
                            </div>
                        </div>
                        <a href="users.php?ver_status=pending_approval" class="btn-pill btn-pill-sm">
                            Inspect Pending
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Filter & Search Bar -->
                <div class="card-paper p-4 mb-4">
                    <form action="users.php" method="GET" class="form-paper">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="search-user">Search User Directory</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="q" id="search-user" class="form-control" placeholder="Search name, email, MOA, ID..." value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label" for="role-select">Role</label>
                                <select name="role" id="role-select" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="student" <?= ($role_filter === 'student') ? 'selected' : '' ?>>Students</option>
                                    <option value="employer" <?= ($role_filter === 'employer') ? 'selected' : '' ?>>Employers / Offices</option>
                                    <option value="admin" <?= ($role_filter === 'admin') ? 'selected' : '' ?>>Administrators</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="emp-select">Employer Classification</label>
                                <select name="emp_type" id="emp-select" class="form-select">
                                    <option value="">All Employer Types</option>
                                    <option value="university_office" <?= ($emp_type_filter === 'university_office') ? 'selected' : '' ?>>University Offices</option>
                                    <option value="approved_partner" <?= ($emp_type_filter === 'approved_partner') ? 'selected' : '' ?>>Approved Partners</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label" for="ver-select">Accreditation</label>
                                <select name="ver_status" id="ver-select" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending_approval" <?= ($ver_filter === 'pending_approval') ? 'selected' : '' ?>>Pending Review</option>
                                    <option value="verified" <?= ($ver_filter === 'verified') ? 'selected' : '' ?>>Verified</option>
                                    <option value="rejected" <?= ($ver_filter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>

                            <div class="col-md-1 d-flex gap-1">
                                <button type="submit" class="btn-pill w-100 p-0" title="Apply Filter" style="height: 48px;">
                                    <i class="bi bi-funnel-fill"></i>
                                </button>
                                <?php if ($role_filter || $emp_type_filter || $ver_filter || $search): ?>
                                    <a href="users.php" class="btn-pill-outline btn-pill-sm p-0 flex-shrink-0" style="width: 48px; height: 48px;" title="Reset Filters">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Users Directory Table -->
                <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
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
                                        <th class="ps-4">User Details</th>
                                        <th>Role &amp; Classification</th>
                                        <th>Organization / Program</th>
                                        <th>ID / Accreditation Code</th>
                                        <th>Accreditation Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): 
                                        $is_partner = ($u['employer_type'] ?? '') === 'approved_partner';
                                        $ver_status = $u['verification_status'] ?? 'verified';
                                        $org = $u['organization_name'] ?? ($u['department'] ?? 'Campus Organization');
                                    ?>
                                        <tr>
                                            <td class="ps-4" data-label="User Details">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="icon-circle icon-circle-sm icon-circle-accent flex-shrink-0">
                                                        <?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-ink"><?= htmlspecialchars($u['name']) ?></div>
                                                        <span class="small text-muted-custom"><?= htmlspecialchars($u['email']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Role & Classification">
                                                <?php if ($u['role'] === 'student'): ?>
                                                    <span class="chip"><i class="bi bi-mortarboard me-1"></i>Student</span>
                                                <?php elseif ($u['role'] === 'employer'): ?>
                                                    <?php if ($is_partner): ?>
                                                        <span class="chip active"><i class="bi bi-patch-check-fill text-accent me-1"></i>Approved Partner</span>
                                                    <?php else: ?>
                                                        <span class="chip"><i class="bi bi-bank text-accent me-1"></i>Campus Office</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="pill-badge" style="font-size: 11px;"><i class="bi bi-shield-lock me-1"></i>Administrator</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Organization / Program">
                                                <div class="small fw-semibold text-ink"><?= htmlspecialchars($org) ?></div>
                                                <span class="small text-muted-custom"><?= htmlspecialchars($u['course'] ?? ($u['office_location'] ?? '')) ?></span>
                                            </td>
                                            <td data-label="ID / Accreditation">
                                                <span class="chip" style="font-size: 11px;">
                                                    <?= htmlspecialchars($u['accreditation_number'] ?? ($u['student_id'] ?? 'INTERNAL')) ?>
                                                </span>
                                            </td>
                                            <td data-label="Accreditation Status">
                                                <?php if ($ver_status === 'verified'): ?>
                                                    <span class="badge-status--accepted"><i class="bi bi-check-circle me-1"></i>Verified</span>
                                                <?php elseif ($ver_status === 'rejected'): ?>
                                                    <span class="badge-status--declined"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                                <?php else: ?>
                                                    <span class="badge-status--pending"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4" data-label="Actions">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <?php if ($u['role'] === 'employer'): ?>
                                                        <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-toggle="modal" data-bs-target="#verifyModal<?= $u['id'] ?>">
                                                            <i class="bi bi-file-earmark-check"></i> Inspect
                                                        </button>
                                                        
                                                        <?php if ($ver_status !== 'verified'): ?>
                                                            <a href="users.php?approve_id=<?= $u['id'] ?>" class="btn-pill btn-pill-sm" title="Approve Verification">
                                                                <i class="bi bi-check-lg"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="small text-muted-custom">Active</span>
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
                            <div class="icon-circle icon-circle-sm icon-circle-accent">
                                <i class="bi bi-patch-check-fill text-accent"></i>
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
                                <a href="users.php?reject_id=<?= $u['id'] ?>" class="btn-pill-outline btn-pill-sm text-danger border-danger" onclick="return confirm('Reject this employer registration?')">
                                    <i class="bi bi-x-circle"></i> Reject
                                </a>
                                <a href="users.php?approve_id=<?= $u['id'] ?>" class="btn-pill btn-pill-sm">
                                    <i class="bi bi-check-circle-fill"></i> Approve &amp; Verify
                                </a>
                            <?php else: ?>
                                <a href="users.php?reject_id=<?= $u['id'] ?>&notes=Revoked+by+admin" class="btn-pill-outline btn-pill-sm text-danger border-danger" onclick="return confirm('Revoke verification for this partner?')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Revoke
                                </a>
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
</script>
