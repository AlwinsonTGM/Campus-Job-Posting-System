<?php
/**
 * View template for employer/applicants.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php
        ob_start();
        require_once __DIR__ . '/../navbar.php';
        $navbar_html = ob_get_clean();
        echo str_replace('<input type="text" class="paper-search-input"', '<input type="search" class="paper-search-input"', $navbar_html);
        ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                $head_actions = '
                    <a href="create-job.php" class="btn-pill">
                        <i class="bi bi-plus-circle-fill"></i> Post Vacancy
                    </a>
                    <a href="dashboard.php" class="btn-pill-outline">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                ';
                render_page_head(
                    '',
                    'Student Applicant Evaluation Roster',
                    'Review candidate profiles, inspect weekly class schedule availability, and coordinate interview appointments.',
                    $head_actions
                );
                ?>

                <!-- Filter Bar -->
                <div class="card-paper p-4 mb-4">
                    <form action="applicants.php" method="GET" class="form-paper auto-filter-form">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-xl-4 col-lg-4 col-md-12">
                                <label class="form-label" for="search-candidate">Search Candidates</label>
                                <div class="search-input-wrap">
                                    <i class="bi bi-search text-muted-custom"></i>
                                    <input type="text" name="q" id="search-candidate" data-alias="search-applicant" class="form-control" placeholder="Candidate, email, degree..." value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-12 col-xl-4 col-lg-4 col-md-6">
                                <label class="form-label" for="filter-job">Filter by Job Requisition</label>
                                <select name="job_id" id="filter-job" class="form-select">
                                    <option value="">All Department Openings</option>
                                    <?php foreach ($dept_jobs as $j): ?>
                                        <option value="<?= $j['id'] ?>" <?= ($job_filter == $j['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($j['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-8 col-xl-3 col-lg-3 col-md-5">
                                <label class="form-label" for="filter-status">Filter by Status</label>
                                <select name="status" id="filter-status" class="form-select">
                                    <option value="">All Application Stages</option>
                                    <option value="pending" <?= ($status_filter === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                                    <option value="review" <?= ($status_filter === 'review') ? 'selected' : '' ?>>Under Evaluation</option>
                                    <option value="interview" <?= ($status_filter === 'interview') ? 'selected' : '' ?>>Interview Scheduled</option>
                                    <option value="accepted" <?= ($status_filter === 'accepted') ? 'selected' : '' ?>>Accepted / Hired</option>
                                    <option value="declined" <?= ($status_filter === 'declined') ? 'selected' : '' ?>>Declined / Filled</option>
                                </select>
                            </div>

                            <div class="col-4 col-xl-1 col-lg-1 col-md-1 d-flex justify-content-end">
                                <div>
                                    <label class="form-label d-none d-md-block" style="visibility: hidden;">Reset</label>
                                    <a href="applicants.php" class="btn-filter-reset" title="Reset all filters" aria-label="Reset all filters">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Applicant Table -->
                <div id="filter-results-container" class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
                    <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center bg-surface">
                        <div>
                            <h3 class="card-paper-title mb-1">Candidate Submissions</h3>
                            <span class="small text-muted-custom">Total candidates matching criteria: <strong><?= count($all_dept_apps) ?></strong></span>
                        </div>
                    </div>

                    <?php if (empty($all_dept_apps)): ?>
                        <div class="p-4">
                            <?php
                            render_empty_state(
                                'bi-people',
                                'No Applicants Found',
                                'No student candidates have submitted applications matching the selected criteria.',
                                'applicants.php',
                                'Reset Roster Filters'
                            );
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-paper table-paper-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Candidate Profile</th>
                                        <th>Target Vacancy</th>
                                        <th>Degree Program</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_dept_apps as $app): ?>
                                        <tr>
                                            <td class="ps-4" data-label="Candidate Profile">
                                                <div class="fw-bold text-ink"><?= htmlspecialchars($app['student_name']) ?></div>
                                                <div class="small text-muted-custom">
                                                    <span><?= htmlspecialchars($app['student_email']) ?></span>
                                                    <?php if (!empty($app['sex']) || !empty($app['age'])): ?>
                                                        &bull; <span><?= htmlspecialchars($app['sex'] ?? 'Male') ?>, <?= htmlspecialchars((string)($app['age'] ?? 20)) ?> yrs</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td data-label="Target Vacancy">
                                                <span class="fw-semibold text-ink"><?= htmlspecialchars($app['job_title']) ?></span>
                                            </td>
                                            <td data-label="Degree Program">
                                                <div class="small fw-semibold text-ink"><?= htmlspecialchars($app['course'] ?? 'BS Information Systems') ?></div>
                                                <div class="small text-muted-custom"><?= htmlspecialchars($app['year_level'] ?? '2nd Year') ?></div>
                                            </td>
                                            <td data-label="Applied Date" class="small text-muted-custom">
                                                <?= format_display_date($app['applied_at']) ?>
                                            </td>
                                            <td data-label="Status">
                                                <?= render_status_badge($app['status']) ?>
                                            </td>
                                            <td class="text-end pe-4" data-label="Actions">
                                                <div class="d-flex align-items-center justify-content-end gap-1">
                                                    <a href="../view-resume.php?app_id=<?= $app['id'] ?>" target="_blank" class="btn-pill-outline btn-pill-sm py-1 px-2" title="View Attached PDF Resume" style="font-size: 11px;">
                                                        <i class="bi bi-file-earmark-pdf text-danger"></i> Resume
                                                    </a>
                                                    <a href="review-app.php?id=<?= $app['id'] ?>" class="btn-pill btn-pill-sm py-1 px-3">
                                                        <i class="bi bi-clipboard-check"></i> Evaluate
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

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

