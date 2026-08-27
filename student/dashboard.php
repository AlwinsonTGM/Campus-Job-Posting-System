<?php
/**
 * Campus Job Posting System - Student Dashboard
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['student', 'admin']);
$user = get_logged_user();
$page_title = 'Student Dashboard';

// Student applications
$my_apps = get_applications($user['id']);
$total_applied = count($my_apps);
$pending_count = count(array_filter($my_apps, fn($a) => $a['status'] === 'pending'));
$interview_count = count(array_filter($my_apps, fn($a) => $a['status'] === 'interview_scheduled'));
$accepted_count = count(array_filter($my_apps, fn($a) => $a['status'] === 'accepted'));

// Recommended jobs
$all_jobs = get_jobs();
$recommended_jobs = array_slice($all_jobs, 0, 4);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <!-- Welcome Banner -->
        <div class="card border-line shadow-sm rounded-4 p-4 p-md-4 mb-4 text-white bg-kld-gradient">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-accent-soft text-ink p-2 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-mortarboard-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="pill-badge pill-badge-ink text-uppercase small">Student Portal</span>
                            <h3 class="fw-bold text-white mb-0 mt-1">Welcome back, <?= htmlspecialchars($user['name']) ?>!</h3>
                        </div>
                    </div>
                    <p class="text-white-50 mb-0 small">
                        <strong>Student ID:</strong> <?= htmlspecialchars($user['student_id'] ?? '2024-00123') ?> &bull; 
                        <strong>Course:</strong> <?= htmlspecialchars($user['course'] ?? 'BS Information Systems') ?> &bull; 
                        <strong>Year:</strong> <?= htmlspecialchars($user['year_level'] ?? '2nd Year') ?>
                    </p>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-center justify-content-md-end">
                    <a href="jobs.php" class="btn-accent-pill py-2 px-3 shadow-sm">
                        <i class="bi bi-search me-1"></i> BROWSE OPENINGS
                    </a>
                    <a href="my-applications.php" class="btn-outline-pill py-2 px-3 text-white border-white">
                        <i class="bi bi-folder2-open me-1"></i> MY APPLICATIONS
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Total Applied</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $total_applied ?></div>
                        </div>
                        <div class="stat-icon bg-accent-soft text-ink">
                            <i class="bi bi-send-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Pending Review</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $pending_count ?></div>
                        </div>
                        <div class="stat-icon bg-cream text-ink">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Interviews</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $interview_count ?></div>
                        </div>
                        <div class="stat-icon bg-cream text-ink">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted-custom small fw-semibold text-uppercase" style="font-size: 11px;">Accepted / Hired</div>
                            <div class="h3 fw-bold text-ink mb-0"><?= $accepted_count ?></div>
                        </div>
                        <div class="stat-icon bg-accent-soft text-ink">
                            <i class="bi bi-check-circle-fill text-accent"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Active Applications Status Timeline -->
            <div class="col-lg-7">
                <div class="card border-line shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-ink mb-0"><i class="bi bi-clock-history text-accent me-2"></i> Active Applications</h5>
                        <a href="my-applications.php" class="text-decoration-none small fw-bold text-accent">View All <i class="bi bi-chevron-right"></i></a>
                    </div>

                    <?php if (empty($my_apps)): ?>
                        <div class="text-center py-5 text-muted-custom">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-accent"></i>
                            <p class="mb-3">You have not submitted any job applications yet.</p>
                            <a href="jobs.php" class="btn-accent-pill">Find a Campus Vacancy</a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach (array_slice($my_apps, 0, 3) as $app): ?>
                                <div class="p-3 rounded-3 border-line border bg-surface">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold text-ink mb-1"><?= htmlspecialchars($app['job_title']) ?></h6>
                                            <span class="text-muted-custom small"><i class="bi bi-building me-1 text-accent"></i><?= htmlspecialchars($app['department']) ?></span>
                                        </div>
                                        <?php
                                        $badge_pill = match($app['status']) {
                                            'accepted' => 'pill-badge',
                                            'rejected' => 'pill-badge pill-badge-ink',
                                            'interview_scheduled' => 'pill-badge',
                                            default => 'chip-tag'
                                        };
                                        ?>
                                        <span class="<?= $badge_pill ?>" style="font-size: 11px;">
                                            <?= htmlspecialchars($app['status_label']) ?>
                                        </span>
                                    </div>

                                    <?php if ($app['status'] === 'interview_scheduled' && !empty($app['interview_date'])): ?>
                                        <div class="p-2 bg-cream border-line border rounded-3 small mb-2 text-ink">
                                            <i class="bi bi-calendar-check text-accent me-1"></i>
                                            <strong>Interview:</strong> <?= htmlspecialchars($app['interview_date']) ?> at <?= htmlspecialchars($app['interview_time']) ?> &bull; 
                                            <em>Venue: <?= htmlspecialchars($app['interview_venue']) ?></em>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center small text-muted-custom pt-2 border-top border-line">
                                        <span>Applied: <?= date('M d, Y', strtotime($app['applied_at'])) ?></span>
                                        <a href="my-applications.php" class="text-decoration-none fw-bold text-accent">Track Details &rarr;</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Recommended Job Vacancies -->
            <div class="col-lg-5">
                <div class="card border-line shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-ink mb-0"><i class="bi bi-stars text-accent me-2"></i> Recommended Jobs</h5>
                        <a href="jobs.php" class="text-decoration-none small fw-bold text-accent">Explore &rarr;</a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($recommended_jobs as $job): ?>
                            <div class="p-3 rounded-3 border-line border bg-surface">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0">
                                        <a href="job-details.php?id=<?= $job['id'] ?>" class="text-decoration-none text-ink">
                                            <?= htmlspecialchars($job['title']) ?>
                                        </a>
                                    </h6>
                                    <span class="pill-badge" style="font-size: 11px;"><?= htmlspecialchars($job['pay_rate']) ?></span>
                                </div>
                                <div class="text-muted-custom small mb-2"><?= htmlspecialchars($job['department']) ?></div>
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-muted-custom"><i class="bi bi-clock me-1 text-accent"></i><?= htmlspecialchars($job['hours_per_week']) ?></span>
                                    <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn-outline-pill py-1 px-3" style="font-size: 11px;">Apply</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
