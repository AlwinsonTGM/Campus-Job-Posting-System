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
        <?php require_once __DIR__ . '/../navbar.php'; ?>

        <main class="py-5 employer-workspace">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                $head_actions = '<a href="create-job.php" class="employer-button employer-button--primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Post Vacancy</a>';
                render_page_head('', 'Student applicants', 'Review profiles, compare availability, and coordinate your next hiring decision.', $head_actions);
                ?>

                <div class="employer-panel">
                    <form action="applicants.php" method="GET" class="auto-filter-form employer-filter-form" role="search" aria-label="Filter applicants">
                        <div class="employer-filter-field">
                            <label for="search-candidate">Search candidates</label>
                            <div class="search-input-wrap"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="q" id="search-candidate" data-alias="search-applicant" class="form-control" placeholder="Name, email, or degree…" value="<?= htmlspecialchars($search ?? '') ?>"></div>
                        </div>
                        <div class="employer-filter-field">
                            <label for="filter-job">Vacancy</label>
                            <select name="job_id" id="filter-job" class="form-select"><option value="">All department openings</option><?php foreach ($dept_jobs as $j): ?><option value="<?= (int)$j['id'] ?>" <?= ($job_filter == $j['id']) ? 'selected' : '' ?>><?= htmlspecialchars($j['title']) ?></option><?php endforeach; ?></select>
                        </div>
                        <div class="employer-filter-field">
                            <label for="filter-status">Application stage</label>
                            <select name="status" id="filter-status" class="form-select">
                                <option value="">All application stages</option>
                                <?php foreach (['pending' => 'Pending Review', 'review' => 'Under Evaluation', 'interview' => 'Interview Scheduled', 'accepted' => 'Accepted / Hired', 'declined' => 'Declined / Filled', 'withdrawn' => 'Candidate Withdrawn'] as $stage_value => $stage_label): ?>
                                    <option value="<?= $stage_value ?>" <?= $status_filter === $stage_value ? 'selected' : '' ?>><?= $stage_label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <a href="applicants.php" class="employer-button employer-button--icon employer-filter-reset" title="Reset all filters" aria-label="Reset all filters"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></a>
                        <details class="employer-more-filters" <?= !empty($fit_filter) || !empty($rank_sort) ? 'open' : '' ?>>
                            <summary><i class="bi bi-sliders2" aria-hidden="true"></i>Availability &amp; sorting</summary>
                            <div class="employer-additional-filters">
                                <div class="employer-filter-field">
                                    <label for="filter-fit">Schedule availability</label>
                                    <select name="fit" id="filter-fit" class="form-select"><option value="">All availability levels</option>
                                        <?php foreach (['optimal' => 'Strong fit · 20+ hours', 'moderate' => 'Moderate fit · 12–16 hours', 'limited' => 'Limited fit · 4–8 hours', 'conflict' => 'Conflict risk · No shifts'] as $fit_value => $fit_label): ?><option value="<?= $fit_value ?>" <?= $fit_filter === $fit_value ? 'selected' : '' ?>><?= $fit_label ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="employer-filter-field">
                                    <label for="filter-sort">Sort candidates</label>
                                    <select name="sort" id="filter-sort" class="form-select"><option value="">Latest submissions</option>
                                        <?php foreach (['sched_desc' => 'Highest availability', 'sched_asc' => 'Conflict risk first', 'name_asc' => 'Candidate name (A–Z)', 'date_asc' => 'Oldest submissions'] as $sort_value => $sort_label): ?><option value="<?= $sort_value ?>" <?= $rank_sort === $sort_value ? 'selected' : '' ?>><?= $sort_label ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </details>
                        <noscript><button class="employer-button" type="submit">Apply filters</button></noscript>
                    </form>
                </div>

                <section id="filter-results-container" class="employer-panel" aria-labelledby="submissions-title">
                    <div class="employer-panel-heading"><div><h2 id="submissions-title">Candidate submissions</h2><p role="status" aria-live="polite"><?= (int)$total_apps ?> candidate<?= $total_apps === 1 ? '' : 's' ?> matching your filters</p></div></div>
                    <?php if (empty($all_dept_apps)): ?>
                        <div class="employer-empty"><i class="bi bi-people" aria-hidden="true"></i><h3>No applicants found</h3><p><?= !empty($job_filter) ? 'No candidates match this vacancy and the selected filters. Reset filters to view all department submissions.' : 'No candidates match the selected filters. Try another name or reset the filters.' ?></p><a href="applicants.php" class="employer-button employer-button--compact">Reset filters</a></div>
                    <?php else: ?>
                        <div class="employer-table-scroll" data-pagination-content>
                            <table class="employer-table">
                                <caption class="visually-hidden">Department candidate submissions</caption>
                                <thead><tr><th scope="col">Candidate</th><th scope="col">Target vacancy</th><th scope="col">Availability</th><th scope="col">Applied</th><th scope="col">Application stage</th><th scope="col" class="employer-actions-cell">Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($all_dept_apps as $app):
                                        $sched_fit = $app['schedule_summary'] ?? get_schedule_summary($app);
                                        $pSummary = $app['placement_summary'] ?? ApplicationService::getStudentPlacementSummary((int)($app['student_id'] ?? 0), (int)($app['job_id'] ?? 0));
                                        $fit_label = ['optimal' => 'Strong fit', 'moderate' => 'Moderate fit', 'limited' => 'Limited fit', 'conflict' => 'No shifts declared'][$sched_fit['tier']] ?? $sched_fit['label'];
                                    ?>
                                        <tr id="app-<?= (int)$app['id'] ?>" data-highlight-id="app-<?= (int)$app['id'] ?>">
                                            <td data-label="Candidate">
                                                <strong><?= htmlspecialchars($app['student_name']) ?></strong>
                                                <small><?= htmlspecialchars($app['student_email']) ?></small>
                                                <small><?= htmlspecialchars($app['course'] ?? 'Degree not provided') ?> · <?= htmlspecialchars($app['year_level'] ?? 'Year not provided') ?></small>
                                                <?php if (!empty($app['sex']) || !empty($app['age'])): ?><small><?= htmlspecialchars(implode(' · ', array_filter([$app['sex'] ?? '', !empty($app['age']) ? $app['age'] . ' yrs' : '']))) ?></small><?php endif; ?>
                                                <?php if (!empty($pSummary['is_employed'])): ?>
                                                    <div class="employer-placement"><i class="bi bi-briefcase" aria-hidden="true"></i>Employed · <?= htmlspecialchars($pSummary['active_placement']['department'] ?? 'Campus') ?></div>
                                                <?php elseif (!empty($pSummary['other_total_count'])): ?>
                                                    <div class="employer-placement"><i class="bi bi-layers" aria-hidden="true"></i><?= (int)$pSummary['other_total_count'] ?> other application<?= $pSummary['other_total_count'] > 1 ? 's' : '' ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Target vacancy"><strong><?= htmlspecialchars($app['job_title']) ?></strong></td>
                                            <td data-label="Availability" class="employer-availability"><strong><?= (int)$sched_fit['hours'] ?> hrs / week</strong><small class="<?= in_array($sched_fit['tier'], ['limited', 'conflict'], true) ? 'employer-fit-warning' : '' ?>"><?= htmlspecialchars($fit_label) ?></small></td>
                                            <td data-label="Applied" class="employer-date"><?= format_display_date($app['applied_at']) ?></td>
                                            <td data-label="Application stage"><?= render_status_text($app['status']) ?></td>
                                            <td data-label="Actions" class="employer-actions-cell">
                                                <div class="employer-actions employer-actions--applicants">
                                                    <a href="../view-resume.php?app_id=<?= (int)$app['id'] ?>" target="_blank" rel="noopener" class="employer-button" aria-label="View resume for <?= htmlspecialchars($app['student_name'], ENT_QUOTES) ?> (opens in new tab)"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Resume</a>
                                                    <a href="review-app.php?id=<?= (int)$app['id'] ?>" class="employer-button employer-button--primary" aria-label="Evaluate <?= htmlspecialchars($app['student_name'], ENT_QUOTES) ?>"><i class="bi bi-clipboard2-check" aria-hidden="true"></i> Evaluate</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="employer-table-foot"><i class="bi bi-clock-history" aria-hidden="true"></i><p>Availability is based on declared shifts and the institutional 20-hour weekly workload policy.</p></div>
                        <?php if (isset($total_pages) && $total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-top border-line bg-surface">
                                <div class="small text-muted">
                                    Showing <?= (($page - 1) * $per_page) + 1 ?>–<?= min($total_apps, $page * $per_page) ?> of <?= $total_apps ?> applicants
                                </div>
                                <nav aria-label="Applicant roster pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php
                                            $queryParams = $_GET;
                                            unset($queryParams['highlight'], $queryParams['from_notification']);
                                            $prevPage = max(1, $page - 1);
                                            $nextPage = min($total_pages, $page + 1);
                                        ?>
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $prevPage])) ?>" aria-label="Previous">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                            <?php if ($p === 1 || $p === $total_pages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                                                <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $p])) ?>"><?= $p ?></a>
                                                </li>
                                            <?php elseif ($p === $page - 3 || $p === $page + 3): ?>
                                                <li class="page-item disabled"><span class="page-link">…</span></li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $nextPage])) ?>" aria-label="Next">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>
