<?php
/**
 * Campus Job Posting System - Employer / Department Dashboard
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$page_title = 'Employer Dashboard';

// Filter jobs by this employer/department
$dept = $user['department'] ?? 'Office of the University Registrar';
$all_dept_jobs = get_jobs(null, null, $dept);
$dept_apps = get_applications(null, null, $dept);

$active_jobs_count = count(array_filter($all_dept_jobs, fn($j) => $j['status'] === 'active'));
$total_applicants_count = count($dept_apps);
$pending_reviews_count = count(array_filter($dept_apps, fn($a) => $a['status'] === 'pending'));
$hired_count = count(array_filter($dept_apps, fn($a) => $a['status'] === 'accepted'));

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <!-- Welcome Banner -->
        <?php 
        $is_partner = ($user['employer_type'] ?? '') === 'approved_partner';
        $org_name = $user['organization_name'] ?? ($user['department'] ?? 'Campus Organization');
        $accreditation = $user['accreditation_number'] ?? ($is_partner ? 'MOA-VERIFIED' : 'INTERNAL-UNIV');
        $ver_status = $user['verification_status'] ?? 'verified';
        ?>
        <div class="card border-line shadow-sm rounded-4 p-4 p-md-4 mb-4 text-white bg-kld-gradient">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-accent-soft text-ink p-2 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <?php if ($is_partner): ?>
                                <i class="bi bi-patch-check-fill fs-4 text-accent"></i>
                            <?php else: ?>
                                <i class="bi bi-bank fs-4"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="pill-badge pill-badge-ink text-uppercase small">
                                    <?= $is_partner ? 'Approved Industry Partner' : 'University Academic / Office' ?>
                                </span>
                                <?php if ($ver_status === 'verified'): ?>
                                    <span class="badge bg-success small"><i class="bi bi-shield-check me-1"></i> Verified & Accredited</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark small"><i class="bi bi-hourglass-split me-1"></i> Pending Verification</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="fw-bold text-white mb-0 mt-1"><?= htmlspecialchars($org_name) ?></h3>
                        </div>
                    </div>
                    <p class="text-white-50 mb-0 small">
                        <strong>Lead Representative:</strong> <?= htmlspecialchars($user['name']) ?> &bull; 
                        <strong>Accreditation / MOA:</strong> <span class="text-white fw-bold"><?= htmlspecialchars($accreditation) ?></span> &bull; 
                        <strong>Workplace:</strong> <?= htmlspecialchars($user['office_location'] ?? ($user['location'] ?? 'Campus Main Office')) ?>
                    </p>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-center justify-content-md-end">
                    <a href="create-job.php" class="btn-accent-pill py-2 px-3 shadow-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i> POST OPPORTUNITY
                    </a>
                    <a href="applicants.php" class="btn-outline-pill py-2 px-3 text-white border-white">
                        <i class="bi bi-people-fill me-1"></i> REVIEW APPLICANTS
                    </a>
                </div>
            </div>
        </div>

        <?php if ($is_partner && $ver_status !== 'verified'): ?>
            <div class="card border-line shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning text-dark p-2 rounded-circle flex-shrink-0" style="width:44px;height:44px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold text-ink mb-0">Business Legitimacy Verification Pending Review</h6>
                            <span class="badge bg-warning text-dark small">Under Career Services Evaluation</span>
                        </div>
                        <p class="text-muted-custom small mb-0 mt-1">
                            Your registration reference code (<strong><?= htmlspecialchars($accreditation) ?></strong>) <?= !empty($user['permit_file']) ? 'and uploaded permit/certificate photo are' : 'is' ?> currently being manually reviewed by the University Administration. Once approved, your partner badge will update to verified.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Metric KPI Cards -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Active Postings</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $active_jobs_count ?></div>
                        </div>
                        <div class="stat-icon bg-accent-soft text-ink">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Total Applicants</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $total_applicants_count ?></div>
                        </div>
                        <div class="stat-icon bg-cream text-ink">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Pending Review</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $pending_reviews_count ?></div>
                        </div>
                        <div class="stat-icon bg-cream text-ink">
                            <i class="bi bi-hourglass-top"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Hired SAs</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $hired_count ?></div>
                        </div>
                        <div class="stat-icon bg-accent-soft text-ink">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Department Job Postings Table -->
        <div class="card border-line shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-ink mb-0"><i class="bi bi-folder-check text-accent me-2"></i> Department Vacancy Requisitions</h5>
                    <span class="text-muted-custom small">Manage job listings posted by your office</span>
                </div>
                <a href="create-job.php" class="btn-accent-pill py-2 px-3">
                    <i class="bi bi-plus-lg me-1"></i> Post Vacancy
                </a>
            </div>

            <?php if (empty($all_dept_jobs)): ?>
                <div class="text-center py-5 text-muted-custom">
                    <i class="bi bi-briefcase fs-1 d-block mb-2 text-accent"></i>
                    <p class="mb-3">Your department has no job postings active at the moment.</p>
                    <a href="create-job.php" class="btn-accent-pill">Create First Job Posting</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-cream border-bottom border-line">
                            <tr>
                                <th class="ps-4 text-ink fw-bold">Job Title</th>
                                <th class="text-ink fw-bold">Category</th>
                                <th class="text-ink fw-bold">Slots</th>
                                <th class="text-ink fw-bold">Stipend Rate</th>
                                <th class="text-ink fw-bold">Deadline</th>
                                <th class="text-ink fw-bold">Status</th>
                                <th class="text-end pe-4 text-ink fw-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_dept_jobs as $job): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-ink"><?= htmlspecialchars($job['title']) ?></div>
                                        <span class="text-muted-custom small"><i class="bi bi-geo-alt me-1 text-accent"></i><?= htmlspecialchars($job['location']) ?></span>
                                    </td>
                                    <td>
                                        <span class="pill-badge" style="font-size: 11px;"><?= htmlspecialchars($job['category']) ?></span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold text-ink"><?= $job['vacancies'] ?> open</span>
                                    </td>
                                    <td class="small fw-bold text-ink">
                                        <?= htmlspecialchars($job['pay_rate']) ?>
                                    </td>
                                    <td class="small text-muted-custom">
                                        <?= date('M d, Y', strtotime($job['deadline'])) ?>
                                    </td>
                                    <td>
                                        <span class="pill-badge <?= $job['status'] === 'active' ? '' : 'pill-badge-ink' ?>" style="font-size: 11px;">
                                            <?= htmlspecialchars($job['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="applicants.php?job_id=<?= $job['id'] ?>" class="btn-accent-pill py-1 px-3 me-1" style="font-size: 12px;" title="View Applicants">
                                            <i class="bi bi-people"></i> Applicants
                                        </a>
                                        <a href="edit-job.php?id=<?= $job['id'] ?>" class="btn-outline-pill py-1 px-2" style="font-size: 12px;" title="Edit Posting">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
