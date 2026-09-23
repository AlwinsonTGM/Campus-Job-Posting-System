<?php
/**
 * Campus Job Posting System - Student Job Listings & Search
 * Archetype B/C: Search & Card Grid (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$page_title = 'Browse Campus Vacancies & Assistantships';

// GET Parameter Contract
$keyword = trim($_GET['keyword'] ?? $_GET['kw'] ?? $_GET['q'] ?? '');
$category = trim($_GET['category'] ?? $_GET['cat'] ?? '');
$department = trim($_GET['department'] ?? $_GET['dept'] ?? '');
$job_type = trim($_GET['job_type'] ?? '');
$work_setup = trim($_GET['work_setup'] ?? '');
$pay_type = trim($_GET['pay_type'] ?? '');
$employer_type = trim($_GET['employer_type'] ?? '');

// Handle aliases for quick filters
$is_lab_assistant_active = (strcasecmp($category, 'Science & Computer Lab Assistant') === 0 || strcasecmp($job_type, 'Lab Assistant') === 0);
$is_library_aide_active = (strcasecmp($category, 'Library Services') === 0 || strcasecmp($job_type, 'Library Aide') === 0 || strcasecmp($job_type, 'Library') === 0);

if (strcasecmp($job_type, 'Lab Assistant') === 0) {
    if (empty($category)) {
        $category = 'Science & Computer Lab Assistant';
    }
    $job_type = '';
} elseif (strcasecmp($job_type, 'Library Aide') === 0 || strcasecmp($job_type, 'Library') === 0) {
    if (empty($category)) {
        $category = 'Library Services';
    }
    $job_type = '';
}

$active_filters_count = 0;
if (!empty($keyword)) $active_filters_count++;
if (!empty($category)) $active_filters_count++;
if (!empty($department)) $active_filters_count++;
if (!empty($job_type)) $active_filters_count++;
if (!empty($work_setup)) $active_filters_count++;
if (!empty($employer_type)) $active_filters_count++;

$jobs = get_jobs(
    $category ?: null,
    $keyword ?: null,
    $department ?: null,
    $pay_type ?: null,
    $job_type ?: null,
    $employer_type ?: null,
    $work_setup ?: null
);

$categories = get_categories();
$all_job_types = get_job_types();
$all_work_setups = get_work_setups();

// Preload view data — no service/DB calls in the template
$view_get_kld_institutes_and_courses = get_kld_institutes_and_courses();

// Last line: view template
require __DIR__ . '/../includes/templates/student-jobs-view.php';

