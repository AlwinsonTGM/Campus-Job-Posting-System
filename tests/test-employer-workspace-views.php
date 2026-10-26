<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

function check_employer_view(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo "PASS: {$message}\n";
}

// Render synthetic records without inserting, archiving, or publishing anything.
$user = quick_login('admin');
if (!$user) throw new RuntimeException('A local admin is required for view checks.');
$is_archived_view = true;
$org_name = 'View fixture office';
$active_dept_jobs = $dept_apps = [];
$active_jobs_count = $total_applicants_count = $interview_count = $hired_count = 0;
$job_applicant_counts = [991 => 12];
$archived_dept_jobs = $all_dept_jobs = [['id' => 991, 'title' => 'Archived "fixture"', 'category' => 'Office', 'pay_rate' => '₱80 / hour', 'is_archived' => true, 'deadline' => '2026-11-12']];
ob_start();
require __DIR__ . '/../includes/templates/employer-dashboard-view.php';
$dashboard = ob_get_clean();
check_employer_view(str_contains($dashboard, 'value="restore_job"') && str_contains($dashboard, 'value="991"') && str_contains($dashboard, 'name="csrf_token"'), 'Admin archived view retains its CSRF-protected restore form.');
check_employer_view(str_contains($dashboard, 'applicants.php?job_id=991'), 'Archived records retain their applicant link.');

$user = quick_login('employer');
if (!$user) throw new RuntimeException('A local employer is required for view checks.');
$error = null;
$dept_updates = [['id' => 'fixture-bulletin', 'title' => 'Headline "quoted" & safe', 'summary' => 'Summary', 'content' => '<p>Content</p>', 'image' => 'assets/img/hero-office.jpg', 'published_at' => '2026-10-26', 'author' => ['name' => 'Fixture author', 'role' => 'Supervisor', 'office' => $org_name]]];
ob_start();
require __DIR__ . '/../includes/templates/employer-updates-view.php';
$updates = ob_get_clean();
check_employer_view(str_contains($updates, 'data-id="fixture-bulletin"') && str_contains($updates, 'data-title="Headline &quot;quoted&quot; &amp; safe"') && str_contains($updates, 'data-content="&lt;p&gt;Content&lt;/p&gt;"'), 'Populated announcement rows retain escaped data for the real edit modal.');
check_employer_view(str_contains($updates, 'value="archive"') && str_contains($updates, 'value="fixture-bulletin"') && str_contains($updates, 'name="csrf_token"'), 'Announcement archive forms retain record IDs and CSRF tokens.');
check_employer_view(str_contains($updates, 'id="edit_emp_content"') && str_contains($updates, 'value="edit"') && str_contains($updates, 'value="create"'), 'Create and edit announcement forms remain connected to the controller.');
check_employer_view(!str_contains($updates, 'Auto-Approved') && !str_contains($updates, 'Publish Live Immediately'), 'Redundant auto-approval and live labels are removed.');
