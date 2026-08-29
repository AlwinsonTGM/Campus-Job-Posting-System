<?php
/**
 * Campus Job Posting System - PDF Resume Viewer & Document Server
 * Allows employers, admins, and student owners to view attached PDF resumes.
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

require_auth();
$current_user = get_logged_user();

$app_id = $_GET['app_id'] ?? ($_GET['id'] ?? null);
$user_id = $_GET['user_id'] ?? null;
$file_param = $_GET['file'] ?? null;
$force_html = isset($_GET['render_html']) && $_GET['render_html'] == '1';

$app = null;
$target_student = null;
$resume_filename = 'Student_Resume.pdf';

if ($app_id) {
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    foreach ($apps as $a) {
        if ($a['id'] == $app_id) {
            $app = $a;
            $resume_filename = $a['resume_file'] ?? 'Student_Resume.pdf';
            break;
        }
    }

    if (!$app) {
        http_response_code(404);
        die('The specified student application could not be found.');
    }

    if (!can_view_student_resume($app, null, $current_user)) {
        http_response_code(403);
        die('Access Denied: You are not authorized to view this candidate credential.');
    }

    $users = $_SESSION['users'] ?? load_json_file('users.json');
    foreach ($users as $u) {
        if ($u['id'] == ($app['student_id'] ?? 0) || $u['email'] === ($app['student_email'] ?? '')) {
            $target_student = $u;
            break;
        }
    }
} elseif ($user_id) {
    if (!can_view_student_resume(null, $user_id, $current_user)) {
        http_response_code(403);
        if (($current_user['role'] ?? '') === 'employer') {
            die('Access Denied: You are not authorized to view this candidate credential.');
        } else {
            die('Access Denied: You are not authorized to inspect other student records.');
        }
    }

    $users = $_SESSION['users'] ?? load_json_file('users.json');
    foreach ($users as $u) {
        if ($u['id'] == $user_id) {
            $target_student = $u;
            $resume_filename = ($u['name'] ?? 'Student') . '_Resume.pdf';
            break;
        }
    }

    if (!$target_student) {
        http_response_code(404);
        die('Student record not found.');
    }
} else {
    $target_student = $current_user;
    if ($file_param) {
        $resume_filename = basename($file_param);
    }
}

$student_name = $app['student_name'] ?? ($target_student['name'] ?? 'Juan Dela Cruz');
$student_number = $app['student_number'] ?? ($target_student['student_id'] ?? '2024-00123');
$student_email = $app['student_email'] ?? ($target_student['email'] ?? 'student@kld.edu.ph');
$student_course = $app['course'] ?? ($target_student['course'] ?? 'BS Information Systems (BSIS)');
$student_year = $app['year_level'] ?? ($target_student['year_level'] ?? '2nd Year');
$student_phone = $app['phone'] ?? ($target_student['phone'] ?? '+63 917 123 4567');
$student_sex = $app['sex'] ?? ($target_student['sex'] ?? 'Male');
$student_age = $app['age'] ?? ($target_student['age'] ?? 21);
$cover_letter = $app['cover_letter'] ?? 'Eager to contribute technical, administrative, and organizational capabilities to campus office operations while maintaining strong academic standing.';
$availability = $app['availability'] ?? ($target_student['availability'] ?? [
    'Monday - Morning (8AM–12NN)',
    'Wednesday - Morning (8AM–12NN)',
    'Friday - Afternoon (1PM–5PM)'
]);

$candidate_paths = [
    __DIR__ . '/' . ltrim($resume_filename, '/'),
    __DIR__ . '/uploads/resumes/' . basename($resume_filename),
    __DIR__ . '/uploads/proofs/' . basename($resume_filename),
    __DIR__ . '/uploads/resumes/Juan_Dela_Cruz_Resume.pdf',
    __DIR__ . '/uploads/proofs/proof_1787818447_8a7d0655.pdf'
];

$physical_pdf_path = null;
if (!$force_html) {
    foreach ($candidate_paths as $p) {
        if (file_exists($p) && is_file($p) && strtolower(pathinfo($p, PATHINFO_EXTENSION)) === 'pdf') {
            $physical_pdf_path = $p;
            break;
        }
    }
}

if ($physical_pdf_path && file_exists($physical_pdf_path)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($resume_filename) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . filesize($physical_pdf_path));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    readfile($physical_pdf_path);
    exit;
}
?>
<!DOCTYPE html>
<html lang=en>
<head>
    <meta charset=UTF-8>
    <meta name=viewport content=width=device-width, initial-scale=1.0>
    <title><?= htmlspecialchars($student_name) ?> - Official Student Resume (KLD)</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="apple-touch-icon" href="assets/img/favicon.svg">
    <link rel=preconnect href=https://fonts.googleapis.com>
    <link rel=preconnect href=https://fonts.gstatic.com crossorigin>
    <link href=https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap rel=stylesheet>
    <link rel=stylesheet href=https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css>
    <link rel=stylesheet href=https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css>
    <style>
        :root {
            --kld-green: #0a3d24;
            --kld-accent: #0f5132;
            --kld-gold: #c59b27;
            --ink: #1a1a1a;
            --paper: #ffffff;
            --cream: #fbfbf9;
            --line: #e2e4e0;
        }
        body {
            background-color: #525659;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            padding: 30px 15px;
            margin: 0;
        }
        .resume-page {
            background: #ffffff;
            max-width: 850px;
            min-height: 1100px;
            margin: 0 auto 30px;
            padding: 48px 56px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.35);
            border-radius: 4px;
            position: relative;
        }
        .resume-header {
            border-bottom: 2px solid var(--kld-green);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .resume-name {
            font-family: 'Cinzel', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--kld-green);
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .resume-sub {
            font-size: 13.5px;
            color: #4a5568;
            font-weight: 500;
        }
        .section-heading {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--kld-green);
            border-bottom: 1.5px solid var(--line);
            padding-bottom: 5px;
            margin-top: 24px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .badge-tag {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11.5px;
            border-radius: 4px;
            background-color: #eef7f2;
            color: var(--kld-green);
            border: 1px solid #cce8d7;
            font-weight: 500;
        }
        .matrix-pill {
            background: #f8faf9;
            border: 1px solid var(--line);
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 6px;
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
            color: #2d3748;
        }
        .floating-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            display: flex;
            gap: 10px;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .floating-controls {
                display: none !important;
            }
            .resume-page {
                box-shadow: none;
                margin: 0;
                padding: 30px;
                max-width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <div class=floating-controls no-print>
    <div class="floating-controls no-print">
        <button type="button" class="btn btn-dark btn-sm shadow d-flex align-items-center gap-1 px-3 py-2" onclick="window.print()">
            <i class="bi bi-printer-fill"></i> Print / Save as PDF
        </button>
        <button type="button" class="btn btn-light btn-sm shadow d-flex align-items-center gap-1 px-3 py-2" onclick="window.close()">
            <i class="bi bi-x-lg"></i> Close
        </button>
    </div>

    <div class="resume-page">
        <div class="resume-header d-flex justify-content-between align-items-start">
            <div>
                <div class="resume-name"><?= htmlspecialchars($student_name) ?></div>
                <div class="resume-sub">
                    <?= htmlspecialchars($student_course) ?> &bull; <?= htmlspecialchars($student_year) ?>
                </div>
                <div class="small text-muted mt-1">
                    Student ID: <strong class="text-dark"><?= htmlspecialchars($student_number) ?></strong> &bull; <?= htmlspecialchars($student_sex) ?>, <?= htmlspecialchars((string)$student_age) ?> yrs old
                </div>
            </div>
            <div class="text-end small">
                <div class="fw-semibold text-dark"><i class="bi bi-envelope-fill text-success me-1"></i><?= htmlspecialchars($student_email) ?></div>
                <div class="text-muted"><i class="bi bi-telephone-fill text-success me-1"></i><?= htmlspecialchars($student_phone) ?></div>
                <div class="text-muted mt-1">Kolehiyo ng Lungsod ng Dasmariñas</div>
            </div>
        </div>

        <div class="section-heading">
            <i class="bi bi-bullseye"></i> Statement of Intent & Career Objective
        </div>
        <p class="small text-secondary lh-lg mb-3">
            <?= nl2br(htmlspecialchars($cover_letter)) ?>
        </p>

        <div class="section-heading">
            <i class="bi bi-mortarboard-fill"></i> Academic Education
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-baseline">
                <strong class="text-dark fs-6">Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong>
                <span class="small text-muted">2024 – Present</span>
            </div>
            <div class="small text-secondary">
                <?= htmlspecialchars($student_course) ?> &bull; <?= htmlspecialchars($student_year) ?>
            </div>
            <div class="small text-muted mt-1">
                Institute of Computing and Digital Innovation (ICDI) &bull; In Good Academic Standing (GWA: 1.45)
            </div>
        </div>

        <div class="section-heading">
            <i class="bi bi-calendar-check-fill"></i> Class-Free Shift Availability Matrix
        </div>
        <div class="mb-3">
            <p class="small text-muted mb-2">Verified weekly timeslots free from enrolled lecture and laboratory courses (Max 20 hrs/week):</p>
            <div>
                <?php if (!empty($availability)): ?>
                    <?php foreach ($availability as $slot): ?>
                        <span class="matrix-pill">
                            <i class="bi bi-check-circle-fill text-success me-1"></i><?= htmlspecialchars($slot) ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="small text-muted">Flexible Schedule Available Upon Request</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-heading">
            <i class="bi bi-tools"></i> Core Competencies & Skills
        </div>
        <div class="mb-3 d-flex flex-wrap gap-2">
            <span class="badge-tag">Office Clerical & Data Encoding</span>
            <span class="badge-tag">PC Hardware & Software Troubleshooting</span>
            <span class="badge-tag">Local Area Network (LAN) Configuration</span>
            <span class="badge-tag">Google Workspace & MS Office 365</span>
            <span class="badge-tag">HTML5 / CSS3 / Web Technologies</span>
            <span class="badge-tag">Student Support & Campus Records Management</span>
        </div>

        <div class="section-heading">
            <i class="bi bi-shield-check"></i> Institutional Verification
        </div>
        <div class="p-3 bg-light rounded border small text-muted">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-patch-check-fill text-success fs-5"></i>
                <strong class="text-dark">KLD Career & Student Assistantship Service Office</strong>
            </div>
            This document is the official digital student curriculum vitae generated for the KLD Campus Job Posting System in compliance with the Philippine Data Privacy Act of 2012 (RA 10173).
        </div>

    </div>

</body>
</html>
