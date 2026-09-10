<?php
/**
 * Campus Job Posting System - About Us / Developers Page
 * Archetype F: Public & Team Showcase (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Meet the Development Team & Project Mission';

// 6 Developer Profiles Configuration
$team_members = [
    [
        'name' => 'Bustamante, Alwinson',
        'role' => 'Lead System Architect & Core Backend',
        'student_id' => '2025-2-000065',
        'section' => 'BSIS201',
        'email' => 'abustamante@kld.edu.ph',
        'image' => 'assets/img/developers/BUSTAMANTE.jpg',
        'image_dark' => 'assets/img/developers/BUSTAMANTE_dark.jpg',
        'bio' => 'Oversees the end-to-end system architecture, routing logic, modular template structure, and data engine.',
        'tasks' => ['System Routing & State Engine', 'Architecture Blueprint', 'Session Handlers']
    ],
    [
        'name' => 'Baco, Nico',
        'role' => 'Public Suite & Legal Compliance Specialist',
        'student_id' => '2025-2-000032',
        'section' => 'BSIS201',
        'email' => 'nbaco@kld.edu.ph',
        'image' => 'assets/img/developers/BACO.jpg',
        'image_dark' => 'assets/img/developers/BACO_dark.jpg',
        'bio' => 'Designs and implements the public interface, responsive landing page, Data Privacy Policy, and Terms of Service.',
        'tasks' => ['Index & Landing Page', 'Data Privacy (RA 10173)', 'Terms of Service Page']
    ],
    [
        'name' => 'Cruzpe, Julius Robert',
        'role' => 'Authentication & Client Validation Engineer',
        'student_id' => '2025-2-000091',
        'section' => 'BSIS201',
        'email' => 'jrcruzpe@kld.edu.ph',
        'image' => 'assets/img/developers/CRUZPE.jpg',
        'image_dark' => 'assets/img/developers/CRUZPE_dark.jpg',
        'bio' => 'Specializes in user authentication flows, dynamic real-time password strength algorithms, and account security.',
        'tasks' => ['Password Strength Meter JS', 'Multi-Role Login & Register', 'Forgot Password Flow']
    ],
    [
        'name' => 'Layco, Andrei Von Breydan',
        'role' => 'Student Portal & Application Flow Engineer',
        'student_id' => '2025-2-000176',
        'section' => 'BSIS201',
        'email' => 'avblayco@kld.edu.ph',
        'image' => 'assets/img/developers/LAYCO.jpg',
        'image_dark' => 'assets/img/developers/LAYCO_dark.jpg',
        'bio' => 'Crafts the student dashboard, job browsing with live filters, job application modal, and application status tracker.',
        'tasks' => ['Student Dashboard', 'Job Filter & Details', 'My Applications Tracker']
    ],
    [
        'name' => 'Salognon, Joeven',
        'role' => 'Department & Hiring Workflow Engineer',
        'student_id' => '2025-2-000269',
        'section' => 'BSIS201',
        'email' => 'jsalognon@kld.edu.ph',
        'image' => 'assets/img/developers/SOLOGNON.jpg',
        'image_dark' => 'assets/img/developers/SOLOGNON_dark.jpg',
        'bio' => 'Builds the campus office portal, vacancy posting forms, candidate evaluation drawers, and interview scheduling triggers.',
        'tasks' => ['Employer Dashboard', 'Create & Edit Job Forms', 'Applicant Review Suite']
    ],
    [
        'name' => 'Jurado, Marl Jordan',
        'role' => 'System Administration & QA Lead',
        'student_id' => '2025-2-000166',
        'section' => 'BSIS201',
        'email' => 'mjjurado@kld.edu.ph',
        'image' => 'assets/img/developers/JURADO.jpg',
        'image_dark' => 'assets/img/developers/JURADO_dark.jpg',
        'bio' => 'Manages administrative category controls, user accounts, printable analytics reports, and cross-browser quality assurance.',
        'tasks' => ['Categories & User Control', 'Printable Analytics Reports', 'Mobile QA & Validation']
    ]
];

$devblogs = get_devblogs();

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <!-- Page Head & Photo Standards Policy Split Header -->
                <div class="page-head-split reveal-fade-rise mb-5 pb-4 border-bottom border-line">
                    <h1 class="page-head-title mb-3">Meet the Development Team</h1>
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <p class="page-head-lead mb-0 text-justify" style="text-align: justify; text-justify: inter-word;">
                                We are a 6-member team of 2nd Year Bachelor of Science in Information Systems (BSIS) students from the Institute of Computing and Digital Innovation (ICDI) at Kolehiyo ng Lungsod ng Dasmariñas (KLD) who built the KLD Campus Job Posting System for our COAL101: Web Systems and Technologies midterm lab project. Together, we designed and engineered an intuitive, secure, and accessible platform that streamlines the student assistantship application and hiring lifecycle across campus offices, featuring weekly schedule availability matching, transparent application pipelines, and role-based workflows.
                            </p>
                        </div>
                        <div class="col-lg-5">
                            <div class="card-paper bg-cream p-4 border border-line">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; aspect-ratio: 1 / 1; font-size: 1.2rem;">
                                        <i class="bi bi-patch-check-fill"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-paper-title mb-1" style="font-size: 17px;">Developer 2x2 Photo Standards Policy</h4>
                                        <p class="text-muted-custom small mb-0 lh-base">
                                            All developer profiles feature 2x2 formal institutional portrait photos in KLD Green blazers and attire with clean backgrounds, strictly free of casual headwear, sunglasses, headphones, or accessories.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6 Developer Cards Grid -->
                <div class="row g-4 mb-5" id="developers">
                    <?php foreach ($team_members as $dev): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="dev-card reveal-fade-rise">
                                <div class="dev-photo-glitch-frame dev-card-photo-frame" data-light-src="<?= $base_url . $dev['image'] ?>" data-dark-src="<?= $base_url . $dev['image_dark'] ?>">
                                    <img src="<?= $base_url . $dev['image'] ?>" 
                                         data-light-src="<?= $base_url . $dev['image'] ?>" 
                                         data-dark-src="<?= $base_url . $dev['image_dark'] ?>" 
                                         alt="<?= htmlspecialchars($dev['name']) ?>" 
                                         class="dev-card-photo dev-photo-target">
                                </div>
                                <h3 class="dev-card-name"><?= htmlspecialchars($dev['name']) ?></h3>
                                <div class="dev-card-role"><?= htmlspecialchars($dev['role']) ?></div>

                                <div class="dev-card-bottom">
                                    <div class="dev-card-meta text-start">
                                        <span class="dev-card-section-heading small fw-bold text-ink d-block mb-1">Details:</span>
                                        <ul class="list-unstyled text-muted-custom mb-0">
                                            <li class="mb-1"><i class="bi bi-person-badge me-1 text-ink"></i> Student ID: <strong><?= htmlspecialchars($dev['student_id']) ?></strong></li>
                                            <li class="mb-1"><i class="bi bi-mortarboard me-1 text-ink"></i> Section: <strong><?= htmlspecialchars($dev['section']) ?></strong></li>
                                            <li class="text-truncate mt-1">
                                                <a href="mailto:<?= htmlspecialchars($dev['email']) ?>" class="text-muted-custom text-decoration-none" title="<?= htmlspecialchars($dev['email']) ?>">
                                                    <i class="bi bi-envelope me-1 text-ink"></i><?= htmlspecialchars($dev['email']) ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="dev-card-scope text-start">
                                        <span class="dev-card-section-heading small fw-bold text-ink d-block mb-1">Key Deliverables:</span>
                                        <ul class="list-unstyled text-muted-custom mb-0">
                                            <?php foreach ($dev['tasks'] as $task): ?>
                                                <li class="mb-1"><i class="bi bi-check2 text-ink me-1"></i> <?= htmlspecialchars($task) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ============================================================
                     DEVBLOG & DAILY CHRONICLES (3D Stage Coverflow Section)
                     ============================================================ -->
                <section class="devblog-section mb-5 py-4 reveal-fade-rise" id="devblog">
                    <div class="text-center mb-4">
                        <h2 class="h1 fw-extrabold text-ink mb-2">Behind the Code: Lead Developer DevBlog</h2>
                        <p class="text-muted-custom col-lg-8 mx-auto">
                            Follow Alwinson's daily engineering chronicles—from initial topic selection, architecture planning, and all-night coding marathons to collaborative Git mentoring and UI/UX design refinements.
                        </p>
                    </div>

                    <!-- 3D Stage Container with Flanking Left/Right Floating Arrows -->
                    <div class="devblog-stage-container position-relative">
                        <!-- Flanking Left Navigation Arrow (Newer / Latest) - Desktop View -->
                        <button type="button" id="devblog-prev-btn" class="devblog-nav-arrow devblog-nav-left devblog-prev-btn d-none d-lg-inline-flex"
                            aria-label="Previous Day" title="Newer Log" disabled>
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <!-- Flanking Right Navigation Arrow (Older / Project Kickoff) - Desktop View -->
                        <button type="button" id="devblog-next-btn" class="devblog-nav-arrow devblog-nav-right devblog-next-btn d-none d-lg-inline-flex"
                            aria-label="Next Day" title="Older Log">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                        <!-- 3D Track Viewport -->
                        <div class="devblog-track" id="devblog-track">
                            <?php foreach ($devblogs as $idx => $blog):
                                $stateClass = ($idx === 0) ? 'is-active' : (($idx === 1) ? 'is-next' : 'is-hidden is-hidden-right');
                            ?>
                                <article class="devblog-card <?= $stateClass ?>" data-index="<?= $idx ?>" data-id="<?= htmlspecialchars($blog['id'] ?? ('day-' . $idx)) ?>" tabindex="0" role="group" aria-label="DevBlog: <?= htmlspecialchars($blog['title'] ?? 'Development Log') ?>">
                                    <div class="devblog-card-photo-wrap">
                                        <?php 
                                        $blog_cover = $blog['cover_image'] ?? 'assets/img/kld-campus.jpg';
                                        $blog_cover_src = (str_starts_with($blog_cover, 'http://') || str_starts_with($blog_cover, 'https://') || str_starts_with($blog_cover, '/'))
                                            ? $blog_cover 
                                            : ($base_url . ltrim($blog_cover, '/'));
                                        ?>
                                        <img src="<?= htmlspecialchars($blog_cover_src) ?>"
                                            alt="<?= htmlspecialchars($blog['title'] ?? 'DevBlog') ?>" loading="lazy"
                                            onerror="this.src='<?= $base_url ?>assets/img/kld-campus.jpg';">
                                        <div class="devblog-card-badges">
                                            <span class="badge-tag-overlay" style="background-color: var(--ink); color: #fff;">
                                                <i class="bi bi-flag-fill text-accent me-1"></i>DAY <?= sprintf('%02d', (int)($blog['sprint_number'] ?? ($idx + 1))) ?>
                                            </span>
                                            <span class="badge-tag-overlay">
                                                <i class="bi bi-clock-history me-1 text-accent"></i><?= htmlspecialchars($blog['read_time'] ?? '5 min read') ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="devblog-card-body">
                                        <div class="devblog-author-row mb-2">
                                            <div class="devblog-author-img-wrap dev-photo-glitch-frame" data-light-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE.jpg" data-dark-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE_dark.jpg">
                                                <img src="<?= $base_url . ($blog['author_image'] ?? 'assets/img/developers/BUSTAMANTE.jpg') ?>" 
                                                     data-light-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE.jpg"
                                                     data-dark-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE_dark.jpg"
                                                     alt="<?= htmlspecialchars($blog['author_name'] ?? 'Alwinson Bustamante') ?>" 
                                                     class="devblog-author-img dev-photo-target">
                                            </div>
                                            <div class="devblog-author-info">
                                                <div class="devblog-author-name"><?= htmlspecialchars($blog['author_name'] ?? 'Alwinson Bustamante') ?></div>
                                                <div class="devblog-author-role"><?= htmlspecialchars($blog['author_role'] ?? 'Lead Developer & System Architect') ?></div>
                                            </div>
                                            <span class="devblog-date-pill ms-auto">
                                                <i class="bi bi-calendar3 me-1 text-accent"></i><?= htmlspecialchars(!empty($blog['date']) ? date('M j, Y', strtotime($blog['date'])) : 'Aug 2026') ?>
                                            </span>
                                        </div>

                                        <h3 class="devblog-card-title"><?= htmlspecialchars($blog['title'] ?? 'Development Sprint') ?></h3>

                                        <p class="devblog-card-excerpt">
                                            <?= htmlspecialchars($blog['summary_excerpt'] ?? '') ?>
                                        </p>

                                        <div class="devblog-card-footer mt-auto pt-2 border-top border-line">
                                            <button type="button" class="btn btn-accent w-100 justify-content-center py-2 devblog-read-trigger" data-blog-index="<?= $idx ?>">
                                                <i class="bi bi-book-half me-1"></i> Read Full Daily Log
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Progress Step Indicators & Counter -->
                    <div class="devblog-controls-strip d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mt-4 pt-3">
                        <div class="small fw-semibold text-muted-custom">
                            <i class="bi bi-layers-fill text-accent me-1"></i>
                            Showing Day <strong id="devblog-counter-current" class="text-ink"><?= $devblogs[0]['sprint_number'] ?? '04' ?></strong> of <strong class="text-ink"><?= sprintf('%02d', count($devblogs)) ?></strong>
                        </div>

                        <!-- Tactile Navigation Cluster (Arrows + Dots) -->
                        <div class="devblog-nav-cluster d-flex align-items-center gap-2">
                            <button type="button" id="devblog-cluster-prev-btn" class="devblog-nav-cluster-btn devblog-prev-btn"
                                aria-label="Previous Day" title="Newer Log" disabled
                                onclick="if(window.devblogGoPrev){window.devblogGoPrev();}else{var b=document.getElementById('devblog-prev-btn');if(b)b.click();}">
                                <i class="bi bi-chevron-left" style="pointer-events: none;"></i>
                            </button>

                            <div class="devblog-step-dots" id="devblog-step-dots" role="tablist" aria-label="DevBlog day navigation">
                                <?php foreach ($devblogs as $idx => $blog): ?>
                                    <button type="button" class="devblog-dot <?= ($idx === 0) ? 'is-active' : '' ?>"
                                        data-dot-index="<?= $idx ?>"
                                        aria-label="Day <?= htmlspecialchars($blog['sprint_number'] ?? ($idx + 1)) ?>"
                                        title="Day <?= htmlspecialchars($blog['sprint_number'] ?? ($idx + 1)) ?>: <?= htmlspecialchars($blog['title'] ?? '') ?>"></button>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" id="devblog-cluster-next-btn" class="devblog-nav-cluster-btn devblog-next-btn"
                                aria-label="Next Day" title="Older Log"
                                onclick="if(window.devblogGoNext){window.devblogGoNext();}else{var b=document.getElementById('devblog-next-btn');if(b)b.click();}">
                                <i class="bi bi-chevron-right" style="pointer-events: none;"></i>
                            </button>
                        </div>

                        <script>
                        (function() {
                            function syncNavCluster() {
                                var desktopPrev = document.getElementById('devblog-prev-btn');
                                var desktopNext = document.getElementById('devblog-next-btn');
                                var clusterPrev = document.getElementById('devblog-cluster-prev-btn');
                                var clusterNext = document.getElementById('devblog-cluster-next-btn');
                                if (desktopPrev && clusterPrev) clusterPrev.disabled = desktopPrev.disabled;
                                if (desktopNext && clusterNext) clusterNext.disabled = desktopNext.disabled;
                            }
                            window.syncDevblogNavButtons = syncNavCluster;
                            var prevEl = document.getElementById('devblog-prev-btn');
                            var nextEl = document.getElementById('devblog-next-btn');
                            if (window.MutationObserver && prevEl && nextEl) {
                                var obs = new MutationObserver(syncNavCluster);
                                obs.observe(prevEl, { attributes: true, attributeFilter: ['disabled'] });
                                obs.observe(nextEl, { attributes: true, attributeFilter: ['disabled'] });
                            }
                            document.addEventListener('click', function() { setTimeout(syncNavCluster, 60); });
                            document.addEventListener('touchend', function() { setTimeout(syncNavCluster, 60); }, { passive: true });
                        })();
                        </script>

                        <div class="small text-muted-custom d-none d-md-block">
                            <kbd class="bg-surface text-ink border border-line px-2 py-1 small rounded-1 font-monospace">
                                <i class="bi bi-keyboard me-1"></i> &larr; &rarr; Arrow Keys
                            </kbd>
                        </div>
                    </div>
                </section>

                <!-- Mission & Vision and Tech Stack -->
                <div class="row g-4 pt-2">
                    <!-- Project Mission & Vision -->
                    <div class="col-lg-5">
                        <div class="card-paper h-100 reveal-fade-rise d-flex flex-column">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; aspect-ratio: 1 / 1; font-size: 1.2rem;">
                                    <i class="bi bi-compass"></i>
                                </div>
                                <h3 class="card-paper-title mb-0">Our Project Mission</h3>
                            </div>
                            <p class="text-muted-custom mb-3">
                                To simplify and digitize the student assistantship application workflow across KLD institutes and offices. By replacing manual paperwork and unorganized bulletin boards with an automated portal, we empower KLD students to gain valuable workplace experience while prioritizing their studies.
                            </p>
                            <div class="p-3 bg-cream rounded-3 border border-line mb-3">
                                <span class="d-block small fw-bold text-ink mb-1">Academic Alignment</span>
                                <div class="small text-muted-custom d-flex flex-column gap-1">
                                    <div>Course: <strong>COAL101 - Web Systems and Technologies</strong></div>
                                    <div>Activity: <strong>Midterm Lab Project</strong></div>
                                    <div>Submission Deadline: <strong>September 2, 2026</strong></div>
                                    <div>Institution: <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong></div>
                                </div>
                            </div>
                            <div class="p-3 bg-surface rounded-3 border border-line mt-auto">
                                <span class="d-block small fw-bold text-ink mb-2">Institutional Safeguards &amp; Compliance</span>
                                <ul class="list-unstyled small text-muted-custom mb-0 d-flex flex-column gap-1">
                                    <li><i class="bi bi-shield-check text-accent me-1"></i> RA 10173 Data Privacy Act (2012) compliant storage</li>
                                    <li><i class="bi bi-clock-history text-accent me-1"></i> Statutory 20-hour weekly academic labor cap enforcement</li>
                                    <li><i class="bi bi-calendar-check text-accent me-1"></i> Class schedule conflict prevention matrix</li>
                                    <li><i class="bi bi-person-check text-accent me-1"></i> Equal-opportunity, transparent hiring audit trail</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Tech Stack Showcase: Interactive Knowledge Graph -->
                    <div class="col-lg-7">
                        <div class="card-paper h-100 reveal-fade-rise tech-graph-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; aspect-ratio: 1 / 1; font-size: 1.2rem;">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <div>
                                    <h3 class="card-paper-title mb-0">Technical Architecture</h3>
                                    <span class="small text-muted-custom">Interactive knowledge graph · Drag nodes to explore ecosystem connections</span>
                                </div>
                            </div>

                            <!-- Graph Controls & Cluster Filters -->
                            <div class="tech-graph-controls">
                                <div class="tech-graph-filters" role="group" aria-label="Filter architecture cluster">
                                    <button type="button" class="tech-graph-filter-btn is-active" data-cluster="all">
                                        <i class="bi bi-grid-fill"></i> All Systems
                                    </button>
                                    <button type="button" class="tech-graph-filter-btn" data-cluster="frontend">
                                        <i class="bi bi-window-stack"></i> 3D &amp; UI
                                    </button>
                                    <button type="button" class="tech-graph-filter-btn" data-cluster="backend">
                                        <i class="bi bi-hdd-rack"></i> Backend
                                    </button>
                                    <button type="button" class="tech-graph-filter-btn" data-cluster="data">
                                        <i class="bi bi-database"></i> Database
                                    </button>
                                    <button type="button" class="tech-graph-filter-btn" data-cluster="ai">
                                        <i class="bi bi-shield-check"></i> AI &amp; QA
                                    </button>
                                </div>
                                <button type="button" class="tech-graph-reset-btn" id="tech-graph-reset-btn" title="Reset Graph Layout">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset View
                                </button>
                            </div>

                            <!-- Canvas Viewport -->
                            <div class="tech-graph-canvas-wrap" id="tech-graph-canvas-wrap">
                                <div class="tech-graph-hint">
                                    <i class="bi bi-hand-index-thumb"></i> Drag or hover nodes
                                </div>
                                <canvas id="tech-graph-canvas" class="tech-graph-canvas" aria-label="Interactive Technical Architecture Network Graph"></canvas>
                                <!-- Floating Tooltip -->
                                <div class="tech-graph-tooltip" id="tech-graph-tooltip" role="tooltip">
                                    <span class="tech-graph-tooltip-tag">Component</span>
                                    <div class="tech-graph-tooltip-title">Technology Name</div>
                                    <p class="tech-graph-tooltip-desc">Description</p>
                                </div>
                            </div>

                            <!-- Bottom Inspector HUD Panel (Click-to-Pin) -->
                            <div class="tech-graph-inspector mt-auto" id="tech-graph-inspector" aria-live="polite">
                                <div class="tech-graph-inspector-icon">
                                    <i class="bi bi-boxes"></i>
                                </div>
                                <div class="tech-graph-inspector-content">
                                    <div class="tech-graph-inspector-meta">
                                        <span class="tech-graph-inspector-tag">3D Engine &amp; Mascot</span>
                                        <span class="tech-graph-inspector-cluster">3D &amp; UI</span>
                                    </div>
                                    <h4 class="tech-graph-inspector-title">Three.js WebGL &amp; GLTFLoader</h4>
                                    <p class="tech-graph-inspector-desc">
                                        Hardware-accelerated 3D mascot (<code>cute_robot.glb</code>) with 13 procedural kinematics &amp; OLED visor shader
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- ============================================================
             INTERACTIVE DEVBLOG READER MODAL
             ============================================================ -->
        <div class="modal fade" id="devblogModal" tabindex="-1" aria-labelledby="devblogModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content paper-modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom border-line pb-3 bg-surface">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-ink text-white px-2 py-1 small fw-bold" id="devblog-modal-sprint-badge">
                                <i class="bi bi-flag-fill text-accent me-1"></i>DAY 11 · MODULAR CSS &amp; 20H POLICY SAFEGUARDS
                            </span>
                            <span class="badge bg-cream text-muted-custom border border-line small" id="devblog-modal-readtime">
                                <i class="bi bi-clock-history me-1 text-accent"></i>5 min read
                            </span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-white" id="devblog-modal-body">
                        <!-- Cover Banner -->
                        <div class="devblog-modal-banner-wrap mb-4">
                            <img src="" id="devblog-modal-banner" alt="Log Cover" class="w-100 rounded-3 object-fit-cover" style="max-height: 280px;">
                        </div>

                        <!-- Title -->
                        <h2 class="h3 fw-extrabold text-ink mb-3" id="devblog-modal-title"></h2>

                        <!-- Author Meta Bar -->
                        <div class="devblog-author-row p-3 bg-cream rounded-3 border border-line mb-4">
                            <div class="devblog-author-img-wrap dev-photo-glitch-frame" data-light-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE.jpg" data-dark-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE_dark.jpg">
                                <img src="" id="devblog-modal-author-img" alt="Author" class="devblog-author-img dev-photo-target"
                                     data-light-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE.jpg"
                                     data-dark-src="<?= $base_url ?>assets/img/developers/BUSTAMANTE_dark.jpg"
                                     style="width: 46px; height: 46px;">
                            </div>
                            <div class="devblog-author-info">
                                <div class="fw-bold text-ink" id="devblog-modal-author-name"></div>
                                <div class="small text-muted-custom" id="devblog-modal-author-role"></div>
                            </div>
                            <div class="ms-auto text-end small text-muted-custom">
                                <div><i class="bi bi-calendar3 text-accent me-1"></i><span id="devblog-modal-date"></span></div>
                            </div>
                        </div>

                        <!-- Full Story Content -->
                        <div class="devblog-modal-content mb-4" id="devblog-modal-story"></div>

                        <!-- Key Deliverables & Takeaways Box -->
                        <div class="p-3 bg-surface rounded-3 border border-line mb-4">
                            <h5 class="h6 fw-bold text-ink mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check2-circle text-accent fs-5"></i> Key Engineering Deliverables &amp; Takeaways
                            </h5>
                            <ul class="list-unstyled small text-muted-custom mb-0 d-flex flex-column gap-2" id="devblog-modal-takeaways"></ul>
                        </div>

                        <!-- Tech Stack Tags -->
                        <div>
                            <span class="small fw-bold text-ink d-block mb-2">Technologies &amp; Modules Applied:</span>
                            <div class="d-flex flex-wrap gap-2" id="devblog-modal-techstack"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Embedded DevBlogs Data for Instant Client-Side Rendering -->
        <script id="devblogs-data" type="application/json">
            <?= json_encode($devblogs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
        </script>
        <script src="<?= $base_url ?>assets/js/tech-graph.js?v=1.0"></script>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
