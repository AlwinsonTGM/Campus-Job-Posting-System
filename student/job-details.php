<?php
/**
 * Campus Job Posting System - Job Opportunity Details
 * Archetype C: Detail & Action Sidebar (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$job_id = $_GET['id'] ?? ($_GET['job_id'] ?? null);
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The requested opportunity could not be found or has been closed.');
    header('Location: jobs.php');
    exit;
}

$user = get_logged_user();
$already_applied = false;
if ($user) {
    $my_apps = get_applications($user['id'] ?? 0);
    foreach ($my_apps as $a) {
        if (($a['job_id'] ?? 0) == $job['id']) {
            $already_applied = true;
            $app_status = $a['status'] ?? 'pending';
            break;
        }
    }
}

$is_partner = ($job['employer_type'] ?? '') === 'approved_partner';
$org_name = $job['organization_name'] ?? ($job['department'] ?? 'Campus Organization');
$jtype = $job['job_type'] ?? 'Student Assistant';
$wsetup = $job['work_setup'] ?? 'On-Campus';
$slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
$slots_filled = (int)($job['slots_filled'] ?? 0);
$pct = ($slots_total > 0) ? round(($slots_filled / $slots_total) * 100) : 0;

$page_title = $job['title'] . ' | Opportunity Details';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Breadcrumb & Page Head -->
                <div class="mb-4">
                    <a href="jobs.php" class="text-ink fw-bold small text-decoration-none d-inline-flex align-items-center gap-1 mb-3">
                        <i class="bi bi-arrow-left"></i> Back to Open Vacancies
                    </a>
                    
                    <?php
                    render_page_head(
                        '',
                        $job['title'],
                        $org_name . ' • ' . $job['location'] . ' • ' . $job['pay_rate']
                    );
                    ?>
                </div>

                <div class="row g-4 mb-5">
                    <!-- Left 8-col: Main Description & Responsibilities -->
                    <div class="col-lg-8">
                        <div class="card-paper p-4 p-md-5 mb-4">
                            
                            <!-- Badges Header -->
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom border-line">
                                <span class="chip active">
                                    <i class="bi bi-tag-fill text-accent"></i> <?= htmlspecialchars($jtype) ?>
                                </span>
                                <span class="chip">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($wsetup) ?>
                                </span>
                                <span class="chip">
                                    <i class="bi bi-clock"></i> <?= htmlspecialchars($job['hours_per_week'] ?? 'Max 20 hrs/week') ?>
                                </span>
                                <span class="badge-status--accepted ms-auto">
                                    <?= htmlspecialchars($job['pay_rate']) ?>
                                </span>
                            </div>

                            <!-- Role Overview -->
                            <div class="mb-4">
                                <h3 class="card-paper-title mb-3">
                                    <i class="bi bi-file-text text-accent me-2"></i> Role Description
                                </h3>
                                <p class="text-ink lh-lg mb-0" style="font-size: 15px;">
                                    <?= nl2br(htmlspecialchars($job['description'])) ?>
                                </p>
                            </div>

                            <!-- Key Duties & Responsibilities -->
                            <?php if (!empty($job['responsibilities'])): ?>
                                <div class="mb-4 pt-3 border-top border-line">
                                    <h3 class="card-paper-title mb-3">
                                        <i class="bi bi-list-check text-accent me-2"></i> Key Duties & Responsibilities
                                    </h3>
                                    <ul class="d-flex flex-column gap-2 text-ink ps-3 mb-0" style="font-size: 14.5px;">
                                        <?php foreach ($job['responsibilities'] as $resp): ?>
                                            <li><?= htmlspecialchars($resp) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Qualifications & Eligibility -->
                            <?php if (!empty($job['qualifications'])): ?>
                                <div class="mb-4 pt-3 border-top border-line">
                                    <h3 class="card-paper-title mb-3">
                                        <i class="bi bi-patch-check text-accent me-2"></i> Minimum Qualifications
                                    </h3>
                                    <ul class="d-flex flex-column gap-2 text-ink ps-3 mb-0" style="font-size: 14.5px;">
                                        <?php foreach ($job['qualifications'] as $qual): ?>
                                            <li><?= htmlspecialchars($qual) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Safety & Academic Safeguard Banner -->
                            <div class="card-paper bg-cream p-3 mt-4 border border-line">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle icon-circle-sm icon-circle-success">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <div class="small text-muted-custom">
                                        <strong class="text-ink">University Vetted & Protected:</strong> This assistantship complies with campus safety regulations, non-hazardous work standards, and strict 20 hrs/week academic term limits.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right 4-col: Action Sidebar -->
                    <div class="col-lg-4">
                        <div class="card-paper p-4 position-sticky" style="top: 90px;">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                Requisition Summary
                            </h3>

                            <div class="d-flex flex-column gap-3 mb-4 small">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted-custom">Compensation:</span>
                                    <span class="fw-bold text-ink fs-6"><?= htmlspecialchars($job['pay_rate']) ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted-custom">Workplace Setup:</span>
                                    <span class="fw-semibold text-ink"><?= htmlspecialchars($wsetup) ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted-custom">Weekly Duty Limit:</span>
                                    <span class="badge-status--accepted" style="font-size: 10px;">&le; 20 hrs/week</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted-custom">Office Location:</span>
                                    <span class="fw-semibold text-ink text-end"><?= htmlspecialchars($job['location']) ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted-custom">Application Deadline:</span>
                                    <span class="fw-bold text-danger"><?= htmlspecialchars($job['deadline']) ?></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted-custom">Hiring Supervisor:</span>
                                    <span class="fw-semibold text-ink"><?= htmlspecialchars($job['employer_name'] ?? 'Office Head') ?></span>
                                </div>

                                <!-- Slots Progress -->
                                <div class="pt-2 border-top border-line">
                                    <div class="d-flex justify-content-between small text-muted-custom mb-1">
                                        <span><?= $slots_filled ?> of <?= $slots_total ?> slots filled</span>
                                        <span class="fw-bold text-ink"><?= $pct ?>%</span>
                                    </div>
                                    <div class="progress-paper">
                                        <div class="progress-paper-bar" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action CTA -->
                            <?php if ($already_applied): ?>
                                <div class="p-3 bg-cream rounded-3 border border-line text-center mb-3">
                                    <div class="small fw-bold text-ink mb-1">
                                        <i class="bi bi-check-circle-fill text-accent me-1"></i> Application Active
                                    </div>
                                    <div class="small text-muted-custom mb-2">
                                        Status: <?= render_status_badge($app_status ?? 'pending') ?>
                                    </div>
                                    <a href="my-applications.php" class="btn-pill-outline btn-pill-sm w-100">
                                        Track Application
                                    </a>
                                </div>
                            <?php elseif ($user && $user['role'] === 'employer'): ?>
                                <div class="p-3 bg-cream rounded-3 border border-line text-center mb-3">
                                    <div class="small fw-bold text-ink mb-1">
                                        <i class="bi bi-building-check text-accent me-1"></i> Employer Preview Mode
                                    </div>
                                    <p class="small text-muted-custom mb-2" style="font-size: 12px;">
                                        Applications are reserved for students.
                                    </p>
                                    <?php if (($job['employer_id'] ?? 0) == $user['id']): ?>
                                        <a href="../employer/edit-job.php?id=<?= $job['id'] ?>" class="btn-pill btn-pill-sm w-100">
                                            <i class="bi bi-pencil-square"></i> Edit Requisition
                                        </a>
                                    <?php else: ?>
                                        <a href="../employer/dashboard.php" class="btn-pill-outline btn-pill-sm w-100">
                                            <i class="bi bi-speedometer2"></i> Employer Dashboard
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($user && $user['role'] === 'admin'): ?>
                                <div class="p-3 bg-cream rounded-3 border border-line text-center mb-3">
                                    <div class="small fw-bold text-ink mb-1">
                                        <i class="bi bi-shield-lock text-accent me-1"></i> Admin Preview Mode
                                    </div>
                                    <a href="../admin/reports.php" class="btn-pill-outline btn-pill-sm w-100">
                                        <i class="bi bi-bar-chart"></i> View Reports
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="apply.php?id=<?= $job['id'] ?>&job_id=<?= $job['id'] ?>" class="btn-pill w-100 mb-2">
                                    <i class="bi bi-send-fill"></i> APPLY FOR THIS POSITION
                                </a>
                            <?php endif; ?>

                            <a href="jobs.php" class="btn-pill-outline btn-pill-sm w-100 text-center">
                                <i class="bi bi-arrow-left"></i> Back to Vacancies
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
