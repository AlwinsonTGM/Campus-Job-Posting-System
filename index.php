<?php
/**
 * Campus Job Posting System - Index / Landing Page
 * Paper Sheet Redesign (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}

$page_title = 'Empowering Students, Supporting Campus Offices';

// Data from helper
$categories = get_categories();
$all_active_jobs = array_values(array_filter(get_jobs(), fn($j) => in_array(strtolower($j['status'] ?? 'active'), ['active', 'open'])));
// Qualified Featured Vacancies (Jobs with an attached photo/flyer)
$featured_jobs = array_values(array_filter($all_active_jobs, fn($j) => !empty($j['image']) || !empty($j['is_featured'])));
if (empty($featured_jobs)) {
    // Graceful fallback to general active jobs if no photo-backed jobs exist yet
    $featured_jobs = array_slice($all_active_jobs, 0, 5);
} else {
    $featured_jobs = array_slice($featured_jobs, 0, 8);
}

// Key Metrics
$metric_active_jobs = get_metrics_total_active_jobs();
$metric_partnered_offices = get_metrics_partnered_offices();
$metric_students_hired = get_metrics_students_hired();
$metric_avg_pay = get_metrics_avg_hourly_pay();

// Interactive 3D Hero Scripts
$extra_js = [
    'assets/js/three.min.js',
    'assets/js/GLTFLoader.js',
    'assets/js/hero-robot.js?v=' . time()
];
// Last line: view template
require __DIR__ . '/includes/templates/index-view.php';

