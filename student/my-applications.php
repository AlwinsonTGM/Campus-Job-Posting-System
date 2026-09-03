<?php
/**
 * Campus Job Posting System - Student Application Tracker
 * Archetype D: Status Tracker & 4-Step Stepper (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['student', 'admin']);
$user = get_logged_user();
$page_title = 'My Assistantship Applications';

// Handle withdrawal
if (isset($_POST['withdraw_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security validation failed (invalid CSRF session token). Please refresh and try again.');
        header('Location: my-applications.php');
        exit;
    }

    $withdraw_id = (int)$_POST['withdraw_id'];
    $target_app = get_application_by_id($withdraw_id);

    if (!$target_app || (int)$target_app['student_id'] !== (int)($user['id'] ?? 0)) {
        set_flash('danger', 'Unauthorized or non-existent application.');
        header('Location: my-applications.php');
        exit;
    }

    // Only allow withdrawing applications that are still pending review
    if (!in_array(strtolower($target_app['status'] ?? ''), ['pending', 'pending review'])) {
        set_flash('warning', 'Applications that are under review, scheduled for interview, or accepted cannot be withdrawn.');
        header('Location: my-applications.php');
        exit;
    }

    delete_application($withdraw_id, $user['id'] ?? null);
    set_flash('info', 'Application was successfully withdrawn.');
    header('Location: my-applications.php');
    exit;
}

$my_apps = get_applications($user['id'] ?? 0);
$filter_status = trim($_GET['status'] ?? '');

if (!empty($filter_status)) {
    $my_apps = array_filter($my_apps, function($a) use ($filter_status) {
        $st = strtolower($a['status'] ?? '');
        $target = strtolower($filter_status);
        if ($target === 'pending') return in_array($st, ['pending', 'pending review']);
        if ($target === 'review') return in_array($st, ['under_review', 'under review', 'under evaluation']);
        if ($target === 'interview') return in_array($st, ['interview_scheduled', 'interview scheduled']);
        if ($target === 'accepted') return in_array($st, ['accepted', 'accepted / hired']);
        if ($target === 'declined') return in_array($st, ['declined', 'rejected', 'declined / position filled']);
        return true;
    });
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                $head_actions = '
                    <a href="jobs.php" class="btn-pill">
                        <i class="bi bi-search"></i> Apply for More Opportunities
                    </a>
                ';
                render_page_head(
                    '<i class="bi bi-folder-check text-accent me-1"></i> Application Tracker & Status',
                    'My Assistantship Applications',
                    'Monitor your evaluation progress, scheduled interviews, and appointment confirmations in real-time.',
                    $head_actions
                );
                ?>

                <!-- Filter Tabs -->
                <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom border-line">
                    <a href="my-applications.php" class="chip chip-selectable <?= empty($filter_status) ? 'active' : '' ?>">
                        All Submissions
                    </a>
                    <a href="my-applications.php?status=pending" class="chip chip-selectable <?= ($filter_status === 'pending') ? 'active' : '' ?>">
                        <i class="bi bi-hourglass-split text-accent"></i> Pending Review
                    </a>
                    <a href="my-applications.php?status=review" class="chip chip-selectable <?= ($filter_status === 'review') ? 'active' : '' ?>">
                        <i class="bi bi-search text-accent"></i> Under Evaluation
                    </a>
                    <a href="my-applications.php?status=interview" class="chip chip-selectable <?= ($filter_status === 'interview') ? 'active' : '' ?>">
                        <i class="bi bi-calendar-event text-accent"></i> Interviews Scheduled
                    </a>
                    <a href="my-applications.php?status=accepted" class="chip chip-selectable <?= ($filter_status === 'accepted') ? 'active' : '' ?>">
                        <i class="bi bi-check-circle-fill text-accent"></i> Accepted / Hired
                    </a>
                </div>

                <!-- Applications List -->
                <?php if (empty($my_apps)): ?>
                    <?php
                    render_empty_state(
                        'bi-folder2-open',
                        'No Applications Found',
                        'You do not have any submitted applications in this view category. Explore active campus vacancies to apply.',
                        'jobs.php',
                        'Explore Campus Opportunities'
                    );
                    ?>
                <?php else: ?>
                    <div class="d-flex flex-column gap-4 mb-5">
                        <?php foreach ($my_apps as $app): 
                            $is_pending = in_array(strtolower($app['status'] ?? ''), ['pending', 'pending review']);
                        ?>
                            <div class="card-paper p-4 p-md-4 reveal-fade-rise">
                                
                                <!-- Top Bar: Title + Department + Status Badge -->
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3 pb-3 border-bottom border-line">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle" style="font-size: 11px;">
                                                #APP-<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?>
                                            </span>
                                            <span class="small text-muted-custom">
                                                Applied on <?= date('F d, Y', strtotime($app['applied_at'] ?? 'now')) ?>
                                            </span>
                                        </div>
                                        <h3 class="card-paper-title fs-5 mb-1">
                                            <a href="job-details.php?id=<?= $app['job_id'] ?? 0 ?>" class="text-ink text-decoration-none">
                                                <?= htmlspecialchars($app['job_title']) ?>
                                            </a>
                                        </h3>
                                        <span class="small text-muted-custom">
                                            <i class="bi bi-building text-accent me-1"></i><?= htmlspecialchars($app['department']) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <?= render_status_badge($app['status']) ?>
                                    </div>
                                </div>

                                <!-- 4-Step Stepper Component -->
                                <div class="my-4 px-2 px-md-4">
                                    <?php render_stepper($app['status']); ?>
                                </div>

                                <!-- Conditional Notice Callouts -->
                                <?php if (in_array(strtolower($app['status']), ['interview_scheduled', 'interview scheduled']) && !empty($app['interview_date'])): ?>
                                    <div class="card-paper bg-cream p-3 mb-3 border border-line">
                                        <div class="d-flex align-items-center gap-2 fw-bold text-ink mb-1">
                                            <i class="bi bi-calendar-check-fill text-accent fs-5"></i>
                                            <span>Official Interview Schedule</span>
                                        </div>
                                        <div class="small text-ink">
                                            <strong>Date & Time:</strong> <?= htmlspecialchars($app['interview_date']) ?> at <?= htmlspecialchars($app['interview_time']) ?><br>
                                            <strong>Interview Venue / Room:</strong> <?= htmlspecialchars($app['interview_venue']) ?><br>
                                            <span class="text-muted-custom"><em>Please bring your valid student ID card and latest study load.</em></span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (in_array(strtolower($app['status']), ['accepted', 'accepted / hired'])): ?>
                                    <div class="card-paper p-3 mb-3 border border-accent bg-surface">
                                        <div class="d-flex align-items-center gap-2 fw-bold text-accent mb-1">
                                            <i class="bi bi-check-circle-fill fs-5"></i>
                                            <span>Congratulations! You are officially appointed as Student Assistant.</span>
                                        </div>
                                        <p class="small text-muted-custom mb-0">
                                            Please report to <strong><?= htmlspecialchars($app['department']) ?></strong> to sign your student assistantship agreement and receive your Daily Time Record (DTR) orientation.
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <!-- Availability & Attached Documents -->
                                <div class="row g-3 small text-muted-custom mb-3 pt-2">
                                    <div class="col-md-7">
                                        <span class="fw-bold text-ink d-block mb-1">Indicated Free Class Shift Availability:</span>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (($app['availability'] ?? []) as $av): ?>
                                                <span class="chip" style="font-size: 11px;"><?= htmlspecialchars($av) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <span class="fw-bold text-ink d-block mb-1">Attached Credentials:</span>
                                        <div class="d-flex align-items-center justify-content-between p-2 bg-surface rounded-3 border border-line">
                                            <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                                                <span class="text-ink small text-truncate"><?= htmlspecialchars($app['resume_file'] ?? 'Juan_Dela_Cruz_Resume.pdf') ?></span>
                                            </div>
                                            <a href="../view-resume.php?app_id=<?= $app['id'] ?>" target="_blank" class="btn-pill-outline btn-pill-sm py-0 px-2" style="font-size: 11px; white-space: nowrap;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> View PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions Footer -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-line">
                                    <a href="job-details.php?id=<?= $app['job_id'] ?? 0 ?>" class="btn-pill-outline btn-pill-sm">
                                        <i class="bi bi-eye"></i> View Requisition
                                    </a>

                                    <?php if ($is_pending): ?>
                                        <form action="my-applications.php" method="POST" onsubmit="return confirm('Are you sure you want to withdraw this application?');">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="withdraw_id" value="<?= $app['id'] ?>">
                                            <button type="submit" class="btn-pill-outline btn-pill-sm text-danger border-danger">
                                                <i class="bi bi-trash"></i> Withdraw
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
