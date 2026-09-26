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

// Department aggregations
$departments = [
    'Management Information Systems (MIS)' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 6],
    'Office of the University Registrar' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 8],
    'KLD University Library' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 5],
    'Institute of Computing and Digital Innovation (ICDI)' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 10],
    'Institute of Nursing & Health Sciences' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 4],
    'Institute of Engineering' => ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 6],
];

foreach ($all_jobs as $j) {
    $dept_name = $j['department'] ?? 'General';
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 4];
    }
    $departments[$dept_name]['jobs']++;
}

foreach ($all_apps as $a) {
    $dept_name = $a['department'] ?? 'General';
    if (!isset($departments[$dept_name])) {
        $departments[$dept_name] = ['jobs' => 0, 'apps' => 0, 'hired' => 0, 'quota' => 4];
    }
    $departments[$dept_name]['apps']++;
    $st = strtolower($a['status'] ?? '');
    if (in_array($st, ['accepted', 'accepted / hired', 'hired'], true)) {
        $departments[$dept_name]['hired']++;
    }
}

// Department quota narrative analytics (read-only explainer over existing rollups).
$quota_narrative = get_quota_narrative($departments, $total_jobs, $total_apps, $total_hired, $total_interviews);

// 1. Categories Chart Data
$category_chart_labels = [];
$category_chart_counts = [];
$category_chart_pcts = [];
foreach ($categories as $c) {
    $cat_name = $c['name'];
    $cat_job_count = count(array_filter($all_jobs, fn($j) => ($j['category'] ?? '') === $cat_name));
    $pct = $total_jobs > 0 ? round(($cat_job_count / $total_jobs) * 100) : 0;
    $category_chart_labels[] = $cat_name;
    $category_chart_counts[] = $cat_job_count;
    $category_chart_pcts[] = $pct;
}

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

// 3. Department Quota vs Hired Data
$dept_quota_labels = [];
$dept_quota_targets = [];
$dept_quota_hired = [];
$dept_quota_fill_pcts = [];
foreach ($departments as $dept_name => $stats) {
    $q = (int)($stats['quota'] ?? 4);
    $h = (int)($stats['hired'] ?? 0);
    $fill_pct = round(($h / max(1, $q)) * 100);
    $dept_quota_labels[] = $dept_name;
    $dept_quota_targets[] = $q;
    $dept_quota_hired[] = $h;
    $dept_quota_fill_pcts[] = $fill_pct;
}

// 4. Filtered Flagged Departments for Advisory Drawer
$quota_notes = $quota_narrative['notes'] ?? [];
$flagged_departments = array_values(array_filter($quota_notes, function($n) {
    return !empty($n['flags']);
}));

// Load Chart.js offline vendor asset
$extra_js = ['assets/vendor/chart.js/chart.umd.min.js'];

// Last line: view template
require __DIR__ . '/../includes/templates/admin-reports-view.php';

