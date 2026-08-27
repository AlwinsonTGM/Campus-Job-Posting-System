<?php
/**
 * Campus Job Posting System - Job Details Page
 */
require_once __DIR__ . '/../includes/data-helper.php';

$job_id = $_GET['id'] ?? null;
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'Job opening not found or has been closed.');
    header('Location: jobs.php');
    exit;
}

$is_partner = ($job['employer_type'] ?? '') === 'approved_partner';
$org_name = $job['organization_name'] ?? ($job['department'] ?? 'Campus Department');
$jtype = $job['job_type'] ?? 'Student Assistant';
$wsetup = $job['work_setup'] ?? 'On-Campus';

$page_title = $job['title'] . ' | Opportunity Details';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="jobs.php">Job Vacancies</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($job['title']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Left 8-col: Main Description & Requirements -->
            <div class="col-lg-8">
                <div class="card border-line shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                        <div>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="pill-badge <?= $is_partner ? 'pill-badge-ink' : '' ?>">
                                    <?php if ($is_partner): ?>
                                        <i class="bi bi-patch-check-fill text-accent me-1"></i>Approved Industry Partner
                                    <?php else: ?>
                                        <i class="bi bi-bank text-accent me-1"></i>University Academic Office
                                    <?php endif; ?>
                                </span>
                                <span class="badge bg-cream text-ink border border-line py-1 px-2" style="font-size: 12px;">
                                    <?= htmlspecialchars($jtype) ?>
                                </span>
                                <span class="badge bg-light text-muted-custom border border-line py-1 px-2" style="font-size: 12px;">
                                    <?= htmlspecialchars($wsetup) ?>
                                </span>
                            </div>

                            <h2 class="fw-bold text-ink mb-1"><?= htmlspecialchars($job['title']) ?></h2>
                            <p class="text-muted-custom fs-6 mb-0">
                                <i class="bi bi-building me-1 text-accent"></i> <strong><?= htmlspecialchars($org_name) ?></strong>
                            </p>
                        </div>
                        <span class="pill-badge pill-badge-ink fs-6 fw-bold px-3 py-2">
                            <?= htmlspecialchars($job['pay_rate']) ?>
                        </span>
                    </div>

                    <hr class="my-4 border-line">

                    <!-- Overview -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-ink mb-3"><i class="bi bi-card-text text-accent me-2"></i> Role Overview</h5>
                        <p class="text-muted-custom lh-lg">
                            <?= nl2br(htmlspecialchars($job['description'])) ?>
                        </p>
                    </div>

                    <!-- Key Responsibilities -->
                    <?php if (!empty($job['responsibilities'])): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold text-ink mb-3"><i class="bi bi-list-check text-accent me-2"></i> Key Duties & Responsibilities</h5>
                            <ul class="text-muted-custom d-flex flex-column gap-2">
                                <?php foreach ($job['responsibilities'] as $resp): ?>
                                    <li><i class="bi bi-check2-circle text-accent me-2"></i><?= htmlspecialchars($resp) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Qualifications -->
                    <?php if (!empty($job['qualifications'])): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold text-ink mb-3"><i class="bi bi-patch-check text-accent me-2"></i> Qualifications & Eligibility</h5>
                            <ul class="text-muted-custom d-flex flex-column gap-2">
                                <?php foreach ($job['qualifications'] as $qual): ?>
                                    <li><i class="bi bi-check-circle-fill text-accent me-2"></i><?= htmlspecialchars($qual) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-light border-line bg-cream d-flex align-items-center gap-3 p-3 rounded-4 mt-4 text-ink">
                        <div class="fs-3 text-accent"><i class="bi bi-shield-check"></i></div>
                        <div class="small text-muted-custom">
                            <strong class="text-ink">University Vetted & Safe Employment:</strong> This opportunity has been vetted by the University Career Services to guarantee compliance with student safety, non-hazardous workplaces, fair compensation, and a strict 20-hour weekly semester limit.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 4-col: Summary & Apply Card -->
            <div class="col-lg-4">
                <div class="card border-line shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 85px;">
                    <h5 class="fw-bold text-ink mb-3 pb-2 border-bottom border-line">Opportunity Summary</h5>

                    <div class="d-flex flex-column gap-3 mb-4 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted-custom"><i class="bi bi-tag me-1 text-accent"></i> Role Type:</span>
                            <span class="fw-bold text-ink"><?= htmlspecialchars($jtype) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted-custom"><i class="bi bi-laptop me-1 text-accent"></i> Workplace Setup:</span>
                            <span class="fw-bold text-ink"><?= htmlspecialchars($wsetup) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted-custom"><i class="bi bi-clock me-1 text-accent"></i> Duty Hours:</span>
                            <span class="fw-bold text-ink"><?= htmlspecialchars($job['hours_per_week']) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted-custom"><i class="bi bi-geo-alt me-1 text-accent"></i> Location:</span>
                            <span class="fw-bold text-ink text-end"><?= htmlspecialchars($job['location']) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted-custom"><i class="bi bi-people me-1 text-accent"></i> Open Slots:</span>
                            <span class="pill-badge" style="font-size: 11px;"><?= $job['vacancies'] ?> open</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted-custom"><i class="bi bi-calendar-x me-1 text-accent"></i> Deadline:</span>
                            <span class="fw-bold text-danger"><?= date('F d, Y', strtotime($job['deadline'])) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted-custom"><i class="bi bi-person me-1 text-accent"></i> Supervisor:</span>
                            <span class="fw-semibold text-ink"><?= htmlspecialchars($job['employer_name']) ?></span>
                        </div>
                    </div>

                    <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn-accent-pill w-100 py-3 mb-2">
                        <i class="bi bi-send-fill"></i> APPLY FOR THIS POSITION
                    </a>

                    <a href="jobs.php" class="btn-outline-pill w-100 py-2 btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Vacancies
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
