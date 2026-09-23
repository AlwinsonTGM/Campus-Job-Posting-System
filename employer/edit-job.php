<?php
/**
 * Campus Job Posting System - Edit Job Requisition Form
 * Archetype C: Detail & Sidebar Action Form (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$job_id = $_GET['id'] ?? null;
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The specified job requisition could not be found.');
    header('Location: dashboard.php');
    exit;
}

if (!can_manage_job($job, $user)) {
    set_flash('danger', 'Unauthorized: You can only edit requisitions posted by your office.');
    header('Location: dashboard.php');
    exit;
}

$categories = get_categories();
$job_types = get_job_types();
$work_setups = get_work_setups();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = $_POST['category'] ?? $job['category'];
        $job_type = $_POST['job_type'] ?? ($job['job_type'] ?? 'Student Assistant');
        $work_setup = $_POST['work_setup'] ?? ($job['work_setup'] ?? 'On-Campus');
        $location = trim($_POST['location'] ?? $job['location']);
        
        // Separated stipend rate
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
            $pay_type = $job['pay_type'] ?? 'Hourly';
        } else {
            $pay_rate = $job['pay_rate'] ?? '₱80.00 / hour';
            $pay_type = $job['pay_type'] ?? 'Hourly';
        }

        $hours_per_week = trim($_POST['hours_per_week'] ?? $job['hours_per_week']);
        $valid_hours = ['10 - 20 hrs/week', 'Up to 15 hrs/week', 'Up to 20 hrs/week', 'Flexible Schedule (Max 20 hrs/week)', 'Flexible Schedule'];
        if (!in_array($hours_per_week, $valid_hours) || preg_match('/\b(2[1-9]|[3-9]\d)\b/', $hours_per_week)) {
            $hours_per_week = 'Up to 20 hrs/week';
        }
        $vacancies = isset($_POST['vacancies']) ? max(1, (int)$_POST['vacancies']) : 1;
        $deadline = $_POST['deadline'] ?? $job['deadline'];
        $status = $_POST['status'] ?? $job['status'];
        $description = trim($_POST['description'] ?? $job['description']);
        
        // Dynamic lines handling
        $raw_resp = $_POST['responsibilities'] ?? [];
        $responsibilities = is_array($raw_resp)
            ? array_values(array_filter(array_map('trim', $raw_resp), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_resp)), fn($v) => $v !== ''));

        $raw_qual = $_POST['qualifications'] ?? [];
        $qualifications = is_array($raw_qual)
            ? array_values(array_filter(array_map('trim', $raw_qual), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_qual)), fn($v) => $v !== ''));

        if (empty($title) || empty($description)) {
            $error = 'Please provide the vacancy title and detailed description.';
        } elseif ($vacancies < 1) {
            $error = 'Vacancy quota must be at least 1 position.';
        } else {
            // Resolve category_id
            $category_id = (int)($job['category_id'] ?? 3);
            foreach ($categories as $cat) {
                if (strcasecmp($cat['name'], $category) === 0) {
                    $category_id = (int)$cat['id'];
                    break;
                }
            }

            $photo_file = $_FILES['job_photo'] ?? null;
            $remove_photo = !empty($_POST['remove_photo']);

            update_job($job['id'], [
                'title' => $title,
                'category' => $category,
                'category_id' => $category_id,
                'job_type' => $job_type,
                'work_setup' => $work_setup,
                'location' => $location,
                'pay_rate' => $pay_rate,
                'pay_type' => $pay_type,
                'hours_per_week' => $hours_per_week,
                'vacancies' => $vacancies,
                'deadline' => $deadline,
                'status' => $status,
                'description' => $description,
                'remove_photo' => $remove_photo,
                'responsibilities' => $responsibilities,
                'qualifications' => $qualifications
            ], $photo_file);

            set_flash('success', "Vacancy '{$title}' updated successfully!");
            header('Location: dashboard.php');
            exit;
        }
    }
}

// Parse existing stipend amount and period
$parsed_amount = '';
$parsed_period = '/ hour';
if (!empty($job['pay_rate'])) {
    if (preg_match('/(?:₱|PHP)?\s*([\d,]+(?:\.\d+)?)\s*(?:\/|\bper\b)?\s*(.+)?/iu', $job['pay_rate'], $pm)) {
        $parsed_amount = str_replace(',', '', trim($pm[1]));
        $raw_period = trim($pm[2] ?? '');
        if (stripos($raw_period, 'hour') !== false || stripos($raw_period, 'hr') !== false) {
            $parsed_period = '/ hour';
        } elseif (stripos($raw_period, 'day') !== false) {
            $parsed_period = '/ day';
        } elseif (stripos($raw_period, 'week') !== false) {
            $parsed_period = '/ week';
        } elseif (stripos($raw_period, 'month') !== false || stripos($raw_period, 'mo') !== false) {
            $parsed_period = '/ month';
        } elseif (stripos($raw_period, 'sem') !== false) {
            $parsed_period = '/ semester';
        } elseif (stripos($raw_period, 'fixed') !== false) {
            $parsed_period = 'fixed stipend';
        } elseif (!empty($raw_period)) {
            $parsed_period = '/ ' . $raw_period;
        }
    }
}

$resp_items = is_array($job['responsibilities'] ?? null) ? $job['responsibilities'] : (!empty($job['responsibilities']) ? explode("\n", $job['responsibilities']) : []);
$qual_items = is_array($job['qualifications'] ?? null) ? $job['qualifications'] : (!empty($job['qualifications']) ? explode("\n", $job['qualifications']) : []);

$page_title = 'Edit ' . $job['title'];
// Last line: view template
require __DIR__ . '/../includes/templates/employer-edit-job-view.php';

