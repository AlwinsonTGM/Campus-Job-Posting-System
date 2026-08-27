<?php
/**
 * Campus Job Posting System - Employer Applicant Roster
 * Archetype D/G: Applicant Management Table (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$page_title = 'Applicant Evaluation Roster';

$dept = $user['organization_name'] ?? ($user['department'] ?? 'Office of the University Registrar');
$job_filter = $_GET['job_id'] ?? null;
$status_filter = $_GET['status'] ?? null;

// Handle decision submissions via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    $app_id = $_POST['app_id'];
    $action_type = $_POST['action_type'];
    $notes = trim($_POST['supervisor_notes'] ?? '');

    $interview_data = [];
    if ($action_type === 'interview_scheduled') {
        $interview_data = [
            'date' => $_POST['interview_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'time' => $_POST['interview_time'] ?? '10:00 AM',
            'venue' => $_POST['interview_venue'] ?? ($user['office_location'] ?? 'Admin Building Room 102')
        ];
    }

    update_application_status($app_id, $action_type, $notes, $interview_data);
    set_flash('success', 'Applicant status has been updated successfully!');
    header('Location: applicants.php' . ($job_filter ? "?job_id={$job_filter}" : ''));
    exit;
}

$all_dept_apps = get_applications(null, $job_filter, ($user['role'] === 'admin' ? null : $dept));

if ($status_filter) {
    $all_dept_apps = array_filter($all_dept_apps, function($a) use ($status_filter) {
        $st = strtolower($a['status'] ?? '');
        $target = strtolower($status_filter);
        if ($target === 'pending') return in_array($st, ['pending', 'pending review']);
        if ($target === 'review') return in_array($st, ['under_review', 'under review', 'under evaluation']);
        if ($target === 'interview') return in_array($st, ['interview_scheduled', 'interview scheduled']);
        if ($target === 'accepted') return in_array($st, ['accepted', 'accepted / hired']);
        if ($target === 'declined') return in_array($st, ['declined', 'rejected', 'declined / position filled']);
        return $st === $target;
    });
}

$dept_jobs = get_jobs(null, null, ($user['role'] === 'admin' ? null : $dept));

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
                    <a href="create-job.php" class="btn-pill">
                        <i class="bi bi-plus-circle-fill"></i> Post Vacancy
                    </a>
                    <a href="dashboard.php" class="btn-pill-outline">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                ';
                render_page_head(
                    '<i class="bi bi-people-fill text-accent me-1"></i> Applicant Management',
                    'Student Applicant Evaluation Roster',
                    'Review candidate profiles, inspect weekly class schedule availability, and coordinate interview appointments.',
                    $head_actions
                );
                ?>

                <!-- Filter Bar -->
                <div class="card-paper p-4 mb-4">
                    <form action="applicants.php" method="GET" class="form-paper">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label" for="filter-job">Filter by Job Requisition</label>
                                <select name="job_id" id="filter-job" class="form-select">
                                    <option value="">All Department Openings</option>
                                    <?php foreach ($dept_jobs as $j): ?>
                                        <option value="<?= $j['id'] ?>" <?= ($job_filter == $j['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($j['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label" for="filter-status">Filter by Status</label>
                                <select name="status" id="filter-status" class="form-select">
                                    <option value="">All Application Stages</option>
                                    <option value="pending" <?= ($status_filter === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                                    <option value="review" <?= ($status_filter === 'review') ? 'selected' : '' ?>>Under Evaluation</option>
                                    <option value="interview" <?= ($status_filter === 'interview') ? 'selected' : '' ?>>Interview Scheduled</option>
                                    <option value="accepted" <?= ($status_filter === 'accepted') ? 'selected' : '' ?>>Accepted / Hired</option>
                                    <option value="declined" <?= ($status_filter === 'declined') ? 'selected' : '' ?>>Declined / Filled</option>
                                </select>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn-pill w-100">
                                    <i class="bi bi-funnel-fill"></i> Filter
                                </button>
                                <?php if ($job_filter || $status_filter): ?>
                                    <a href="applicants.php" class="btn-pill-outline btn-pill-sm p-0 flex-shrink-0" style="width: 48px; height: 48px;" title="Clear Filters">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Applicant Table -->
                <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
                    <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center bg-surface">
                        <div>
                            <h3 class="card-paper-title mb-1">Candidate Submissions</h3>
                            <span class="small text-muted-custom">Total candidates matching criteria: <strong><?= count($all_dept_apps) ?></strong></span>
                        </div>
                    </div>

                    <?php if (empty($all_dept_apps)): ?>
                        <div class="p-4">
                            <?php
                            render_empty_state(
                                'bi-people',
                                'No Applicants Found',
                                'No student candidates have submitted applications matching the selected criteria.',
                                'applicants.php',
                                'Reset Roster Filters'
                            );
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-paper table-paper-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Candidate Profile</th>
                                        <th>Target Vacancy</th>
                                        <th>Degree Program</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_dept_apps as $app): ?>
                                        <tr>
                                            <td class="ps-4" data-label="Candidate Profile">
                                                <div class="fw-bold text-ink"><?= htmlspecialchars($app['student_name']) ?></div>
                                                <span class="small text-muted-custom"><?= htmlspecialchars($app['student_email']) ?></span>
                                            </td>
                                            <td data-label="Target Vacancy">
                                                <span class="fw-semibold text-ink"><?= htmlspecialchars($app['job_title']) ?></span>
                                            </td>
                                            <td data-label="Degree Program">
                                                <div class="small fw-semibold text-ink"><?= htmlspecialchars($app['course'] ?? 'BS Information Systems') ?></div>
                                                <span class="chip" style="font-size: 10px;"><?= htmlspecialchars($app['year_level'] ?? '2nd Year') ?></span>
                                            </td>
                                            <td data-label="Applied Date" class="small text-muted-custom">
                                                <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                            </td>
                                            <td data-label="Status">
                                                <?= render_status_badge($app['status']) ?>
                                            </td>
                                            <td class="text-end pe-4" data-label="Actions">
                                                <a href="review-app.php?id=<?= $app['id'] ?>" class="btn-pill btn-pill-sm">
                                                    <i class="bi bi-clipboard-check"></i> Evaluate Candidate
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
