<?php
/**
 * Campus Job Posting System - Live Search API Endpoint
 * Provides instant JSON search results for the Floating Spotlight Search Modal
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/data-helper.php';

// Safely parse and guard query parameters against non-string/array types (PHP 8.2 compatibility)
$raw_q = $_GET['q'] ?? $_GET['keyword'] ?? '';
$query = is_string($raw_q) ? trim($raw_q) : '';

$raw_job_type = $_GET['job_type'] ?? '';
$job_type = is_string($raw_job_type) ? trim($raw_job_type) : '';

$raw_department = $_GET['department'] ?? '';
$department = is_string($raw_department) ? trim($raw_department) : '';

$raw_work_setup = $_GET['work_setup'] ?? '';
$work_setup = is_string($raw_work_setup) ? trim($raw_work_setup) : '';

$raw_limit = $_GET['limit'] ?? null;
$limit = (is_scalar($raw_limit) && is_numeric($raw_limit)) ? max(1, min(20, (int)$raw_limit)) : 8;

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
    'results' => $formatted_results,
    'jobs' => $formatted_results
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
