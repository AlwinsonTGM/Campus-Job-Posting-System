<?php
/**
 * Campus Job Posting System - About Us / Developers Page
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'About the Development Team';

// 6 Developer Profiles Configuration
$team_members = [
    [
        'name' => 'Member 1 (Project Lead)',
        'role' => 'Lead System Architect & Core Backend',
        'student_id' => '2024-00101',
        'section' => 'BSIS 2-A',
        'email' => 'lead.developer@kld.edu.ph',
        'image' => 'assets/img/developers/member1.svg',
        'bio' => 'Oversees the end-to-end system architecture, routing logic, modular template structure, and data engine.',
        'tasks' => ['System Routing & State Engine', 'Architecture Blueprint', 'Session Handlers']
    ],
    [
        'name' => 'Member 2 (Frontend Lead)',
        'role' => 'Public Suite & Legal Compliance Specialist',
        'student_id' => '2024-00102',
        'section' => 'BSIS 2-A',
        'email' => 'frontend.lead@kld.edu.ph',
        'image' => 'assets/img/developers/member2.svg',
        'bio' => 'Designs and implements the public interface, responsive landing page, Data Privacy Policy, and Terms of Service.',
        'tasks' => ['Index & Landing Page', 'Data Privacy (RA 10173)', 'Terms of Service Page']
    ],
    [
        'name' => 'Member 3 (Security Specialist)',
        'role' => 'Authentication & Client Validation Engineer',
        'student_id' => '2024-00103',
        'section' => 'BSIS 2-A',
        'email' => 'security.engineer@kld.edu.ph',
        'image' => 'assets/img/developers/member3.svg',
        'bio' => 'Specializes in user authentication flows, dynamic real-time password strength algorithms, and account security.',
        'tasks' => ['Password Strength Meter JS', 'Multi-Role Login & Register', 'Forgot Password Flow']
    ],
    [
        'name' => 'Member 4 (Student UX Specialist)',
        'role' => 'Student Portal & Application Flow Engineer',
        'student_id' => '2024-00104',
        'section' => 'BSIS 2-A',
        'email' => 'student.ux@kld.edu.ph',
        'image' => 'assets/img/developers/member4.svg',
        'bio' => 'Crafts the student dashboard, job browsing with live filters, job application modal, and application status tracker.',
        'tasks' => ['Student Dashboard', 'Job Filter & Details', 'My Applications Tracker']
    ],
    [
        'name' => 'Member 5 (Employer UX Specialist)',
        'role' => 'Department & Hiring Workflow Engineer',
        'student_id' => '2024-00105',
        'section' => 'BSIS 2-A',
        'email' => 'employer.ux@kld.edu.ph',
        'image' => 'assets/img/developers/member5.svg',
        'bio' => 'Builds the campus office portal, vacancy posting forms, candidate evaluation drawers, and interview scheduling triggers.',
        'tasks' => ['Employer Dashboard', 'Create & Edit Job Forms', 'Applicant Review Suite']
    ],
    [
        'name' => 'Member 6 (Admin & QA Specialist)',
        'role' => 'System Administration & QA Lead',
        'student_id' => '2024-00106',
        'section' => 'BSIS 2-A',
        'email' => 'admin.qa@kld.edu.ph',
        'image' => 'assets/img/developers/member6.svg',
        'bio' => 'Manages administrative category controls, user accounts, printable analytics reports, and cross-browser quality assurance.',
        'tasks' => ['Categories & User Control', 'Printable Analytics Reports', 'Mobile QA & Validation']
    ]
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5 bg-surface flex-grow-1">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center max-w-700 mx-auto mb-5">
            <div class="mb-2">
                <span class="pill-badge">
                    <i class="bi bi-code-slash text-accent"></i> KLD ICDI &bull; BSIS 2-A Midterm Lab Team
                </span>
            </div>
            <h1 class="fw-bold text-ink mb-3">Meet the Development Team</h1>
            <p class="text-muted-custom lead fs-6">
                We are a 6-member team of 2nd Year Bachelor of Science in Information Systems (BSIS) students from the <strong>Institute of Computing and Digital Innovation (ICDI)</strong> at <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong> who built the <strong>Campus Job Posting System</strong> for our <em>COAL101: Web Systems and Technologies</em> midterm lab project.
            </p>
        </div>

        <!-- Photo & Professional Standards Banner -->
        <div class="card border-line shadow-sm d-flex flex-row align-items-center gap-3 mb-5 p-3 rounded-4 bg-white">
            <div class="stat-icon bg-accent-soft text-ink fs-3 ms-2">
                <i class="bi bi-camera-fill"></i>
            </div>
            <div>
                <strong class="d-block text-ink mb-1">Developer Photo Standards Policy</strong>
                <span class="small text-muted-custom">
                    All developer profiles feature formal institutional blazers and attire with clean backgrounds, strictly free of casual headwear, sunglasses, headphones, or accessories to uphold campus professional standards.
                </span>
            </div>
        </div>

        <!-- 6 Developer Cards Grid -->
        <div class="row g-4 mb-5" id="team">
            <?php foreach ($team_members as $index => $dev): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="developer-card">
                        <div class="developer-avatar-wrapper">
                            <img src="<?= $base_url . $dev['image'] ?>" alt="<?= htmlspecialchars($dev['name']) ?>" class="developer-avatar">
                            <div class="mt-2">
                                <span class="formal-badge-note">
                                    <i class="bi bi-shield-check me-1 text-accent"></i> Attire Verified
                                </span>
                            </div>
                        </div>

                        <div class="p-4 text-center">
                            <h5 class="fw-bold text-ink mb-1"><?= htmlspecialchars($dev['name']) ?></h5>
                            <span class="pill-badge pill-badge-ink mb-3"><?= htmlspecialchars($dev['role']) ?></span>

                            <div class="text-muted-custom small mb-3">
                                <div><i class="bi bi-person-badge me-1 text-accent"></i> Student ID: <strong><?= htmlspecialchars($dev['student_id']) ?></strong></div>
                                <div><i class="bi bi-mortarboard me-1 text-accent"></i> Section: <strong><?= htmlspecialchars($dev['section']) ?></strong></div>
                                <div class="text-break"><i class="bi bi-envelope me-1 text-accent"></i> <?= htmlspecialchars($dev['email']) ?></div>
                            </div>

                            <p class="text-muted-custom small mb-3 text-start bg-cream p-3 rounded-3">
                                <?= htmlspecialchars($dev['bio']) ?>
                            </p>

                            <div class="text-start">
                                <span class="small fw-bold text-ink d-block mb-1">Key Deliverables:</span>
                                <ul class="list-unstyled small text-muted-custom mb-0">
                                    <?php foreach ($dev['tasks'] as $task): ?>
                                        <li><i class="bi bi-check2 text-accent me-1"></i> <?= htmlspecialchars($task) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Mission & Tech Stack Section -->
        <div class="row g-4 pt-3">
            <div class="col-lg-6">
                <div class="card h-100 border-line shadow-sm p-4 rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon bg-accent-soft text-ink">
                            <i class="bi bi-compass"></i>
                        </div>
                        <h4 class="fw-bold text-ink mb-0">Our Project Mission</h4>
                    </div>
                    <p class="text-muted-custom">
                        To simplify and digitize the student assistantship application workflow across campus institutes and offices. By replacing manual paperwork and unorganized bulletin boards with an automated portal, we empower students to gain valuable workplace experience while prioritizing their studies.
                    </p>
                    <hr class="border-line">
                    <h6 class="fw-bold text-ink mb-2">Academic Alignment</h6>
                    <p class="text-muted-custom small mb-0">
                        Course: <strong>COAL101 - Web Systems and Technologies</strong><br>
                        Activity: <strong>Midterm Lab Project</strong><br>
                        Submission Deadline: <strong>September 2, 2026</strong><br>
                        Institution: <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong>
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100 border-line shadow-sm p-4 rounded-4 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon bg-cream text-ink">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                        <h4 class="fw-bold text-ink mb-0">Technical Architecture</h4>
                    </div>
                    <p class="text-muted-custom mb-3">
                        Built adhering strictly to the required project stack constraints without external heavy frameworks:
                    </p>
                    
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2 px-3 bg-cream rounded-3">
                            <span><i class="bi bi-filetype-php text-accent me-2 fs-5"></i><strong>Native PHP 8.x</strong></span>
                            <span class="pill-badge">Backend & Session State</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 px-3 bg-cream rounded-3">
                            <span><i class="bi bi-bootstrap-fill text-accent me-2 fs-5"></i><strong>Bootstrap 5.3 + Icons</strong></span>
                            <span class="pill-badge">Responsive UI & Layout</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 px-3 bg-cream rounded-3">
                            <span><i class="bi bi-filetype-js text-ink me-2 fs-5"></i><strong>Vanilla JavaScript (ES6)</strong></span>
                            <span class="pill-badge">Real-time Meter & UI</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 px-3 bg-cream rounded-3">
                            <span><i class="bi bi-database-slash text-ink me-2 fs-5"></i><strong>JSON + Session Engine</strong></span>
                            <span class="pill-badge">Zero-DB Persistence</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
