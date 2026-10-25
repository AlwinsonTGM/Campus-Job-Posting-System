<?php
/**
 * View template for admin/users.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                $ver_btn_class = $pending_count > 0 ? 'btn-pill me-2' : 'btn-pill-outline me-2';
                $head_actions = '
                    <a href="verification-deck.php" class="' . $ver_btn_class . '">
                        <i class="bi bi-shield-check me-1"></i> Verification Queue (' . $pending_count . ')
                    </a>
                    <a href="reports.php" class="btn-pill">
                        <i class="bi bi-bar-chart-fill"></i> System Reports
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
                        <div class="d-flex align-items-center gap-2">
                            <a href="verification-deck.php" class="btn-pill btn-pill-sm">
                                <i class="bi bi-shield-check me-1"></i> Verification Queue (<?= $pending_count ?>)
                            </a>
                            <a href="users.php?ver_status=pending_approval#filter-results-container" id="inspectPendingAccountsBtn" class="btn-pill-outline btn-pill-sm">
                                Table View
                            </a>
                        </div>
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
                                <colgroup>
                                    <col style="width: 15%;">
                                    <col style="width: 32%;">
                                    <col style="width: 28%;">
                                    <col style="width: 12%;">
                                    <col style="width: 13%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="ps-4 text-start">Request Ref</th>
                                        <th class="text-start">Student Details</th>
                                        <th class="text-start">Target Program</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_profile_requests as $req): 
                                        $req_status = $req['status'] ?? 'pending';
                                        $proof = $req['proof_file'] ?? '';
                                    ?>
                                        <tr>
                                            <td class="ps-4 text-start text-nowrap" data-label="Request Ref">
                                                <span class="font-monospace fw-bold text-ink small">#REQ-<?= str_pad((string)$req['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                                <div class="small text-muted-custom" style="font-size: 11px;"><?= format_display_date($req['created_at']) ?></div>
                                            </td>
                                            <td class="text-start" data-label="Student Details" style="max-width: 240px;">
                                                <div class="fw-bold text-ink text-truncate" title="<?= htmlspecialchars($req['user_name']) ?>"><?= htmlspecialchars($req['user_name']) ?></div>
                                                <div class="small text-muted-custom text-truncate" style="font-size: 11px;" title="<?= htmlspecialchars($req['user_email']) ?>">
                                                    <span><?= htmlspecialchars($req['student_id'] ?? '') ?></span> &bull; 
                                                    <span><?= htmlspecialchars($req['user_email']) ?></span>
                                                </div>
                                                <?php if (!empty($req['requested_profile']['email']) && strtolower($req['requested_profile']['email']) !== strtolower($req['user_email'])): ?>
                                                    <div class="mt-1">
                                                        <span class="badge bg-warning text-dark border border-warning" style="font-size: 10px;">
                                                            <i class="bi bi-arrow-repeat me-1"></i><?= htmlspecialchars($req['requested_profile']['email']) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-start" data-label="Target Program" style="max-width: 260px;">
                                                <div class="small fw-semibold text-ink text-truncate" title="<?= htmlspecialchars(($req['requested_profile']['course'] ?? '') . ' • ' . ($req['requested_profile']['year_level'] ?? '')) ?>">
                                                    <?= htmlspecialchars($req['requested_profile']['course'] ?? '') ?>
                                                </div>
                                                <div class="small text-muted-custom" style="font-size: 11px;">
                                                    <span class="fw-semibold text-ink"><?= htmlspecialchars($req['requested_profile']['year_level'] ?? '') ?></span> &bull; <?= htmlspecialchars($req['requested_profile']['sex'] ?? 'Male') ?>, <?= htmlspecialchars((string)($req['requested_profile']['age'] ?? 20)) ?> yrs
                                                </div>
                                            </td>
                                            <td class="text-center text-nowrap" data-label="Status">
                                                <?php if ($req_status === 'approved'): ?>
                                                    <span class="badge-status--accepted"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                                <?php elseif ($req_status === 'rejected'): ?>
                                                    <span class="badge-status--declined"><i class="bi bi-x-circle me-1"></i>Declined</span>
                                                <?php else: ?>
                                                    <span class="badge-status--pending"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap" data-label="Actions">
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
                <?php
                $sort_filter = $_GET['sort'] ?? 'newest';
                $search_field = $_GET['search_field'] ?? null;
                $active_filters_count = (!empty($search) ? 1 : 0) + (!empty($role_filter) ? 1 : 0) + (!empty($emp_type_filter) ? 1 : 0) + (!empty($ver_filter) ? 1 : 0) + (!empty($search_field) ? 1 : 0) + (($sort_filter !== 'newest' && !empty($sort_filter)) ? 1 : 0);
                $has_active_filters = $active_filters_count > 0;
                ?>
                <div class="card-paper p-3 p-md-4 mb-4">
                    <form action="users.php" method="GET" class="form-paper auto-filter-form">
                        <!-- Filter Header Toolbar -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom border-line" id="directoryFilterBadges">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold text-ink small text-uppercase d-inline-flex align-items-center gap-1" style="font-size: 11px; letter-spacing: 0.08em;">
                                    <i class="bi bi-funnel-fill text-accent"></i> Directory Search & Filters
                                </span>
                            </div>
                            <?php if ($has_active_filters): ?>
                                <a href="users.php" class="small text-muted-custom text-decoration-none d-inline-flex align-items-center gap-1 hover-ink fw-semibold" style="font-size: 11.5px;">
                                    <i class="bi bi-arrow-counterclockwise text-accent"></i> Clear all filters
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Primary Search & Sort By -->
                        <div class="row g-3">
                            <div class="col-12 col-lg-8">
                                <label class="form-label" for="search-user">
                                    <i class="bi bi-search text-accent me-1"></i>Search User Directory
                                </label>
                                <div class="search-input-wrap roblox-search-container" id="robloxSearchWrap">
                                    <i class="bi bi-search text-accent search-leading-icon"></i>
                                    <input 
                                        type="text" 
                                        name="q" 
                                        id="search-user" 
                                        class="form-control <?= !empty($search) ? 'filter-applied' : '' ?>" 
                                        placeholder="Search name, email, ID / code, organization, program..." 
                                        value="<?= htmlspecialchars($search ?? '') ?>"
                                        autocomplete="off"
                                        spellcheck="false"
                                        role="combobox"
                                        aria-autocomplete="list"
                                        aria-expanded="false"
                                        aria-controls="robloxSearchDropdown"
                                        aria-haspopup="listbox"
                                    >
                                    <button 
                                        type="button" 
                                        id="searchUserClearBtn" 
                                        class="btn-clear-search <?= empty($search) ? 'd-none' : '' ?>" 
                                        aria-label="Clear search input" 
                                        title="Clear search"
                                    >
                                        <i class="bi bi-x-circle-fill text-accent"></i>
                                    </button>

                                    <!-- Roblox-Style Category Dropdown -->
                                    <div id="robloxSearchDropdown" class="roblox-search-dropdown shadow-lg" role="listbox" aria-label="Search categories">
                                        <div class="roblox-search-list" id="robloxSearchList"></div>
                                        <div class="roblox-search-footer">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <kbd class="roblox-kbd">↑</kbd><kbd class="roblox-kbd">↓</kbd> <span>navigate</span>
                                                <span class="mx-1 text-muted-custom">&bull;</span>
                                                <kbd class="roblox-kbd">Enter</kbd> <span>select</span>
                                            </span>
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <kbd class="roblox-kbd">Esc</kbd> <span>close</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label class="form-label" for="sort-select">
                                    <i class="bi bi-arrow-down-up text-accent me-1"></i>Sort By
                                </label>
                                <select name="sort" id="sort-select" class="form-select <?= ($sort_filter !== 'newest') ? 'filter-applied' : '' ?>">
                                    <option value="newest" <?= ($sort_filter === 'newest') ? 'selected' : '' ?>>Recently Registered (Newest)</option>
                                    <option value="oldest" <?= ($sort_filter === 'oldest') ? 'selected' : '' ?>>Earliest Registered (Oldest)</option>
                                    <option value="name_asc" <?= ($sort_filter === 'name_asc') ? 'selected' : '' ?>>Name: A → Z (Alphabetical)</option>
                                    <option value="name_desc" <?= ($sort_filter === 'name_desc') ? 'selected' : '' ?>>Name: Z → A (Reverse Alphabetical)</option>
                                    <option value="email_asc" <?= ($sort_filter === 'email_asc') ? 'selected' : '' ?>>Email: A → Z</option>
                                    <option value="pending_first" <?= ($sort_filter === 'pending_first') ? 'selected' : '' ?>>Pending Verification First</option>
                                    <option value="verified_first" <?= ($sort_filter === 'verified_first') ? 'selected' : '' ?>>Verified Accounts First</option>
                                    <option value="role_student" <?= ($sort_filter === 'role_student') ? 'selected' : '' ?>>Role: Students First</option>
                                    <option value="role_employer" <?= ($sort_filter === 'role_employer') ? 'selected' : '' ?>>Role: Employers First</option>
                                    <option value="role_admin" <?= ($sort_filter === 'role_admin') ? 'selected' : '' ?>>Role: Administrators First</option>
                                </select>
                            </div>
                        </div>

                        <!-- Scoped category state inputs managed by Roblox Search -->
                        <input type="hidden" name="role" id="role-select" value="<?= htmlspecialchars($role_filter ?? '') ?>">
                        <input type="hidden" name="emp_type" id="emp-select" value="<?= htmlspecialchars($emp_type_filter ?? '') ?>">
                        <input type="hidden" name="ver_status" id="ver-select" value="<?= htmlspecialchars($ver_filter ?? '') ?>">
                        <input type="hidden" name="search_field" id="search-field" value="<?= htmlspecialchars($search_field ?? '') ?>">
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
                                        <th class="text-center pe-4" style="width: 140px;">Actions</th>
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
                                                            <?php if ((int)($u['is_email_verified'] ?? 1) === 0): ?>
                                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1" style="font-size: 9.5px; padding: 1px 4px;"><i class="bi bi-envelope-exclamation me-1"></i>Unconfirmed</span>
                                                            <?php endif; ?>
                                                            <?php if ($u['role'] === 'student' && (!empty($u['sex']) || !empty($u['age']))): ?>
                                                                &bull; <span><?= htmlspecialchars($u['sex'] ?? 'Male') ?>, <?= htmlspecialchars((string)($u['age'] ?? 20)) ?> yrs</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Role" class="text-nowrap">
                                                <?php if ($u['role'] === 'student'): ?>
                                                    <span class="chip" style="font-size: 11px;"><i class="bi bi-mortarboard text-accent me-1"></i>Student</span>
                                                <?php elseif ($u['role'] === 'employer'): ?>
                                                    <?php if ($is_partner): ?>
                                                        <span class="chip" style="font-size: 11px;"><i class="bi bi-patch-check-fill text-accent me-1"></i>Partner</span>
                                                    <?php else: ?>
                                                        <span class="chip" style="font-size: 11px;"><i class="bi bi-bank text-accent me-1"></i>Office</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="chip" style="font-size: 11px;"><i class="bi bi-shield-lock text-accent me-1"></i>Admin</span>
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
                                                <?php if (($u['status'] ?? 'active') === 'suspended' || !empty($u['is_archived'])): ?>
                                                    <span class="badge-status--declined"><i class="bi bi-archive me-1"></i>Archived</span>
                                                <?php elseif ((int)($u['is_email_verified'] ?? 1) === 0): ?>
                                                    <span class="badge-status--pending text-warning border-warning"><i class="bi bi-envelope-exclamation me-1"></i>Awaiting OTP</span>
                                                <?php elseif ($ver_status === 'revoked' || !empty($u['is_revoked'])): ?>
                                                    <span class="badge-status--declined text-danger border-danger"><i class="bi bi-arrow-counterclockwise me-1"></i>Revoked</span>
                                                <?php elseif ($ver_status === 'verified'): ?>
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
                                                        <button type="button" class="btn-pill-outline btn-pill-sm py-1 px-2" style="font-size: 11.5px;" data-bs-toggle="modal" data-bs-target="#adminModal<?= $u['id'] ?>">
                                                            <i class="bi bi-shield-check"></i> Inspect
                                                        </button>
                                                        <span class="d-inline-block" style="width: 62px;" aria-hidden="true"></span>
                                                    <?php endif; ?>

                                                    <?php if ($u['role'] !== 'admin'): ?>
                                                        <?php if (($u['status'] ?? 'active') === 'suspended'): ?>
                                                            <form action="users.php" method="POST" class="d-inline" onsubmit="return confirm('Reactivate this account?');">
                                                                <input type="hidden" name="action" value="reactivate_user">
                                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                                <button type="submit" class="btn-pill btn-pill-sm py-1 px-2 text-success border-success" style="font-size: 11.5px;" title="Reactivate Account">
                                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form action="users.php" method="POST" class="d-inline" onsubmit="return confirm('Archive and remove this user from the website? The account will be soft-deleted from all website listings and user counts, but preserved in the database.');">
                                                                <input type="hidden" name="action" value="suspend_user">
                                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                                <button type="submit" class="btn-pill-outline btn-pill-sm py-1 px-2 text-danger border-danger" style="font-size: 11.5px;" title="Archive & Remove from Website">
                                                                    <i class="bi bi-archive"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (isset($total_pages) && $total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-top border-line bg-surface">
                                <div class="small text-muted">
                                    Showing <?= (($page - 1) * $per_page) + 1 ?>–<?= min($total_filtered_users, $page * $per_page) ?> of <?= $total_filtered_users ?> accounts
                                </div>
                                <nav aria-label="User directory pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php
                                            $queryParams = $_GET;
                                            $prevPage = max(1, $page - 1);
                                            $nextPage = min($total_pages, $page + 1);
                                        ?>
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $prevPage])) ?>#filter-results-container" aria-label="Previous">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                            <?php if ($p === 1 || $p === $total_pages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                                                <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $p])) ?>#filter-results-container"><?= $p ?></a>
                                                </li>
                                            <?php elseif ($p === $page - 3 || $p === $page + 3): ?>
                                                <li class="page-item disabled"><span class="page-link">…</span></li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $nextPage])) ?>#filter-results-container" aria-label="Next">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
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
            $proof_disk_path = dirname(__DIR__, 2) . '/' . ltrim((string)$proof, '/');
            $has_proof = !empty($proof) && file_exists($proof_disk_path);

            // Establish full baseline student profile from live user record
            $live_user = get_user_by_id($req['user_id'] ?? 0) ?: [];
            $base_profile = [
                'name'       => $live_user['name'] ?? ($req['user_name'] ?? ''),
                'email'      => $live_user['email'] ?? ($req['user_email'] ?? ''),
                'department' => $live_user['department'] ?? '',
                'course'     => $live_user['course'] ?? '',
                'year_level' => $live_user['year_level'] ?? '',
                'sex'        => $live_user['sex'] ?? 'Male',
                'birthdate'  => $live_user['birthdate'] ?? '',
                'age'        => (string)($live_user['age'] ?? ''),
            ];

            $clean_curr = is_array($curr) ? array_filter($curr, fn($v) => !empty(trim((string)$v))) : [];
            $clean_next = is_array($next) ? array_filter($next, fn($v) => !empty(trim((string)$v))) : [];

            // Merge so full student identity is always visible
            $curr = array_merge($base_profile, $clean_curr);
            $next = array_merge($curr, $clean_next);
            $student_id_disp = !empty($req['student_id']) ? $req['student_id'] : ($live_user['student_id'] ?? 'Student');
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
                                    <?= htmlspecialchars($req['user_name']) ?> (<?= htmlspecialchars($student_id_disp) ?>) &bull; <?= format_display_date($req['created_at'], true) ?>
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

                                <div class="mb-3">
                                    <table class="table-paper mb-0 small w-100" style="table-layout: fixed;">
                                        <colgroup>
                                            <col style="width: 38%;">
                                            <col style="width: 31%;">
                                            <col style="width: 31%;">
                                        </colgroup>
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
                                                'email' => 'Institutional / Login Email',
                                                'department' => 'Academic Institute',
                                                'course' => 'Degree Program / Course',
                                                'year_level' => 'Year Level / Standing',
                                                'sex' => 'Biological Sex',
                                                'birthdate' => 'Date of Birth',
                                                'age' => 'Derived Age'
                                            ];
                                            foreach (array_keys($clean_next) as $extra_k) {
                                                if (!isset($fields_to_compare[$extra_k])) {
                                                    $fields_to_compare[$extra_k] = ucwords(str_replace('_', ' ', $extra_k));
                                                }
                                            }
                                            foreach ($fields_to_compare as $f_key => $f_label):
                                                $curr_val = (string)($curr[$f_key] ?? '');
                                                $next_val = (string)($next[$f_key] ?? '');
                                                $is_changed = ($curr_val !== $next_val && !empty($next_val));
                                                $curr_disp = ($f_key === 'birthdate' && $curr_val && $curr_val !== '—') ? format_display_date($curr_val) : ($curr_val ?: '—');
                                                $next_disp = ($f_key === 'birthdate' && $next_val && $next_val !== '—') ? format_display_date($next_val) : ($next_val ?: '—');
                                            ?>
                                                <tr class="<?= $is_changed ? 'bg-cream' : '' ?>">
                                                    <td class="fw-bold text-ink" style="word-break: break-word; overflow-wrap: break-word;">
                                                        <?= $f_label ?>
                                                        <?php if ($is_changed): ?>
                                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">MODIFIED</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-muted-custom" style="word-break: break-word; overflow-wrap: break-word;">
                                                        <?= htmlspecialchars($curr_disp) ?>
                                                    </td>
                                                    <td class="<?= $is_changed ? 'fw-bold text-accent' : 'text-ink' ?>" style="word-break: break-word; overflow-wrap: break-word;">
                                                        <?= htmlspecialchars($next_disp) ?>
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
                                            <div class="badge-status--accepted d-inline-flex align-items-center gap-1 py-2 px-3 rounded-3" style="font-size: 0.85rem; white-space: normal; text-align: center; line-height: 1.3;">
                                                <i class="bi bi-check-circle flex-shrink-0"></i>
                                                <span>Request Approved &amp; Record Updated</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="badge-status--declined d-inline-flex align-items-center gap-1 py-2 px-3 rounded-3" style="font-size: 0.85rem; white-space: normal; text-align: center; line-height: 1.3;">
                                                <i class="bi bi-x-circle flex-shrink-0"></i>
                                                <span>Request Declined</span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="small text-muted-custom mt-2">Resolved on <?= format_display_date($req['resolved_at'] ?? $req['created_at'], true) ?></div>
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
        <div id="user-modals-container">
        <?php foreach ($users as $u): 
            if ($u['role'] !== 'employer') continue;
            $is_partner = ($u['employer_type'] ?? '') === 'approved_partner';
            $ver_status = $u['verification_status'] ?? 'verified';
            $org = $u['organization_name'] ?? ($u['department'] ?? 'Campus Organization');
            $accreditation = $u['accreditation_number'] ?? 'PENDING-VERIFICATION';
            $permit_doc = $u['permit_file'] ?? null;
            $permit_disk_path = dirname(__DIR__, 2) . '/' . ltrim((string)$permit_doc, '/');
            $has_permit_file = !empty($permit_doc) && file_exists($permit_disk_path);
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
                                    <li>
                                        <strong class="text-ink">Email:</strong> <?= htmlspecialchars($u['email']) ?>
                                        <?php if ((int)($u['is_email_verified'] ?? 1) === 1): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px;"><i class="bi bi-patch-check-fill me-1"></i>OTP Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1" style="font-size: 10px;"><i class="bi bi-clock-history me-1"></i>Awaiting OTP</span>
                                        <?php endif; ?>
                                    </li>
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
                                <?php elseif (!empty($permit_doc)): ?>
                                    <div class="card-paper p-3 text-center bg-surface border border-line">
                                        <i class="bi bi-file-earmark-text text-accent fs-1 d-block mb-2"></i>
                                        <strong class="text-ink small d-block mb-1">Document Attachment</strong>
                                        <span class="small text-muted-custom d-block mb-2"><?= htmlspecialchars(basename($permit_doc)) ?></span>
                                        <a href="../<?= htmlspecialchars($permit_doc) ?>" target="_blank" class="btn-pill btn-pill-sm d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-box-arrow-up-right"></i> View Document
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

                        <?php if (!empty($u['rejection_reason'])): ?>
                            <div class="mt-3 p-3 bg-danger-subtle text-danger-emphasis rounded-3 border border-danger-subtle small">
                                <strong class="d-block mb-1"><i class="bi bi-chat-left-quote me-1"></i>Current Rejection / Revision Note:</strong>
                                <p class="mb-0"><?= htmlspecialchars($u['rejection_reason']) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="collapse mt-3" id="rejectPanelEmp<?= $u['id'] ?>">
                            <div class="p-3 bg-danger-subtle rounded-3 border border-danger-subtle">
                                <form action="users.php" method="POST">
                                    <input type="hidden" name="action" value="reject_employer">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <label class="form-label small fw-bold text-danger mb-1" for="notes-emp-<?= $u['id'] ?>">
                                        <i class="bi bi-chat-left-dots me-1"></i> Rejection Reason / Revision Notes
                                    </label>
                                    <textarea name="notes" id="notes-emp-<?= $u['id'] ?>" class="form-control form-control-sm mb-2" rows="2" placeholder="Explain why accreditation was not approved (e.g. Expired business permit, accreditation mismatch, illegible document)..."></textarea>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-toggle="collapse" data-bs-target="#rejectPanelEmp<?= $u['id'] ?>">Cancel</button>
                                        <button type="submit" class="btn-pill btn-pill-sm text-white border-0" style="background: #dc3545;">
                                            <i class="bi bi-x-circle"></i> Confirm Rejection
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-cream border-top border-line py-3 px-4 d-flex justify-content-between">
                        <div>
                            <?php if ($ver_status === 'verified'): ?>
                                <span class="badge-status--accepted"><i class="bi bi-shield-check me-1"></i> Officially Verified</span>
                            <?php elseif ($ver_status === 'revoked' || !empty($u['is_revoked'])): ?>
                                <span class="badge-status--declined text-danger border-danger"><i class="bi bi-arrow-counterclockwise me-1"></i> Revoked by Admin</span>
                            <?php elseif ($ver_status === 'rejected'): ?>
                                <span class="badge-status--declined"><i class="bi bi-x-circle me-1"></i> Revision Requested</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($ver_status !== 'verified'): ?>
                                <button type="button" class="btn-pill-outline btn-pill-sm text-danger border-danger" data-bs-toggle="collapse" data-bs-target="#rejectPanelEmp<?= $u['id'] ?>" aria-expanded="false" aria-controls="rejectPanelEmp<?= $u['id'] ?>">
                                    <i class="bi bi-x-circle"></i> Reject / Request Revision
                                </button>
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
                                    <input type="hidden" name="action" value="revoke_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="notes" value="Verification revoked by admin">
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
            $proof_disk_path = dirname(__DIR__, 2) . '/' . ltrim((string)$proof_doc, '/');
            $has_proof_file = !empty($proof_doc) && file_exists($proof_disk_path);
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

                                        <div class="col-5 text-muted-custom">Email Ownership:</div>
                                        <div class="col-7">
                                            <?php if ((int)($u['is_email_verified'] ?? 1) === 1): ?>
                                                <span class="text-success fw-semibold"><i class="bi bi-patch-check-fill me-1"></i>Confirmed (OTP Verified)</span>
                                            <?php else: ?>
                                                <span class="text-warning fw-bold"><i class="bi bi-clock-history me-1"></i>Awaiting 6-Digit OTP</span>
                                            <?php endif; ?>
                                        </div>

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
                                        <div class="col-7 text-ink"><?= format_display_date($u['created_at'], true) ?></div>

                                        
                                        
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

                        <div class="collapse mt-3" id="rejectPanelStudent<?= $u['id'] ?>">
                            <div class="p-3 bg-danger-subtle rounded-3 border border-danger-subtle">
                                <form action="users.php" method="POST">
                                    <input type="hidden" name="action" value="reject_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <label class="form-label small fw-bold text-danger mb-1" for="notes-stu-<?= $u['id'] ?>">
                                        <i class="bi bi-chat-left-dots me-1"></i> Rejection Reason / Revision Notes
                                    </label>
                                    <textarea name="notes" id="notes-stu-<?= $u['id'] ?>" class="form-control form-control-sm mb-2" rows="2" placeholder="Explain why the registration was declined (e.g. Unclear Certificate of Registration, student ID mismatch, illegible photo)..."></textarea>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-toggle="collapse" data-bs-target="#rejectPanelStudent<?= $u['id'] ?>">Cancel</button>
                                        <button type="submit" class="btn-pill btn-pill-sm text-white border-0" style="background: #dc3545;">
                                            <i class="bi bi-x-circle"></i> Confirm Rejection
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-cream border-top border-line py-3 px-4 d-flex justify-content-between">
                        <div>
                            <?php if ($ver_status === 'verified'): ?>
                                <span class="badge-status--accepted"><i class="bi bi-shield-check me-1"></i> Verified Student</span>
                            <?php elseif ($ver_status === 'revoked' || !empty($u['is_revoked'])): ?>
                                <span class="badge-status--declined text-danger border-danger"><i class="bi bi-arrow-counterclockwise me-1"></i> Revoked by Admin</span>
                            <?php elseif ($ver_status === 'rejected'): ?>
                                <span class="badge-status--declined"><i class="bi bi-x-circle me-1"></i> Revision Requested</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($ver_status !== 'verified'): ?>
                                <button type="button" class="btn-pill-outline btn-pill-sm text-danger border-danger" data-bs-toggle="collapse" data-bs-target="#rejectPanelStudent<?= $u['id'] ?>" aria-expanded="false" aria-controls="rejectPanelStudent<?= $u['id'] ?>">
                                    <i class="bi bi-x-circle"></i> Reject / Request Revision
                                </button>
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
                                    <input type="hidden" name="action" value="revoke_user">
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

        <!-- Institutional Administrator Profile Modals -->
        <?php foreach ($users as $u): 
            if ($u['role'] !== 'admin') continue;
        ?>
        <div class="modal fade" id="adminModal<?= $u['id'] ?>" tabindex="-1" aria-labelledby="adminModalLabel<?= $u['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle icon-circle-sm icon-circle-dark">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0" id="adminModalLabel<?= $u['id'] ?>">Administrator Profile</h5>
                                <span class="small text-muted-custom">Institutional System Administrator Record</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 bg-surface rounded-4 border border-line mb-3">
                            <span class="small text-muted-custom d-block mb-1">Administrative Account</span>
                            <strong class="text-ink fs-6"><?= htmlspecialchars($u['name']) ?></strong>
                            <div class="small text-muted-custom mt-1">
                                <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($u['email']) ?>
                            </div>
                        </div>

                        <div class="p-3 bg-cream rounded-4 border border-line mb-3">
                            <span class="small fw-bold text-ink text-uppercase d-block mb-2" style="font-size: 11px;">
                                <i class="bi bi-shield-check text-accent me-1"></i> Role &amp; Access Clearance
                            </span>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="chip" style="font-size: 11px;"><i class="bi bi-shield-lock text-accent me-1"></i>Admin</span>
                                <span class="badge-status--accepted"><i class="bi bi-check-circle me-1"></i>System Root</span>
                            </div>
                            <p class="small text-muted-custom mb-0" style="font-size: 11.5px;">
                                Institutional Administrator accounts hold system authority for verifying employer partners, approving student profile updates, and maintaining portal parameters.
                            </p>
                        </div>

                        <ul class="list-unstyled small text-muted-custom mb-0 d-flex flex-column gap-2">
                            <li><strong class="text-ink">Institutional Unit:</strong> <?= htmlspecialchars($u['department'] ?? 'Student Affairs & Services Office (SASO)') ?></li>
                            <li><strong class="text-ink">Campus Location:</strong> <?= htmlspecialchars($u['office_location'] ?? 'KLD Main Building, 2nd Floor') ?></li>
                            <li><strong class="text-ink">Contact Line:</strong> <?= htmlspecialchars($u['phone'] ?? '(046) 416-0000 loc 101') ?></li>
                            <li><strong class="text-ink">Institutional ID:</strong> <span class="font-monospace text-ink fw-semibold">INTERNAL</span></li>
                        </ul>
                    </div>
                    <div class="modal-footer bg-cream border-top border-line py-3 px-4 d-flex justify-content-between">
                        <span class="badge-status--accepted"><i class="bi bi-shield-check me-1"></i> Active Administrator</span>
                        <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div><!-- /#user-modals-container -->

        
<script src="<?= $base_url ?>assets/js/roblox-search.js?v=<?= time() ?>"></script>
<?php require_once __DIR__ . '/../footer.php'; ?>
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
    const inspectPendingBtn = document.getElementById('inspectPendingAccountsBtn');
    if (inspectPendingBtn) {
        inspectPendingBtn.addEventListener('click', function(e) {
            const verSelect = document.getElementById('ver-select');
            const target = document.getElementById('filter-results-container');
            if (verSelect && target) {
                e.preventDefault();
                const roleSelect = document.getElementById('role-select');
                if (roleSelect && roleSelect.value) roleSelect.value = '';
                const empSelect = document.getElementById('emp-select');
                if (empSelect && empSelect.value) empSelect.value = '';
                const searchInput = document.querySelector('.auto-filter-form input[name="search"]');
                if (searchInput && searchInput.value) searchInput.value = '';

                verSelect.value = 'pending_approval';
                verSelect.dispatchEvent(new Event('change', { bubbles: true }));

                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', 'users.php?ver_status=pending_approval#filter-results-container');
                }
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    if (window.location.hash === '#filter-results-container' || window.location.search.includes('page=')) {
        setTimeout(() => {
            const target = document.getElementById('filter-results-container');
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }

    // Delegated submit handler so dynamically swapped modals also show loading indicator
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('form[action="users.php"][method="POST"]');
        if (!form) return;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            setTimeout(() => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';
            }, 10);
        }
    });
});
</script>

