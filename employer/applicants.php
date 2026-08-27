<?php
/**
 * Campus Job Posting System - Employer Applicant Roster & Evaluation
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$page_title = 'Applicant Review Roster';

$dept = $user['department'] ?? 'Office of the University Registrar';
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
    $all_dept_apps = array_filter($all_dept_apps, fn($a) => $a['status'] === $status_filter);
}

$dept_jobs = get_jobs(null, null, ($user['role'] === 'admin' ? null : $dept));

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">Employer Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Applicant Roster</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-ink mb-0">Student Applicant Evaluation Roster</h2>
            </div>
            <div class="mt-2 mt-md-0 text-muted-custom small">
                Showing <strong><?= count($all_dept_apps) ?></strong> candidates
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-line shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="applicants.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-ink mb-1">Filter by Job Posting</label>
                    <select name="job_id" class="form-select">
                        <option value="">All Department Jobs</option>
                        <?php foreach ($dept_jobs as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= ($job_filter == $j['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($j['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-ink mb-1">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Application Statuses</option>
                        <option value="pending" <?= ($status_filter === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                        <option value="interview_scheduled" <?= ($status_filter === 'interview_scheduled') ? 'selected' : '' ?>>Interview Scheduled</option>
                        <option value="accepted" <?= ($status_filter === 'accepted') ? 'selected' : '' ?>>Accepted / Hired</option>
                        <option value="declined" <?= ($status_filter === 'declined') ? 'selected' : '' ?>>Declined / Filled</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-1 mt-auto">
                    <button type="submit" class="btn-accent-pill w-100 mt-md-4 py-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <?php if ($job_filter || $status_filter): ?>
                        <a href="applicants.php" class="btn-circle-icon flex-shrink-0 mt-md-4" style="width: 40px; height: 40px;" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Applicants Table -->
        <?php if (empty($all_dept_apps)): ?>
            <div class="card border-line shadow-sm rounded-4 p-5 text-center bg-white my-4">
                <div class="stat-icon bg-surface text-muted-custom mx-auto mb-3 fs-1">
                    <i class="bi bi-people"></i>
                </div>
                <h4 class="fw-bold text-ink">No Student Applications Found</h4>
                <p class="text-muted-custom small mb-0">No candidates have applied under the selected filters yet.</p>
            </div>
        <?php else: ?>
            <div class="card border-line shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-cream border-bottom border-line">
                            <tr>
                                <th class="ps-4 text-ink fw-bold">Candidate Profile</th>
                                <th class="text-ink fw-bold">Applied Vacancy</th>
                                <th class="text-ink fw-bold">Academic Standing</th>
                                <th class="text-ink fw-bold">Applied On</th>
                                <th class="text-ink fw-bold">Status</th>
                                <th class="text-end pe-4 text-ink fw-bold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_dept_apps as $app): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-ink"><?= htmlspecialchars($app['student_name']) ?></div>
                                        <span class="text-muted-custom small"><?= htmlspecialchars($app['student_email']) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-ink"><?= htmlspecialchars($app['job_title']) ?></span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-ink"><?= htmlspecialchars($app['course']) ?></div>
                                        <span class="chip-tag" style="font-size: 10px;"><?= htmlspecialchars($app['year_level']) ?></span>
                                    </td>
                                    <td class="small text-muted-custom">
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_pill = match($app['status']) {
                                            'accepted' => 'pill-badge',
                                            'declined', 'rejected' => 'pill-badge pill-badge-ink',
                                            'interview_scheduled' => 'pill-badge',
                                            default => 'chip-tag'
                                        };
                                        ?>
                                        <span class="<?= $badge_pill ?>" style="font-size: 11px;">
                                            <?= htmlspecialchars($app['status_label']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn-accent-pill py-1 px-3" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $app['id'] ?>">
                                            <i class="bi bi-clipboard-check me-1"></i> Review Candidate
                                        </button>
                                    </td>
                                </tr>

                                <!-- Comprehensive Candidate Review Modal -->
                                <div class="modal fade" id="reviewModal<?= $app['id'] ?>" tabindex="-1" aria-labelledby="reviewLabel<?= $app['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                                        <div class="modal-content rounded-4 border-line shadow-lg">
                                            <div class="modal-header bg-kld-gradient text-white">
                                                <h5 class="modal-title fw-bold" id="reviewLabel<?= $app['id'] ?>">
                                                    <i class="bi bi-person-lines-fill me-1 text-accent"></i> Candidate Application: <?= htmlspecialchars($app['student_name']) ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4 bg-surface">
                                                
                                                <!-- Top Applicant Card -->
                                                <div class="p-3 bg-cream rounded-3 border-line border mb-4">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <span class="text-muted-custom small">Applicant Name:</span>
                                                            <div class="fw-bold text-ink fs-6"><?= htmlspecialchars($app['student_name']) ?></div>
                                                            <div class="small text-muted-custom">Student ID: <strong class="text-ink"><?= htmlspecialchars($app['student_number'] ?? '2024-00123') ?></strong></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="text-muted-custom small">Target Position:</span>
                                                            <div class="fw-bold text-ink"><?= htmlspecialchars($app['job_title']) ?></div>
                                                            <div class="small text-muted-custom">Department: <?= htmlspecialchars($app['department']) ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="text-muted-custom small">Contact Phone & Email:</span>
                                                            <div class="small text-ink"><?= htmlspecialchars($app['phone'] ?? 'N/A') ?> &bull; <?= htmlspecialchars($app['student_email']) ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="text-muted-custom small">Degree & Year:</span>
                                                            <div class="small text-ink"><?= htmlspecialchars($app['course']) ?> (<?= htmlspecialchars($app['year_level']) ?>)</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Cover Letter -->
                                                <div class="mb-4">
                                                    <h6 class="fw-bold text-ink text-uppercase small"><i class="bi bi-file-text text-accent me-1"></i> Statement of Intent / Cover Letter</h6>
                                                    <div class="p-3 bg-white border-line border rounded-3 text-muted-custom small lh-lg">
                                                        <?= nl2br(htmlspecialchars($app['cover_letter'])) ?>
                                                    </div>
                                                </div>

                                                <!-- Available Shifts -->
                                                <div class="mb-4">
                                                    <h6 class="fw-bold text-ink text-uppercase small"><i class="bi bi-calendar3 text-accent me-1"></i> Candidate Shift Availability (Vacant Periods)</h6>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php if (!empty($app['availability'])): ?>
                                                            <?php foreach ($app['availability'] as $av): ?>
                                                                <span class="chip-tag"><?= htmlspecialchars($av) ?></span>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted-custom small">Flexible schedule</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Document Attachment Preview Mock -->
                                                <div class="p-3 bg-white rounded-3 border-line border d-flex justify-content-between align-items-center mb-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                                        <div>
                                                            <div class="fw-semibold text-ink small"><?= htmlspecialchars($app['resume_file']) ?></div>
                                                            <span class="text-muted-custom small">Official Student Resume & COR</span>
                                                        </div>
                                                    </div>
                                                    <span class="pill-badge" style="font-size: 11px;">Verified Enrollment</span>
                                                </div>

                                                <hr class="border-line">

                                                <!-- Decision Action Form -->
                                                <h6 class="fw-bold text-ink text-uppercase small mb-3"><i class="bi bi-check2-circle text-accent me-1"></i> Hiring Decision & Status Controls</h6>
                                                
                                                <form action="applicants.php" method="POST">
                                                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">

                                                    <!-- Interview Scheduling Fields -->
                                                    <div class="p-3 bg-cream rounded-3 border-line border mb-3">
                                                        <label class="form-label small fw-bold text-ink mb-2">Schedule Face-to-Face or Online Interview:</label>
                                                        <div class="row g-2">
                                                            <div class="col-md-4">
                                                                <label class="form-label small text-muted-custom mb-0">Interview Date</label>
                                                                <input type="date" name="interview_date" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small text-muted-custom mb-0">Interview Time</label>
                                                                <input type="text" name="interview_time" class="form-control form-control-sm" value="02:00 PM" placeholder="e.g. 10:00 AM">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small text-muted-custom mb-0">Venue / Room / Link</label>
                                                                <input type="text" name="interview_venue" class="form-control form-control-sm" value="<?= htmlspecialchars($user['office_location'] ?? 'Admin Bldg Room 102') ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-ink">Supervisor Remarks / Feedback to Student</label>
                                                        <textarea name="supervisor_notes" rows="2" class="form-control form-control-sm" placeholder="Add specific feedback or interview instructions..."><?= htmlspecialchars($app['supervisor_notes'] ?? '') ?></textarea>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-2 pt-2">
                                                        <button type="submit" name="action_type" value="interview_scheduled" class="btn-accent-pill py-1 px-3" style="font-size: 12px;">
                                                            <i class="bi bi-calendar-check me-1"></i> Shortlist & Set Interview
                                                        </button>

                                                        <button type="submit" name="action_type" value="accepted" class="btn-accent-pill py-1 px-3" style="font-size: 12px;">
                                                            <i class="bi bi-person-check-fill me-1"></i> Accept & Hire Applicant
                                                        </button>

                                                        <button type="submit" name="action_type" value="declined" class="btn btn-outline-danger btn-sm rounded-pill px-3" style="font-size: 12px;">
                                                            <i class="bi bi-x-circle me-1"></i> Decline Application
                                                        </button>

                                                        <button type="button" class="btn-soft-pill py-1 px-3 ms-auto" style="font-size: 12px;" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
