<?php
/**
 * Campus Job Posting System - About Us / Developers Page
 */
require_once __DIR__ . '/includes/data-helper.php';

$page_title = 'About the Development Team';

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
        'image' => 'assets/img/developers/member3.svg',
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
        'student_id' => '2024-00105',
        'section' => 'BSIS201',
        'email' => 'employer.ux@kld.edu.ph',
        'image' => 'assets/img/developers/member5.svg',
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
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-kld-soft text-kld-green border border-success-subtle px-3 py-2 fw-bold text-uppercase mb-2">
                <i class="bi bi-code-slash"></i> KLD ICDI &bull; BSIS201 Midterm Lab Team
            </span>
            <h1 class="fw-bold text-dark mb-3">Meet the Development Team</h1>
            <p class="text-muted lead fs-6">
                We are a 6-member team of 2nd Year Bachelor of Science in Information Systems (BSIS) students from the <strong>Institute of Computing and Digital Innovation (ICDI)</strong> at <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong> who built the <strong>KLD Campus Job Posting System</strong> for our <em>COAL101: Web Systems and Technologies</em> midterm lab project.
            </p>
        </div>

        <!-- Photo & Professional Standards Banner -->
        <div class="alert alert-light border border-success-subtle shadow-sm d-flex align-items-center gap-3 mb-5 p-3 rounded-3">
            <div class="fs-2 text-kld-green"><i class="bi bi-camera-fill"></i></div>
            <div>
                <strong class="d-block text-dark mb-1">Developer Photo Standards Policy</strong>
                <span class="small text-secondary">
                    All developer profiles feature formal KLD Green institutional blazers and attire with clean backgrounds, strictly free of casual headwear, sunglasses, headphones, or accessories to uphold campus professional standards.
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
                                    <i class="bi bi-shield-check me-1 text-warning"></i> KLD Attire Verified
                                </span>
                            </div>
                        </div>

                        <div class="p-4 text-center">
                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($dev['name']) ?></h5>
                            <span class="badge text-white mb-3" style="background-color: var(--kld-green-primary);"><?= htmlspecialchars($dev['role']) ?></span>

                            <div class="text-muted small mb-3">
                                <div><i class="bi bi-person-badge me-1 text-kld-green"></i> Student ID: <strong><?= htmlspecialchars($dev['student_id']) ?></strong></div>
                                <div><i class="bi bi-mortarboard me-1 text-kld-green"></i> Section: <strong><?= htmlspecialchars($dev['section']) ?></strong></div>
                                <div class="text-break"><i class="bi bi-envelope me-1 text-kld-green"></i> <?= htmlspecialchars($dev['email']) ?></div>
                            </div>

                            <p class="text-secondary small mb-3 text-start bg-light p-3 rounded-2">
                                <?= htmlspecialchars($dev['bio']) ?>
                            </p>

                            <div class="text-start">
                                <span class="small fw-bold text-dark d-block mb-1">Key Deliverables:</span>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <?php foreach ($dev['tasks'] as $task): ?>
                                        <li><i class="bi bi-check2 text-kld-green me-1"></i> <?= htmlspecialchars($task) ?></li>
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
                <div class="card h-100 border-0 shadow-sm p-4 rounded-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon stat-icon-kld">
                            <i class="bi bi-compass"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-0">Our Project Mission</h4>
                    </div>
                    <p class="text-secondary">
                        To simplify and digitize the student assistantship application workflow across KLD institutes and offices. By replacing manual paperwork and unorganized bulletin boards with an automated portal, we empower KLD students to gain valuable workplace experience while prioritizing their studies.
                    </p>
                    <hr>
                    <h6 class="fw-bold text-dark mb-2">Academic Alignment</h6>
                    <p class="text-muted small mb-0">
                        Course: <strong>COAL101 - Web Systems and Technologies</strong><br>
                        Activity: <strong>Midterm Lab Project</strong><br>
                        Submission Deadline: <strong>September 2, 2026</strong><br>
                        Institution: <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong>
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm p-4 rounded-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon stat-icon-kld-gold">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-0">Technical Architecture</h4>
                    </div>
                    <p class="text-secondary mb-3">
                        Built adhering strictly to the required project stack constraints without external heavy frameworks:
                    </p>
                    
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                            <span><i class="bi bi-filetype-php text-kld-green me-2 fs-5"></i><strong>Native PHP 8.x</strong></span>
                            <span class="badge bg-kld-soft text-kld-green border border-success-subtle">Backend & Session State</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                            <span><i class="bi bi-bootstrap-fill text-kld-green me-2 fs-5"></i><strong>Bootstrap 5.3 + Icons</strong></span>
                            <span class="badge bg-kld-soft text-kld-green border border-success-subtle">Responsive UI & Layout</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                            <span><i class="bi bi-filetype-js text-kld-gold me-2 fs-5"></i><strong>Vanilla JavaScript (ES6)</strong></span>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle">Real-time Password Meter & UI</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                            <span><i class="bi bi-database-slash text-danger me-2 fs-5"></i><strong>JSON + Session Engine</strong></span>
                            <span class="badge bg-success-subtle text-success">Zero-DB Persistence</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
