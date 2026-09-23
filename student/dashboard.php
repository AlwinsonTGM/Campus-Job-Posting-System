<?php
/**
 * Campus Job Posting System - Student Dashboard
 * Archetype B: Student Dashboard & Application Hub (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['student', 'admin']);
$user = get_logged_user();
$page_title = 'Student Dashboard & Application Portal';

// Applications for current student
$my_apps = get_applications($user['id'] ?? 0);
$total_applied = count($my_apps);
$pending_count = count(array_filter($my_apps, fn($a) => in_array($a['status'] ?? '', ['pending', 'Pending Review', 'under_review', 'Under Evaluation'])));
$interview_count = count(array_filter($my_apps, fn($a) => in_array($a['status'] ?? '', ['interview_scheduled', 'Interview Scheduled'])));
$accepted_count = count(array_filter($my_apps, fn($a) => in_array($a['status'] ?? '', ['accepted', 'Accepted / Hired'])));

$pending_profile_req = get_pending_profile_request($user['id'] ?? 0);

// Recommended Jobs (filtered by student course / general assistantships)
$all_jobs = get_jobs();
$recommended_jobs = array_slice($all_jobs, 0, 3);
// Last line: view template
require __DIR__ . '/../includes/templates/student-dashboard-view.php';

