<?php
/**
 * Campus Job Posting System - Index / Landing Page
 * Paper Sheet Redesign (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}

$page_title = 'Empowering Students, Supporting Campus Offices';

// Data from helper
$categories = get_categories();
$featured_jobs = get_featured_jobs(5);

// Key Metrics
$metric_active_jobs = get_metrics_total_active_jobs();
$metric_partnered_offices = get_metrics_partnered_offices();
$metric_students_hired = get_metrics_students_hired();
$metric_avg_pay = get_metrics_avg_hourly_pay();

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main>
            <!-- ============================================================
                 SECTION 1: HERO (bg --surface)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface position-relative reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="row align-items-center py-4 py-lg-5">
                        <div class="col-xl-10 mx-auto text-center">
                            <!-- Giant Two-Line Heading with Inline Rounded Image Pill -->
                            <h1 class="hero-headline mb-4">
                                Empowering <span class="hero-inline-pill-img"><img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=400&auto=format&fit=crop" alt="Campus Students"></span> Students,<br class="d-none d-sm-block">
                                Connecting Trusted Employers
                            </h1>

                            <!-- Subtitle -->
                            <p class="lead text-muted-custom col-lg-8 mx-auto mb-5">
                                Discover on-campus student assistantships in university offices alongside accredited part-time jobs, academic internships (OJT), and project roles from verified partner employers — scheduled flexibly around your classes.
                            </p>

                            <!-- CTAs -->
                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 mb-5">
                                <a href="student/jobs.php" class="btn-accent-pill">
                                    <i class="bi bi-search"></i> EXPLORE VACANCIES
                                </a>
                                <a href="employer/create-job.php" class="btn-outline-pill">
                                    FOR EMPLOYERS & OFFICES <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Strip (Partners & Scroll Indicator) -->
                    <div class="hero-partner-strip">
                        <div class="row align-items-center justify-content-between g-3">
                            <!-- Left: Trusted Campus & Industry Partners -->
                            <div class="col-lg-9 col-md-9">
                                <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4">
                                    <span class="eyebrow-badge text-muted-custom">ACCREDITED OFFICES & PARTNERS</span>
                                    <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4 text-ink fw-bold small">
                                        <span class="partner-logo-item"><i class="bi bi-building text-accent"></i> University Registrar</span>
                                        <span class="partner-logo-item"><i class="bi bi-cpu text-accent"></i> MIS & Tech Center</span>
                                        <span class="partner-logo-item"><i class="bi bi-book text-accent"></i> Campus Library</span>
                                        <span class="partner-logo-item"><i class="bi bi-briefcase text-accent"></i> TechVanguard Solutions</span>
                                        <span class="partner-logo-item"><i class="bi bi-camera-reels text-accent"></i> Dasma Creative Media</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Scroll Down Indicator -->
                            <div class="col-lg-3 col-md-3 text-start text-md-end">
                                <a href="#search-widget" class="text-decoration-none d-inline-flex align-items-center gap-2 text-ink fw-bold small">
                                    <span class="eyebrow-badge">SCROLL DOWN</span>
                                    <span class="btn-circle-icon" style="width: 34px; height: 34px;">
                                        <i class="bi bi-arrow-down"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 2: SEARCH WIDGET (#search-widget, bg --cream + faint blobs)
                 ============================================================ -->
            <section id="search-widget" class="py-5 py-lg-6 bg-cream search-widget-section border-top border-line reveal-fade-rise">
                <!-- Faint Background Blobs -->
                <div class="search-blob search-blob-1"></div>
                <div class="search-blob search-blob-2"></div>

                <div class="container-fluid px-lg-5 position-relative z-2">
                    <div class="search-card">
                        <!-- Card Header -->
                        <div class="text-center mb-4">
                            <h2 class="h3 fw-extrabold text-ink mb-1">Find Student Assistantships &amp; Verified Jobs</h2>
                            <p class="text-muted-custom small mb-3">Browse opportunities from campus offices, academic laboratories, and approved industry partners</p>

                            <!-- Pay Type Toggle Switch -->
                            <div class="pay-toggle-wrapper">
                                <span id="pay-hourly-label" class="pay-toggle-label text-accent">Hourly Pay</span>
                                <div class="form-check form-switch m-0 p-0 d-inline-flex align-items-center">
                                    <input class="form-check-input ms-0" type="checkbox" role="switch" id="pay-type-toggle" aria-label="Toggle Pay Type">
                                </div>
                                <span id="pay-stipend-label" class="pay-toggle-label">Monthly Stipend</span>
                            </div>
                        </div>

                        <!-- Search Form (Native GET) -->
                        <form action="student/jobs.php" method="GET">
                            <!-- Hidden input synced with pay toggle switch -->
                            <input type="hidden" name="pay_type" id="pay-type-hidden" value="Hourly">

                            <!-- Keyword & Employer Inputs -->
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label for="search-keyword" class="form-label small fw-bold text-ink mb-1">Keyword Search</label>
                                    <div class="search-input-wrap">
                                        <i class="bi bi-search text-muted-custom"></i>
                                        <input type="text" id="search-keyword" name="q" class="form-control bg-surface border-line" placeholder="Role title, skill, or department…">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="search-dept" class="form-label small fw-bold text-ink mb-1">Employer / Organization</label>
                                    <div class="search-input-wrap">
                                        <i class="bi bi-building text-muted-custom"></i>
                                        <select id="search-dept" name="dept" class="form-select bg-surface border-line">
                                            <option value="">All Employers (Offices & Partners)</option>
                                            <optgroup label="University Offices">
                                                <option value="Management Information Systems (MIS)">Management Information Systems (MIS)</option>
                                                <option value="Office of the University Registrar">Office of the University Registrar</option>
                                                <option value="University Library Services">University Library Services</option>
                                                <option value="Institute of Science and Mathematics (ISM)">Institute of Science and Mathematics (ISM)</option>
                                                <option value="College of Science & Laboratories">College of Science & Laboratories</option>
                                            </optgroup>
                                            <optgroup label="Approved External Partners">
                                                <option value="TechVanguard Solutions Inc.">TechVanguard Solutions (IT Partner)</option>
                                                <option value="Dasma Creative Media Studio">Dasma Creative Media (Multimedia)</option>
                                                <option value="Campus Cafe & Co.">Campus Cafe & Co. (Concessionaire)</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Multi-Select Opportunity Types -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-ink mb-2 d-block">Opportunity Type</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div>
                                        <input type="checkbox" class="search-chip-input" name="job_type[]" value="Student Assistant" id="chip-sa">
                                        <label for="chip-sa" class="search-chip-label"><i class="bi bi-mortarboard"></i> Student Assistant (SA)</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" class="search-chip-input" name="job_type[]" value="Part-Time Job" id="chip-pt">
                                        <label for="chip-pt" class="search-chip-label"><i class="bi bi-clock"></i> Part-Time Job</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" class="search-chip-input" name="job_type[]" value="Internship / OJT" id="chip-ojt">
                                        <label for="chip-ojt" class="search-chip-label"><i class="bi bi-briefcase"></i> Internship / OJT</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" class="search-chip-input" name="job_type[]" value="Peer Tutor" id="chip-tutor">
                                        <label for="chip-tutor" class="search-chip-label"><i class="bi bi-person-video3"></i> Peer Tutor</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Employer Type Radio Selection -->
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-ink mb-1">Employer Classification</label>
                                    <select name="employer_type" class="form-select bg-surface border-line">
                                        <option value="">All Verified Classifications</option>
                                        <option value="university_office">University Academic / Admin Offices Only</option>
                                        <option value="approved_partner">Approved Industry / Campus Partners Only</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-ink mb-1">Workplace Arrangement</label>
                                    <select name="work_setup" class="form-select bg-surface border-line">
                                        <option value="">Any Work Setup</option>
                                        <option value="On-Campus">On-Campus (University Buildings)</option>
                                        <option value="Near-Campus">Near-Campus (Partner Offices)</option>
                                        <option value="Hybrid">Hybrid (Campus + Remote)</option>
                                        <option value="Remote">Remote / Online</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Form Action Buttons -->
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between pt-2">
                                <button type="submit" class="btn-accent-pill flex-grow-1">
                                    <i class="bi bi-arrow-right-circle"></i> SEARCH OPPORTUNITIES
                                </button>
                                <a href="student/jobs.php" class="btn-soft-pill text-center">
                                    BROWSE ALL JOBS
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 3: HOW IT WORKS & ACCREDITATION PROCESS (bg --surface)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="text-center mb-5">
                        <h2 class="h1 fw-extrabold text-ink mb-2">How Approved Employers &amp; Offices Deploy Vacancies</h2>
                        <p class="text-muted-custom col-lg-7 mx-auto">
                            Every opportunity on this platform undergoes institutional vetting before publishing, protecting student academic focus, fair compensation, and safe workplaces.
                        </p>
                    </div>

                    <div class="row g-4">
                        <!-- Step 1: Institutional Accreditation -->
                        <div class="col-lg-4">
                            <div class="why-card h-100">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center bg-cream rounded-circle p-3" style="width: 56px; height: 56px;">
                                            <i class="bi bi-file-earmark-check-fill fs-4 text-ink"></i>
                                        </div>
                                        <span class="pill-badge pill-badge-ink" style="font-size: 11px;">STEP 01</span>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Accreditation & Partnership</h3>
                                    <p class="text-muted-custom small mb-4">
                                        University offices register with departmental authorization. External employers and industry partners submit business registration and formal University Partnership Agreements (MOA).
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line mt-auto">
                                    <span class="chip-tag"># Verified Credentials</span>
                                    <span class="chip-tag"># Formal MOA</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Vetting & Compliance Audit -->
                        <div class="col-lg-4">
                            <div class="why-card h-100">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center bg-cream rounded-circle p-3" style="width: 56px; height: 56px;">
                                            <i class="bi bi-shield-lock-fill fs-4 text-ink"></i>
                                        </div>
                                        <span class="pill-badge pill-badge-ink" style="font-size: 11px;">STEP 02</span>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Career Center Vetting</h3>
                                    <p class="text-muted-custom small mb-4">
                                        The University Career Center audits job descriptions to verify student-safe pay rates, ethical workload scopes, and strict enforcement of the 20-hour weekly maximum during active semesters.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line mt-auto">
                                    <span class="chip-tag"># Max 20 hrs/wk Limit</span>
                                    <span class="chip-tag"># Fair Student Wage</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Authorized Deployment & Direct Tracking -->
                        <div class="col-lg-4">
                            <div class="why-card h-100">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center bg-cream rounded-circle p-3" style="width: 56px; height: 56px;">
                                            <i class="bi bi-person-check-fill fs-4 text-ink"></i>
                                        </div>
                                        <span class="pill-badge pill-badge-ink" style="font-size: 11px;">STEP 03</span>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Authorized Deployment</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Only verified accounts deploy live requisitions. Students submit applications securely, schedule interviews with supervisors, and track hiring decisions directly on their student dashboard.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line mt-auto">
                                    <span class="chip-tag"># Verified Listings</span>
                                    <span class="chip-tag"># Direct Status Tracking</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 3: JOB CATEGORIES GRID (bg --surface)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="text-center mb-5">
                        <h2 class="h1 fw-extrabold text-ink mb-2">Where Do You Want To Work?</h2>
                        <p class="text-muted-custom col-lg-6 mx-auto">
                            Explore dynamic student assistantship roles distributed across specialized campus divisions.
                        </p>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($categories as $idx => $cat): ?>
                            <div class="col-6 col-lg-2">
                                <a href="student/jobs.php?category=<?= htmlspecialchars($cat['id']) ?>" class="category-mini-card">
                                    <div class="category-mini-arrow">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </div>
                                    <div class="category-mini-icon-circle">
                                        <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                                    </div>
                                    <h3 class="h6 fw-bold text-ink mb-1 text-center" style="font-size: 0.875rem;">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </h3>
                                    <span class="text-muted-custom small mt-auto">
                                        <?= (int)($cat['job_count'] ?? 0) ?> Openings
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 4: WHY WORK ON-CAMPUS (3-card services archetype, bg --surface)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
                        <div>
                            <h2 class="h1 fw-extrabold text-ink mb-0">Study Close, Work Close, Grow Faster</h2>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <a href="faqs.php" class="btn-outline-pill">
                                LEARN MORE <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Card 1: Flexible Hours -->
                        <div class="col-lg-4">
                            <div class="why-card">
                                <div>
                                    <div class="d-inline-flex align-items-center justify-content-center bg-cream rounded-circle p-3 mb-4" style="width: 56px; height: 56px;">
                                        <i class="bi bi-clock-history fs-4 text-ink"></i>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Flexible Hours</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Shifts are adjusted around your class schedule, with a 20-hour weekly cap during regular semesters.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                    <span class="chip-tag"># Max 20 hrs/wk</span>
                                    <span class="chip-tag"># Exam-week adjustments</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Close To Everything -->
                        <div class="col-lg-4">
                            <div class="why-card">
                                <div>
                                    <div class="d-inline-flex align-items-center justify-content-center bg-cream rounded-circle p-3 mb-4" style="width: 56px; height: 56px;">
                                        <i class="bi bi-geo-alt-fill fs-4 text-ink"></i>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Close To Everything</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Work minutes from your classes in official, university-certified offices — safe and supervised.
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-line">
                                    <div class="d-flex align-items-center gap-1 text-accent fs-6">
                                        <i class="bi bi-building"></i>
                                        <i class="bi bi-shield-fill-check"></i>
                                        <i class="bi bi-award"></i>
                                    </div>
                                    <span class="small fw-bold text-ink">Official Campus Certification</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Tuition & Stipend Support -->
                        <div class="col-lg-4">
                            <div class="why-card">
                                <div>
                                    <div class="d-inline-flex align-items-center justify-content-center bg-cream rounded-circle p-3 mb-4" style="width: 56px; height: 56px;">
                                        <i class="bi bi-cash-stack fs-4 text-ink"></i>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Tuition & Stipend Support</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Earn semi-monthly stipends or hourly pay processed by the University Cashier Office upon signed DTRs.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                    <span class="chip-tag"># Semi-monthly pay</span>
                                    <span class="chip-tag"># DTR-based</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 5: KEY METRICS (cycling counters, bg crossfade + arc)
                 ============================================================ -->
            <section class="py-5 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="metrics-section">
                        <!-- Ambient Glowing Arc -->
                        <div class="metrics-arc-glow"></div>

                        <div class="row align-items-center g-4 position-relative z-2">
                            <div class="col-lg-4">
                                <span class="pill-badge pill-badge-ink bg-white text-dark mb-3">
                                    <i class="bi bi-bar-chart-fill text-accent"></i> Our Impact
                                </span>
                                <h2 class="h1 fw-extrabold text-white mb-2">Together, We're Building Careers</h2>
                                <p class="text-white-50 small mb-0">Live data synchronized from active university department requisitions.</p>
                            </div>
                            <div class="col-lg-8">
                                <div class="row g-4 text-center text-sm-start">
                                    <div class="col-6 col-md-3">
                                        <div class="metric-counter-number" data-counter-target="<?= $metric_active_jobs ?>"><?= $metric_active_jobs ?></div>
                                        <div class="metric-counter-label">Total Active Postings</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="metric-counter-number" data-counter-target="<?= $metric_partnered_offices ?>"><?= $metric_partnered_offices ?></div>
                                        <div class="metric-counter-label">Partnered Campus Offices</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="metric-counter-number" data-counter-target="<?= $metric_students_hired ?>"><?= $metric_students_hired ?></div>
                                        <div class="metric-counter-label">Students Hired to date</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="metric-counter-number" data-counter-target="<?= preg_replace('/[^\d]/', '', $metric_avg_pay) ?>" data-counter-prefix="₱"><?= htmlspecialchars($metric_avg_pay) ?></div>
                                        <div class="metric-counter-label">Average Hourly Pay</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 6: FEATURED JOB POSTINGS (center-mode carousel, bg --surface)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
                        <div>
                            <h2 class="h1 fw-extrabold text-ink mb-0">Featured Campus &amp; Partner Opportunities</h2>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <a href="student/jobs.php" class="btn-outline-pill">
                                VIEW ALL JOBS <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                        </div>
                    </div>

                    <!-- Carousel Viewport -->
                    <div class="carousel-viewport-container">
                        <div id="featured-carousel-track" class="carousel-track">
                            <?php foreach ($featured_jobs as $idx => $job): 
                                $slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
                                $slots_filled = (int)($job['slots_filled'] ?? 0);
                                $pct = ($slots_total > 0) ? round(($slots_filled / $slots_total) * 100) : 0;
                                $badges = $job['badges'] ?? [$job['job_type'] ?? 'Student Assistant', $job['work_setup'] ?? 'On-Campus'];
                                $is_partner = ($job['employer_type'] ?? '') === 'approved_partner';
                                $org_name = $job['organization_name'] ?? ($job['department'] ?? 'University Department');
                            ?>
                                <div class="featured-job-card <?= ($idx === 0) ? 'is-active' : '' ?>">
                                    <div class="featured-card-photo-wrap">
                                        <img src="<?= htmlspecialchars($job['image'] ?? 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=900&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($job['title']) ?>">
                                        <div class="featured-card-badges">
                                            <?php if ($is_partner): ?>
                                                <span class="badge-tag-overlay" style="background-color: var(--ink); color: #fff;">
                                                    <i class="bi bi-patch-check-fill text-accent"></i> Approved Partner
                                                </span>
                                            <?php endif; ?>
                                            <?php foreach ($badges as $b): ?>
                                                <span class="badge-tag-overlay <?= (stripos($b, 'urgent') !== false || stripos($b, 'ojt') !== false) ? 'urgent' : '' ?>">
                                                    <?= htmlspecialchars($b) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="featured-card-apply-overlay">
                                            <a href="student/apply.php?id=<?= $job['id'] ?>" class="btn-accent-pill py-1 px-3 small" style="font-size: 0.75rem;">
                                                APPLY NOW <i class="bi bi-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="featured-card-body">
                                        <div class="small fw-semibold text-muted-custom mb-1 d-flex align-items-center gap-1">
                                            <?php if ($is_partner): ?>
                                                <i class="bi bi-patch-check-fill text-accent"></i>
                                            <?php else: ?>
                                                <i class="bi bi-building text-muted-custom"></i>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($org_name) ?></span>
                                        </div>
                                        <h3 class="h5 fw-bold text-ink mb-1" style="font-weight: 700;">
                                            <?= htmlspecialchars($job['title']) ?>
                                        </h3>
                                        <div class="text-muted-custom small mb-3">
                                            <?= htmlspecialchars($job['location']) ?> · <strong class="text-ink"><?= htmlspecialchars($job['pay_rate']) ?></strong>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between small text-muted-custom">
                                                <span><?= $slots_filled ?> of <?= $slots_total ?> slots filled</span>
                                                <span class="fw-bold text-ink"><?= $pct ?>%</span>
                                            </div>
                                            <div class="progress-slots-bar">
                                                <div class="progress-slots-fill" style="width: <?= $pct ?>%;"></div>
                                            </div>
                                            <div class="small text-muted-custom mt-2">
                                                <i class="bi bi-calendar-event me-1"></i> Deadline: <?= htmlspecialchars($job['deadline'] ?? 'Open') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Bottom-Right Carousel Navigation Controls (Hidden on Mobile) -->
                    <div class="d-none d-md-flex justify-content-end gap-2 mt-3">
                        <button id="carousel-prev-btn" class="carousel-nav-btn" aria-label="Previous Featured Job Slide">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                        <button id="carousel-next-btn" class="carousel-nav-btn" aria-label="Next Featured Job Slide">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 7: CAMPUS UPDATES (staggered news carousel, bg --surface)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
                        <div>
                            <h2 class="h1 fw-extrabold text-ink mb-0">Updates From The Career Center</h2>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <a href="about-us.php" class="btn-outline-pill">
                                SEE ALL <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 col-lg-3">
                            <a href="faqs.php" class="update-news-card update-card-offset-1">
                                <img src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=600&auto=format&fit=crop" class="update-news-photo" alt="Spring Career Fair">
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <span class="eyebrow-badge text-muted-custom mb-1">June 12, 2026</span>
                                    <h3 class="h6 fw-bold text-ink mb-0">Spring Career Fair Welcomes Over 40 Campus Offices</h3>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="employer/dashboard.php" class="update-news-card update-card-offset-2">
                                <img src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?q=80&w=600&auto=format&fit=crop" class="update-news-photo" alt="Employer Portal">
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <span class="eyebrow-badge text-muted-custom mb-1">June 5, 2026</span>
                                    <h3 class="h6 fw-bold text-ink mb-0">Employer Portal Opens to All Departments</h3>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="faqs.php" class="update-news-card update-card-offset-1">
                                <img src="https://images.unsplash.com/photo-1515187029135-18ee286d815b?q=80&w=600&auto=format&fit=crop" class="update-news-photo" alt="Resume Workshop">
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <span class="eyebrow-badge text-muted-custom mb-1">June 2, 2026</span>
                                    <h3 class="h6 fw-bold text-ink mb-0">Resume Workshop Helps 200 Students Apply</h3>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="about-us.php" class="update-news-card update-card-offset-2">
                                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop" class="update-news-photo" alt="Alumni Mentors">
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <span class="eyebrow-badge text-muted-custom mb-1">May 29, 2026</span>
                                    <h3 class="h6 fw-bold text-ink mb-0">Alumni Mentors Inspire the Whole Campus</h3>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 8: CTA (bg --cream, floating circular campus photos 64–280px)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="cta-section-wrap">
                        <!-- Floating Circular Campus Photos (64–280px) -->
                        <div class="floating-photo-circle photo-pos-1">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=400&auto=format&fit=crop" alt="Campus Student">
                        </div>
                        <div class="floating-photo-circle photo-pos-2">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=400&auto=format&fit=crop" alt="Campus Assistant">
                        </div>
                        <div class="floating-photo-circle photo-pos-3">
                            <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?q=80&w=400&auto=format&fit=crop" alt="Student Researcher">
                        </div>
                        <div class="floating-photo-circle photo-pos-4">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=400&auto=format&fit=crop" alt="Peer Tutor">
                        </div>

                        <div class="position-relative z-2 max-w-640 mx-auto" style="max-width: 620px;">
                            <span class="pill-badge mb-3">
                                <i class="bi bi-lightning-charge-fill text-accent"></i> TAKE ACTION
                            </span>
                            <h2 class="h1 fw-extrabold text-ink mb-3">Join Us In Shaping Your Future</h2>
                            <p class="text-muted-custom mb-4">
                                Gain practical on-campus workplace experience, earn tuition allowances, and expand your university network today.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="register.php" class="btn-accent-pill">
                                    <i class="bi bi-person-plus-fill"></i> CREATE FREE ACCOUNT
                                </a>
                                <a href="about-us.php" class="btn-outline-pill">
                                    LEARN MORE <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
