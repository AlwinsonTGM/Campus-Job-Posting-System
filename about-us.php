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
// Last line: view template
require __DIR__ . '/includes/templates/about-us-view.php';

