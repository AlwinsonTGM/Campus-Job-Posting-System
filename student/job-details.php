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

$page_title = $job['title'] . ' | Job Details';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-light flex-grow-1">
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
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 mb-2">
                                <?= htmlspecialchars($job['category']) ?>
                            </span>
                            <h2 class="fw-bold text-dark mb-1"><?= htmlspecialchars($job['title']) ?></h2>
                            <p class="text-muted fs-6 mb-0">
                                <i class="bi bi-building me-1 text-primary"></i> <strong><?= htmlspecialchars($job['department']) ?></strong>
                            </p>
                        </div>
                        <span class="badge bg-success-subtle text-success fs-6 fw-bold px-3 py-2">
                            <?= htmlspecialchars($job['pay_rate']) ?>
                        </span>
                    </div>

                    <hr class="my-4">

                    <!-- Overview -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-card-text text-primary me-2"></i> Role Overview</h5>
                        <p class="text-secondary lh-lg">
                            <?= nl2br(htmlspecialchars($job['description'])) ?>
                        </p>
                    </div>

                    <!-- Key Responsibilities -->
                    <?php if (!empty($job['responsibilities'])): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-primary me-2"></i> Key Duties & Responsibilities</h5>
                            <ul class="text-secondary d-flex flex-column gap-2">
                                <?php foreach ($job['responsibilities'] as $resp): ?>
                                    <li><?= htmlspecialchars($resp) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Qualifications -->
                    <?php if (!empty($job['qualifications'])): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-patch-check text-primary me-2"></i> Qualifications & Eligibility</h5>
                            <ul class="text-secondary d-flex flex-column gap-2">
                                <?php foreach ($job['qualifications'] as $qual): ?>
                                    <li><?= htmlspecialchars($qual) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-light border border-info-subtle d-flex align-items-center gap-3 p-3 rounded-3 mt-4">
                        <div class="fs-3 text-info"><i class="bi bi-info-circle-fill"></i></div>
                        <div class="small text-secondary">
                            <strong>Academic-First Policy:</strong> This job has been pre-screened to ensure duty hours will not exceed 20 hours per week during regular class semesters.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 4-col: Summary & Apply Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 85px;">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">Opportunity Summary</h5>

                    <div class="d-flex flex-column gap-3 mb-4 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-clock me-1"></i> Duty Hours:</span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($job['hours_per_week']) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Campus Location:</span>
                            <span class="fw-bold text-dark text-end"><?= htmlspecialchars($job['location']) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-people me-1"></i> Vacancy Slots:</span>
                            <span class="badge bg-primary text-white"><?= $job['vacancies'] ?> open</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-calendar-x me-1"></i> Deadline:</span>
                            <span class="fw-bold text-danger"><?= date('F d, Y', strtotime($job['deadline'])) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-person me-1"></i> Supervisor:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($job['employer_name']) ?></span>
                        </div>
                    </div>

                    <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-gold w-100 py-2 fw-bold shadow-sm mb-2">
                        <i class="bi bi-send-fill me-1"></i> Apply for this Job
                    </a>

                    <a href="jobs.php" class="btn btn-outline-secondary w-100 py-2 btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to All Vacancies
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
