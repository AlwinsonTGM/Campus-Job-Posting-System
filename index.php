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
// Fetch featured active jobs directly using optimized SQL instead of in-memory array filtering
$featured_jobs = get_featured_jobs(8);

// Key Metrics
$metric_active_jobs = get_metrics_total_active_jobs();
$metric_partnered_offices = get_metrics_partnered_offices();
$metric_students_hired = get_metrics_students_hired();
$metric_avg_pay = get_metrics_avg_hourly_pay();

// Interactive 3D Hero Scripts (Hardware-Accelerated 3D WebGL Mascot)
$is_3d = function_exists('is_3d_robot_enabled') ? is_3d_robot_enabled() : (function_exists('is_local_environment') && is_local_environment());
if ($is_3d) {
    $extra_js = [
        'assets/js/three.min.js',
        'assets/js/GLTFLoader.js',
        'assets/js/hero-robot.js?v=' . time()
    ];
} else {
    $extra_js = [
        'assets/js/hero-robot.js?v=' . time()
    ];
};
// Last line: view template
require __DIR__ . '/includes/templates/index-view.php';

