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
$featured_jobs = array_slice(array_filter(get_jobs(), fn($j) => ($j['status'] ?? 'active') === 'active'), 0, 5);

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
                                <a href="#categories" class="text-decoration-none d-inline-flex align-items-center gap-2 text-ink fw-bold small">
                                    <span class="eyebrow-badge">EXPLORE CATEGORIES</span>
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
                 SECTION 2: JOB CATEGORIES GRID (#categories, bg --cream)
                 ============================================================ -->
            <section id="categories" class="py-5 py-lg-6 bg-cream border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="text-center mb-5 pt-3 pt-md-4">
                        <h2 class="h1 fw-extrabold text-ink mb-2">Where Do You Want To Work?</h2>
                        <p class="text-muted-custom col-lg-6 mx-auto">
                            Explore dynamic student assistantship roles and verified part-time openings distributed across campus divisions.
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
                 SECTION 3: HOW IT WORKS & ACCREDITATION PROCESS (Treasure Map / Journey Trail)
                 ============================================================ -->
            <section id="how-it-works" class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="text-center mb-5 pt-3 pt-md-4">
                        <h2 class="h1 fw-extrabold text-ink mb-2">How Approved Employers &amp; Offices Deploy Vacancies</h2>
                        <p class="text-muted-custom col-lg-7 mx-auto">
                            A secure institutional framework ensuring every partner entity, campus department, and job requisition undergoes formal verification before reaching students.
                        </p>
                    </div>

                    <div class="map-journey-container" id="map-journey-container">
                        <!-- Winding Dashed Journey Trail (Desktop SVG) -->
                        <svg class="map-journey-track-svg" viewBox="0 0 1000 640" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M 320 80 C 750 80, 750 280, 680 280 C 580 280, 250 360, 320 540" class="map-journey-path" />
                        </svg>

                        <!-- Step 1: Institutional Accreditation (Left-aligned) -->
                        <div class="row align-items-center map-journey-row" data-step="1">
                            <div class="map-mobile-marker">01</div>
                            <div class="col-lg-7">
                                <div class="map-step-card">
                                    <div class="map-stamp-watermark">
                                        <i class="bi bi-patch-check-fill"></i> ACCREDITED
                                    </div>
                                    <div class="map-checkpoint-header">
                                        <span class="map-waypoint-badge">
                                            <i class="bi bi-geo-alt-fill text-accent"></i> CHECKPOINT <span class="step-num">01</span>
                                        </span>
                                        <div class="map-card-icon-wrap">
                                            <i class="bi bi-file-earmark-check-fill"></i>
                                        </div>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Accreditation &amp; Partnership</h3>
                                    <p class="text-muted-custom small mb-4">
                                        University offices register with departmental authorization. External employers and industry partners submit corporate credentials and formal University Partnership Agreements (MOA).
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                        <span class="chip-tag"><i class="bi bi-patch-check-fill text-accent me-1"></i> Department Authorization</span>
                                        <span class="chip-tag"><i class="bi bi-file-text-fill text-accent me-1"></i> Formal MOA Accord</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                                <div class="map-preview-card">
                                    <div class="map-preview-topbar">
                                        <span><i class="bi bi-patch-check-fill text-accent me-1"></i> PARTNER ACCREDITATION PASS</span>
                                        <span class="text-white-50">#ACCR-2026</span>
                                    </div>
                                    <div class="map-preview-body">
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-building"></i> Registered Unit</span>
                                            <span class="map-preview-val">MIS &amp; Tech Center</span>
                                        </div>
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-file-earmark-text"></i> Legal Accord</span>
                                            <span class="map-preview-val">University MOA Accord</span>
                                        </div>
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-shield-check"></i> Status</span>
                                            <span class="map-preview-val text-success-emphasis"><i class="bi bi-check-circle-fill text-accent me-1"></i> Authorized Partner</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Vetting & Compliance Audit (Right-aligned) -->
                        <div class="row align-items-center map-journey-row" data-step="2">
                            <div class="map-mobile-marker">02</div>
                            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                                <div class="map-preview-card">
                                    <div class="map-preview-topbar">
                                        <span><i class="bi bi-shield-check text-accent me-1"></i> CAREER CENTER AUDIT PASS</span>
                                        <span class="text-white-50">#AUDIT-SAFE</span>
                                    </div>
                                    <div class="map-preview-body">
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-clipboard-check"></i> Role Evaluation</span>
                                            <span class="map-preview-val">Quality &amp; Ethics Approved</span>
                                        </div>
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-person-badge"></i> Supervisor Auth</span>
                                            <span class="map-preview-val">Faculty / Staff Verified</span>
                                        </div>
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-shield-lock"></i> Compliance</span>
                                            <span class="map-preview-val text-success-emphasis"><i class="bi bi-check-circle-fill text-accent me-1"></i> 100% Policy Approved</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="map-step-card">
                                    <div class="map-stamp-watermark">
                                        <i class="bi bi-shield-check"></i> AUDITED &amp; VETTED
                                    </div>
                                    <div class="map-checkpoint-header">
                                        <span class="map-waypoint-badge">
                                            <i class="bi bi-geo-alt-fill text-accent"></i> CHECKPOINT <span class="step-num">02</span>
                                        </span>
                                        <div class="map-card-icon-wrap">
                                            <i class="bi bi-shield-lock-fill"></i>
                                        </div>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Job Scope &amp; Quality Audit</h3>
                                    <p class="text-muted-custom small mb-4">
                                        The University Career Center conducts a comprehensive audit of each role description, evaluating workload ethics, skill development opportunities, and supervisor qualifications.
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                        <span class="chip-tag"><i class="bi bi-shield-check text-accent me-1"></i> Job Scope Review</span>
                                        <span class="chip-tag"><i class="bi bi-person-badge-fill text-accent me-1"></i> Supervisor Vetting</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Authorized Deployment (Left/Center-aligned) -->
                        <div class="row align-items-center map-journey-row" data-step="3">
                            <div class="map-mobile-marker">03</div>
                            <div class="col-lg-7">
                                <div class="map-step-card">
                                    <div class="map-stamp-watermark">
                                        <i class="bi bi-broadcast"></i> DEPLOYED LIVE
                                    </div>
                                    <div class="map-checkpoint-header">
                                        <span class="map-waypoint-badge">
                                            <i class="bi bi-geo-alt-fill text-accent"></i> CHECKPOINT <span class="step-num">03</span>
                                        </span>
                                        <div class="map-card-icon-wrap">
                                            <i class="bi bi-person-check-fill"></i>
                                        </div>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Live Broadcast &amp; Applicant CRM</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Approved listings are published instantly across the campus portal. Recruiters manage applications, shortlist qualified candidates, and coordinate interviews through a centralized dashboard.
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                        <span class="chip-tag"><i class="bi bi-broadcast-pin text-accent me-1"></i> Campus Broadcast</span>
                                        <span class="chip-tag"><i class="bi bi-kanban-fill text-accent me-1"></i> Integrated Applicant CRM</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                                <div class="map-preview-card">
                                    <div class="map-preview-topbar">
                                        <span><i class="bi bi-broadcast text-accent me-1"></i> LIVE VACANCY REQUISITION</span>
                                        <span class="text-white-50">#LIVE-SYNC</span>
                                    </div>
                                    <div class="map-preview-body">
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-person-badge"></i> Position Category</span>
                                            <span class="map-preview-val">Student Assistant / OJT</span>
                                        </div>
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-kanban"></i> Application Flow</span>
                                            <span class="map-preview-val">Direct Supervisor Queue</span>
                                        </div>
                                        <div class="map-preview-row">
                                            <span class="map-preview-label"><i class="bi bi-lightning-charge"></i> Broadcast Status</span>
                                            <span class="map-preview-val text-success-emphasis"><i class="bi bi-rocket-takeoff-fill text-accent me-1"></i> Live on Campus Portal</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 4: WHY WORK ON-CAMPUS (Student Benefits - bg --cream)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-cream border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5 pt-3 pt-md-4">
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
                        <!-- Card 1: Academic-First Scheduling -->
                        <div class="col-lg-4">
                            <div class="student-benefit-card">
                                <div>
                                    <div class="benefit-icon-box">
                                        <i class="bi bi-calendar2-check-fill"></i>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Academic-First Scheduling</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Your education always comes first. Work schedules are structured strictly around your lecture hours and lab times, with built-in flexibility during midterm and final exam weeks.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                    <span class="benefit-chip"><i class="bi bi-calendar-event me-1"></i> Class-priority shifts</span>
                                    <span class="benefit-chip"><i class="bi bi-journal-bookmark-fill me-1"></i> Exam-week flexibility</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Zero-Commute Convenience -->
                        <div class="col-lg-4">
                            <div class="student-benefit-card">
                                <div>
                                    <div class="benefit-icon-box">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Zero-Commute Convenience</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Work steps away from your lecture halls, libraries, and student lounges. Save valuable hours and daily transit costs by working directly on campus or with verified nearby partners.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                    <span class="benefit-chip"><i class="bi bi-signpost-2-fill me-1"></i> Zero transit costs</span>
                                    <span class="benefit-chip"><i class="bi bi-buildings-fill me-1"></i> On-campus locations</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Real Career Mentorship -->
                        <div class="col-lg-4">
                            <div class="student-benefit-card">
                                <div>
                                    <div class="benefit-icon-box">
                                        <i class="bi bi-award-fill"></i>
                                    </div>
                                    <h3 class="h4 fw-bold text-ink mb-2">Real Career Mentorship</h3>
                                    <p class="text-muted-custom small mb-4">
                                        Gain hands-on workplace experience, develop transferable professional skills, and earn direct supervisor recommendation letters that elevate your resume before graduation.
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2 pt-3 border-top border-line">
                                    <span class="benefit-chip"><i class="bi bi-award-fill me-1"></i> Supervisor references</span>
                                    <span class="benefit-chip"><i class="bi bi-stars me-1"></i> Early resume building</span>
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
                                <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-white text-dark mb-3">
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
                                                <span class="badge-tag-overlay">
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
                            <a href="<?= $base_url ?>updates.php" class="btn-outline-pill">
                                SEE ALL <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                            </a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <?php 
                        $home_updates = function_exists('get_latest_career_updates') ? get_latest_career_updates(4) : [];
                        foreach ($home_updates as $i => $u):
                            $offset_class = ($i % 2 === 0) ? 'update-card-offset-1' : 'update-card-offset-2';
                            $u_date = date('F j, Y', strtotime($u['published_at'] ?? 'now'));
                        ?>
                            <div class="col-md-6 col-lg-3">
                                <a href="<?= $base_url ?>update-detail.php?id=<?= urlencode($u['id']) ?>" class="update-news-card <?= $offset_class ?>">
                                    <img src="<?= htmlspecialchars($u['image'] ?? 'assets/img/hero-office.jpg') ?>" class="update-news-photo" alt="<?= htmlspecialchars($u['title']) ?>">
                                    <div class="p-3 d-flex flex-column flex-grow-1">
                                        <span class="eyebrow-badge text-muted-custom mb-1"><?= $u_date ?></span>
                                        <h3 class="h6 fw-bold text-ink mb-0 line-clamp-2"><?= htmlspecialchars($u['title']) ?></h3>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SECTION 8: CTA (bg --cream, floating circular campus photos 64–280px)
                 ============================================================ -->
            <section class="py-5 py-lg-6 bg-surface border-top border-line reveal-fade-rise">
                <div class="container-fluid px-lg-5">
                    <div class="cta-section-wrap">
                        <!-- Floating Circular Developer Photos -->
                        <div class="floating-photo-circle photo-pos-1" title="Alwinson Bustamante - Lead System Architect">
                            <img src="<?= $base_url ?>assets/img/developers/BUSTAMANTE.jpg" alt="Alwinson Bustamante">
                        </div>
                        <div class="floating-photo-circle photo-pos-2" title="Nico Baco - Public Suite & Compliance Specialist">
                            <img src="<?= $base_url ?>assets/img/developers/BACO.jpg" alt="Nico Baco">
                        </div>
                        <div class="floating-photo-circle photo-pos-3" title="Julius Robert Cruzpe - Authentication & Client Validation Engineer">
                            <img src="<?= $base_url ?>assets/img/developers/CRUZPE.jpg" alt="Julius Robert Cruzpe">
                        </div>
                        <div class="floating-photo-circle photo-pos-4" title="Andrei Von Breydan Layco - Student Portal & Application Flow Engineer">
                            <img src="<?= $base_url ?>assets/img/developers/LAYCO.jpg" alt="Andrei Von Breydan Layco">
                        </div>
                        <div class="floating-photo-circle photo-pos-5" title="Joeven Salognon - Department & Hiring Workflow Engineer">
                            <img src="<?= $base_url ?>assets/img/developers/SOLOGNON.jpg" alt="Joeven Salognon">
                        </div>
                        <div class="floating-photo-circle photo-pos-6" title="Marl Jordan Jurado - System Administration & QA Lead">
                            <img src="<?= $base_url ?>assets/img/developers/JURADO.jpg" alt="Marl Jordan Jurado">
                        </div>

                        <div class="position-relative z-2 max-w-640 mx-auto" style="max-width: 620px;">
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle mb-3">
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
