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

// Phase 3-B: Laya quota narrative (read-only explainer over existing rollups;
// never changes quotas or postings — admin actions stay the sole gate).
$laya_narrative = laya_quota_narrative($departments, $total_jobs, $total_apps, $total_hired, $total_interviews);
// Last line: view template
require __DIR__ . '/../includes/templates/admin-reports-view.php';

