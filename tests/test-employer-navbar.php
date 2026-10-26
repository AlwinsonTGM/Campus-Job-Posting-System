<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}

function check(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException("FAILED: {$message}");
    echo "PASS: {$message}\n";
}

// 1. Check Employer Navbar
$current_user = [
    'id' => 10,
    'name' => 'Acme Corp Employer',
    'email' => 'employer@acme.edu.ph',
    'role' => 'employer'
];
$base_url = '/final-campus-job-posting-system/';
$dashboard_link = $base_url . 'employer/dashboard.php';
$user_unread_notifs_count = 2;
$user_recent_notifs = [];
$current_script = 'dashboard.php';

ob_start();
require __DIR__ . '/../includes/templates/includes-navbar-view.php';
$html = ob_get_clean();

// Check mobile search button exists
check(str_contains($html, 'btn-nav-search-mobile'), 'Mobile search button is present for employer');
// Check desktop circular search button exists
check(str_contains($html, 'btn-circle-icon d-none d-lg-inline-flex'), 'Desktop circular search button is present for employer');
// Check notifications button exists
check(str_contains($html, 'navNotificationDropdown'), 'Notifications dropdown is present for employer');

// 2. Check Admin Navbar
$current_user['role'] = 'admin';
$dashboard_link = $base_url . 'admin/dashboard.php';
ob_start();
require __DIR__ . '/../includes/templates/includes-navbar-view.php';
$admin_html = ob_get_clean();

check(str_contains($admin_html, 'btn-nav-search-mobile'), 'Mobile search button is present for admin');
check(str_contains($admin_html, 'btn-circle-icon d-none d-lg-inline-flex'), 'Desktop circular search button is present for admin');
check(str_contains($admin_html, 'navNotificationDropdown'), 'Notifications dropdown is present for admin');
check(str_contains($admin_html, 'admin-workspace-nav'), 'Admin navbar has workspace nav class');
check(str_contains($admin_html, 'bi-people'), 'Admin navbar includes Users & Verification icon');
check(str_contains($admin_html, 'bi-bar-chart'), 'Admin navbar includes Reports icon');
check(str_contains($admin_html, 'bi-tags'), 'Admin navbar includes Categories icon');
check(str_contains($admin_html, 'bi-megaphone'), 'Admin navbar includes Dispatches icon');
check(str_contains($admin_html, 'bi-briefcase'), 'Admin navbar includes Jobs Directory icon');
check(str_contains($admin_html, '<span><span class="d-inline d-lg-none d-xl-inline">Users &amp; </span>Verification</span>'), 'Admin Users & Verification label is wrapped in single span to prevent flexbox gap splitting words');
check(str_contains($admin_html, '<span>Jobs<span class="d-inline d-lg-none d-xl-inline"> Directory</span></span>'), 'Admin Jobs Directory label is wrapped in single span to prevent flexbox gap splitting words');
check(str_contains($html, '<span>Dashboard</span>'), 'Employer Dashboard label is wrapped in single span');

// Check active state on admin page (reports.php)
$current_script = 'reports.php';
$_SERVER['SCRIPT_NAME'] = '/final-campus-job-posting-system/admin/reports.php';
ob_start();
require __DIR__ . '/../includes/templates/includes-navbar-view.php';
$admin_reports_html = ob_get_clean();
check(str_contains($admin_reports_html, 'href="/final-campus-job-posting-system/admin/reports.php" aria-current="page"'), 'Reports link has aria-current="page" when active');

// Check active state on verification-deck.php activates Users & Verification
$current_script = 'verification-deck.php';
$_SERVER['SCRIPT_NAME'] = '/final-campus-job-posting-system/admin/verification-deck.php';
ob_start();
require __DIR__ . '/../includes/templates/includes-navbar-view.php';
$admin_deck_html = ob_get_clean();
check(str_contains($admin_deck_html, 'href="/final-campus-job-posting-system/admin/users.php" aria-current="page"'), 'Users link has aria-current="page" when on verification-deck.php');

echo "ALL NAVBAR CHECKS PASSED!\n";
