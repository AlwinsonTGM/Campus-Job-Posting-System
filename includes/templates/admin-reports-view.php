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
                        'Real-time overview of student job vacancies, application volumes, candidate pipeline status, and hiring placement ratios.',
                        $actions
                    );
                    ?>
                </div>

                <?php if ($total_pending_actions > 0): ?>
                    <!-- Verification Queue Summary (Institutional Design System) -->
                    <div class="card-paper p-3 mb-4 no-print d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="icon-circle flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; font-size: 16px; background-color: var(--surface); border: 1px solid var(--line); color: var(--ink);">
                                <i class="bi bi-shield-exclamation"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="small fw-bold text-ink">
                                    <?= (int)$total_pending_actions ?> pending verification<?= $total_pending_actions === 1 ? '' : 's' ?> awaiting review
                                </div>
                                <div class="text-muted-custom" style="font-size: 11px;">
                                    <?= (int)$pending_students_count ?> student COR<?= $pending_students_count === 1 ? '' : 's' ?> &bull;
                                    <?= (int)$pending_employers_count ?> partner permit<?= $pending_employers_count === 1 ? '' : 's' ?> &bull;
                                    <?= (int)$pending_profile_count ?> profile correction<?= $pending_profile_count === 1 ? '' : 's' ?>
                                </div>
                            </div>
                        </div>
                        <a href="users.php?ver_status=pending_approval" class="btn-pill btn-sm py-1 px-3 text-nowrap d-inline-flex align-items-center gap-1 text-decoration-none">
                            Review Queue <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Executive Institutional Scorecard (Open Design Balanced Metric Ribbon) -->
                <div class="row g-3 mb-4">
                    <!-- 1. Total Users -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <div class="metric h-100 reveal-fade-rise">
                            <div class="d-flex flex-column justify-content-between h-100 w-100">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="metric-lbl">Total Users</span>
                                    <i class="bi bi-people-fill text-muted-custom fs-6"></i>
                                </div>
                                <div class="my-1">
                                    <span class="metric-val"><?= (int)$total_users ?></span>
                                </div>
                                <div class="metric-sub small text-muted-custom mt-auto">
                                    <span class="fw-semibold text-ink"><?= (int)$total_students ?></span> Students &bull; <span class="fw-semibold text-ink"><?= (int)$total_employers ?></span> Partners
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Total Vacancies -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <div class="metric h-100 reveal-fade-rise">
                            <div class="d-flex flex-column justify-content-between h-100 w-100">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="metric-lbl">Total Vacancies</span>
                                    <i class="bi bi-briefcase-fill text-muted-custom fs-6"></i>
                                </div>
                                <div class="my-1">
                                    <span class="metric-val"><?= (int)$total_jobs ?></span>
                                </div>
                                <div class="metric-sub small text-muted-custom mt-auto">
                                    <span class="fw-semibold text-ink"><?= count($categories) ?></span> Job Categories
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Applications Filed -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <div class="metric h-100 reveal-fade-rise">
                            <div class="d-flex flex-column justify-content-between h-100 w-100">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="metric-lbl">Applications Filed</span>
                                    <i class="bi bi-send-fill text-muted-custom fs-6"></i>
                                </div>
                                <div class="my-1">
                                    <span class="metric-val"><?= (int)$total_apps ?></span>
                                </div>
                                <div class="metric-sub small text-muted-custom mt-auto">
                                    <?php $unique_applicants = count(array_unique(array_column($all_apps, 'student_id'))); ?>
                                    <span class="fw-semibold text-ink"><?= $unique_applicants ?></span> Unique Applicants
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Interviews Held -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <div class="metric h-100 reveal-fade-rise">
                            <div class="d-flex flex-column justify-content-between h-100 w-100">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="metric-lbl">Interviews Scheduled</span>
                                    <i class="bi bi-calendar-check-fill text-muted-custom fs-6"></i>
                                </div>
                                <div class="my-1">
                                    <span class="metric-val"><?= (int)$total_interviews ?></span>
                                </div>
                                <div class="metric-sub small text-muted-custom mt-auto">
                                    <?php $interview_rate = $total_apps > 0 ? round(($total_interviews / $total_apps) * 100) : 0; ?>
                                    <span class="fw-semibold text-ink"><?= $interview_rate ?>%</span> Candidate Interview Rate
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Officially Hired -->
                    <div class="col-12 col-sm-6 col-lg-4 col-xl">
                        <div class="metric h-100 reveal-fade-rise">
                            <div class="d-flex flex-column justify-content-between h-100 w-100">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="metric-lbl">Officially Hired</span>
                                    <i class="bi bi-person-check-fill text-muted-custom fs-6"></i>
                                </div>
                                <div class="my-1">
                                    <span class="metric-val"><?= (int)$total_hired ?></span>
                                </div>
                                <div class="metric-sub small text-muted-custom mt-auto">
                                    <?php $placement_rate = $total_apps > 0 ? round(($total_hired / $total_apps) * 100) : 0; ?>
                                    <span class="fw-semibold text-ink"><?= $placement_rate ?>%</span> Placement Success Rate
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-Tier Visual Analytics Grid (Editorial Warm Layout) -->
                <div class="row g-4 mb-5">
                    <!-- Chart 1: In-Demand Categories (Interactive Donut with Editorial Legend) -->
                    <div class="col-lg-5">
                        <div class="card-paper h-100 reveal-fade-rise d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-line flex-wrap gap-2">
                                <h3 class="card-paper-title mb-0">
                                    <i class="bi bi-pie-chart-fill text-accent me-2"></i> In-Demand Categories
                                </h3>
                                <span class="small text-muted-custom font-mono"><?= (int)$total_jobs ?> Openings</span>
                            </div>

                            <div class="chart-canvas-wrapper position-relative my-2" style="height: 200px;">
                                <div class="donut-center-callout" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <span class="donut-number"><?= (int)$total_jobs ?></span>
                                    <span class="donut-label">Openings</span>
                                </div>
                                <canvas id="categoryDonutChart" aria-label="Most in-demand categories chart" role="img"></canvas>
                            </div>

                            <!-- Editorial Category Legend List -->
                            <div class="mt-3 pt-3 border-top border-line flex-grow-1">
                                <div class="d-flex flex-column gap-2">
                                    <?php 
                                     $palette_dots = ['#16A34A', '#0F766E', '#2563EB', '#D97706', '#7C3AED', '#DB2777', '#475569', '#0284C7'];
                                     foreach (array_slice($category_data ?? [], 0, 5) as $idx => $cat): 
                                         $dotColor = $palette_dots[$idx % count($palette_dots)];
                                     ?>
                                        <div class="d-flex align-items-center justify-content-between small">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: <?= $dotColor ?>; flex-shrink: 0;"></span>
                                                <span class="text-ink fw-medium text-truncate" title="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2 font-mono">
                                                <span class="text-muted-custom" style="font-size: 12px;"><?= $cat['count'] ?> pos.</span>
                                                <span class="text-muted-custom opacity-50">&bull;</span>
                                                <span class="fw-semibold text-ink" style="font-size: 12px;"><?= $cat['pct'] ?>%</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Applications Per Department (Horizontal Bar) -->
                    <div class="col-lg-7">
                        <div class="card-paper h-100 reveal-fade-rise d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-line flex-wrap gap-2">
                                <div>
                                    <h3 class="card-paper-title mb-0">
                                        <i class="bi bi-bar-chart-fill text-accent me-2"></i> Applications Per Department
                                    </h3>
                                    <p class="text-muted-custom small mb-0 mt-0.5">Distribution of candidate interest across campus offices</p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted-custom font-mono me-1"><?= (int)$total_apps ?> Submissions</span>
                                    <div class="btn-group btn-group-sm no-print" role="group" aria-label="Department view filter">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 active" id="btnDeptTop6" onclick="setDeptChartFilter('top6')">Top 6</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnDeptAll" onclick="setDeptChartFilter('all')">All (<?= count($departments) ?>)</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-grow-1 d-flex flex-column justify-content-center">
                                <div class="chart-canvas-wrapper" style="height: 330px;">
                                    <canvas id="departmentBarChart" aria-label="Applications per department chart" role="img"></canvas>
                                </div>
                            </div>
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

    let catChart = null;
    let deptBarChart = null;

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
                '#16A34A', '#0F766E', '#2563EB', '#D97706', 
                '#7C3AED', '#DB2777', '#475569', '#0284C7'
            ],
            deptBarBg: isDark ? 'rgba(52, 216, 101, 0.8)' : 'rgba(22, 163, 74, 0.85)',
            deptBarBorder: isDark ? '#34D865' : '#16A34A'
        };
    }

    function initCharts() {
        if (!window.Chart) return;
        const tokens = getThemeTokens();

        // 1. Categories Donut Chart (Editorial clean ring, legend handled by HTML list)
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
                    cutout: '74%',
                    animation: { duration: 800, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
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
                        maxBarThickness: 20
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
                                font: { family: "'Inter', sans-serif", size: 12, weight: '500' },
                                callback: function (value) {
                                    const label = this.getLabelForValue(value) || '';
                                    return label.length > 28 ? label.slice(0, 26) + '…' : label;
                                }
                            }
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
            deptBarChart.data.datasets[0].borderColor = tokens.deptBarBorder;
            deptBarChart.options.scales.x.ticks.color = tokens.mutedColor;
            deptBarChart.options.scales.x.grid.color = tokens.lineColor;
            deptBarChart.options.scales.y.ticks.color = tokens.textColor;
            deptBarChart.update('none');
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

    // Print event listeners
    window.addEventListener('beforeprint', function () {
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


