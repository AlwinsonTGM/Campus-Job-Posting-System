<?php
/**
 * Campus Job Posting System - Job Listings & Search
 */
require_once __DIR__ . '/../includes/data-helper.php';

$page_title = 'Browse Campus Vacancies';

$kw = $_GET['kw'] ?? null;
$cat = $_GET['cat'] ?? null;
$dept = $_GET['dept'] ?? null;

$jobs = get_jobs($cat, $kw, $dept);
$categories = get_categories();
$departments = get_departments();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-light flex-grow-1">
    <div class="container">
        
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Job Vacancies</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-dark mb-0">Browse On-Campus Opportunities</h2>
            </div>
            <div class="mt-2 mt-md-0 text-muted small">
                Showing <strong><?= count($jobs) ?></strong> active vacancy positions
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="jobs.php" method="GET" class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="kw" id="live-job-search" class="form-control" placeholder="Search title, skills, keywords..." value="<?= htmlspecialchars($kw ?? '') ?>">
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-grid-fill text-muted"></i></span>
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

                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-building text-muted"></i></span>
                        <select name="dept" class="form-select">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= htmlspecialchars($d) ?>" <?= ($dept === $d) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-academic w-100 fw-semibold">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    <?php if ($kw || $cat || $dept): ?>
                        <a href="jobs.php" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Popular Quick Tags Ribbon -->
            <div class="d-flex align-items-center flex-wrap gap-1.5 mt-3 pt-2.5 border-top small">
                <span class="text-muted fw-semibold me-1"><i class="bi bi-lightning-charge-fill text-warning"></i> Quick Filters:</span>
                <a href="jobs.php?kw=Tech+Support" class="quick-tag-pill <?= ($kw === 'Tech Support') ? 'active' : '' ?>">#Tech Support</a>
                <a href="jobs.php?kw=Library" class="quick-tag-pill <?= ($kw === 'Library') ? 'active' : '' ?>">#Library</a>
                <a href="jobs.php?kw=Urgent" class="quick-tag-pill <?= ($kw === 'Urgent') ? 'active' : '' ?>">#Urgent</a>
                <a href="jobs.php?kw=Flexible+Shift" class="quick-tag-pill <?= ($kw === 'Flexible Shift') ? 'active' : '' ?>">#Flexible Shift</a>
                <a href="jobs.php?kw=Office+Work" class="quick-tag-pill <?= ($kw === 'Office Work') ? 'active' : '' ?>">#Office Work</a>
                <a href="jobs.php?kw=High+Pay" class="quick-tag-pill <?= ($kw === 'High Pay') ? 'active' : '' ?>">#High Pay</a>
                <?php if ($kw || $cat || $dept): ?>
                    <a href="jobs.php" class="quick-tag-pill text-danger border-danger-subtle ms-auto"><i class="bi bi-trash3 me-1"></i>Reset All</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Job Cards Grid -->
        <?php if (empty($jobs)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
                <div class="stat-icon bg-light text-muted mx-auto mb-3 fs-1">
                    <i class="bi bi-search"></i>
                </div>
                <h4 class="fw-bold text-dark">No vacancies match your search criteria</h4>
                <p class="text-muted small mb-3">Try adjusting your search keywords, department name, or selecting all categories.</p>
                <div>
                    <a href="jobs.php" class="btn btn-academic btn-sm px-4">Reset All Filters</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($jobs as $job): ?>
                    <div class="col-lg-4 col-md-6 job-item-card">
                        <div class="job-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="badge-kld-tag">
                                    <i class="bi bi-tag-fill me-1 text-kld-gold"></i><?= htmlspecialchars($job['category']) ?>
                                </span>
                                <span class="job-tag">
                                    <i class="bi bi-clock me-1"></i><?= htmlspecialchars($job['hours_per_week']) ?>
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mb-1">
                                <a href="job-details.php?id=<?= $job['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted small mb-2">
                                <i class="bi bi-building me-1 text-kld-green"></i><?= htmlspecialchars($job['department']) ?>
                            </p>

                            <p class="text-secondary small mb-3">
                                <i class="bi bi-geo-alt me-1 text-kld-green"></i><?= htmlspecialchars($job['location']) ?>
                            </p>

                            <p class="text-secondary small mb-3">
                                <?= htmlspecialchars(substr($job['description'], 0, 100)) ?>...
                            </p>

                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php if (!empty($job['tags'])): ?>
                                    <?php foreach ($job['tags'] as $tag): ?>
                                        <span class="badge bg-light text-secondary border small"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="border-top pt-3 mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Stipend Rate</div>
                                    <div class="fw-bold text-kld-green"><?= htmlspecialchars($job['pay_rate']) ?></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="job-details.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        Details
                                    </a>
                                    <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-academic">
                                        Apply Now
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
