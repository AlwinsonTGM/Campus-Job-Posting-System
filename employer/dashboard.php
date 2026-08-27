<?php
/**
 * Campus Job Posting System - Employer / Department Dashboard
 * Archetype B: Employer Portal & Requisitions Hub (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$page_title = 'Employer & Department Dashboard';

// Filter jobs by this employer/department
$dept = $user['organization_name'] ?? ($user['department'] ?? 'Office of the University Registrar');
$all_dept_jobs = get_jobs(null, null, ($user['role'] === 'admin' ? null : $dept));
$dept_apps = get_applications(null, null, ($user['role'] === 'admin' ? null : $dept));

$active_jobs_count = count(array_filter($all_dept_jobs, fn($j) => in_array($j['status'] ?? '', ['active', 'Active'])));
$total_applicants_count = count($dept_apps);
$interview_count = count(array_filter($dept_apps, fn($a) => in_array($a['status'] ?? '', ['interview_scheduled', 'Interview Scheduled'])));
$hired_count = count(array_filter($dept_apps, fn($a) => in_array($a['status'] ?? '', ['accepted', 'Accepted / Hired'])));

$is_partner = ($user['employer_type'] ?? '') === 'approved_partner';
$org_name = $user['organization_name'] ?? ($user['department'] ?? 'Campus Organization');
$accreditation = $user['accreditation_number'] ?? ($is_partner ? 'MOA-VERIFIED' : 'INTERNAL-UNIV');

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
                    <a href="create-job.php" class="btn-pill">
                        <i class="bi bi-plus-circle-fill"></i> Post New Vacancy
                    </a>
                    <a href="applicants.php" class="btn-pill-outline">
                        <i class="bi bi-people-fill"></i> View Applicants (' . $total_applicants_count . ')
                    </a>
                    <a href="../admin/reports.php" class="btn-pill-outline">
                        <i class="bi bi-bar-chart-fill"></i> Hiring Report
                    </a>
                ';
                render_page_head(
                    '<i class="bi bi-building-fill text-accent me-1"></i> ' . ($is_partner ? 'Approved Campus Partner' : 'University Office') . ' · ' . htmlspecialchars($accreditation),
                    'Welcome back, ' . htmlspecialchars($user['name']),
                    htmlspecialchars($org_name) . ' • Manage active student assistant openings, candidate evaluations, and hiring quotas.',
                    $head_actions
                );
                ?>

                <!-- 4 KPI Metrics -->
                <div class="row g-3 mb-5">
                    <div class="col-6 col-lg-3">
                        <?php render_metric($active_jobs_count, 'Active Requisitions', 'bi-briefcase-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($total_applicants_count, 'Total Applicants', 'bi-people-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($interview_count, 'Interviews Scheduled', 'bi-calendar-event-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($hired_count, 'Officially Appointed', 'bi-person-check-fill'); ?>
                    </div>
                </div>

                <!-- Manage Department Requisitions Table -->
                <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
                    <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center bg-surface">
                        <div>
                            <h3 class="card-paper-title mb-1">
                                <i class="bi bi-folder-check text-accent me-2"></i> Department Vacancy Requisitions
                            </h3>
                            <p class="text-muted-custom small mb-0">Overview of student assistantship postings published by your office</p>
                        </div>
                        <a href="create-job.php" class="btn-pill btn-pill-sm">
                            <i class="bi bi-plus-lg"></i> Post Vacancy
                        </a>
                    </div>

                    <?php if (empty($all_dept_jobs)): ?>
                        <div class="p-4">
                            <?php
                            render_empty_state(
                                'bi-briefcase',
                                'No Active Requisitions',
                                'Your department has not posted any student assistant openings yet. Create your first opening to receive student applications.',
                                'create-job.php',
                                'Post a New Vacancy'
                            );
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-paper table-paper-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Vacancy Title</th>
                                        <th>Category</th>
                                        <th>Slot Quota</th>
                                        <th>Rate</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_dept_jobs as $job): 
                                        $slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
                                        $slots_filled = (int)($job['slots_filled'] ?? 0);
                                        $pct = ($slots_total > 0) ? round(($slots_filled / $slots_total) * 100) : 0;
                                    ?>
                                        <tr>
                                            <td class="ps-4" data-label="Vacancy Title">
                                                <a href="../student/job-details.php?id=<?= $job['id'] ?>" class="fw-bold text-ink text-decoration-none">
                                                    <?= htmlspecialchars($job['title']) ?>
                                                </a>
                                                <div class="small text-muted-custom"><?= htmlspecialchars($job['job_type'] ?? 'Student Assistant') ?> &bull; <?= htmlspecialchars($job['work_setup'] ?? 'On-Campus') ?></div>
                                            </td>
                                            <td data-label="Category">
                                                <span class="chip"><?= htmlspecialchars($job['category']) ?></span>
                                            </td>
                                            <td data-label="Slot Quota">
                                                <div class="d-flex align-items-center gap-2" style="min-width: 110px;">
                                                    <div class="progress-paper flex-grow-1">
                                                        <div class="progress-paper-bar" style="width: <?= $pct ?>%;"></div>
                                                    </div>
                                                    <span class="small text-ink fw-bold"><?= $slots_filled ?>/<?= $slots_total ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Rate" class="fw-bold text-ink">
                                                <?= htmlspecialchars($job['pay_rate']) ?>
                                            </td>
                                            <td data-label="Deadline" class="small text-muted-custom">
                                                <?= htmlspecialchars($job['deadline']) ?>
                                            </td>
                                            <td data-label="Status">
                                                <?= render_status_badge($job['status'] ?? 'Active') ?>
                                            </td>
                                            <td class="text-end pe-4" data-label="Actions">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="applicants.php?job_id=<?= $job['id'] ?>" class="btn-pill btn-pill-sm">
                                                        <i class="bi bi-people"></i> Applicants
                                                    </a>
                                                    <a href="edit-job.php?id=<?= $job['id'] ?>" class="btn-pill-outline btn-pill-sm" title="Edit Posting">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Candidate Submissions -->
                <div class="card-paper p-4 mb-5 reveal-fade-rise">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-line">
                        <div>
                            <h3 class="card-paper-title mb-1">
                                <i class="bi bi-person-lines-fill text-accent me-2"></i> Recent Candidate Applications
                            </h3>
                            <p class="text-muted-custom small mb-0">Incoming submissions awaiting department evaluation</p>
                        </div>
                        <a href="applicants.php" class="btn-pill-outline btn-pill-sm">
                            View All (<?= $total_applicants_count ?>)
                        </a>
                    </div>

                    <?php if (empty($dept_apps)): ?>
                        <div class="text-center py-4 text-muted-custom small">
                            No student applications submitted yet.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach (array_slice($dept_apps, 0, 4) as $app): ?>
                                <div class="p-3 bg-surface rounded-4 border border-line d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong class="text-ink fs-6"><?= htmlspecialchars($app['student_name']) ?></strong>
                                            <span class="small text-muted-custom">(<?= htmlspecialchars($app['course']) ?> &bull; <?= htmlspecialchars($app['year_level']) ?>)</span>
                                        </div>
                                        <span class="small text-muted-custom">
                                            Applied for: <strong class="text-ink"><?= htmlspecialchars($app['job_title']) ?></strong> &bull; <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <?= render_status_badge($app['status']) ?>
                                        <a href="review-app.php?id=<?= $app['id'] ?>" class="btn-pill btn-pill-sm">
                                            Evaluate
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
