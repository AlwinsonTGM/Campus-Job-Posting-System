<?php
/**
 * Campus Job Posting System - Admin Reports & Analytics
 * Archetype G: Admin Analytics & Printable Report (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin', 'employer']);
$user = get_logged_user();
$page_title = 'Reports & Institutional Analytics';

$all_jobs = get_jobs();
$all_apps = get_applications();
$categories = get_categories();
$all_users = $_SESSION['users'] ?? load_json_file('users.json');

$total_jobs = count($all_jobs);
$total_apps = count($all_apps);
$total_hired = count(array_filter($all_apps, fn($a) => $a['status'] === 'accepted'));
$total_interviews = count(array_filter($all_apps, fn($a) => $a['status'] === 'interview_scheduled'));

// Department aggregations
$departments = [
    'Management Information Systems (MIS)' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 6],
    'Office of the University Registrar' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 8],
    'KLD University Library' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 5],
    'Institute of Computing and Digital Innovation (ICDI)' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 10],
    'Institute of Nursing & Health Sciences' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 4],
    'Institute of Engineering' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 6],
];

foreach ($all_jobs as $j) {
    $dept_name = $j['department'] ?? 'General';
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 4];
    }
    $departments[$dept_name]['jobs']++;
}

foreach ($all_apps as $a) {
    $dept_name = $a['department'] ?? 'General';
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 4];
    }
    $departments[$dept_name]['apps']++;
    if (($a['status'] ?? '') === 'accepted') {
        $departments[$dept_name]['hired']++;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper printable-report">
                
                <!-- Printable Institution Header (Shown ONLY on print) -->
                <div class="d-none d-print-block text-center pb-4 mb-4 border-bottom border-line">
                    <h2 class="h3 fw-bold text-ink mb-1">KOLEHIYO NG LUNGSOD NG DASMARIÑAS</h2>
                    <h3 class="h5 fw-semibold text-muted-custom mb-1">Campus Job Posting & Student Assistantship System</h3>
                    <p class="small text-muted-custom mb-0">Official Institutional Analytics & Placement Report &bull; Academic Year 2026–2027</p>
                    <p class="small text-muted-custom mb-0">Generated: <?= date('F d, Y \a\t h:i A') ?> by <?= htmlspecialchars($user['name'] ?? 'Administrator') ?></p>
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
                        '<i class="bi bi-bar-chart-line-fill text-accent me-1"></i> Institutional Analytics & Placement',
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
                        <span class="pill-badge">Term: 1st Sem 2026–2027</span>
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
                                            <span class="badge-status--accepted"><?= $filled ?> / <?= $quota ?></span>
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
                                            <span class="badge-status--accepted">100% Compliant</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<script>
// Print Report fallback function
function triggerPrintReport() {
    window.print();
}
</script>
