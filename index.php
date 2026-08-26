<?php
/**
 * Campus Job Posting System - Index / Landing Page
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'Find On-Campus Jobs & Student Assistantships';
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
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase mb-3">
                        <i class="bi bi-stars"></i> Official University Job Board
                    </span>
                    <h1 class="display-4 fw-bold text-white mb-3 lh-sm">
                        Find Purposeful Work <br class="d-none d-md-block">
                        <span class="text-warning">Right on Campus</span>
                    </h1>
                    <p class="lead text-light opacity-90 mb-4 pe-lg-5">
                        Discover flexible student assistantships, technical lab support, clerical roles, and peer tutoring opportunities tailored around your class schedules.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="student/jobs.php" class="btn btn-gold btn-lg px-4 shadow">
                            <i class="bi bi-search me-2"></i> Browse All Vacancies
                        </a>
                        <a href="register.php?role=employer" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-building me-2"></i> Post Department Vacancy
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-4 text-light small">
                        <div><i class="bi bi-check-circle-fill text-warning me-1"></i> Flexible Shift Adjustments</div>
                        <div><i class="bi bi-check-circle-fill text-warning me-1"></i> Max 20 hrs/week Safe Limit</div>
                        <div><i class="bi bi-check-circle-fill text-warning me-1"></i> Official Certificate of Service</div>
                    </div>
                </div>

                <!-- Hero Search Widget -->
                <div class="col-lg-5">
                    <div class="hero-search-card">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-funnel-fill text-primary"></i> Quick Job Search</h5>
                        <form action="student/jobs.php" method="GET">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Keyword or Job Title</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                    <input type="text" name="kw" class="form-control" placeholder="e.g. Lab Assistant, Clerk, Tutor">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Category / Field</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-grid-fill"></i></span>
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
                                    <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
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
                        <div class="display-6 fw-bold text-primary mb-1"><?= $total_jobs ?></div>
                        <div class="text-muted small fw-semibold text-uppercase">Active Vacancies</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3">
                        <div class="display-6 fw-bold text-warning mb-1"><?= $total_cats ?></div>
                        <div class="text-muted small fw-semibold text-uppercase">Job Categories</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3">
                        <div class="display-6 fw-bold text-success mb-1">12+</div>
                        <div class="text-muted small fw-semibold text-uppercase">Partner Offices</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3">
                        <div class="display-6 fw-bold text-info mb-1"><?= $total_apps ?></div>
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
                    <span class="text-primary fw-bold small text-uppercase letter-spacing-1">Immediate Openings</span>
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
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <?= htmlspecialchars($job['category']) ?>
                                </span>
                                <span class="job-tag">
                                    <i class="bi bi-clock-history me-1"></i><?= htmlspecialchars($job['hours_per_week']) ?>
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mb-1">
                                <a href="student/job-details.php?id=<?= $job['id'] ?>" class="text-decoration-none text-dark hover-primary">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted small mb-3">
                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($job['department']) ?>
                            </p>

                            <p class="text-secondary small mb-3 text-truncate-2">
                                <?= htmlspecialchars(substr($job['description'], 0, 110)) ?>...
                            </p>

                            <div class="border-top pt-3 mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Hourly Stipend</div>
                                    <div class="fw-bold text-success"><?= htmlspecialchars($job['pay_rate']) ?></div>
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

    <!-- Categories Section -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold small text-uppercase">Explore by Department</span>
                <h2 class="fw-bold text-dark">Job Categories</h2>
                <p class="text-muted">Find opportunities matching your skills, academic major, and career interests.</p>
            </div>

            <div class="row g-4">
                <?php foreach ($categories as $cat): ?>
                    <div class="col-lg-4 col-md-6">
                        <a href="student/jobs.php?cat=<?= urlencode($cat['name']) ?>" class="category-card">
                            <div class="category-icon-box bg-<?= $cat['color'] ?>-subtle text-<?= $cat['color'] ?>">
                                <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                            </div>
                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($cat['name']) ?></h5>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($cat['description']) ?></p>
                            <span class="badge bg-light text-dark border">
                                <?= $cat['job_count'] ?? 3 ?> Open Positions <i class="bi bi-chevron-right ms-1"></i>
                            </span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Why Work on Campus Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="text-warning fw-bold small text-uppercase">Student Benefits</span>
                    <h2 class="fw-bold text-dark mb-4">Why Apply for an On-Campus Student Job?</h2>
                    
                    <div class="d-flex gap-3 mb-4">
                        <div class="stat-icon bg-primary text-white flex-shrink-0">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Class-First Flexible Schedules</h5>
                            <p class="text-muted small mb-0">Supervisors respect your study timetable. Duty shifts are scheduled during your vacant periods and exam weeks.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="stat-icon bg-success text-white flex-shrink-0">
                            <i class="bi bi-pin-map"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Zero Commute & Expenses</h5>
                            <p class="text-muted small mb-0">Work inside university buildings between your lectures without spending extra on transit or meal fares.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="stat-icon bg-warning text-dark flex-shrink-0">
                            <i class="bi bi-award"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Valuable Resume Experience & Certificate</h5>
                            <p class="text-muted small mb-0">Earn official Certificates of Service, department supervisor recommendation letters, and real workplace skills.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 p-md-5 bg-primary text-white rounded-4 shadow position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f2c59 0%, #1e3a8a 100%);">
                        <div class="position-relative z-1">
                            <span class="badge bg-warning text-dark px-3 py-2 fw-bold text-uppercase mb-3">Campus Employers</span>
                            <h3 class="fw-bold text-white mb-3">Looking to Hire Qualified Student Assistants?</h3>
                            <p class="text-light opacity-90 mb-4">
                                Department chairs, administrative heads, and laboratory custodians can post requisitions, evaluate candidate profiles, and organize interviews seamlessly.
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
