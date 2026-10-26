<?php
/**
 * View template for employer/dashboard.php
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
                render_page_head(
                    '',
                    'Your department at a glance',
                    ($org_name ?? 'Campus Organization') . ' • Manage openings, applications, and appointments.',
                    $head_actions
                );
                ?>

                <?php if (($user['verification_status'] ?? 'verified') === 'pending_approval'): ?>
                    <div class="alert-paper alert-paper--warning mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-hourglass-split text-warning fs-3 flex-shrink-0"></i>
                            <div>
                                <strong class="text-ink d-block mb-1">Organization Accreditation Pending Review</strong>
                                <span class="small text-muted-custom">
                                    Your employer registration and organization credentials have been submitted to Career Services. Once verified by the Campus Administrator, you will be authorized to post student assistant vacancies.
                                </span>
                            </div>
                        </div>
                    </div>
                <?php elseif (($user['verification_status'] ?? '') === 'rejected'): ?>
                    <div class="alert-paper alert-paper--danger mb-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger fs-3 flex-shrink-0"></i>
                                <div>
                                    <strong class="text-ink d-block mb-1">Accreditation Requires Revision</strong>
                                    <span class="small text-muted-custom">
                                        <?= htmlspecialchars($user['rejection_reason'] ?: 'Your permit documents or institutional accreditation were not approved.') ?>
                                    </span>
                                </div>
                            </div>
                            <a href="../settings.php" class="btn-pill-outline btn-pill-sm text-nowrap">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i> Resubmit Credentials
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="employer-metrics" aria-label="Department overview">
                    <?php foreach ([
                        [$active_jobs_count, 'Active vacancies', 'briefcase'],
                        [$total_applicants_count, 'Total applicants', 'people'],
                        [$interview_count, 'Interviews scheduled', 'calendar-event'],
                        [$hired_count, 'Officially appointed', 'person-check'],
                    ] as [$metric_value, $metric_label, $metric_icon]): ?>
                        <div class="employer-metric">
                            <div class="employer-icon"><i class="bi bi-<?= $metric_icon ?>" aria-hidden="true"></i></div>
                            <div><strong><?= (int)$metric_value ?></strong><p><?= $metric_label ?></p></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <section class="employer-panel" aria-labelledby="vacancies-title">
                    <div class="employer-panel-heading">
                        <div class="employer-heading-icon">
                            <div class="employer-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></div>
                            <div><h2 id="vacancies-title">Department vacancies</h2><p><?= $is_archived_view ? 'Archived postings and applicant records are preserved. Only admins can restore them.' : 'Student assistant openings published by your office.' ?></p></div>
                        </div>
                        <nav class="employer-filters" aria-label="Filter vacancies">
                            <a href="dashboard.php" class="status-filter <?= !$is_archived_view ? 'active' : '' ?>" <?= !$is_archived_view ? 'aria-current="page"' : '' ?>><i class="bi bi-briefcase" aria-hidden="true"></i> Active (<?= count($active_dept_jobs) ?>)</a>
                            <a href="dashboard.php?tab=archived" class="status-filter <?= $is_archived_view ? 'active' : '' ?>" <?= $is_archived_view ? 'aria-current="page"' : '' ?>><i class="bi bi-archive" aria-hidden="true"></i> Archived (<?= count($archived_dept_jobs) ?>)</a>
                        </nav>
                    </div>
                    <?php if (empty($all_dept_jobs)): ?>
                        <div class="employer-empty">
                            <i class="bi bi-<?= $is_archived_view ? 'archive' : 'briefcase' ?>" aria-hidden="true"></i>
                            <h3><?= $is_archived_view ? 'No archived vacancies' : 'No active vacancies' ?></h3>
                            <p><?= $is_archived_view ? 'Archived postings will appear here with their applicant records preserved.' : 'Post your first opening to receive student applications.' ?></p>
                            <?php if (!$is_archived_view): ?><a href="create-job.php" class="employer-text-button">Post a vacancy <i class="bi bi-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="employer-table-scroll">
                            <table class="employer-table">
                                <caption class="visually-hidden"><?= $is_archived_view ? 'Archived' : 'Active' ?> department vacancies</caption>
                                <thead><tr><th scope="col">Vacancy</th><th scope="col">Category</th><th scope="col">Appointments</th><th scope="col">Rate</th><th scope="col">Deadline</th><th scope="col">Status</th><th scope="col" class="employer-actions-cell">Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($all_dept_jobs as $job):
                                        $slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
                                        $slots_filled = (int)($job['slots_filled'] ?? 0);
                                        $job_apps = $job_applicant_counts[(int)$job['id']] ?? 0;
                                    ?>
                                        <tr>
                                            <td data-label="Vacancy"><a href="../student/job-details.php?id=<?= (int)$job['id'] ?>" class="text-ink text-decoration-none"><strong><?= htmlspecialchars($job['title']) ?></strong></a><small><?= htmlspecialchars($job['job_type'] ?? 'Student Assistant') ?> · <?= htmlspecialchars($job['work_setup'] ?? 'On-Campus') ?></small></td>
                                            <td data-label="Category" class="employer-secondary"><?= htmlspecialchars($job['category']) ?></td>
                                            <td data-label="Appointments"><?= $slots_filled ?> of <?= $slots_total ?> hired</td>
                                            <td data-label="Rate"><?= htmlspecialchars($job['pay_rate']) ?></td>
                                            <td data-label="Deadline" class="employer-date"><?= htmlspecialchars(format_display_date($job['deadline'] ?? 'Open')) ?></td>
                                            <td data-label="Status"><?= render_status_text(!empty($job['is_archived']) ? 'archived' : ($job['status'] ?? 'Active')) ?></td>
                                            <td data-label="Actions" class="employer-actions-cell">
                                                <div class="employer-actions <?= $is_archived_view ? 'employer-actions--archived' : '' ?>">
                                                    <a href="applicants.php?job_id=<?= (int)$job['id'] ?>" class="employer-button" title="View applicants for <?= htmlspecialchars($job['title'], ENT_QUOTES) ?>"><i class="bi bi-people" aria-hidden="true"></i> Applicants (<?= $job_apps ?>)</a>
                                                    <?php if (!$is_archived_view): ?>
                                                        <a href="edit-job.php?id=<?= (int)$job['id'] ?>" class="employer-button" aria-label="Edit <?= htmlspecialchars($job['title'], ENT_QUOTES) ?>"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a>
                                                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Archive this vacancy? All applicant records and evaluations will be preserved.');">
                                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                            <input type="hidden" name="action" value="archive_job">
                                                            <input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>">
                                                            <button type="submit" class="employer-button employer-button--icon" title="Archive <?= htmlspecialchars($job['title'], ENT_QUOTES) ?>" aria-label="Archive <?= htmlspecialchars($job['title'], ENT_QUOTES) ?>"><i class="bi bi-archive" aria-hidden="true"></i></button>
                                                        </form>
                                                    <?php elseif (($user['role'] ?? '') === 'admin'): ?>
                                                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Restore this vacancy to active status?');">
                                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                            <input type="hidden" name="action" value="restore_job">
                                                            <input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>">
                                                            <button type="submit" class="employer-button employer-button--primary"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Restore</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <div class="employer-status"><i class="bi bi-lock" aria-hidden="true"></i> Admin restore only</div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="employer-panel" aria-labelledby="recent-applicants-title">
                    <div class="employer-panel-heading"><div class="employer-heading-icon"><div class="employer-icon"><i class="bi bi-people" aria-hidden="true"></i></div><div><h2 id="recent-applicants-title">Recent applications</h2><p>The latest candidates for your department.</p></div></div></div>
                    <?php if (empty($dept_apps)): ?>
                        <div class="employer-empty"><p>No student applications submitted yet.</p></div>
                    <?php else: ?>
                        <div class="employer-recent">
                            <?php foreach (array_slice($dept_apps, 0, 4) as $app): ?>
                                <div class="employer-recent-item">
                                    <div class="min-w-0"><strong><?= htmlspecialchars($app['student_name']) ?></strong><p><?= htmlspecialchars($app['job_title']) ?> · <?= format_display_date($app['applied_at']) ?></p><?= render_status_text($app['status']) ?></div>
                                    <a href="review-app.php?id=<?= (int)$app['id'] ?>" class="employer-button employer-button--compact" aria-label="Review <?= htmlspecialchars($app['student_name'], ENT_QUOTES) ?>">Review <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

