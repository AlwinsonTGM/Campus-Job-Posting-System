<?php
/**
 * Campus Job Posting System - Shared Navbar Partial
 */
$current_user = get_logged_user();
$current_script = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-xl navbar-dark navbar-academic sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $base_url ?>index.php">
            <i class="bi bi-mortarboard-fill text-warning fs-4"></i>
            <span>KLD CampusJobs</span>
            <span class="brand-badge">PORTAL</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $current_script === 'index.php' ? 'active' : '' ?>" href="<?= $base_url ?>index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_script === 'jobs.php' ? 'active' : '' ?>" href="<?= $base_url ?>student/jobs.php">
                        <i class="bi bi-briefcase me-1"></i> Browse Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_script === 'faqs.php' ? 'active' : '' ?>" href="<?= $base_url ?>faqs.php">
                        <i class="bi bi-question-circle me-1"></i> FAQs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_script === 'about-us.php' ? 'active' : '' ?>" href="<?= $base_url ?>about-us.php">
                        <i class="bi bi-people me-1"></i> About Us
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <!-- Quick Demo Role Switcher (For easy testing and presentation) -->
                <li class="nav-item dropdown">
                    <button class="nav-btn nav-btn-demo dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-lightning-charge-fill text-warning"></i> Demo Switcher
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header"><i class="bi bi-person-badge"></i> Quick 1-Click Role Switch</h6></li>
                        <li><a class="dropdown-item" href="<?= $base_url ?>login.php?demo=student"><i class="bi bi-mortarboard text-success me-2"></i> Switch to <strong>Student</strong> (Juan Dela Cruz)</a></li>
                        <li><a class="dropdown-item" href="<?= $base_url ?>login.php?demo=employer"><i class="bi bi-building text-warning me-2"></i> Switch to <strong>Employer</strong> (KLD Registrar)</a></li>
                        <li><a class="dropdown-item" href="<?= $base_url ?>login.php?demo=admin"><i class="bi bi-shield-lock text-danger me-2"></i> Switch to <strong>Admin</strong> (KLD SASO Admin)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-muted small" href="<?= $base_url ?>login.php?reset=1"><i class="bi bi-arrow-counterclockwise me-2"></i> Reset Demo Dataset</a></li>
                    </ul>
                </li>

                <?php if ($current_user): ?>
                    <!-- Logged-in User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-btn nav-btn-user dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="user-role-badge"><?= htmlspecialchars($current_user['role']) ?></span>
                            <span class="fw-semibold text-truncate" style="max-width: 150px;"><?= htmlspecialchars($current_user['name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><h6 class="dropdown-header"><?= htmlspecialchars($current_user['email']) ?></h6></li>
                            <?php if ($current_user['role'] === 'student'): ?>
                                <li><a class="dropdown-item" href="<?= $base_url ?>student/dashboard.php"><i class="bi bi-speedometer2 me-2 text-kld-green"></i> Student Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>student/my-applications.php"><i class="bi bi-file-earmark-text me-2 text-kld-green"></i> My Applications</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>student/jobs.php"><i class="bi bi-search me-2 text-kld-green"></i> Find Vacancies</a></li>
                            <?php elseif ($current_user['role'] === 'employer'): ?>
                                <li><a class="dropdown-item" href="<?= $base_url ?>employer/dashboard.php"><i class="bi bi-speedometer2 me-2 text-kld-green"></i> Office Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>employer/create-job.php"><i class="bi bi-plus-circle me-2 text-kld-green"></i> Post New Job</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>employer/applicants.php"><i class="bi bi-people me-2 text-kld-green"></i> View Applicants</a></li>
                            <?php elseif ($current_user['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= $base_url ?>admin/reports.php"><i class="bi bi-speedometer2 me-2 text-kld-green"></i> Admin Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>admin/categories.php"><i class="bi bi-tags me-2 text-kld-gold"></i> Categories</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>admin/users.php"><i class="bi bi-person-gear me-2 text-danger"></i> User Management</a></li>
                                <li><a class="dropdown-item" href="<?= $base_url ?>admin/reports.php"><i class="bi bi-bar-chart-line me-2 text-kld-green"></i> Reports & Analytics</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= $base_url ?>settings.php"><i class="bi bi-gear me-2 text-secondary"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base_url ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Guest Links (2-Column Grid on Mobile, Inline on Desktop) -->
                    <li class="nav-item">
                        <div class="nav-guest-row">
                            <a class="nav-btn nav-btn-signin" href="<?= $base_url ?>login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </a>
                            <a class="nav-btn nav-btn-register" href="<?= $base_url ?>register.php">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Message Container -->
<?php $flash = get_flash(); if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm alert-auto-dismiss" role="alert">
        <div class="d-flex align-items-center gap-2">
            <?php if ($flash['type'] === 'success'): ?>
                <i class="bi bi-check-circle-fill fs-5"></i>
            <?php elseif ($flash['type'] === 'danger'): ?>
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <?php elseif ($flash['type'] === 'warning'): ?>
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
            <?php else: ?>
                <i class="bi bi-info-circle-fill fs-5"></i>
            <?php endif; ?>
            <div><?= htmlspecialchars($flash['message']) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>
