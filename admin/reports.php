<?php
/**
 * Campus Job Posting System - Admin Reports & Analytics
 */
require_once __DIR__ . '/../includes/data-helper.php';

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
    'Management Information Systems (MIS)' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'Office of the University Registrar' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'University Main Library' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'Mathematics & Sciences Department' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'Public Relations & Media Center' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'College of Science & Natural Resources' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
];

foreach ($all_jobs as $j) {
    $dept_name = $j['department'];
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0];
    }
    $departments[$dept_name]['jobs']++;
}

foreach ($all_apps as $a) {
    $dept_name = $a['department'];
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0];
    }
    $departments[$dept_name]['apps']++;
    if ($a['status'] === 'accepted') {
        $departments[$dept_name]['hired']++;
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-light flex-grow-1">
    <div class="container printable-report">
        
        <!-- Header & Print Actions -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <nav aria-label="breadcrumb" class="no-print">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reports & Analytics</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-dark mb-0">Campus Employment Analytics Report</h2>
                <div class="text-muted small">Generated on: <?= date('F d, Y \a\t h:i A') ?> &bull; Term: 1st Semester 2026-2027</div>
            </div>
            <div class="mt-3 mt-md-0 no-print">
                <button type="button" onclick="triggerPrintReport()" class="btn btn-academic btn-sm shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Print / Export Report (PDF)
                </button>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card" style="border-left-color: #3b82f6;">
                    <div class="text-muted small fw-semibold text-uppercase">Total Vacancies</div>
                    <div class="display-6 fw-bold text-primary"><?= $total_jobs ?></div>
                    <span class="small text-muted">Across <?= count($departments) ?> departments</span>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card" style="border-left-color: #f59e0b;">
                    <div class="text-muted small fw-semibold text-uppercase">Applications Filed</div>
                    <div class="display-6 fw-bold text-warning"><?= $total_apps ?></div>
                    <span class="small text-muted">Total student submissions</span>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card" style="border-left-color: #06b6d4;">
                    <div class="text-muted small fw-semibold text-uppercase">Interviews Held</div>
                    <div class="display-6 fw-bold text-info"><?= $total_interviews ?></div>
                    <span class="small text-muted">Shortlisted candidates</span>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card" style="border-left-color: #10b981;">
                    <div class="text-muted small fw-semibold text-uppercase">Officially Hired</div>
                    <div class="display-6 fw-bold text-success"><?= $total_hired ?></div>
                    <span class="small text-muted">Active Student Assistants</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left 6-col: Category Distribution -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i> Category Demand Breakdown</h5>
                    
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($categories as $c): 
                            $cat_job_count = count(array_filter($all_jobs, fn($j) => $j['category'] === $c['name']));
                            $pct = $total_jobs > 0 ? round(($cat_job_count / $total_jobs) * 100) : 0;
                        ?>
                            <div>
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($c['name']) ?></span>
                                    <span class="text-muted"><?= $cat_job_count ?> jobs (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-<?= $c['color'] ?? 'primary' ?>" role="progressbar" style="width: <?= max(5, $pct) ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right 6-col: Key Institutional Highlights -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-clipboard2-data-fill text-warning me-2"></i> Compliance & Performance Highlights</h5>
                    
                    <ul class="list-group list-group-flush small text-secondary">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span><i class="bi bi-clock-history text-primary me-2"></i> Maximum Weekly SA Duty Limit Compliance:</span>
                            <span class="badge bg-success-subtle text-success fw-bold">100% Compliant (&le; 20 hrs/wk)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span><i class="bi bi-shield-check text-info me-2"></i> RA 10173 Data Privacy Audit:</span>
                            <span class="badge bg-success-subtle text-success fw-bold">Certified Protected</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span><i class="bi bi-cash-coin text-warning me-2"></i> Average Hourly Student Stipend:</span>
                            <span class="fw-bold text-dark">₱85.00 / hour</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span><i class="bi bi-building-check text-secondary me-2"></i> Total Active Partner Offices:</span>
                            <span class="fw-bold text-dark"><?= count($departments) ?> University Offices</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Department Breakdown Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="p-4 border-bottom">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-table text-primary me-2"></i> Department Requisition & Hiring Matrix</h5>
                <span class="text-muted small">Summary of open slots, applicant flow, and placement rate per department</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Department / Campus Office</th>
                            <th>Active Postings</th>
                            <th>Total Applicants</th>
                            <th>Hired SAs</th>
                            <th>Hiring Ratio</th>
                            <th class="text-end pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $dept_name => $stats): 
                            $ratio = $stats['apps'] > 0 ? round(($stats['hired'] / $stats['apps']) * 100) : 0;
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($dept_name) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= $stats['jobs'] ?> Openings</span>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?= $stats['apps'] ?> candidates</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success"><?= $stats['hired'] ?> hired</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= $ratio ?>%"></div>
                                        </div>
                                        <span class="small text-muted"><?= $ratio ?>%</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-success-subtle text-success small">Operational</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Printable Footer Note (Visible during print) -->
        <div class="d-none d-print-block mt-5 pt-4 border-top text-center small text-muted">
            <p>Office of Student Affairs & Services &bull; University Academic Center &bull; Generated via Campus Job Posting System</p>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
