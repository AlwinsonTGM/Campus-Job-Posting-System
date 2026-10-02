<?php
/**
 * Campus Job Posting System - Admin Reports & Analytics
 * Archetype G: Admin Analytics & Printable Report (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Reports & Institutional Analytics';

$all_jobs = get_jobs();
$all_apps = get_applications();
$categories = get_categories();
$all_users = get_all_users();

$total_jobs = count($all_jobs);
$total_apps = count($all_apps);
$total_hired = count(array_filter($all_apps, function($a) {
    $st = strtolower($a['status'] ?? '');
    return in_array($st, ['accepted', 'accepted / hired', 'hired'], true);
}));
$total_interviews = count(array_filter($all_apps, function($a) {
    $st = strtolower($a['status'] ?? '');
    return in_array($st, ['interview_scheduled', 'interview scheduled', 'interview'], true);
}));

// User population & verification pipeline analytics
$total_users = count($all_users);
$total_students = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'student'));
$total_employers = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'employer'));
$total_admins = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'admin'));

$pending_students_count = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'student' && ($u['verification_status'] ?? '') === 'pending_approval'));
$pending_employers_count = count(array_filter($all_users, fn($u) => ($u['role'] ?? '') === 'employer' && ($u['verification_status'] ?? '') === 'pending_approval'));
$total_pending_verifications = $pending_students_count + $pending_employers_count;

$all_profile_requests = get_profile_requests();
$pending_profile_requests = array_filter($all_profile_requests, fn($r) => ($r['status'] ?? '') === 'pending');
$pending_profile_count = count($pending_profile_requests);
$total_pending_actions = $total_pending_verifications + $pending_profile_count;

// Department aggregations (real activity from jobs & applications)
$departments = [
    'Management Information Systems (MIS)' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'Office of the University Registrar' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'KLD University Library' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'Institute of Computing and Digital Innovation (ICDI)' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'Institute of Nursing & Health Sciences' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
    'Institute of Engineering' => ['jobs' => 0, 'apps' => 0, 'hired' => 0],
];

foreach ($all_jobs as $j) {
    $dept_name = trim($j['department'] ?? 'General');
    if ($dept_name === '') $dept_name = 'General';
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0];
    }
    $departments[$dept_name]['jobs']++;
}

// Application status breakdown and departmental application count
$status_counts = [
    'pending'             => 0,
    'under_review'        => 0,
    'interview_scheduled' => 0,
    'accepted'            => 0,
    'declined'            => 0,
];

foreach ($all_apps as $a) {
    $dept_name = trim($a['department'] ?? 'General');
    if ($dept_name === '') $dept_name = 'General';
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0];
    }
    $departments[$dept_name]['apps']++;
    $st = strtolower($a['status'] ?? 'pending');
    if (in_array($st, ['accepted', 'accepted / hired', 'hired'], true)) {
        $departments[$dept_name]['hired']++;
        $status_counts['accepted']++;
    } elseif (in_array($st, ['interview_scheduled', 'interview scheduled', 'interview'], true)) {
        $status_counts['interview_scheduled']++;
    } elseif (in_array($st, ['under_review', 'under review', 'reviewed', 'shortlisted'], true)) {
        $status_counts['under_review']++;
    } elseif (in_array($st, ['declined', 'rejected'], true)) {
        $status_counts['declined']++;
    } else {
        $status_counts['pending']++;
    }
}

// 1. Categories Chart Data (sorted descending by active job demand)
$category_data = [];
foreach ($categories as $c) {
    $cat_name = $c['name'];
    $cat_job_count = count(array_filter($all_jobs, fn($j) => ($j['category'] ?? '') === $cat_name));
    $pct = $total_jobs > 0 ? round(($cat_job_count / $total_jobs) * 100) : 0;
    $category_data[] = [
        'name'  => $cat_name,
        'count' => $cat_job_count,
        'pct'   => $pct,
    ];
}
usort($category_data, fn($a, $b) => $b['count'] <=> $a['count']);

$category_chart_labels = array_column($category_data, 'name');
$category_chart_counts = array_column($category_data, 'count');
$category_chart_pcts   = array_column($category_data, 'pct');

// 2. Department Applications Data (sorted descending by application volume)
$dept_sorted_by_apps = $departments;
uasort($dept_sorted_by_apps, function($a, $b) {
    return ($b['apps'] ?? 0) <=> ($a['apps'] ?? 0);
});

$dept_app_all_labels = [];
$dept_app_all_counts = [];
$dept_app_all_pcts = [];
foreach ($dept_sorted_by_apps as $d_name => $stats) {
    $dept_app_all_labels[] = $d_name;
    $dept_app_all_counts[] = (int)($stats['apps'] ?? 0);
    $dept_app_all_pcts[] = $total_apps > 0 ? round(((int)$stats['apps'] / $total_apps) * 100) : 0;
}

// Top 6 subset for the primary view
$dept_app_top_labels = array_slice($dept_app_all_labels, 0, 6);
$dept_app_top_counts = array_slice($dept_app_all_counts, 0, 6);
$dept_app_top_pcts = array_slice($dept_app_all_pcts, 0, 6);

// Load Chart.js offline vendor asset
$extra_js = ['assets/vendor/chart.js/chart.umd.min.js'];

// Last line: view template
require __DIR__ . '/../includes/templates/admin-reports-view.php';

