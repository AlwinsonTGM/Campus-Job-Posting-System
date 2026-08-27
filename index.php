<?php
/**
 * Campus Job Posting System - Index / Landing Page
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'Official Student Employment Portal - KLD';
$all_jobs = get_jobs();
$categories = get_categories();
$featured_jobs = array_slice($all_jobs, 0, 6);

// Statistics
$total_jobs = count($all_jobs);
$total_apps = count(get_applications());
$total_cats = count($categories);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section text-center text-lg-start">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase mb-3 d-inline-block">
                        <i class="bi bi-stars"></i> Official KLD Student Employment Portal
                    </span>
                    <h1 class="hero-title fw-bold text-white mb-3">
                        Find Purposeful Work <br class="d-none d-md-block">
                        <span class="text-warning">at Kolehiyo ng Lungsod ng Dasmariñas</span>
                    </h1>
                    <p class="lead hero-subtitle text-light opacity-90 mb-4 pe-lg-4">
                        Discover flexible student assistantships, technical lab support, administrative roles, and peer tutoring opportunities tailored around your KLD class schedules.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2 gap-md-3 mb-4">
                        <a href="student/jobs.php" class="btn btn-gold btn-lg px-3 px-sm-4 shadow">
                            <i class="bi bi-search me-2"></i> Browse All Vacancies
                        </a>
                        <a href="register.php?role=employer" class="btn btn-outline-light btn-lg px-3 px-sm-4">
                            <i class="bi bi-building me-2"></i> Post Department Vacancy
                        </a>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2 gap-md-4 text-light small">
                        <div><i class="bi bi-check-circle-fill text-warning me-1"></i> Flexible Shift Adjustments</div>
                        <div><i class="bi bi-check-circle-fill text-warning me-1"></i> Max 20 hrs/week Safe Limit</div>
                        <div><i class="bi bi-check-circle-fill text-warning me-1"></i> Official Certificate of Service</div>
                    </div>
                </div>

                <!-- Hero Search Widget -->
                <div class="col-lg-5">
                    <div class="hero-search-card">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-funnel-fill text-kld-green"></i> Quick Job Search</h5>
                        <form action="student/jobs.php" method="GET">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Keyword or Job Title</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="kw" class="form-control" placeholder="e.g. Lab Assistant, Clerk, Tutor">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Category / Field</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-grid-fill text-muted"></i></span>
                                    <select name="cat" class="form-select">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Campus Department / Office</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-building text-muted"></i></span>
                                    <input type="text" name="dept" class="form-control" placeholder="e.g. MIS, Library, Registrar">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-academic w-100 py-2">
                                <i class="bi bi-arrow-right-circle me-1"></i> Search Campus Jobs
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Bar -->
    <section class="py-4 bg-white border-bottom shadow-sm">
        <div class="container">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="p-3">
                        <div class="display-6 fw-bold text-kld-green mb-1"><?= $total_jobs ?></div>
                        <div class="text-muted small fw-semibold text-uppercase">Active Vacancies</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3">
                        <div class="display-6 fw-bold text-kld-green mb-1"><?= $total_cats ?></div>
                        <div class="text-muted small fw-semibold text-uppercase">Job Categories</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3">
                        <div class="display-6 fw-bold text-kld-green mb-1">12+</div>
                        <div class="text-muted small fw-semibold text-uppercase">Partner Offices</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3">
                        <div class="display-6 fw-bold text-kld-green mb-1"><?= $total_apps ?></div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Applications</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Jobs Section -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
                <div>
                    <span class="text-kld-green fw-bold small text-uppercase letter-spacing-1">Immediate Openings</span>
                    <h2 class="fw-bold text-dark mb-0">Featured Campus Opportunities</h2>
                </div>
                <a href="student/jobs.php" class="btn btn-outline-academic mt-3 mt-md-0">
                    View All Jobs (<?= $total_jobs ?>) <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php foreach ($featured_jobs as $job): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="job-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="badge-kld-tag">
                                    <i class="bi bi-tag-fill me-1 text-kld-gold"></i><?= htmlspecialchars($job['category']) ?>
                                </span>
                                <span class="job-tag">
                                    <i class="bi bi-clock-history me-1"></i><?= htmlspecialchars($job['hours_per_week']) ?>
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mb-1">
                                <a href="student/job-details.php?id=<?= $job['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted small mb-3">
                                <i class="bi bi-building me-1 text-kld-green"></i><?= htmlspecialchars($job['department']) ?>
                            </p>

                            <p class="text-secondary small mb-3 text-truncate-2">
                                <?= htmlspecialchars(substr($job['description'], 0, 110)) ?>...
                            </p>

                            <div class="border-top pt-3 mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Hourly Stipend</div>
                                    <div class="fw-bold text-kld-green"><?= htmlspecialchars($job['pay_rate']) ?></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="student/job-details.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        Details
                                    </a>
                                    <a href="student/apply.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-academic">
                                        Apply
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Categories Bento Grid Section -->
    <?php
    $cats_by_id = [];
    foreach ($categories as $c) {
        $cats_by_id[$c['id']] = $c;
    }
    $it_cat = $cats_by_id[1] ?? ($categories[0] ?? null);
    $lib_cat = $cats_by_id[2] ?? ($categories[1] ?? null);
    $admin_cat = $cats_by_id[3] ?? ($categories[2] ?? null);
    $lab_cat = $cats_by_id[4] ?? ($categories[3] ?? null);
    $tutor_cat = $cats_by_id[5] ?? ($categories[4] ?? null);
    $media_cat = $cats_by_id[6] ?? ($categories[5] ?? null);
    ?>
    <section class="py-5 bento-section border-top border-bottom">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bento-pill-header px-3 py-2 fw-bold text-uppercase mb-2 d-inline-block">
                    <i class="bi bi-compass-fill me-1"></i> Interactive Department Explorer
                </span>
                <h2 class="fw-bold text-dark mb-2">Explore Opportunities by Campus Field</h2>
                <p class="text-muted col-lg-7 mx-auto">
                    Browse specialized assistantships tailored to your institute curriculum. Select any sub-role to filter instant campus openings.
                </p>
            </div>

            <div class="bento-grid">
                <!-- Bento Row 1: Hero Card (IT) + Highlight Card (Library) -->
                <div class="row g-4 mb-4 align-items-stretch">
                    <?php if ($it_cat): ?>
                        <div class="col-lg-7">
                            <div class="bento-card bento-card-hero bento-card-green-theme">
                                <div class="row align-items-stretch h-100 g-4">
                                    <div class="col-md-5 order-1 order-md-2 d-flex flex-column">
                                        <div class="bento-capsule-img-wrapper" style="min-height: 240px; height: 100%;">
                                            <img src="<?= htmlspecialchars($it_cat['image'] ?? 'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=900&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($it_cat['name']) ?>">
                                            <div class="bento-capsule-overlay"></div>
                                            <div class="bento-floating-icon text-kld-green">
                                                <i class="bi bi-laptop"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-7 order-2 order-md-1 d-flex flex-column justify-content-between h-100">
                                        <div>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                                <span class="bento-pill-badge bento-badge-kld-green">
                                                    <i class="bi <?= htmlspecialchars($it_cat['badge_icon'] ?? 'bi-fire') ?> text-kld-green"></i> <?= htmlspecialchars($it_cat['badge_tag'] ?? 'Most In-Demand') ?>
                                                </span>
                                                <span class="bento-meta-pill text-kld-green fw-bold">
                                                    <i class="bi bi-cash-stack text-kld-green"></i> <?= htmlspecialchars($it_cat['hourly_range'] ?? '₱85.00 / hr') ?>
                                                </span>
                                            </div>
                                            <h3 class="fw-bold text-dark mb-2">
                                                <a href="student/jobs.php?cat=<?= urlencode($it_cat['name']) ?>" class="text-decoration-none text-dark">
                                                    <?= htmlspecialchars($it_cat['name']) ?>
                                                </a>
                                            </h3>
                                            <p class="text-muted small mb-3">
                                                <?= htmlspecialchars($it_cat['description']) ?>
                                            </p>
                                            
                                            <div class="small fw-semibold text-secondary mb-1">
                                                <i class="bi bi-stars text-kld-green me-1"></i> Popular Student Roles:
                                            </div>
                                            <div class="bento-role-chips">
                                                <?php foreach (($it_cat['popular_roles'] ?? ['Lab Assistant', 'Tech Support']) as $role): ?>
                                                    <a href="student/jobs.php?kw=<?= urlencode($role) ?>&cat=<?= urlencode($it_cat['name']) ?>" class="bento-chip" title="Filter by <?= htmlspecialchars($role) ?>">
                                                        <i class="bi bi-laptop me-1 text-kld-green small"></i><?= htmlspecialchars($role) ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                                                <i class="bi bi-briefcase-fill text-kld-green me-1"></i> <?= $it_cat['job_count'] ?? 4 ?> Vacancies
                                            </span>
                                            <a href="student/jobs.php?cat=<?= urlencode($it_cat['name']) ?>" class="bento-arrow-btn" title="Explore <?= htmlspecialchars($it_cat['name']) ?> Jobs">
                                                <i class="bi bi-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($lib_cat): ?>
                        <div class="col-lg-5">
                            <div class="bento-card bento-card-green-theme">
                                <div class="bento-capsule-img-wrapper mb-3" style="height: 165px; min-height: 165px;">
                                    <img src="<?= htmlspecialchars($lib_cat['image'] ?? 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($lib_cat['name']) ?>">
                                    <div class="bento-capsule-overlay"></div>
                                    <div class="bento-floating-icon text-kld-green">
                                        <i class="bi bi-book"></i>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <span class="bento-pill-badge bento-badge-kld-green">
                                        <i class="bi <?= htmlspecialchars($lib_cat['badge_icon'] ?? 'bi-bookmark-star') ?> text-kld-green"></i> <?= htmlspecialchars($lib_cat['badge_tag'] ?? 'Quiet Study Hub') ?>
                                    </span>
                                    <span class="bento-meta-pill text-kld-green fw-bold"><?= htmlspecialchars($lib_cat['hourly_range'] ?? '₱80.00 / hr') ?></span>
                                </div>

                                <h4 class="fw-bold text-dark mb-1">
                                    <a href="student/jobs.php?cat=<?= urlencode($lib_cat['name']) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($lib_cat['name']) ?>
                                    </a>
                                </h4>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars($lib_cat['description']) ?>
                                </p>

                                <div class="bento-role-chips">
                                    <?php foreach (($lib_cat['popular_roles'] ?? ['Cataloger', 'Circulation Aid']) as $role): ?>
                                        <a href="student/jobs.php?kw=<?= urlencode($role) ?>&cat=<?= urlencode($lib_cat['name']) ?>" class="bento-chip" title="Filter by <?= htmlspecialchars($role) ?>">
                                            <i class="bi bi-book me-1 text-kld-green small"></i><?= htmlspecialchars($role) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                    <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                                        <i class="bi bi-briefcase-fill text-kld-green me-1"></i> <?= $lib_cat['job_count'] ?? 3 ?> Vacancies
                                    </span>
                                    <a href="student/jobs.php?cat=<?= urlencode($lib_cat['name']) ?>" class="bento-arrow-btn" title="Explore <?= htmlspecialchars($lib_cat['name']) ?> Jobs">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bento Row 2: 3 Visual Category Cards with Capsule Photography & Unified KLD Palette -->
                <div class="row g-4 mb-4 align-items-stretch">
                    <!-- Administrative & Clerical -->
                    <?php if ($admin_cat): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="bento-card bento-card-green-theme">
                                <div class="bento-capsule-img-wrapper mb-3" style="height: 150px;">
                                    <img src="<?= htmlspecialchars($admin_cat['image'] ?? 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($admin_cat['name']) ?>">
                                    <div class="bento-capsule-overlay"></div>
                                    <div class="bento-floating-icon text-kld-green">
                                        <i class="bi <?= htmlspecialchars($admin_cat['icon']) ?>"></i>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="bento-pill-badge bento-badge-kld-green">
                                        <i class="bi <?= htmlspecialchars($admin_cat['badge_icon'] ?? 'bi-building') ?> text-kld-green"></i> <?= htmlspecialchars($admin_cat['badge_tag'] ?? 'Campus Admin') ?>
                                    </span>
                                    <span class="bento-meta-pill fw-bold text-kld-green"><?= htmlspecialchars($admin_cat['hourly_range'] ?? '₱80/hr') ?></span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">
                                    <a href="student/jobs.php?cat=<?= urlencode($admin_cat['name']) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($admin_cat['name']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars($admin_cat['description']) ?>
                                </p>

                                <div class="bento-role-chips">
                                    <?php foreach (($admin_cat['popular_roles'] ?? ['Records Clerk', 'Data Encoder']) as $role): ?>
                                        <a href="student/jobs.php?kw=<?= urlencode($role) ?>&cat=<?= urlencode($admin_cat['name']) ?>" class="bento-chip" title="Filter by <?= htmlspecialchars($role) ?>">
                                            <i class="bi bi-file-earmark-text me-1 text-kld-green small"></i><?= htmlspecialchars($role) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                    <span class="badge bg-light text-dark border px-2 py-2 fw-semibold small">
                                        <i class="bi bi-briefcase-fill text-kld-green me-1"></i> <?= $admin_cat['job_count'] ?? 5 ?> Vacancies
                                    </span>
                                    <a href="student/jobs.php?cat=<?= urlencode($admin_cat['name']) ?>" class="bento-arrow-btn" title="Explore <?= htmlspecialchars($admin_cat['name']) ?>">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Laboratory Assistant -->
                    <?php if ($lab_cat): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="bento-card bento-card-green-theme">
                                <div class="bento-capsule-img-wrapper mb-3" style="height: 150px;">
                                    <img src="<?= htmlspecialchars($lab_cat['image'] ?? 'https://images.unsplash.com/photo-1582719471384-894fbb16e074?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($lab_cat['name']) ?>">
                                    <div class="bento-capsule-overlay"></div>
                                    <div class="bento-floating-icon text-kld-green">
                                        <i class="bi <?= htmlspecialchars($lab_cat['icon']) ?>"></i>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="bento-pill-badge bento-badge-kld-green">
                                        <i class="bi <?= htmlspecialchars($lab_cat['badge_icon'] ?? 'bi-moisture') ?> text-kld-green"></i> <?= htmlspecialchars($lab_cat['badge_tag'] ?? 'Science & Health') ?>
                                    </span>
                                    <span class="bento-meta-pill fw-bold text-kld-green"><?= htmlspecialchars($lab_cat['hourly_range'] ?? '₱85/hr') ?></span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">
                                    <a href="student/jobs.php?cat=<?= urlencode($lab_cat['name']) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($lab_cat['name']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars($lab_cat['description']) ?>
                                </p>

                                <div class="bento-role-chips">
                                    <?php foreach (($lab_cat['popular_roles'] ?? ['Lab Aid', 'Apparatus Prep']) as $role): ?>
                                        <a href="student/jobs.php?kw=<?= urlencode($role) ?>&cat=<?= urlencode($lab_cat['name']) ?>" class="bento-chip" title="Filter by <?= htmlspecialchars($role) ?>">
                                            <i class="bi bi-eyedropper me-1 text-kld-green small"></i><?= htmlspecialchars($role) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                    <span class="badge bg-light text-dark border px-2 py-2 fw-semibold small">
                                        <i class="bi bi-briefcase-fill text-kld-green me-1"></i> <?= $lab_cat['job_count'] ?? 2 ?> Vacancies
                                    </span>
                                    <a href="student/jobs.php?cat=<?= urlencode($lab_cat['name']) ?>" class="bento-arrow-btn" title="Explore <?= htmlspecialchars($lab_cat['name']) ?>">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Academic Peer Tutor -->
                    <?php if ($tutor_cat): ?>
                        <div class="col-lg-4 col-md-12">
                            <div class="bento-card bento-card-green-theme">
                                <div class="bento-capsule-img-wrapper mb-3" style="height: 150px;">
                                    <img src="<?= htmlspecialchars($tutor_cat['image'] ?? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($tutor_cat['name']) ?>">
                                    <div class="bento-capsule-overlay"></div>
                                    <div class="bento-floating-icon text-kld-green">
                                        <i class="bi <?= htmlspecialchars($tutor_cat['icon']) ?>"></i>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="bento-pill-badge bento-badge-kld-green">
                                        <i class="bi <?= htmlspecialchars($tutor_cat['badge_icon'] ?? 'bi-award-fill') ?> text-kld-green"></i> <?= htmlspecialchars($tutor_cat['badge_tag'] ?? 'Academic Mentoring') ?>
                                    </span>
                                    <span class="bento-meta-pill fw-bold text-kld-green"><?= htmlspecialchars($tutor_cat['hourly_range'] ?? '₱100/hr') ?></span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">
                                    <a href="student/jobs.php?cat=<?= urlencode($tutor_cat['name']) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($tutor_cat['name']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars($tutor_cat['description']) ?>
                                </p>

                                <div class="bento-role-chips">
                                    <?php foreach (($tutor_cat['popular_roles'] ?? ['Math Tutor', 'Peer Mentor']) as $role): ?>
                                        <a href="student/jobs.php?kw=<?= urlencode($role) ?>&cat=<?= urlencode($tutor_cat['name']) ?>" class="bento-chip" title="Filter by <?= htmlspecialchars($role) ?>">
                                            <i class="bi bi-mortarboard me-1 text-kld-green small"></i><?= htmlspecialchars($role) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                    <span class="badge bg-light text-dark border px-2 py-2 fw-semibold small">
                                        <i class="bi bi-briefcase-fill text-kld-green me-1"></i> <?= $tutor_cat['job_count'] ?? 3 ?> Vacancies
                                    </span>
                                    <a href="student/jobs.php?cat=<?= urlencode($tutor_cat['name']) ?>" class="bento-arrow-btn" title="Explore <?= htmlspecialchars($tutor_cat['name']) ?>">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bento Row 3: Feature Banner Bento Card (Campus Media & Events) -->
                <?php if ($media_cat): ?>
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="bento-banner-card bento-card-green-theme">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-5 col-md-5">
                                        <div class="bento-capsule-img-wrapper" style="min-height: 220px; height: 100%;">
                                            <img src="<?= htmlspecialchars($media_cat['image'] ?? 'https://images.unsplash.com/photo-1511578314322-379afb476865?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($media_cat['name']) ?>">
                                            <div class="bento-capsule-overlay"></div>
                                            <div class="bento-floating-icon text-kld-green">
                                                <i class="bi bi-camera-reels"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-7 col-md-7 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                <span class="bento-pill-badge bento-badge-kld-green">
                                                    <i class="bi <?= htmlspecialchars($media_cat['badge_icon'] ?? 'bi-broadcast') ?> text-kld-green"></i> <?= htmlspecialchars($media_cat['badge_tag'] ?? 'Live Event Production') ?>
                                                </span>
                                                <span class="bento-meta-pill">
                                                    <i class="bi bi-clock-history text-muted"></i> Event-Based Schedules
                                                </span>
                                                <span class="bento-meta-pill text-kld-green fw-bold">
                                                    <?= htmlspecialchars($media_cat['hourly_range'] ?? '₱90.00 / hr') ?>
                                                </span>
                                            </div>

                                            <h3 class="fw-bold text-dark mb-2">
                                                <a href="student/jobs.php?cat=<?= urlencode($media_cat['name']) ?>" class="text-decoration-none text-dark">
                                                    <?= htmlspecialchars($media_cat['name']) ?>
                                                </a>
                                            </h3>
                                            <p class="text-muted small mb-3">
                                                <?= htmlspecialchars($media_cat['description']) ?>
                                            </p>

                                            <div class="bento-role-chips mb-3">
                                                <?php foreach (($media_cat['popular_roles'] ?? ['AV Operator', 'Live Stream Crew']) as $role): ?>
                                                    <a href="student/jobs.php?kw=<?= urlencode($role) ?>&cat=<?= urlencode($media_cat['name']) ?>" class="bento-chip" title="Filter by <?= htmlspecialchars($role) ?>">
                                                        <i class="bi bi-camera-video me-1 text-kld-green small"></i><?= htmlspecialchars($role) ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                                                <i class="bi bi-briefcase-fill text-kld-green me-1"></i> <?= $media_cat['job_count'] ?? 2 ?> Vacancies
                                            </span>
                                            <a href="student/jobs.php?cat=<?= urlencode($media_cat['name']) ?>" class="bento-arrow-btn" title="Explore <?= htmlspecialchars($media_cat['name']) ?> Jobs">
                                                <i class="bi bi-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Additional Categories (if added dynamically via Admin) -->
                <?php 
                $rendered_ids = [1, 2, 3, 4, 5, 6];
                $extra_cats = array_filter($categories, function($c) use ($rendered_ids) {
                    return !in_array($c['id'], $rendered_ids);
                });
                if (!empty($extra_cats)): ?>
                    <div class="row g-4 mt-2">
                        <?php foreach ($extra_cats as $extra): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="bento-card bento-card-green-theme">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="bento-floating-icon text-kld-green">
                                            <i class="bi <?= htmlspecialchars($extra['icon']) ?>"></i>
                                        </div>
                                        <span class="bento-pill-badge bento-badge-kld-green">Campus Dept</span>
                                    </div>
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($extra['name']) ?></h5>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($extra['description']) ?></p>
                                    <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                        <span class="badge bg-light text-dark border"><i class="bi bi-briefcase-fill text-kld-green me-1"></i><?= $extra['job_count'] ?? 0 ?> Vacancies</span>
                                        <a href="student/jobs.php?cat=<?= urlencode($extra['name']) ?>" class="bento-arrow-btn">
                                            <i class="bi bi-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Why Work on Campus Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="text-kld-green fw-bold small text-uppercase">Student Benefits</span>
                    <h2 class="fw-bold text-dark mb-4">Why Apply for an On-Campus Student Job?</h2>
                    
                    <div class="d-flex gap-3 mb-4">
                        <div class="stat-icon stat-icon-kld flex-shrink-0">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Class-First Flexible Schedules</h5>
                            <p class="text-muted small mb-0">Supervisors respect your study timetable. Duty shifts are scheduled during your vacant periods and exam weeks.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="stat-icon stat-icon-kld flex-shrink-0">
                            <i class="bi bi-pin-map fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Zero Commute & Expenses</h5>
                            <p class="text-muted small mb-0">Work inside KLD campus buildings between your lectures without spending extra on transit or meal fares.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="stat-icon stat-icon-kld flex-shrink-0">
                            <i class="bi bi-award fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Valuable Resume Experience & Certificate</h5>
                            <p class="text-muted small mb-0">Earn official Certificates of Service, department supervisor recommendation letters, and real workplace skills.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 p-md-5 text-white rounded-4 shadow position-relative overflow-hidden bg-kld-gradient">
                        <div class="position-relative z-1">
                            <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase mb-3">KLD Campus Offices</span>
                            <h3 class="fw-bold text-white mb-3">Looking to Hire Qualified KLD Student Assistants?</h3>
                            <p class="text-light opacity-90 mb-4">
                                Institute deans, administrative department heads, and laboratory custodians can post requisitions, evaluate candidate profiles, and organize interviews seamlessly.
                            </p>
                            <a href="register.php?role=employer" class="btn btn-gold btn-lg px-4">
                                <i class="bi bi-building-add me-2"></i> Register Campus Office
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
