<?php
/**
 * Campus Job Posting System - Job Listings & Search
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$page_title = 'Browse Campus & Partner Vacancies';

$kw = $_GET['q'] ?? $_GET['kw'] ?? null;
$cat = $_GET['category'] ?? $_GET['cat'] ?? null;
$dept = $_GET['dept'] ?? null;
$pay_type = $_GET['pay_type'] ?? null;
$job_type = $_GET['job_type'] ?? null;
$employer_type = $_GET['employer_type'] ?? null;
$work_setup = $_GET['work_setup'] ?? null;

$jobs = get_jobs($cat, $kw, $dept, $pay_type, $job_type, $employer_type, $work_setup);
$categories = get_categories();
$all_job_types = get_job_types();
$all_employer_types = get_employer_types();
$all_work_setups = get_work_setups();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Opportunities</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-ink mb-0">Browse Campus & Partner Opportunities</h2>
                <p class="text-muted-custom small mb-0">Vetted student assistantships, part-time roles, and academic internships</p>
            </div>
            <div class="mt-2 mt-md-0 text-muted-custom small">
                Showing <strong><?= count($jobs) ?></strong> active verified requisitions
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card border-line shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="jobs.php" method="GET" class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search text-muted-custom"></i></span>
                        <input type="text" name="kw" id="live-job-search" class="form-control" placeholder="Search title, skills, org..." value="<?= htmlspecialchars($kw ?? '') ?>">
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-mortarboard-fill text-muted-custom"></i></span>
                        <select name="job_type" class="form-select">
                            <option value="">All Opportunity Types</option>
                            <?php foreach ($all_job_types as $k => $label): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= (is_string($job_type) && $job_type === $k) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building text-muted-custom"></i></span>
                        <select name="employer_type" class="form-select">
                            <option value="">All Employers</option>
                            <option value="university_office" <?= ($employer_type === 'university_office') ? 'selected' : '' ?>>University Offices</option>
                            <option value="approved_partner" <?= ($employer_type === 'approved_partner') ? 'selected' : '' ?>>Approved Partners</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt text-muted-custom"></i></span>
                        <select name="work_setup" class="form-select">
                            <option value="">Any Setup</option>
                            <?php foreach ($all_work_setups as $k => $label): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= ($work_setup === $k) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-grid-fill text-muted-custom"></i></span>
                        <select name="cat" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= htmlspecialchars($c['name']) ?>" <?= ($cat === $c['name']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-1 col-md-6 d-flex gap-1">
                    <button type="submit" class="btn-accent-pill w-100 py-2" title="Apply Filters">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                    <?php if ($kw || $cat || $dept || $job_type || $employer_type || $work_setup): ?>
                        <a href="jobs.php" class="btn-circle-icon flex-shrink-0" style="width: 38px; height: 38px;" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Job Cards Grid -->
        <?php if (empty($jobs)): ?>
            <div class="card border-line shadow-sm rounded-4 p-5 text-center bg-white my-4">
                <div class="stat-icon bg-surface text-muted-custom mx-auto mb-3 fs-1">
                    <i class="bi bi-search"></i>
                </div>
                <h4 class="fw-bold text-ink">No opportunities match your search filters</h4>
                <p class="text-muted-custom small mb-4">Try clearing one or more filters or expanding your keyword search.</p>
                <div>
                    <a href="jobs.php" class="btn-accent-pill">Reset All Filters</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($jobs as $job): 
                    $is_partner = ($job['employer_type'] ?? '') === 'approved_partner';
                    $org_name = $job['organization_name'] ?? ($job['department'] ?? 'Campus Organization');
                    $jtype = $job['job_type'] ?? 'Student Assistant';
                    $wsetup = $job['work_setup'] ?? 'On-Campus';
                ?>
                    <div class="col-lg-4 col-md-6 job-item-card">
                        <div class="job-card h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="pill-badge <?= $is_partner ? 'pill-badge-ink' : '' ?>">
                                    <?php if ($is_partner): ?>
                                        <i class="bi bi-patch-check-fill text-accent me-1"></i>Approved Partner
                                    <?php else: ?>
                                        <i class="bi bi-bank text-accent me-1"></i>University Office
                                    <?php endif; ?>
                                </span>
                                <span class="chip-tag">
                                    <i class="bi bi-clock me-1"></i><?= htmlspecialchars($job['hours_per_week']) ?>
                                </span>
                            </div>

                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <span class="badge bg-cream text-ink border border-line" style="font-size: 11px;">
                                    <?= htmlspecialchars($jtype) ?>
                                </span>
                                <span class="badge bg-light text-muted-custom border border-line" style="font-size: 11px;">
                                    <?= htmlspecialchars($wsetup) ?>
                                </span>
                            </div>

                            <h5 class="fw-bold text-ink mb-1">
                                <a href="job-details.php?id=<?= $job['id'] ?>" class="text-decoration-none text-ink">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted-custom small mb-1 fw-semibold">
                                <i class="bi bi-building me-1 text-accent"></i><?= htmlspecialchars($org_name) ?>
                            </p>

                            <p class="text-muted-custom small mb-2">
                                <i class="bi bi-geo-alt me-1 text-accent"></i><?= htmlspecialchars($job['location']) ?>
                            </p>

                            <p class="text-muted-custom small mb-3 flex-grow-1">
                                <?= htmlspecialchars(substr($job['description'], 0, 110)) ?>...
                            </p>

                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php if (!empty($job['tags'])): ?>
                                    <?php foreach ($job['tags'] as $tag): ?>
                                        <span class="chip-tag"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="border-top border-line pt-3 mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted-custom small" style="font-size: 11px;">COMPENSATION</div>
                                    <div class="fw-bold text-ink"><?= htmlspecialchars($job['pay_rate']) ?></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="job-details.php?id=<?= $job['id'] ?>" class="btn-outline-pill py-1 px-3" style="font-size: 12px;">
                                        Details
                                    </a>
                                    <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn-accent-pill py-1 px-3" style="font-size: 12px;">
                                        Apply
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
