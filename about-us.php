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
        'bio' => 'Manages administrative category controls, user accounts, printable analytics reports, and cross-browser quality assurance.',
        'tasks' => ['Categories & User Control', 'Printable Analytics Reports', 'Mobile QA & Validation']
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
                    '<i class="bi bi-code-slash text-accent me-1"></i> KLD ICDI &bull; BSIS201 Midterm Lab Team',
                    'Meet the Development Team',
                    'We are a 6-member team of 2nd Year Bachelor of Science in Information Systems (BSIS) students from the Institute of Computing and Digital Innovation (ICDI) at Kolehiyo ng Lungsod ng Dasmariñas (KLD) who built the KLD Campus Job Posting System for our COAL101: Web Systems and Technologies midterm lab project.'
                );
                ?>

                <!-- Developer Photo Specifications Compliance Note -->
                <div class="card-paper mb-5 bg-cream">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle icon-circle-accent">
                            <i class="bi bi-patch-check-fill text-accent"></i>
                        </div>
                        <div>
                            <h4 class="card-paper-title mb-1">Developer Photo Standards Policy</h4>
                            <p class="text-muted-custom small mb-0">
                                All developer profiles feature formal KLD Green institutional blazers and attire with clean backgrounds, strictly free of casual headwear, sunglasses, headphones, or accessories to uphold campus professional standards.
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
                                <div class="dev-card-role"><?= htmlspecialchars($dev['role']) ?></div>

                                <div class="dev-card-meta">
                                    <div><i class="bi bi-person-badge me-1 text-accent"></i> Student ID: <strong><?= htmlspecialchars($dev['student_id']) ?></strong></div>
                                    <div><i class="bi bi-mortarboard me-1 text-accent"></i> Section: <strong><?= htmlspecialchars($dev['section']) ?></strong></div>
                                    <div class="text-truncate mt-1">
                                        <a href="mailto:<?= htmlspecialchars($dev['email']) ?>" class="text-muted-custom text-decoration-none" title="<?= htmlspecialchars($dev['email']) ?>">
                                            <i class="bi bi-envelope me-1 text-accent"></i><?= htmlspecialchars($dev['email']) ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="dev-card-bio">
                                    <span><?= htmlspecialchars($dev['bio']) ?></span>
                                </div>

                                <div class="dev-card-scope text-start">
                                    <span class="small fw-bold text-ink d-block mb-1">Key Deliverables:</span>
                                    <ul class="list-unstyled small text-muted-custom mb-0">
                                        <?php foreach ($dev['tasks'] as $task): ?>
                                            <li class="mb-1"><i class="bi bi-check2 text-accent me-1"></i> <?= htmlspecialchars($task) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
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
                                <h3 class="card-paper-title mb-0">Our Project Mission</h3>
                            </div>
                            <p class="text-muted-custom mb-3">
                                To simplify and digitize the student assistantship application workflow across KLD institutes and offices. By replacing manual paperwork and unorganized bulletin boards with an automated portal, we empower KLD students to gain valuable workplace experience while prioritizing their studies.
                            </p>
                            <div class="p-3 bg-cream rounded-3 border border-line">
                                <span class="d-block small fw-bold text-ink mb-1">Academic Alignment</span>
                                <div class="small text-muted-custom d-flex flex-column gap-1">
                                    <div>Course: <strong>COAL101 - Web Systems and Technologies</strong></div>
                                    <div>Activity: <strong>Midterm Lab Project</strong></div>
                                    <div>Submission Deadline: <strong>September 2, 2026</strong></div>
                                    <div>Institution: <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong></div>
                                </div>
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
                                <h3 class="card-paper-title mb-0">Technical Architecture</h3>
                            </div>
                            <p class="text-muted-custom mb-3">
                                Built adhering strictly to the required project stack constraints without external heavy frameworks:
                            </p>
                            
                            <div class="d-flex flex-wrap gap-2">
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-filetype-php text-accent fs-5"></i>
                                    <strong>Native PHP 8.x</strong> — Backend & Session State
                                </span>
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-bootstrap-fill text-accent fs-5"></i>
                                    <strong>Bootstrap 5.3 + Icons</strong> — Responsive UI & Layout
                                </span>
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-filetype-css text-accent fs-5"></i>
                                    <strong>CSS3 Tokens</strong> — Custom Styling & Theme
                                </span>
                                <span class="chip chip-selectable p-2 px-3">
                                    <i class="bi bi-filetype-js text-accent fs-5"></i>
                                    <strong>Vanilla JavaScript (ES6)</strong> — Real-time Password Meter & UI
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
