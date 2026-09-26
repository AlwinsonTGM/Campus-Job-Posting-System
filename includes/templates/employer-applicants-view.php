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
                            <div class="col-12 col-md-6 col-lg-6">
                                <label class="form-label" for="search-candidate">Search Candidates</label>
                                <div class="search-input-wrap">
                                    <i class="bi bi-search text-muted-custom"></i>
                                    <input type="text" name="q" id="search-candidate" data-alias="search-applicant" class="form-control" placeholder="Candidate, email, degree..." value="<?= htmlspecialchars($search ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-6">
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

                            <div class="col-12 col-md-4 col-lg-4">
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

                            <div class="col-12 col-md-4 col-lg-4">
                                <label class="form-label" for="filter-fit">Filter by Schedule Availability</label>
                                <select name="fit" id="filter-fit" class="form-select">
                                    <option value="">All Availability Levels</option>
                                    <option value="optimal" <?= ($fit_filter === 'optimal') ? 'selected' : '' ?>>Strong Fit (20h+ / Optimal)</option>
                                    <option value="moderate" <?= ($fit_filter === 'moderate') ? 'selected' : '' ?>>Moderate Fit (12–16h / Balanced)</option>
                                    <option value="limited" <?= ($fit_filter === 'limited') ? 'selected' : '' ?>>Limited Fit (4–8h / Limited Hours)</option>
                                    <option value="conflict" <?= ($fit_filter === 'conflict') ? 'selected' : '' ?>>Conflict Risk (0h / No Shifts)</option>
                                </select>
                            </div>

                            <div class="col-10 col-md-3 col-lg-3">
                                <label class="form-label" for="filter-sort">Rank Candidates</label>
                                <select name="sort" id="filter-sort" class="form-select">
                                    <option value="">Default (Latest Submissions)</option>
                                    <option value="sched_desc" <?= ($rank_sort === 'sched_desc') ? 'selected' : '' ?>>Availability: Highest Availability</option>
                                    <option value="sched_asc" <?= ($rank_sort === 'sched_asc') ? 'selected' : '' ?>>Availability: Conflict Risk First</option>
                                    <option value="name_asc" <?= ($rank_sort === 'name_asc') ? 'selected' : '' ?>>Candidate Name (A–Z)</option>
                                    <option value="date_asc" <?= ($rank_sort === 'date_asc') ? 'selected' : '' ?>>Date Applied (Oldest First)</option>
                                </select>
                            </div>

                            <div class="col-2 col-md-1 col-lg-1 d-flex justify-content-end align-items-end">
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
                            $empty_desc = !empty($job_filter)
                                ? 'No student candidates have submitted applications for this specific vacancy yet. Check back once students apply, or reset filters to view all submissions across your department.'
                                : 'No student candidates have submitted applications matching the selected criteria.';
                            render_empty_state(
                                'bi-people',
                                'No Applicants Found',
                                $empty_desc,
                                'applicants.php',
                                'Reset Roster Filters'
                            );
                            ?>
                        </div>

                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-paper table-paper-responsive table-paper-roster mb-0">
                                <colgroup>
                                    <col style="width: 18%;">
                                    <col style="width: 15%;">
                                    <col style="width: 13%;">
                                    <col style="width: 13%;">
                                    <col style="width: 11%;">
                                    <col style="width: 13%;">
                                    <col style="width: 17%;">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="ps-4">Candidate Profile</th>
                                        <th>Target Vacancy</th>
                                        <th>Degree Program</th>
                                        <th>Schedule Availability</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th class="pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_dept_apps as $app): 
                                        $sched_fit = $app['schedule_summary'] ?? get_schedule_summary($app);
                                    ?>
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
                                            <td data-label="Schedule Availability">
                                                <div class="d-inline-flex flex-column align-items-start gap-1">
                                                    <span class="badge <?= $sched_fit['badge_bg'] ?> border py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 11.5px; font-weight: 600;">
                                                        <i class="bi <?= $sched_fit['icon'] ?>"></i>
                                                        <?= htmlspecialchars($sched_fit['label']) ?>
                                                    </span>
                                                    <span class="text-muted-custom" style="font-size: 11px;">
                                                        <?= htmlspecialchars($sched_fit['hours_desc']) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td data-label="Applied Date" class="small text-muted-custom">
                                                <?= format_display_date($app['applied_at']) ?>
                                            </td>
                                            <td data-label="Status">
                                                <?= render_status_badge($app['status']) ?>
                                            </td>
                                            <td class="pe-4" data-label="Actions">
                                                <div class="table-actions-wrap">
                                                    <a href="../view-resume.php?app_id=<?= $app['id'] ?>" target="_blank" class="btn-pill-outline btn-pill-sm table-action-btn" title="View Attached PDF Resume">
                                                        <i class="bi bi-file-earmark-pdf text-danger"></i> Resume
                                                    </a>
                                                    <a href="review-app.php?id=<?= $app['id'] ?>" class="btn-pill btn-pill-sm table-action-btn" title="Evaluate Application">
                                                        <i class="bi bi-clipboard-check"></i> Evaluate
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 py-2 border-top border-line bg-surface d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-muted-custom flex-shrink-0"></i>
                            <span class="small text-muted-custom" style="font-size: 11px;">Schedule availability is evaluated from student declared shift slots against the institutional 20-hour workload policy.</span>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

