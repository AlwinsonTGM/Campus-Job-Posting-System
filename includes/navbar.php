<?php
/**
 * Campus Job Posting System - Shared Navbar Partial
 * Paper Sheet Aesthetic & Auth-Aware (COAL101 Blueprint)
 */
require_once __DIR__ . '/auth-check.php';

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}

$current_user = get_current_auth_user();
$current_script = basename($_SERVER['PHP_SELF'] ?? '');
?>
<nav class="navbar navbar-expand-lg paper-navbar sticky-top">
    <div class="container-fluid px-lg-4">
        <!-- Left: SVG Mark + SITE_NAME -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $base_url ?>index.php">
            <span class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-3 p-2 shadow-sm" style="width: 36px; height: 36px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#2ECC5E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="fw-extrabold text-ink tracking-tight"><?= htmlspecialchars(SITE_NAME) ?></span>
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="navbar-toggler border-0 shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links & Right Actions -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Center/Left Navigation Links -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1 gap-xl-2 align-items-lg-center">
                <?php if ($current_user && ($current_user['role'] ?? '') === 'admin'): 
                    $nav_pending_count = 0;
                    if (function_exists('get_profile_requests')) {
                        $all_pr = get_profile_requests();
                        $pending_pr = count(array_filter($all_pr, fn($r) => ($r['status'] ?? '') === 'pending'));
                        $all_u = $_SESSION['users'] ?? (function_exists('load_json_file') ? load_json_file('users.json') : []);
                        $pending_emp = count(array_filter($all_u, fn($u) => ($u['role'] ?? '') === 'employer' && ($u['verification_status'] ?? '') === 'pending_approval'));
                        $nav_pending_count = $pending_pr + $pending_emp;
                    }
                ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-1 <?= ($current_script === 'users.php') ? 'active' : '' ?>" href="<?= $base_url ?>admin/users.php">
                            USERS &amp; VERIFICATION
                            <?php if ($nav_pending_count > 0): ?>
                                <span class="badge bg-danger rounded-pill px-2" style="font-size: 10px;"><?= $nav_pending_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'reports.php') ? 'active' : '' ?>" href="<?= $base_url ?>admin/reports.php">
                            REPORTS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'categories.php') ? 'active' : '' ?>" href="<?= $base_url ?>admin/categories.php">
                            CATEGORIES
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'updates.php' && strpos($_SERVER['REQUEST_URI'] ?? '', 'admin') !== false) ? 'active' : '' ?>" href="<?= $base_url ?>admin/updates.php">
                            DISPATCHES
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'jobs.php') ? 'active' : '' ?>" href="<?= $base_url ?>student/jobs.php">
                            JOBS DIRECTORY
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'jobs.php') ? 'active' : '' ?>" href="<?= $base_url ?>student/jobs.php">
                            FIND JOBS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'] ?? '', 'employer') !== false) ? 'active' : '' ?>" href="<?= $base_url ?>employer/dashboard.php">
                            FOR EMPLOYERS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'faqs.php') ? 'active' : '' ?>" href="<?= $base_url ?>faqs.php">
                            FAQs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_script === 'about-us.php') ? 'active' : '' ?>" href="<?= $base_url ?>about-us.php">
                            ABOUT
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right Action Items -->
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <!-- Circular Search-Icon Button (Opens Floating Spotlight Modal) -->
                <button type="button" class="btn-circle-icon" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Search Campus Jobs (Ctrl+K)" aria-label="Search Jobs">
                    <i class="bi bi-search"></i>
                </button>

                <?php if ($current_user): ?>
                    <!-- Logged-in: Dashboard link + Avatar chip -->
                    <?php
                    $dashboard_link = $base_url . 'student/dashboard.php';
                    if ($current_user['role'] === 'employer') {
                        $dashboard_link = $base_url . 'employer/dashboard.php';
                    } elseif ($current_user['role'] === 'admin') {
                        $dashboard_link = $base_url . 'admin/reports.php';
                    }
                    $user_initial = strtoupper(substr($current_user['name'] ?? 'U', 0, 1));
                    ?>
                    <div class="dropdown">
                        <a href="#" class="user-avatar-chip dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-circle"><?= htmlspecialchars($user_initial) ?></span>
                            <span class="d-none d-sm-inline"><?= htmlspecialchars($current_user['name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-line p-2 rounded-4">
                            <li><h6 class="dropdown-header small text-muted-custom"><?= htmlspecialchars($current_user['email']) ?> (<?= htmlspecialchars(ucfirst($current_user['role'])) ?>)</h6></li>
                            <li><a class="dropdown-item rounded-3" href="<?= $dashboard_link ?>"><i class="bi bi-speedometer2 me-2 text-accent"></i> My Dashboard</a></li>
                            <?php if ($current_user['role'] === 'employer'): ?>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>employer/create-job.php"><i class="bi bi-plus-circle me-2 text-accent"></i> Post a Vacancy</a></li>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>employer/updates.php"><i class="bi bi-megaphone me-2 text-accent"></i> Post Dispatch</a></li>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>employer/applicants.php"><i class="bi bi-people me-2"></i> View Applicants</a></li>
                            <?php elseif ($current_user['role'] === 'student'): ?>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>student/my-applications.php"><i class="bi bi-folder-check me-2"></i> My Applications</a></li>
                            <?php elseif ($current_user['role'] === 'admin'): ?>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>admin/users.php"><i class="bi bi-people-fill me-2 text-accent"></i> Users &amp; Verification</a></li>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>admin/updates.php"><i class="bi bi-newspaper me-2 text-accent"></i> Career Dispatches</a></li>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>admin/categories.php"><i class="bi bi-grid-fill me-2"></i> Job Categories</a></li>
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>admin/reports.php"><i class="bi bi-bar-chart-fill me-2"></i> System Reports</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item rounded-3 text-danger" href="<?= $base_url ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest: POST A VACANCY solid pill + Login link -->
                    <a href="<?= $base_url ?>login.php" class="btn-outline-pill d-inline-flex px-3 py-2 text-decoration-none">
                        LOG IN
                    </a>
                    <a href="<?= $base_url ?>employer/create-job.php" class="btn-accent-pill">
                        POST A VACANCY
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Message Container -->
<?php if (isset($_SESSION['flash'])): ?>
<div class="container-paper mt-3">
    <?php render_flash(); ?>
</div>
<?php endif; ?>
