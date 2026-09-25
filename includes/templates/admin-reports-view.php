<?php
/**
 * View template for admin/reports.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper printable-report">
                
                <!-- Printable Institution Header (Shown ONLY on print) -->
                <div class="d-none d-print-block text-center pb-4 mb-4 border-bottom border-line">
                    <h2 class="h3 fw-bold text-ink mb-1">KOLEHIYO NG LUNGSOD NG DASMARIÑAS</h2>
                    <h3 class="h5 fw-semibold text-muted-custom mb-1">Campus Job Posting & Student Assistantship System</h3>
                    <p class="small text-muted-custom mb-0">Official Institutional Analytics & Placement Report &bull; Academic Year 2026–2027</p>
                    <p class="small text-muted-custom mb-0">Generated: <?= format_display_date(time(), true) ?> by <?= htmlspecialchars($user['name'] ?? 'Administrator') ?></p>
                </div>

                <!-- Page Head (Interactive view) -->
                <div class="no-print">
                    <?php
                    $actions = '
                        <button type="button" onclick="window.print()" class="btn-pill">
                            <i class="bi bi-printer-fill"></i> Print Report
                        </button>
                        <button type="button" onclick="window.print()" class="btn-pill-outline">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
                        </button>
                    ';
                    render_page_head(
                        '',
                        'Campus Employment Analytics',
                        'Real-time overview of student job vacancies, application volumes, hiring ratios, and departmental budget compliance.',
                        $actions
                    );
                    ?>
                </div>

                <!-- 4 KPI Metrics Row -->
                <div class="row g-3 mb-5">
                    <div class="col-6 col-lg-3">
                        <?php render_metric($total_jobs, 'Total Vacancies', 'bi-briefcase-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($total_apps, 'Applications Filed', 'bi-send-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($total_interviews, 'Interviews Held', 'bi-calendar-check-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($total_hired, 'Officially Hired', 'bi-person-check-fill'); ?>
                    </div>
                </div>

                <!-- Department Analytics: Quota narrative (read-only explainer, no-print) -->
                <div class="no-print">
                    <?php $quota_notes = $laya_narrative['notes'] ?? []; ?>
                    <?php if (!empty($quota_notes)): ?>
                        <div class="card-paper p-4 mb-4 border border-line">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-ink small d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-bar-chart-line text-accent"></i> Department Analytics — Funnel & Quota Breakdown
                                </span>
                                <span class="small text-muted-custom">Funnel: <?= (int)($laya_narrative['funnel']['applied'] ?? 0) ?> applied &bull; <?= (int)($laya_narrative['funnel']['interview_rate'] ?? 0) ?>% to interview &bull; <?= (int)($laya_narrative['funnel']['hire_rate'] ?? 0) ?>% hired</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($quota_notes as $note): ?>
                                    <div class="p-2 bg-cream rounded-3 border border-line small">
                                        <strong class="text-ink"><?= htmlspecialchars($note['dept'] ?? '') ?></strong>
                                        <span class="text-muted-custom"> — <?= (int)($note['fill_pct'] ?? 0) ?>% quota filled, <?= (int)($note['conversion'] ?? 0) ?>% applicant conversion</span>
                                        <?php foreach (($note['flags'] ?? []) as $flag): ?>
                                            <span class="text-muted-custom d-block" style="font-size: 11px;"><i class="bi bi-dot"></i><?= htmlspecialchars($flag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="small text-muted-custom mt-2 mb-0" style="font-size: 11px;">
                                <i class="bi bi-info-circle me-1"></i> Advisory insights only — quotas and postings are unchanged; all decisions stay with the administrator.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3 Pure-CSS Bar Chart Blocks per Archetype G Spec -->
                <div class="row g-4 mb-5">
                    <!-- Chart 1: Most In-Demand Categories -->
                    <div class="col-lg-6">
                        <div class="card-paper h-100 reveal-fade-rise">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title mb-0">
                                    <i class="bi bi-pie-chart-fill text-accent me-2"></i> Most In-Demand Categories
                                </h3>
                                <span class="chip"><?= count($categories) ?> Categories</span>
                            </div>

                            <div class="bar-chart">
                                <?php foreach ($categories as $c): 
                                    $cat_job_count = count(array_filter($all_jobs, fn($j) => ($j['category'] ?? '') === $c['name']));
                                    $pct = $total_jobs > 0 ? round(($cat_job_count / $total_jobs) * 100) : 0;
                                ?>
                                    <div class="bar-chart-item">
                                        <div class="bar-chart-header">
                                            <span><?= htmlspecialchars($c['name']) ?></span>
                                            <span class="text-muted-custom"><?= $cat_job_count ?> openings (<?= $pct ?>%)</span>
                                        </div>
                                        <div class="bar-chart-track">
                                            <div class="bar-chart-fill" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Applications Per College / Department -->
                    <div class="col-lg-6">
                        <div class="card-paper h-100 reveal-fade-rise">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title mb-0">
                                    <i class="bi bi-building text-accent me-2"></i> Applications Per Department
                                </h3>
                                <span class="chip"><?= $total_apps ?> Total Submissions</span>
                            </div>

                            <div class="bar-chart">
                                <?php foreach ($departments as $dept_name => $stats): 
                                    $dept_apps_count = $stats['apps'];
                                    $pct = $total_apps > 0 ? round(($dept_apps_count / $total_apps) * 100) : 0;
                                ?>
                                    <div class="bar-chart-item">
                                        <div class="bar-chart-header">
                                            <span><?= htmlspecialchars($dept_name) ?></span>
                                            <span class="text-muted-custom"><?= $dept_apps_count ?> applicants (<?= $pct ?>%)</span>
                                        </div>
                                        <div class="bar-chart-track">
                                            <div class="bar-chart-fill" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Department Hiring Quotas vs Filled Positions Table -->
                <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
                    <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center bg-surface">
                        <div>
                            <h3 class="card-paper-title mb-1">
                                <i class="bi bi-table text-accent me-2"></i> Department Hiring Quotas vs Filled Positions
                            </h3>
                            <p class="text-muted-custom small mb-0">Institutional compliance, vacancy quotas, and hiring ratios</p>
                        </div>
                        <span class="small text-muted-custom">Term: 1st Sem 2026–2027</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table-paper table-paper-responsive mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Department / Campus Office</th>
                                    <th>Active Postings</th>
                                    <th>Total Applicants</th>
                                    <th>Hiring Quota</th>
                                    <th>Filled Positions</th>
                                    <th>Placement Ratio</th>
                                    <th class="text-end pe-4">Compliance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($departments)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted-custom small">
                                            <i class="bi bi-inbox me-1"></i> No departmental quota records available for this reporting period.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($departments as $dept_name => $stats): 
                                        $ratio = $stats['apps'] > 0 ? round(($stats['hired'] / $stats['apps']) * 100) : 0;
                                        $quota = $stats['quota'] ?? 6;
                                        $filled = $stats['hired'];
                                        $quota_pct = round(($filled / max(1, $quota)) * 100);
                                    ?>
                                        <tr>
                                            <td class="ps-4" data-label="Department">
                                                <strong class="text-ink"><?= htmlspecialchars($dept_name) ?></strong>
                                            </td>
                                            <td data-label="Active Postings">
                                                <span class="chip"><?= $stats['jobs'] ?> Openings</span>
                                            </td>
                                            <td data-label="Total Applicants">
                                                <span class="fw-semibold text-ink"><?= $stats['apps'] ?> candidates</span>
                                            </td>
                                            <td data-label="Hiring Quota">
                                                <span class="text-muted-custom"><?= $quota ?> slots</span>
                                            </td>
                                            <td data-label="Filled Positions">
                                                <span class="chip"><?= $filled ?> / <?= $quota ?></span>
                                            </td>
                                            <td data-label="Placement Ratio">
                                                <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                                                    <div class="progress-paper flex-grow-1">
                                                        <div class="progress-paper-bar" style="width: <?= min(100, $quota_pct) ?>%;"></div>
                                                    </div>
                                                    <span class="small text-muted-custom"><?= $quota_pct ?>%</span>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4" data-label="Compliance">
                                                <?php if ($filled > $quota): ?>
                                                    <span class="cell-flag cell-flag--bad"><span class="cell-dot"></span>Quota exceeded</span>
                                                <?php elseif ($filled >= $quota): ?>
                                                    <span class="cell-flag cell-flag--ok"><span class="cell-dot"></span>100% filled</span>
                                                <?php elseif ($filled > 0): ?>
                                                    <span class="cell-flag cell-flag--warn"><span class="cell-dot"></span>In progress</span>
                                                <?php else: ?>
                                                    <span class="cell-flag cell-flag--idle"><span class="cell-dot"></span>Open quota</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Printable Signatures & Legal Note (Visible ONLY when printing) -->
                <div class="d-none d-print-block mt-4 pt-2 border-top border-line" style="page-break-inside: avoid; break-inside: avoid;">
                    <div class="row pt-3 text-center">
                        <div class="col-4">
                            <div class="border-bottom border-dark pb-3 mb-2" style="height: 36px;"></div>
                            <strong class="d-block small text-ink">Prepared By:</strong>
                            <span class="small text-muted-custom" style="font-size: 11px;">University Career Services</span>
                        </div>
                        <div class="col-4">
                            <div class="border-bottom border-dark pb-3 mb-2" style="height: 36px;"></div>
                            <strong class="d-block small text-ink">Verified By:</strong>
                            <span class="small text-muted-custom" style="font-size: 11px;">Dean of Student Affairs</span>
                        </div>
                        <div class="col-4">
                            <div class="border-bottom border-dark pb-3 mb-2" style="height: 36px;"></div>
                            <strong class="d-block small text-ink">Noted By:</strong>
                            <span class="small text-muted-custom" style="font-size: 11px;">VP for Academic Affairs</span>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

<script>
// Print Report fallback function
function triggerPrintReport() {
    window.print();
}
</script>

