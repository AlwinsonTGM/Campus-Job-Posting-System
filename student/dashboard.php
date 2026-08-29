<?php
/**
 * Campus Job Posting System - Student Dashboard
 * Archetype B: Student Dashboard & Application Hub (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['student', 'admin']);
$user = get_logged_user();
$page_title = 'Student Dashboard & Application Portal';

// Applications for current student
$my_apps = get_applications($user['id'] ?? 0);
$total_applied = count($my_apps);
$pending_count = count(array_filter($my_apps, fn($a) => in_array($a['status'] ?? '', ['pending', 'Pending Review', 'under_review', 'Under Evaluation'])));
$interview_count = count(array_filter($my_apps, fn($a) => in_array($a['status'] ?? '', ['interview_scheduled', 'Interview Scheduled'])));
$accepted_count = count(array_filter($my_apps, fn($a) => in_array($a['status'] ?? '', ['accepted', 'Accepted / Hired'])));

$pending_profile_req = get_pending_profile_request($user['id'] ?? 0);

// Recommended Jobs (filtered by student course / general assistantships)
$all_jobs = get_jobs();
$recommended_jobs = array_slice($all_jobs, 0, 3);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head with Quick Actions -->
                <?php
                $head_actions = '
                    <a href="jobs.php" class="btn-pill">
                        <i class="bi bi-search"></i> Browse Vacancies
                    </a>
                    <a href="my-applications.php" class="btn-pill-outline">
                        <i class="bi bi-folder2-open"></i> My Applications (' . $total_applied . ')
                    </a>
                ';
                render_page_head(
                    '<i class="bi bi-mortarboard-fill text-accent me-1"></i> Student Career Portal · ' . htmlspecialchars($user['course'] ?? 'BSIS'),
                    'Welcome back, ' . htmlspecialchars($user['name']),
                    'Track your assistantship evaluation status, review upcoming interview schedules, and explore vacancies matching your availability.',
                    $head_actions
                );
                ?>

                <!-- 4 KPI Metrics -->
                <div class="row g-3 mb-5">
                    <div class="col-6 col-lg-3">
                        <?php render_metric($total_applied, 'Applications Filed', 'bi-send-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($pending_count, 'In Review', 'bi-hourglass-split'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($interview_count, 'Interviews Scheduled', 'bi-calendar-event-fill'); ?>
                    </div>
                    <div class="col-6 col-lg-3">
                        <?php render_metric($accepted_count, 'Accepted / Hired', 'bi-check-circle-fill'); ?>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <!-- Left: Recent Applications Stream -->
                    <div class="col-lg-7">
                        <div class="card-paper h-100 reveal-fade-rise">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title mb-0">
                                    <i class="bi bi-clock-history text-accent me-2"></i> Active Application Status
                                </h3>
                                <a href="my-applications.php" class="text-ink fw-bold small text-decoration-none">
                                    View All <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>

                            <?php if (empty($my_apps)): ?>
                                <?php
                                render_empty_state(
                                    'bi-inbox',
                                    'No Active Applications',
                                    'You have not submitted any assistantship applications yet. Browse open campus vacancies to get started.',
                                    'jobs.php',
                                    'Find Open Opportunities'
                                );
                                ?>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach (array_slice($my_apps, 0, 3) as $app): ?>
                                        <div class="p-3 bg-surface rounded-4 border border-line">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div>
                                                    <h4 class="card-paper-title fs-6 mb-1">
                                                        <?= htmlspecialchars($app['job_title']) ?>
                                                    </h4>
                                                    <span class="small text-muted-custom">
                                                        <i class="bi bi-building me-1 text-accent"></i><?= htmlspecialchars($app['department']) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <?= render_status_badge($app['status']) ?>
                                                </div>
                                            </div>

                                            <?php if (in_array($app['status'], ['interview_scheduled', 'Interview Scheduled']) && !empty($app['interview_date'])): ?>
                                                <div class="p-3 bg-cream rounded-3 border border-line small text-ink mb-2">
                                                    <div class="d-flex align-items-center gap-2 fw-bold mb-1">
                                                        <i class="bi bi-calendar-check-fill text-accent"></i>
                                                        <span>Interview Invitation</span>
                                                    </div>
                                                    <div><strong>Schedule:</strong> <?= htmlspecialchars($app['interview_date']) ?> at <?= htmlspecialchars($app['interview_time']) ?></div>
                                                    <div><strong>Venue:</strong> <?= htmlspecialchars($app['interview_venue']) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-line small text-muted-custom">
                                                <span>Applied: <?= date('M d, Y', strtotime($app['applied_at'])) ?></span>
                                                <a href="my-applications.php" class="text-ink fw-bold text-decoration-none">
                                                    Track Details &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right: Student Profile & Schedule Card -->
                    <div class="col-lg-5">
                        <div class="card-paper h-100 reveal-fade-rise">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title mb-0">
                                    <i class="bi bi-person-badge text-accent me-2"></i> Student Profile
                                </h3>
                                <a href="../settings.php" class="text-ink fw-bold small text-decoration-none">
                                    Edit Profile <i class="bi bi-gear"></i>
                                </a>
                            </div>

                            <?php if ($pending_profile_req): ?>
                                <div class="p-2 px-3 bg-cream rounded-3 border border-line small d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-ink fw-semibold"><i class="bi bi-hourglass-split text-warning me-1"></i> Profile Update Pending</span>
                                    <a href="../settings.php" class="small fw-bold text-accent text-decoration-none">View Status</a>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="icon-circle icon-circle-success flex-shrink-0" style="width: 54px; height: 54px; min-width: 54px; min-height: 54px; font-size: 24px; aspect-ratio: 1 / 1;">
                                    <?= strtoupper(substr($user['name'] ?? 'S', 0, 1)) ?>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <h4 class="card-paper-title fs-5 mb-0 text-truncate"><?= htmlspecialchars($user['name']) ?></h4>
                                    <span class="small text-muted-custom text-truncate d-block"><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2 mb-4">
                                <div class="d-flex justify-content-between p-2 px-3 bg-cream rounded-3 small">
                                    <span class="text-muted-custom">Student ID:</span>
                                    <span class="fw-bold text-ink"><?= htmlspecialchars($user['student_id'] ?? '2024-00123') ?></span>
                                </div>
                                <div class="d-flex justify-content-between p-2 px-3 bg-cream rounded-3 small">
                                    <span class="text-muted-custom">Degree Program:</span>
                                    <span class="fw-bold text-ink"><?= htmlspecialchars($user['course'] ?? 'BS Information Systems') ?></span>
                                </div>
                                <div class="d-flex justify-content-between p-2 px-3 bg-cream rounded-3 small">
                                    <span class="text-muted-custom">Year Level / Standing:</span>
                                    <span class="fw-bold text-ink"><?= htmlspecialchars($user['year_level'] ?? '2nd Year') ?></span>
                                </div>
                                <div class="d-flex justify-content-between p-2 px-3 bg-cream rounded-3 small">
                                    <span class="text-muted-custom">Demographics:</span>
                                    <span class="fw-bold text-ink">
                                        <?= htmlspecialchars($user['sex'] ?? 'Male') ?> &bull; 
                                        <?= htmlspecialchars((string)($user['age'] ?? (isset($user['birthdate']) ? calculate_age($user['birthdate']) : 20))) ?> yrs old
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between p-2 px-3 bg-cream rounded-3 small">
                                    <span class="text-muted-custom">Academic Safeguard:</span>
                                    <span class="badge-status--accepted" style="font-size: 10px;">&le; 20 hrs/week</span>
                                </div>
                            </div>

                            <div class="p-3 bg-surface rounded-4 border border-line">
                                <div class="d-flex align-items-center gap-2 mb-1 text-ink fw-bold small">
                                    <i class="bi bi-calendar-week text-accent"></i>
                                    <span>Weekly Availability Matrix</span>
                                </div>
                                <p class="small text-muted-custom mb-2" style="font-size: 12px;">
                                    Keep your vacant time slots updated so campus offices can schedule duty hours around your lectures.
                                </p>
                                <a href="../settings.php#availability" class="btn-pill-outline btn-pill-sm w-100 text-center">
                                    Update Shift Availability
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Recommended Opportunities Section -->
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-line">
                        <div>
                            <h3 class="card-paper-title mb-1">
                                <i class="bi bi-stars text-accent me-2"></i> Recommended Campus Opportunities
                            </h3>
                            <p class="text-muted-custom small mb-0">Verified assistantships and lab roles aligned with your course and student profile</p>
                        </div>
                        <a href="jobs.php" class="btn-pill-outline btn-pill-sm">
                            View All (<?= count($all_jobs) ?>)
                        </a>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($recommended_jobs as $job): ?>
                            <div class="col-md-6 col-lg-4">
                                <?php render_job_card($job, '../'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
