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

                <!-- 2-Tier Visual Analytics Grid (Fixed Height, Responsive Chart.js Suite) -->
                <div class="row g-4 mb-4">
                    <!-- Chart 1: Most In-Demand Categories (Interactive Donut) -->
                    <div class="col-lg-5">
                        <div class="card-paper h-100 reveal-fade-rise d-flex flex-column bar-chart">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title mb-0">
                                    <i class="bi bi-pie-chart-fill text-accent me-2"></i> Most In-Demand Categories
                                </h3>
                                <span class="chip"><?= count($categories) ?> Categories</span>
                            </div>

                            <div class="flex-grow-1 d-flex flex-column justify-content-center">
                                <div class="chart-canvas-wrapper">
                                    <div class="donut-center-callout">
                                        <span class="donut-number"><?= (int)$total_jobs ?></span>
                                        <span class="donut-label">Total Openings</span>
                                    </div>
                                    <canvas id="categoryDonutChart" aria-label="Most in-demand categories chart" role="img"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Applications Per Department (Horizontal Bar) -->
                    <div class="col-lg-7">
                        <div class="card-paper h-100 reveal-fade-rise d-flex flex-column bar-chart">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-line flex-wrap gap-2">
                                <h3 class="card-paper-title mb-0">
                                    <i class="bi bi-bar-chart-fill text-accent me-2"></i> Applications Per Department
                                </h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="chip"><?= (int)$total_apps ?> Submissions</span>
                                    <div class="btn-group btn-group-sm no-print" role="group" aria-label="Department view filter">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 active" id="btnDeptTop6" onclick="setDeptChartFilter('top6')">Top 6</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnDeptAll" onclick="setDeptChartFilter('all')">All (<?= count($departments) ?>)</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-grow-1 d-flex flex-column justify-content-center">
                                <div class="chart-canvas-wrapper">
                                    <canvas id="departmentBarChart" aria-label="Applications per department chart" role="img"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Department Headcount & Quota Analytics (Panoramic Grouped Bar) -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card-paper reveal-fade-rise">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-line flex-wrap gap-2">
                                <div>
                                    <h3 class="card-paper-title mb-1">
                                        <i class="bi bi-building-check text-accent me-2"></i> Department Hiring Quotas vs. Actual Hired
                                    </h3>
                                    <p class="text-muted-custom small mb-0">Requisition quotas compared against official hiring conversions</p>
                                </div>
                                <div class="funnel-badge-pill">
                                    <i class="bi bi-funnel-fill text-accent"></i>
                                    <span><?= (int)($quota_narrative['funnel']['applied'] ?? 0) ?> Applied</span>
                                    <span class="sep">&bull;</span>
                                    <span class="text-accent fw-bold"><?= (int)($quota_narrative['funnel']['interview_rate'] ?? 0) ?>% Interviewed</span>
                                    <span class="sep">&bull;</span>
                                    <span class="text-primary fw-bold"><?= (int)($quota_narrative['funnel']['hire_rate'] ?? 0) ?>% Hired</span>
                                </div>
                            </div>

                            <div class="chart-canvas-wrapper chart-canvas-wrapper--panoramic mb-2">
                                <canvas id="departmentQuotaChart" aria-label="Department hiring quotas vs filled chart" role="img"></canvas>
                            </div>

                            <!-- Phase 3-B: Compact Flagged Departments Advisory Drawer -->
                            <?php if (empty($flagged_departments)): ?>
                                <div class="p-3 bg-cream rounded-3 border border-line d-flex align-items-center gap-2 small text-ink mt-3 no-print">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <span><strong>All departments in compliance:</strong> All academic and administrative units track within assigned quota targets with healthy applicant conversion.</span>
                                </div>
                            <?php else: ?>
                                <div class="mt-3 pt-3 border-top border-line no-print">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                        <span class="fw-bold text-ink small d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-exclamation-triangle-fill text-warning"></i> Flagged Department Advisories (<?= count($flagged_departments) ?> Units)
                                        </span>
                                        <span class="small text-muted-custom" style="font-size: 11px;">
                                            <i class="bi bi-info-circle me-1"></i> Advisory insights only — all quotas and postings stay with the administrator.
                                        </span>
                                    </div>
                                    <div class="row g-2">
                                        <?php foreach ($flagged_departments as $f): ?>
                                            <div class="col-md-6 col-xl-4">
                                                <div class="p-2.5 bg-cream rounded-3 border border-line h-100 small">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="text-ink text-truncate me-1" title="<?= htmlspecialchars($f['dept']) ?>"><?= htmlspecialchars($f['dept']) ?></strong>
                                                        <span class="badge <?= $f['fill_pct'] > 100 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?>"><?= (int)$f['fill_pct'] ?>% filled</span>
                                                    </div>
                                                    <?php foreach (($f['flags'] ?? []) as $flag): ?>
                                                        <div class="text-muted-custom d-flex align-items-start gap-1" style="font-size: 11px; line-height: 1.35;">
                                                            <i class="bi bi-dot text-warning flex-shrink-0 fs-6" style="margin-top: -3px;"></i>
                                                            <span><?= htmlspecialchars($flag) ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Department Hiring Quotas vs Filled Positions Table -->
                <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
                    <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center bg-surface flex-wrap gap-2">
                        <div>
                            <h3 class="card-paper-title mb-1">
                                <i class="bi bi-table text-accent me-2"></i> Department Hiring Quotas vs Filled Positions
                            </h3>
                            <p class="text-muted-custom small mb-0">Institutional compliance, vacancy quotas, and hiring ratios</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted-custom">Term: 1st Sem 2026–2027</span>
                            <button class="btn-pill-outline btn-sm py-1 px-3 d-inline-flex align-items-center gap-1 no-print" type="button" data-bs-toggle="collapse" data-bs-target="#auditTableCollapse" aria-expanded="true" aria-controls="auditTableCollapse" id="auditTableToggleBtn">
                                <i class="bi bi-chevron-up" id="auditTableToggleIcon"></i> <span id="auditTableToggleText">Collapse Table</span>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="auditTableCollapse">
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

<!-- Chart.js Offline Vendor & Institutional Analytics Engine -->
<script src="<?= $base_url ?>assets/vendor/chart.js/chart.umd.min.js"></script>
<script>
// Print Report fallback function
function triggerPrintReport() {
    window.print();
}

(function () {
    'use strict';

    // SSR Data Payload from PHP
    const categoryLabels = <?= json_encode($category_chart_labels ?? []) ?>;
    const categoryCounts = <?= json_encode($category_chart_counts ?? []) ?>;
    const categoryPcts = <?= json_encode($category_chart_pcts ?? []) ?>;

    const deptAppTopLabels = <?= json_encode($dept_app_top_labels ?? []) ?>;
    const deptAppTopCounts = <?= json_encode($dept_app_top_counts ?? []) ?>;
    const deptAppAllLabels = <?= json_encode($dept_app_all_labels ?? []) ?>;
    const deptAppAllCounts = <?= json_encode($dept_app_all_counts ?? []) ?>;

    const deptQuotaLabels = <?= json_encode($dept_quota_labels ?? []) ?>;
    const deptQuotaTargets = <?= json_encode($dept_quota_targets ?? []) ?>;
    const deptQuotaHired = <?= json_encode($dept_quota_hired ?? []) ?>;
    const deptQuotaFillPcts = <?= json_encode($dept_quota_fill_pcts ?? []) ?>;

    let catChart = null;
    let deptBarChart = null;
    let quotaChart = null;

    function getThemeTokens() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            isDark: isDark,
            textColor: isDark ? '#e2e8f0' : '#1e293b',
            mutedColor: isDark ? '#94a3b8' : '#64748b',
            lineColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.07)',
            surfaceColor: isDark ? '#1e293b' : '#ffffff',
            tooltipBg: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(15, 23, 42, 0.92)',
            palette: [
                '#2ECC5E', '#f59e0b', '#3b82f6', '#8b5cf6', 
                '#ec4899', '#06b6d4', '#10b981', '#f97316', 
                '#6366f1', '#14b8a6'
            ],
            quotaTargetBg: isDark ? 'rgba(245, 158, 11, 0.35)' : 'rgba(245, 158, 11, 0.55)',
            quotaTargetBorder: '#f59e0b',
            quotaHiredBg: isDark ? 'rgba(46, 204, 94, 0.85)' : 'rgba(46, 204, 94, 0.95)',
            quotaHiredBorder: '#2ECC5E',
            deptBarBg: isDark ? 'rgba(59, 130, 246, 0.8)' : 'rgba(59, 130, 246, 0.88)',
            deptBarBorder: '#3b82f6'
        };
    }

    function initCharts() {
        if (!window.Chart) return;
        const tokens = getThemeTokens();

        // 1. Categories Donut Chart
        const catCanvas = document.getElementById('categoryDonutChart');
        if (catCanvas) {
            catChart = new Chart(catCanvas, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryCounts,
                        backgroundColor: tokens.palette.slice(0, categoryLabels.length),
                        borderColor: tokens.surfaceColor,
                        borderWidth: 2,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    animation: { duration: 800, easing: 'easeOutQuart' },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: tokens.textColor,
                                boxWidth: 10,
                                boxHeight: 10,
                                padding: 10,
                                font: { family: "'Inter', sans-serif", size: 11, weight: '500' }
                            }
                        },
                        tooltip: {
                            backgroundColor: tokens.tooltipBg,
                            titleColor: '#ffffff',
                            bodyColor: '#e2e8f0',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    const val = context.raw || 0;
                                    const pct = categoryPcts[context.dataIndex] || 0;
                                    return ` ${val} opening${val === 1 ? '' : 's'} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Department Applications Bar Chart (Horizontal)
        const deptCanvas = document.getElementById('departmentBarChart');
        if (deptCanvas) {
            deptBarChart = new Chart(deptCanvas, {
                type: 'bar',
                data: {
                    labels: deptAppTopLabels,
                    datasets: [{
                        label: 'Total Applicants',
                        data: deptAppTopCounts,
                        backgroundColor: tokens.deptBarBg,
                        borderColor: tokens.deptBarBorder,
                        borderWidth: 1.5,
                        borderRadius: 6,
                        maxBarThickness: 22
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 800, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: tokens.tooltipBg,
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    return ` ${context.raw} applicant${context.raw === 1 ? '' : 's'}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: tokens.lineColor },
                            ticks: { color: tokens.mutedColor, precision: 0, font: { family: "'Inter', sans-serif", size: 11 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { 
                                color: tokens.textColor, 
                                font: { family: "'Inter', sans-serif", size: 11, weight: '500' },
                                callback: function (value) {
                                    const label = this.getLabelForValue(value) || '';
                                    return label.length > 24 ? label.slice(0, 22) + '…' : label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 3. Department Quota vs Hired Grouped Bar Chart
        const quotaCanvas = document.getElementById('departmentQuotaChart');
        if (quotaCanvas) {
            quotaChart = new Chart(quotaCanvas, {
                type: 'bar',
                data: {
                    labels: deptQuotaLabels,
                    datasets: [
                        {
                            label: 'Assigned Hiring Quota',
                            data: deptQuotaTargets,
                            backgroundColor: tokens.quotaTargetBg,
                            borderColor: tokens.quotaTargetBorder,
                            borderWidth: 1.5,
                            borderRadius: 5,
                            maxBarThickness: 28
                        },
                        {
                            label: 'Officially Hired / Placed',
                            data: deptQuotaHired,
                            backgroundColor: tokens.quotaHiredBg,
                            borderColor: tokens.quotaHiredBorder,
                            borderWidth: 1.5,
                            borderRadius: 5,
                            maxBarThickness: 28
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 900, easing: 'easeOutQuart' },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                color: tokens.textColor,
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 12,
                                font: { family: "'Inter', sans-serif", size: 12, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: tokens.tooltipBg,
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                afterBody: function (tooltipItems) {
                                    if (!tooltipItems.length) return '';
                                    const idx = tooltipItems[0].dataIndex;
                                    const pct = deptQuotaFillPcts[idx] || 0;
                                    return `Quota Fulfillment: ${pct}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { 
                                color: tokens.textColor, 
                                font: { family: "'Inter', sans-serif", size: 11, weight: '500' },
                                callback: function (value) {
                                    const label = this.getLabelForValue(value) || '';
                                    return label.length > 20 ? label.slice(0, 18) + '…' : label;
                                }
                            }
                        },
                        y: {
                            grid: { color: tokens.lineColor },
                            ticks: { color: tokens.mutedColor, precision: 0, font: { family: "'Inter', sans-serif", size: 11 } }
                        }
                    }
                }
            });
        }
    }

    // Filter switcher for Department Applications Bar Chart
    window.setDeptChartFilter = function (mode) {
        if (!deptBarChart) return;
        const btnTop6 = document.getElementById('btnDeptTop6');
        const btnAll = document.getElementById('btnDeptAll');

        if (mode === 'top6') {
            if (btnTop6) btnTop6.classList.add('active');
            if (btnAll) btnAll.classList.remove('active');
            deptBarChart.data.labels = deptAppTopLabels;
            deptBarChart.data.datasets[0].data = deptAppTopCounts;
        } else {
            if (btnTop6) btnTop6.classList.remove('active');
            if (btnAll) btnAll.classList.add('active');
            deptBarChart.data.labels = deptAppAllLabels;
            deptBarChart.data.datasets[0].data = deptAppAllCounts;
        }
        deptBarChart.update();
    };

    // Update charts dynamically when theme changes
    function updateChartThemes() {
        if (!window.Chart) return;
        const tokens = getThemeTokens();

        if (catChart) {
            catChart.data.datasets[0].borderColor = tokens.surfaceColor;
            catChart.options.plugins.legend.labels.color = tokens.textColor;
            catChart.update('none');
        }

        if (deptBarChart) {
            deptBarChart.data.datasets[0].backgroundColor = tokens.deptBarBg;
            deptBarChart.options.scales.x.ticks.color = tokens.mutedColor;
            deptBarChart.options.scales.x.grid.color = tokens.lineColor;
            deptBarChart.options.scales.y.ticks.color = tokens.textColor;
            deptBarChart.update('none');
        }

        if (quotaChart) {
            quotaChart.data.datasets[0].backgroundColor = tokens.quotaTargetBg;
            quotaChart.data.datasets[1].backgroundColor = tokens.quotaHiredBg;
            quotaChart.options.plugins.legend.labels.color = tokens.textColor;
            quotaChart.options.scales.x.ticks.color = tokens.textColor;
            quotaChart.options.scales.y.ticks.color = tokens.mutedColor;
            quotaChart.options.scales.y.grid.color = tokens.lineColor;
            quotaChart.update('none');
        }
    }

    // Mutation observer for dark/light mode toggle
    const observer = new MutationObserver(function (mutations) {
        for (const m of mutations) {
            if (m.type === 'attributes' && (m.attributeName === 'data-theme' || m.attributeName === 'data-bs-theme')) {
                updateChartThemes();
                break;
            }
        }
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme', 'data-bs-theme'] });

    // Collapsible Audit Table indicator sync
    const tableCollapse = document.getElementById('auditTableCollapse');
    const toggleIcon = document.getElementById('auditTableToggleIcon');
    const toggleText = document.getElementById('auditTableToggleText');

    if (tableCollapse) {
        tableCollapse.addEventListener('show.bs.collapse', function () {
            if (toggleIcon) toggleIcon.className = 'bi bi-chevron-up';
            if (toggleText) toggleText.textContent = 'Collapse Table';
        });
        tableCollapse.addEventListener('hide.bs.collapse', function () {
            if (toggleIcon) toggleIcon.className = 'bi bi-chevron-down';
            if (toggleText) toggleText.textContent = 'Expand Table';
        });
    }

    // Print event listeners
    window.addEventListener('beforeprint', function () {
        if (tableCollapse && typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            const inst = bootstrap.Collapse.getInstance(tableCollapse);
            if (inst) inst.show();
        }
        updateChartThemes();
    });

    window.addEventListener('afterprint', function () {
        updateChartThemes();
    });

    // Run initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }
})();
</script>


