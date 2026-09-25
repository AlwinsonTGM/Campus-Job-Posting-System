<?php
/**
 * Campus Job Posting System - Job Application Form
 * Archetype D: Application & Availability Matrix (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Ensure student auth - employers and admins cannot apply.
// Guests go to the plain login form (never a demo auto-login), then return here.
if (!is_logged_in()) {
    set_flash('info', 'Please sign in with your student account to submit an application.');
    $return_to = 'student/apply.php?id=' . urlencode((string)($_GET['id'] ?? ($_GET['job_id'] ?? '')));
    header('Location: ../login.php?next=' . urlencode($return_to));
    exit;
}

$user = get_logged_user();
if (($user['role'] ?? '') !== 'student') {
    set_flash('warning', 'Access Restricted: Only enrolled students can submit job applications. Employer accounts cannot apply for campus vacancies.');
    if (($user['role'] ?? '') === 'employer') {
        header('Location: ../employer/dashboard.php');
    } else {
        header('Location: ../admin/users.php');
    }
    exit;
}
$job_id = $_GET['id'] ?? ($_GET['job_id'] ?? null);
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The requested opportunity could not be found or has been closed.');
    header('Location: jobs.php');
    exit;
}

// Verification Check
if (($user['verification_status'] ?? 'verified') !== 'verified') {
    set_flash('warning', 'Account Verification Required: Your student registration is currently awaiting administrative review. The administrator must verify your account before you can submit applications.');
    header('Location: dashboard.php');
    exit;
}

// Gating Checks
if (strtolower($job['status'] ?? '') !== 'active') {
    set_flash('danger', 'This requisition has been closed or paused and is not accepting applications.');
    header('Location: job-details.php?id=' . $job['id']);
    exit;
}

if (!empty($job['deadline']) && strtotime($job['deadline']) < strtotime(date('Y-m-d'))) {
    set_flash('danger', 'The application deadline for this position has passed.');
    header('Location: job-details.php?id=' . $job['id']);
    exit;
}

$slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
$slots_filled = (int)($job['slots_filled'] ?? 0);
if ($slots_total > 0 && $slots_filled >= $slots_total) {
    set_flash('danger', 'All vacancy slots for this position have been filled.');
    header('Location: job-details.php?id=' . $job['id']);
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed (invalid CSRF session token). Please refresh and submit again.';
    } else {
        $cover_letter = trim($_POST['cover_letter'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $availability = $_POST['availability'] ?? [];
        $digits_only = preg_replace('/[^0-9]/', '', $phone);

        $resume_name = ($user['name'] ?? 'Student') . '_Resume.pdf';

        if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload failed. Please verify that your resume file is under 5MB.';
            } else {
                $resume_path = save_uploaded_resume($_FILES['resume']);
                if (!$resume_path) {
                    $error = 'Invalid resume format or size. Accepted formats: PDF, DOC, DOCX (Max 5MB).';
                } else {
                    $resume_name = basename($resume_path);
                }
            }
        }

        if (!$error) {
            if (empty($cover_letter)) {
                $error = 'Please provide a brief statement of intent / cover letter.';
            } elseif (empty($phone) || preg_match('/[a-zA-Z]/', $phone) || !preg_match('/^[\+]?[0-9\s\-()]{7,20}$/', $phone) || strlen($digits_only) < 7 || strlen($digits_only) > 15) {
                $error = 'Please provide a valid contact phone number consisting of numbers only (e.g., +63 917 123 4567 or 09171234567).';
            } elseif (empty($availability) || count($availability) === 0) {
                $error = 'Candidate Shift Availability is required and cannot be empty. Please select at least one available weekly timeslot in the matrix.';
            } else {
                $res = create_application([
                    'job_id' => $job['id'],
                    'cover_letter' => $cover_letter,
                    'phone' => $phone,
                    'availability' => $availability,
                    'resume_file' => $resume_name
                ]);

                if ($res['success']) {
                    set_flash('success', "Application successfully submitted for {$job['title']}! You can track its review progress below.");
                    header('Location: my-applications.php');
                    exit;
                }
                $error = $res['message'] ?? 'Failed to submit application. Please try again.';
            }
        }
    }
}

$default_availability = (isset($_POST['availability']) && is_array($_POST['availability']))
    ? $_POST['availability']
    : ((!empty($user['availability']) && is_array($user['availability'])) ? $user['availability'] : [
        'Mon - Morning (8AM–12NN)',
        'Wed - Morning (8AM–12NN)',
        'Fri - Afternoon (1PM–5PM)'
    ]);

$page_title = 'Apply for ' . $job['title'];


// Preload view data — no service/DB calls in the template
$form = $_POST;
$query = $_GET;
$files = $_FILES;
$view_flash = $_SESSION['flash'] ?? null;

// Last line: view template
require __DIR__ . '/../includes/templates/student-apply-view.php';

