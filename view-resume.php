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
$force_html = isset($_GET['render_html']) && $_GET['render_html'] === '1';

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
        $resume_filename = ($target_student['name'] ?? 'Student') . '_Resume.pdf';
    }

    if (!$target_student) {
        http_response_code(404);
        die('Student record not found.');
    }
} else {
    $target_student = $current_user;
    if ($file_param) {
        $requested_file = basename($file_param);

        if (!can_access_resume_file($current_user, $requested_file)) {
            http_response_code(403);
            die('Access Denied: You are not authorized to inspect this document.');
        }

        $resume_filename = $requested_file;
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
if (is_string($availability)) {
    $decoded = json_decode($availability, true);
    $availability = is_array($decoded) ? $decoded : [];
} elseif (!is_array($availability)) {
    $availability = [];
}

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

