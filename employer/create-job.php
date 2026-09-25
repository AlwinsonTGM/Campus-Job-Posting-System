<?php
/**
 * Campus Job Posting System - Create Job Requisition Form
 * Archetype A/C: Multi-Stage Split Card Requisition Wizard (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();

$is_partner = ($user['employer_type'] ?? '') === 'approved_partner';
$ver_status = $user['verification_status'] ?? 'verified';
if ($user['role'] === 'employer' && $is_partner && $ver_status !== 'verified') {
    set_flash('danger', 'Your partner organization account is currently awaiting administrative accreditation. Vacancies cannot be published until verified.');
    header('Location: dashboard.php');
    exit;
}

$categories = get_categories();
$job_types = get_job_types();
$work_setups = get_work_setups();

$error = null;
$initial_step = 1;
$responsibilities = [];
$qualifications = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $job_type = trim($_POST['job_type'] ?? '');
        $work_setup = trim($_POST['work_setup'] ?? '');
        $department = trim($_POST['department'] ?? ($user['organization_name'] ?? ($user['department'] ?? '')));
        $location = trim($_POST['location'] ?? ($user['office_location'] ?? ''));

        // Resolve category_id from category name
        $category_id = 3;
        foreach ($categories as $cat) {
            if (strcasecmp($cat['name'], $category) === 0) {
                $category_id = (int)$cat['id'];
                break;
            }
        }

        // Separated stipend rate & compensation
        $pay_amount = trim($_POST['pay_amount'] ?? '');
        $pay_period = trim($_POST['pay_period'] ?? '/ hour');

        if (is_numeric($pay_amount) && (float)$pay_amount > 0) {
            $formatted_amt = number_format((float)$pay_amount, 2);
            if ($pay_period === 'fixed stipend') {
                $pay_rate = '₱' . $formatted_amt . ' fixed stipend';
                $pay_type = 'Fixed Stipend';
            } else {
                $pay_rate = '₱' . $formatted_amt . ' ' . $pay_period;
                if (stripos($pay_period, 'hour') !== false) {
                    $pay_type = 'Hourly';
                } elseif (stripos($pay_period, 'month') !== false) {
                    $pay_type = 'Monthly';
                } elseif (stripos($pay_period, 'day') !== false) {
                    $pay_type = 'Daily';
                } elseif (stripos($pay_period, 'sem') !== false) {
                    $pay_type = 'Per Semester';
                } else {
                    $pay_type = 'Stipend';
                }
            }
        } elseif (!empty($_POST['pay_rate'])) {
            $pay_rate = trim($_POST['pay_rate']);
            $pay_type = 'Hourly';
        } else {
            $pay_rate = '₱80.00 / hour';
            $pay_type = 'Hourly';
        }

        $hours_per_week = trim($_POST['hours_per_week'] ?? '10 - 20 hrs/week');
        $valid_hours = ['10 - 20 hrs/week', 'Up to 15 hrs/week', 'Up to 20 hrs/week', 'Flexible Schedule (Max 20 hrs/week)', 'Flexible Schedule'];
        if (!in_array($hours_per_week, $valid_hours) || preg_match('/\b(2[1-9]|[3-9]\d)\b/', $hours_per_week)) {
            $hours_per_week = 'Up to 20 hrs/week';
        }
        $vacancies = isset($_POST['vacancies']) ? max(1, (int)$_POST['vacancies']) : 1;
        $deadline = trim($_POST['deadline'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Dynamic lines handling for duties & qualifications
        $raw_resp = $_POST['responsibilities'] ?? [];
        $responsibilities = is_array($raw_resp)
            ? array_values(array_filter(array_map('trim', $raw_resp), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_resp)), fn($v) => $v !== ''));

        $raw_qual = $_POST['qualifications'] ?? [];
        $qualifications = is_array($raw_qual)
            ? array_values(array_filter(array_map('trim', $raw_qual), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_qual)), fn($v) => $v !== ''));

        $tags = trim($_POST['tags'] ?? '');
        if (empty($tags) && (!empty($job_type) || !empty($work_setup))) {
            $tags = implode(', ', array_filter([$job_type, $work_setup]));
        }

        // Server-side step validation
        if (empty($title) || empty($category) || empty($job_type) || empty($work_setup) || empty($description)) {
            $error = 'Please complete all required fields in Step 1 (Vacancy Information).';
            $initial_step = 1;
        } elseif (empty($department) || empty($location) || empty($pay_amount) || empty($deadline)) {
            $error = 'Please complete all required terms & quota fields in Step 3.';
            $initial_step = 3;
        } elseif (!is_numeric($pay_amount) || (float)$pay_amount <= 0) {
            $error = 'Please provide a valid positive stipend rate.';
            $initial_step = 3;
        } elseif ($vacancies < 1) {
            $error = 'Vacancy quota must be at least 1 position.';
            $initial_step = 3;
        } else {
            $photo_file = $_FILES['job_photo'] ?? null;

            $new_id = create_job([
                'title' => $title,
                'category' => $category,
                'category_id' => $category_id,
                'job_type' => $job_type,
                'work_setup' => $work_setup,
                'employer_type' => $user['employer_type'] ?? 'university_office',
                'organization_name' => $user['organization_name'] ?? $department,
                'department' => $department,
                'location' => $location,
                'pay_rate' => $pay_rate,
                'pay_type' => $pay_type,
                'hours_per_week' => $hours_per_week,
                'vacancies' => $vacancies,
                'deadline' => $deadline,
                'description' => $description,
                'responsibilities' => $responsibilities,
                'qualifications' => $qualifications,
                'tags' => $tags
            ], $photo_file);

            if ($new_id > 0) {
                set_flash('success', "New vacancy '{$title}' published successfully!");
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Failed to create vacancy. Please try again.';
                $initial_step = 3;
            }
        }
    }
}

$page_title = 'Post New Opportunity';


// Preload view data — no service/DB calls in the template
$form = $_POST;
$query = $_GET;
$files = $_FILES;
$view_flash = $_SESSION['flash'] ?? null;

// Last line: view template
require __DIR__ . '/../includes/templates/employer-create-job-view.php';

