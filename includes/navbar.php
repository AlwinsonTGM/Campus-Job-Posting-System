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
$user_unread_notifs_count = ($current_user && function_exists('get_unread_notifications_count')) ? get_unread_notifications_count($current_user['id']) : 0;
$user_recent_notifs = ($current_user && function_exists('get_user_notifications')) ? get_user_notifications($current_user['id'], 5) : [];
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

        <!-- Mobile Controls (Search & Notifications to the left of the Hamburger) -->
        <div class="d-flex align-items-center gap-2 d-lg-none ms-auto">
            <button type="button" class="btn-circle-icon btn-nav-search-mobile" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Search Campus Jobs" aria-label="Search Jobs">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($current_user): ?>
                <a href="<?= $base_url ?>notifications.php" class="btn-circle-icon position-relative text-decoration-none" title="Notifications" aria-label="View Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="nav-notif-badge <?= ($user_unread_notifs_count > 0) ? '' : 'd-none' ?>" id="navNotificationBadgeMobile">
                        <?= $user_unread_notifs_count > 99 ? '99+' : $user_unread_notifs_count ?>
                    </span>
                </a>
            <?php endif; ?>
            <button class="navbar-toggler border-0 shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

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
            <div class="paper-nav-actions d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <!-- Circular Search-Icon Button (Desktop only; mobile is in top bar beside hamburger) -->
                <button type="button" class="btn-circle-icon d-none d-lg-inline-flex" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Search Campus Jobs (Ctrl+K)" aria-label="Search Jobs">
                    <i class="bi bi-search"></i>
                </button>

                <?php if ($current_user): ?>
                    <!-- Notification Bell Dropdown (Desktop) -->
                    <div class="dropdown d-none d-lg-block">
                        <button type="button" class="btn-circle-icon position-relative" id="navNotificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" aria-label="Notifications">
                            <i class="bi bi-bell"></i>
                            <span class="nav-notif-badge <?= ($user_unread_notifs_count > 0) ? '' : 'd-none' ?>" id="navNotificationBadge">
                                <?= $user_unread_notifs_count > 99 ? '99+' : $user_unread_notifs_count ?>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu shadow border-line p-0" aria-labelledby="navNotificationDropdown">
                            <div class="notification-dropdown-header d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="mb-0 fw-extrabold text-ink small text-uppercase tracking-wider">Notifications</h6>
                                    <span class="badge bg-cream text-ink border border-line small py-0 px-2" style="font-size: 11px;">Inbox</span>
                                </div>
                                <button type="button" class="btn btn-link p-0 text-muted-custom small text-decoration-none" id="navNotificationMarkAllBtn" style="font-size: 12px;">
                                    <i class="bi bi-check2-all me-1"></i>Mark all read
                                </button>
                            </div>
                            <div class="notification-list-scroll" id="navNotificationList">
                                <?php if (empty($user_recent_notifs)): ?>
                                    <div class="p-4 text-center text-muted-custom">
                                        <i class="bi bi-bell-slash fs-2 mb-2 d-block text-muted opacity-50"></i>
                                        <p class="small mb-0">No notifications yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($user_recent_notifs as $item): 
                                        $is_unread = empty($item['is_read']);
                                        $item_link = $base_url . 'api/notifications.php?action=click&id=' . $item['id'];
                                    ?>
                                        <a href="<?= $item_link ?>" class="notification-item d-flex align-items-start gap-3 p-3 border-bottom border-line text-decoration-none <?= $is_unread ? 'bg-cream-tint' : '' ?>">
                                            <div class="notif-icon-circle bg-<?= htmlspecialchars($item['badge_color'] ?? 'primary') ?>-subtle text-<?= htmlspecialchars($item['badge_color'] ?? 'primary') ?> flex-shrink-0">
                                                <i class="bi <?= htmlspecialchars($item['icon'] ?? 'bi-bell') ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <h6 class="small fw-bold text-ink mb-0 text-truncate pe-2"><?= htmlspecialchars($item['title']) ?></h6>
                                                    <span class="text-muted-custom x-small"><?= htmlspecialchars(time_ago_short($item['created_at'])) ?></span>
                                                </div>
                                                <p class="small text-muted-custom mb-0 line-clamp-2"><?= htmlspecialchars($item['message']) ?></p>
                                            </div>
                                            <?php if ($is_unread): ?>
                                                <span class="badge-dot-unread" title="Unread"></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="p-2 text-center bg-cream border-top border-line">
                                <a href="<?= $base_url ?>notifications.php" class="btn btn-sm btn-link text-ink fw-bold text-decoration-none small py-1">
                                    View All Notifications <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

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
                    <div class="dropdown nav-user-mobile-wrap">
                        <a href="#" class="user-avatar-chip dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-circle"><?= htmlspecialchars($user_initial) ?></span>
                            <span class="user-chip-name"><?= htmlspecialchars($current_user['name']) ?></span>
                            <span class="badge bg-cream text-ink border border-line ms-auto small d-inline-block d-lg-none" style="font-size: 10px;"><?= htmlspecialchars(ucfirst($current_user['role'])) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-line p-2 rounded-4">
                            <li><h6 class="dropdown-header small text-muted-custom"><?= htmlspecialchars($current_user['email']) ?> (<?= htmlspecialchars(ucfirst($current_user['role'])) ?>)</h6></li>
                            <li><a class="dropdown-item rounded-3" href="<?= $dashboard_link ?>"><i class="bi bi-speedometer2 me-2 text-accent"></i> My Dashboard</a></li>
                            <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>notifications.php"><i class="bi bi-bell me-2 text-accent"></i> Notifications Center</a></li>
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
                                <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>admin/ai-settings.php"><i class="bi bi-cpu-fill me-2 text-success"></i> NVIDIA AI &amp; Robot</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item rounded-3" href="<?= $base_url ?>settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item rounded-3 text-danger" href="<?= $base_url ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest: LOG IN + POST A VACANCY action buttons -->
                    <div class="paper-nav-btn-group">
                        <a href="<?= $base_url ?>login.php" class="btn-paper-nav-login">
                            LOG IN
                        </a>
                        <a href="<?= $base_url ?>employer/create-job.php" class="btn-paper-nav-post">
                            POST A VACANCY
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Floating Flash Toast Container -->
<?php if (isset($_SESSION['flash'])): ?>
<div class="flash-toast-wrapper" id="flashToastWrapper" role="region" aria-label="Notifications">
    <?php render_flash(); ?>
</div>
<?php endif; ?>
