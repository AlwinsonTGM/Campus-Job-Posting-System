<?php
/**
 * Campus Job Posting System - About Us / Developers Page
 * Archetype F: Public & Team Showcase (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Meet the Developers & Project Mission';

// 6 Developer Profiles aligned with WBS Table
$team_members = [
    [
        'name' => 'Member 1',
        'role' => 'Project Lead / Core Architect',
        'image' => 'assets/img/developers/member1.svg',
        'scope' => 'System flow, auth state management, and core page routing engine.'
    ],
    [
        'name' => 'Member 2',
        'role' => 'Frontend Lead (Public & Legal Pages)',
        'image' => 'assets/img/developers/member2.svg',
        'scope' => 'Index, About Us, Privacy Policy, Terms of Service, and FAQs.'
    ],
    [
        'name' => 'Member 3',
        'role' => 'Security & Authentication Specialist',
        'image' => 'assets/img/developers/member3.svg',
        'scope' => 'Login, Registration with Password Strength Meter, and Forgot Password flow.'
    ],
    [
        'name' => 'Member 4',
        'role' => 'Student Experience Specialist',
        'image' => 'assets/img/developers/member4.svg',
        'scope' => 'Student Dashboard, Job Search & Filters, Job Details, and Application Form.'
    ],
    [
        'name' => 'Member 5',
        'role' => 'Employer Experience Specialist',
        'image' => 'assets/img/developers/member5.svg',
        'scope' => 'Employer Dashboard, Create/Edit Job, Applicant Roster, and Review Modal.'
    ],
    [
        'name' => 'Member 6',
        'role' => 'Admin & Data Architect',
        'image' => 'assets/img/developers/member6.svg',
        'scope' => 'Categories, User Management, PDF/Printable Reports Mock, and JSON datastore.'
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <!-- Page Head -->
                <?php
                render_page_head(
                    '<i class="bi bi-code-slash text-accent me-1"></i> COAL101 Web Systems & Technologies · 6-Member Team',
                    'Meet the Development Team',
                    'We are BSIS / IT students modernizing campus employment, connecting student talent with university offices and accredited campus partners.'
                );
                ?>

                <!-- Developer Photo Specifications Compliance Note -->
                <!-- Developer Photo Specifications: Clean, high-resolution 1:1 square portrait; Formal/Semi-formal campus attire (blazer/collared shirt); Plain or neutral background; Strictly no accessories (no sunglasses, caps, headphones, or stickers) -->
                <div class="card-paper mb-5 bg-cream">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle icon-circle-accent">
                            <i class="bi bi-patch-check-fill text-accent"></i>
                        </div>
                        <div>
                            <h4 class="card-paper-title mb-1">Developer Photo & Attire Compliance Policy</h4>
                            <p class="text-muted-custom small mb-0">
                                In accordance with COAL101 specifications, all developer profiles feature formal campus attire against neutral backgrounds with strictly no accessories to maintain institutional standards.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 6 Developer Cards Grid -->
                <div class="row g-4 mb-5" id="developers">
                    <?php foreach ($team_members as $dev): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="dev-card reveal-fade-rise">
                                <img src="<?= $base_url . $dev['image'] ?>" alt="<?= htmlspecialchars($dev['name']) ?>" class="dev-card-photo">
                                <h3 class="dev-card-name"><?= htmlspecialchars($dev['name']) ?></h3>
                                <span class="pill-badge pill-badge-ink mb-3"><?= htmlspecialchars($dev['role']) ?></span>
                                <p class="dev-card-scope"><?= htmlspecialchars($dev['scope']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Mission & Vision and Tech Stack -->
                <div class="row g-4 pt-2">
                    <!-- Project Mission & Vision -->
                    <div class="col-lg-6">
                        <div class="card-paper h-100 reveal-fade-rise">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="icon-circle icon-circle-accent">
                                    <i class="bi bi-compass"></i>
                                </div>
                                <h3 class="card-paper-title mb-0">Mission & Vision</h3>
                            </div>
                            <p class="text-muted-custom mb-3">
                                Our goal as BSIS and IT students is to modernize and streamline student employment on campus. We replace fragmented paper notices with a unified, transparent digital portal that protects student study hours while empowering university departments to recruit qualified student assistants.
                            </p>
                            <div class="p-3 bg-cream rounded-3 border border-line">
                                <span class="d-block small fw-bold text-ink mb-1">COAL101 Project Alignment</span>
                                <span class="small text-muted-custom">
                                    Final Term Web Systems & Technologies Deliverable &bull; Target Sprint: 7 Days (Deadline: September 2, 2026)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tech Stack Showcase -->
                    <div class="col-lg-6">
                        <div class="card-paper h-100 reveal-fade-rise">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="icon-circle icon-circle-accent">
                                    <i class="bi bi-stack"></i>
                                </div>
                                <h3 class="card-paper-title mb-0">Tech Stack Showcase</h3>
                            </div>
                            <p class="text-muted-custom mb-3">
                                Built strictly using lightweight native technologies without external heavy frameworks or database engines:
                            </p>
                            
                            <div class="d-flex flex-wrap gap-2">
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-filetype-php text-accent fs-5"></i>
                                    <strong>PHP 8 Native</strong> — Routing & Sessions
                                </span>
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-bootstrap-fill text-accent fs-5"></i>
                                    <strong>Bootstrap 5.3</strong> — Responsive Grid
                                </span>
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-filetype-html text-accent fs-5"></i>
                                    <strong>HTML5 / CSS3</strong> — Design Law Tokens
                                </span>
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-filetype-js text-accent fs-5"></i>
                                    <strong>JavaScript (ES6)</strong> — Dynamic Meter & UI
                                </span>
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-database-slash text-accent fs-5"></i>
                                    <strong>JSON Datastore</strong> — Zero-DB Persistence
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
