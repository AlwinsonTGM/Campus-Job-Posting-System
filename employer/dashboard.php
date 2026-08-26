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

<main class="py-4 bg-light flex-grow-1">
    <div class="container">
        
        <!-- Welcome Banner -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 mb-4 text-white bg-kld-gradient">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-warning text-dark p-2 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark text-uppercase small">Employer / Office Portal</span>
                            <h3 class="fw-bold text-white mb-0"><?= htmlspecialchars($user['name']) ?></h3>
                        </div>
                    </div>
                    <p class="text-light opacity-90 mb-0 small">
                        <strong>Department:</strong> <?= htmlspecialchars($user['department'] ?? 'Office Administration') ?> &bull; 
                        <strong>Office Location:</strong> <?= htmlspecialchars($user['office_location'] ?? 'Admin Building Room 102') ?>
                    </p>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-center justify-content-md-end">
                    <a href="create-job.php" class="btn btn-gold btn-sm px-3 shadow-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i> Post New Vacancy
                    </a>
                    <a href="applicants.php" class="btn btn-outline-light btn-sm px-3">
                        <i class="bi bi-people-fill me-1"></i> Review Applicants
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card" style="border-left-color: var(--kld-green-primary);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Active Postings</div>
                            <div class="h3 fw-bold text-dark mb-0"><?= $active_jobs_count ?></div>
                        </div>
                        <div class="stat-icon stat-icon-kld">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card" style="border-left-color: var(--kld-gold);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Total Applicants</div>
                            <div class="h3 fw-bold text-dark mb-0"><?= $total_applicants_count ?></div>
                        </div>
                        <div class="stat-icon stat-icon-kld-gold">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card" style="border-left-color: var(--kld-gold-accent);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Pending Review</div>
                            <div class="h3 fw-bold text-dark mb-0"><?= $pending_reviews_count ?></div>
                        </div>
                        <div class="stat-icon stat-icon-kld-gold">
                            <i class="bi bi-hourglass-top"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card" style="border-left-color: var(--kld-green-primary);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase">Hired SAs</div>
                            <div class="h3 fw-bold text-dark mb-0"><?= $hired_count ?></div>
                        </div>
                        <div class="stat-icon stat-icon-kld">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Department Job Postings Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-folder-check text-kld-green me-2"></i> Department Vacancy Requisitions</h5>
                    <span class="text-muted small">Manage job listings posted by your office</span>
                </div>
                <a href="create-job.php" class="btn btn-academic btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Post Vacancy
                </a>
            </div>

            <?php if (empty($all_dept_jobs)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-briefcase fs-1 d-block mb-2"></i>
                    <p class="mb-2">Your department has no job postings active at the moment.</p>
                    <a href="create-job.php" class="btn btn-academic btn-sm">Create First Job Posting</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Job Title</th>
                                <th>Category</th>
                                <th>Slots</th>
                                <th>Stipend Rate</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_dept_jobs as $job): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($job['title']) ?></div>
                                        <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['location']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= htmlspecialchars($job['category']) ?></span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold"><?= $job['vacancies'] ?> open</span>
                                    </td>
                                    <td class="small fw-bold text-success">
                                        <?= htmlspecialchars($job['pay_rate']) ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= date('M d, Y', strtotime($job['deadline'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $job['status'] === 'active' ? 'success' : 'secondary' ?> text-uppercase small">
                                            <?= htmlspecialchars($job['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="applicants.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="View Applicants">
                                            <i class="bi bi-people"></i> Applicants
                                        </a>
                                        <a href="edit-job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit Posting">
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
