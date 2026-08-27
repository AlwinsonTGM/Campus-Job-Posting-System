<?php
/**
 * Campus Job Posting System - Live Search API Endpoint
 * Provides instant JSON search results for the Floating Spotlight Search Modal
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/data-helper.php';

$query = trim($_GET['q'] ?? $_GET['keyword'] ?? '');
$job_type = trim($_GET['job_type'] ?? '');
$department = trim($_GET['department'] ?? '');
$work_setup = trim($_GET['work_setup'] ?? '');
$limit = isset($_GET['limit']) ? max(1, min(20, (int)$_GET['limit'])) : 8;

// Fetch filtered jobs using existing system data-helper
$jobs = get_jobs(
    null,
    $query ?: null,
    $department ?: null,
    null,
    $job_type ?: null,
    null,
    $work_setup ?: null
);

$total_matches = count($jobs);
$results_slice = array_slice($jobs, 0, $limit);

$formatted_results = [];
foreach ($results_slice as $job) {
    $org_name = $job['organization_name'] ?? ($job['department'] ?? 'University Department');
    $is_partner = ($job['employer_type'] ?? '') === 'approved_partner';
    
    $formatted_results[] = [
        'id' => (int)($job['id'] ?? 0),
        'title' => $job['title'] ?? 'Untitled Job',
        'department' => $job['department'] ?? '',
        'organization_name' => $org_name,
        'is_partner' => $is_partner,
        'employer_type' => $job['employer_type'] ?? 'university_office',
        'job_type' => $job['job_type'] ?? 'Student Assistant',
        'work_setup' => $job['work_setup'] ?? 'On-Campus',
        'pay_rate' => $job['pay_rate'] ?? '₱65.00 / hr',
        'location' => $job['location'] ?? 'Campus',
        'deadline' => $job['deadline'] ?? 'Open',
        'image' => $job['image'] ?? 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=600&auto=format&fit=crop',
        'badges' => $job['badges'] ?? [$job['job_type'] ?? 'Student Assistant', $job['work_setup'] ?? 'On-Campus']
    ];
}

echo json_encode([
    'status' => 'success',
    'query' => $query,
    'total' => $total_matches,
    'count' => count($formatted_results),
    'results' => $formatted_results
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
