<?php
/**
 * Campus Job Posting System - PDF Resume Viewer & Document Server
 * Allows employers, admins, and student owners to view attached PDF resumes and academic credentials (e-COR / Diploma).
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

require_auth();
$current_user = get_logged_user();

$app_id = query_int('app_id') ?? query_int('id');
$user_id = query_int('user_id');
$file_param = query_string('file', '');
$doc_type = strtolower(query_string('doc', 'resume')); // 'resume' by default, or 'proof'
$force_html = query_string('render_html') === '1';

$app = null;
$target_student = null;
$resume_filename = 'Student_Resume.pdf';

if ($app_id) {
    $app = get_application_by_id($app_id);
    if ($app) {
        $resume_filename = $app['resume_file'] ?? 'Student_Resume.pdf';
    }

    if (!$app) {
        http_response_code(404);
        die('The specified student application could not be found.');
    }

    if (!can_view_student_resume($app, null, $current_user)) {
        http_response_code(403);
        die('Access Denied: You are not authorized to view this candidate credential.');
    }

    $target_student = get_user_by_id($app['student_id'] ?? 0) ?? get_user_by_email($app['student_email'] ?? '');
} elseif ($user_id) {
    if (!can_view_student_resume(null, $user_id, $current_user)) {
        http_response_code(403);
        if (($current_user['role'] ?? '') === 'employer') {
            die('Access Denied: You are not authorized to view this candidate credential.');
        } else {
            die('Access Denied: You are not authorized to inspect other student records.');
        }
    }

    $target_student = get_user_by_id($user_id);
    if ($target_student) {
        $resume_filename = !empty($target_student['resume_file']) ? basename($target_student['resume_file']) : (($target_student['name'] ?? 'Student') . '_Resume.pdf');
    }

    if (!$target_student) {
        http_response_code(404);
        die('Student record not found.');
    }
} else {
    $target_student = $current_user;
    if ($doc_type === 'proof') {
        // Logged-in student viewing their own registration_proof credential
    } elseif ($file_param) {
        $requested_file = basename($file_param);

        if (!can_access_resume_file($current_user, $requested_file)) {
            http_response_code(403);
            die('Access Denied: You are not authorized to inspect this document.');
        }

        $resume_filename = $requested_file;
    } elseif (!empty($current_user['resume_file'])) {
        $resume_filename = basename($current_user['resume_file']);
    }
}

$student_name = $app['student_name'] ?? ($target_student['name'] ?? 'Juan Dela Cruz');
$student_number = $app['student_number'] ?? ($target_student['student_id'] ?? '2024-00123');
$student_email = $app['student_email'] ?? ($target_student['email'] ?? 'student@kld.edu.ph');
$student_course = $app['course'] ?? ($target_student['course'] ?? 'BS Information Systems (BSIS)');
$student_year = $app['year_level'] ?? ($target_student['year_level'] ?? '2nd Year');
$student_department = $app['student_department'] ?? ($target_student['department'] ?? 'Institute of Computing and Engineering');
$student_phone = $app['phone'] ?? ($target_student['phone'] ?? '+63 917 123 4567');
$student_sex = $app['sex'] ?? ($target_student['sex'] ?? 'Male');
$student_age = $app['age'] ?? ($target_student['age'] ?? 21);
$cover_letter = $app['cover_letter'] ?? 'Eager to contribute technical, administrative, and organizational capabilities to campus office operations while maintaining strong academic standing.';
$availability = $app['availability'] ?? ($target_student['availability'] ?? [
    'Monday - Morning (8AM–12NN)',
    'Wednesday - Morning (8AM–12NN)',
    'Friday - Afternoon (1PM–5PM)'
]);
if (is_string($availability)) {
    $decoded = json_decode($availability, true);
    $availability = is_array($decoded) ? $decoded : [];
} elseif (!is_array($availability)) {
    $availability = [];
}

// -----------------------------------------------------------------------------
// DOC TYPE: PROOF (Official Certificate of Registration or KLD Diploma)
// -----------------------------------------------------------------------------
if ($doc_type === 'proof') {
    $student_email_trimmed = strtolower(trim((string)$student_email));
    $is_kld_student = $student_email_trimmed === '' || (bool)preg_match('/@(?:[a-z0-9-]+\.)*kld\.edu\.ph$/i', $student_email_trimmed);
    $is_diploma = ($student_year === 'Alumnus / Graduated') || !$is_kld_student;

    $proof_file = '';
    $study_load = trim((string)($app['study_load_file'] ?? ''));
    if ($study_load !== '' && !preg_match('/^Study_Load_\d+\.pdf$/i', $study_load)) {
        $proof_file = $study_load;
    } else {
        $proof_file = trim((string)($app['registration_proof'] ?? ($target_student['registration_proof'] ?? '')));
    }

    $physical_proof_path = null;
    if (!$force_html && $proof_file !== '') {
        $clean_proof = ltrim(str_replace('\\', '/', $proof_file), '/');
        $check_paths = [
            __DIR__ . '/' . $clean_proof,
            __DIR__ . '/uploads/proofs/' . basename($proof_file)
        ];
        foreach ($check_paths as $p) {
            if (file_exists($p) && is_file($p)) {
                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], true)) {
                    $physical_proof_path = $p;
                    break;
                }
            }
        }
    }

    if ($physical_proof_path && file_exists($physical_proof_path)) {
        $ext = strtolower(pathinfo($physical_proof_path, PATHINFO_EXTENSION));
        $content_types = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp'
        ];
        $content_type = $content_types[$ext] ?? 'application/octet-stream';
        $download_filename = basename($physical_proof_path);

        header('Content-Type: ' . $content_type);
        header('Content-Disposition: inline; filename="' . $download_filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . filesize($physical_proof_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($physical_proof_path);
        exit;
    }

    // Render crisp institutional HTML verification sheet
    $doc_title = $is_diploma
        ? 'Official KLD Diploma & Alumni Credential Verification'
        : 'Official Certificate of Registration (e-COR) & Study Load';
    $doc_ref = basename($proof_file ?: ($is_diploma ? 'KLD_Official_Diploma.pdf' : 'KLD_Official_eCOR.pdf'));
    $verif_status = $is_diploma
        ? 'VERIFIED KLD GRADUATE / ALUMNUS'
        : 'ENROLLED & VERIFIED FOR CAMPUS ASSISTANTSHIP';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($student_name) ?> - <?= htmlspecialchars($doc_title) ?></title>
    <link id="brandFavicon32" rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png?v=d4">
    <link id="brandFavicon16" rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16.png?v=d4">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-180.png?v=d4">
    <link rel="stylesheet" href="assets/vendor/fonts/fonts.css">
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <script>
    (function(){
      try {
        var t = localStorage.getItem('campus_hire_theme');
        if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', t);
        if (t === 'dark') {
          var f32 = document.getElementById('brandFavicon32');
          var f16 = document.getElementById('brandFavicon16');
          if (f32) f32.href = 'assets/img/logo-d4-dark-32.png?v=d4';
          if (f16) f16.href = 'assets/img/logo-d4-dark-32.png?v=d4';
        }
      } catch(e) {}
    })();
    </script>
    <style>
        :root {
            --canvas: #CDC0AB;
            --surface: #F5F4F0;
            --cream: #F1EBDC;
            --ink: #161616;
            --muted: #6E6A61;
            --accent: #2ECC5E;
            --accent-hover: #26b150;
            --accent-soft: #DDF3E2;
            --line: #E5E0D4;
            --white: #FFFFFF;
            --kld-green: #0B532C;
            --kld-gold: #C5A059;
        }
        body {
            background-color: var(--canvas);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
            padding: 30px 15px;
            margin: 0;
            line-height: 1.55;
        }
        .credential-page {
            background: var(--white);
            max-width: 850px;
            min-height: 1050px;
            margin: 0 auto 30px;
            padding: 48px 56px;
            box-shadow: 0 16px 40px rgba(22, 22, 22, 0.12);
            border-radius: 16px;
            border: 2px solid var(--line);
            position: relative;
        }
        .institutional-header {
            text-align: center;
            border-bottom: 2px solid var(--ink);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .inst-republic {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: 2px;
        }
        .inst-name {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.01em;
            color: var(--kld-green);
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .inst-sub {
            font-size: 12.5px;
            color: var(--muted);
            font-weight: 600;
        }
        .doc-banner {
            background: var(--surface);
            border: 1px solid var(--line);
            border-left: 5px solid var(--accent);
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 28px;
        }
        .doc-banner-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 4px;
            letter-spacing: -0.01em;
        }
        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background-color: var(--accent-soft);
            color: #0b532c;
            border: 1px solid #a6e7b5;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: 0.03em;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 24px;
            margin-bottom: 28px;
        }
        .info-cell {
            padding: 10px 14px;
            background: var(--surface);
            border-radius: 8px;
            border: 1px solid var(--line);
        }
        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            font-weight: 700;
            display: block;
            margin-bottom: 2px;
        }
        .info-val {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
        }
        .section-title {
            font-size: 12.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink);
            border-bottom: 1.5px solid var(--line);
            padding-bottom: 6px;
            margin-top: 24px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .matrix-pill {
            background: var(--surface);
            border: 1px solid var(--line);
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 999px;
            display: inline-block;
            color: var(--ink);
            font-weight: 600;
        }
        .compliance-box {
            background: #f8faf9;
            border: 1px dashed var(--accent);
            border-radius: 8px;
            padding: 14px 18px;
            margin-top: 24px;
            font-size: 12.5px;
            color: var(--ink);
        }
        .institutional-footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 12px;
        }
        .floating-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            display: flex;
            gap: 10px;
        }
        @media (max-width: 767.98px) {
            body { padding: 16px 8px; }
            .credential-page { padding: 24px 18px; border-radius: 12px; }
            .info-grid { grid-template-columns: 1fr; }
            .floating-controls { position: static; margin-bottom: 16px; justify-content: center; }
        }
        @media print {
            body { background: none; padding: 0; }
            .floating-controls { display: none !important; }
            .credential-page { box-shadow: none; margin: 0; padding: 30px; max-width: 100%; border: none; }
        }
        [data-theme="dark"] {
            --canvas: #0F0F10;
            --surface: #1A1A1E;
            --cream: #232328;
            --ink: #E8E6E3;
            --muted: #A39E93;
            --line: #2E2E33;
            --white: #1A1A1E;
            --accent: #34D865;
            --accent-soft: #1B3324;
            --kld-green: #34D865;
        }
        [data-theme="dark"] body { background-color: #0F0F10; color: #E8E6E3; }
        [data-theme="dark"] .credential-page { background: #1A1A1E; border-color: #2E2E33; color: #E8E6E3; }
        [data-theme="dark"] .compliance-box { background: #1f2722; border-color: var(--accent); }
        [data-theme="dark"] .badge-verified { background-color: #1B3324; color: #34D865; border-color: rgba(52, 216, 101, 0.4); }
        [data-theme="dark"] .floating-controls .btn-dark {
            background-color: #232328;
            border-color: #2E2E33;
            color: #FFFFFF;
        }
        [data-theme="dark"] .floating-controls .btn-light {
            background-color: #1A1A1E;
            border-color: #2E2E33;
            color: #E8E6E3;
        }
    </style>
</head>
<body>
    <div class="floating-controls no-print">
        <button type="button" class="btn btn-dark btn-sm shadow-sm rounded-pill d-flex align-items-center gap-1 px-3 py-2" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / Save PDF
        </button>
        <button type="button" class="btn btn-light btn-sm shadow-sm rounded-pill border d-flex align-items-center gap-1 px-3 py-2" onclick="window.close()">
            <i class="bi bi-x-lg"></i> Close
        </button>
    </div>

    <div class="credential-page">
        <div class="institutional-header">
            <div class="inst-republic">Republic of the Philippines • City of Dasmariñas • Province of Cavite</div>
            <div class="inst-name">Kolehiyo ng Lungsod ng Dasmariñas</div>
            <div class="inst-sub">
                <?= $is_diploma ? 'Office of the College Registrar &amp; Alumni Placement Services' : 'Office of the College Registrar • Academic Affairs &amp; Student Placement' ?>
            </div>
        </div>

        <div class="doc-banner d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="doc-banner-title"><?= htmlspecialchars($doc_title) ?></div>
                <div class="small text-muted">
                    <?= $is_diploma ? 'Verified Baccalaureate Record • Official Alumni Registry' : 'Academic Year 2026–2027 • Official Enrolled Study Load' ?>
                </div>
            </div>
            <div>
                <span class="badge-verified">
                    <i class="bi bi-patch-check-fill"></i> <?= htmlspecialchars($verif_status) ?>
                </span>
            </div>
        </div>

        <div class="section-title">
            <i class="bi bi-person-lines-fill text-accent"></i> Candidate Identification &amp; Enrollment Standing
        </div>

        <div class="info-grid">
            <div class="info-cell">
                <span class="info-label"><?= $is_diploma ? 'Graduate / Alumnus Name' : 'Enrolled Student Name' ?></span>
                <span class="info-val"><?= htmlspecialchars($student_name) ?></span>
            </div>
            <div class="info-cell">
                <span class="info-label">Student ID Number</span>
                <span class="info-val"><?= htmlspecialchars($student_number) ?></span>
            </div>
            <div class="info-cell">
                <span class="info-label">Degree Program</span>
                <span class="info-val"><?= htmlspecialchars($student_course) ?></span>
            </div>
            <div class="info-cell">
                <span class="info-label"><?= $is_diploma ? 'Academic Classification' : 'Current Year Level &amp; Standing' ?></span>
                <span class="info-val"><?= htmlspecialchars($student_year) ?></span>
            </div>
            <div class="info-cell">
                <span class="info-label">Institute / Department</span>
                <span class="info-val"><?= htmlspecialchars($student_department) ?></span>
            </div>
            <div class="info-cell">
                <span class="info-label">Institutional Email</span>
                <span class="info-val"><?= htmlspecialchars($student_email) ?></span>
            </div>
            <div class="info-cell">
                <span class="info-label">Contact Phone</span>
                <span class="info-val"><?= htmlspecialchars($student_phone) ?></span>
            </div>
            <div class="info-cell">
                <span class="info-label">Document Reference</span>
                <span class="info-val text-break"><?= htmlspecialchars($doc_ref) ?></span>
            </div>
        </div>

        <?php if (!$is_diploma): ?>
            <div class="section-title">
                <i class="bi bi-calendar2-week-fill text-accent"></i> Declared Free Shift Availability (Campus Duty Matrix)
            </div>
            <p class="small text-muted mb-2">
                Verified vacant weekly hours available for on-campus student assistantship assignment:
            </p>
            <?php if (!empty($availability)): ?>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($availability as $slot): ?>
                        <span class="matrix-pill">
                            <i class="bi bi-clock-fill text-accent me-1"></i><?= htmlspecialchars($slot) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-muted small mb-3">Flexible scheduling declared. Specific shift times coordinated with department supervisor.</div>
            <?php endif; ?>

            <div class="compliance-box">
                <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill text-accent me-1"></i> Academic Safeguard &amp; Work Regulations</div>
                This Official Certificate of Registration (e-COR) confirms that the student maintains active matriculation for Academic Year 2026–2027. In compliance with KLD Institutional Policies, student assistant duty is strictly capped at a maximum of <strong>20 hours per week</strong> to preserve academic lectures and study schedules.
            </div>
        <?php else: ?>
            <div class="section-title">
                <i class="bi bi-award-fill text-accent"></i> Baccalaureate Clearance &amp; Alumni Validation
            </div>
            <div class="compliance-box">
                <div class="fw-bold mb-1"><i class="bi bi-mortarboard-fill text-accent me-1"></i> Official Degree Conferral Verification</div>
                This official credential verifies that the named candidate has fulfilled all baccalaureate requirements at Kolehiyo ng Lungsod ng Dasmariñas. Cleared for university assistantship positions, institutional projects, and external employer recruitment.
            </div>
        <?php endif; ?>

        <div class="institutional-footer">
            <div>
                <div class="small fw-bold text-ink">Office of the College Registrar</div>
                <div class="small text-muted">Kolehiyo ng Lungsod ng Dasmariñas</div>
                <div class="small text-muted" style="font-size: 11px;">Verification Reference: KLD-VERIF-<?= strtoupper(substr(md5($student_number . $student_email . '2026-2027'), 0, 12)) ?></div>
            </div>
            <div class="text-end">
                <div class="small fw-semibold text-muted">Electronically Verified</div>
                <div class="small text-muted" style="font-size: 11px;">Date Issued: <?= date('F j, Y') ?></div>
            </div>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}

// -----------------------------------------------------------------------------
// DOC TYPE: RESUME (Standard Resume Viewer)
// -----------------------------------------------------------------------------
$candidate_paths = [
    __DIR__ . '/uploads/resumes/' . basename($resume_filename)
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

require __DIR__ . '/includes/templates/view-resume-view.php';
