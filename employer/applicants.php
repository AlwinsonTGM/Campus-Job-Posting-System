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

<main class="py-4 bg-light flex-grow-1">
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
                <h2 class="fw-bold text-dark mb-0">Student Applicant Evaluation Roster</h2>
            </div>
            <div class="mt-2 mt-md-0 text-muted small">
                Showing <strong><?= count($all_dept_apps) ?></strong> candidates
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="applicants.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-muted mb-1">Filter by Job Posting</label>
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
                    <label class="form-label small fw-semibold text-muted mb-1">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Application Statuses</option>
                        <option value="pending" <?= ($status_filter === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                        <option value="interview_scheduled" <?= ($status_filter === 'interview_scheduled') ? 'selected' : '' ?>>Interview Scheduled</option>
                        <option value="accepted" <?= ($status_filter === 'accepted') ? 'selected' : '' ?>>Accepted / Hired</option>
                        <option value="declined" <?= ($status_filter === 'declined') ? 'selected' : '' ?>>Declined / Filled</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-1 mt-auto">
                    <button type="submit" class="btn btn-academic w-100 mt-md-4">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <?php if ($job_filter || $status_filter): ?>
                        <a href="applicants.php" class="btn btn-outline-secondary mt-md-4" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Applicants Table -->
        <?php if (empty($all_dept_apps)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
                <div class="stat-icon bg-light text-muted mx-auto mb-3 fs-1">
                    <i class="bi bi-people"></i>
                </div>
                <h4 class="fw-bold text-dark">No Student Applications Found</h4>
                <p class="text-muted small mb-0">No candidates have applied under the selected filters yet.</p>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Candidate Profile</th>
                                <th>Applied Vacancy</th>
                                <th>Academic Standing</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_dept_apps as $app): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($app['student_name']) ?></div>
                                        <span class="text-muted small"><?= htmlspecialchars($app['student_email']) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary"><?= htmlspecialchars($app['job_title']) ?></span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= htmlspecialchars($app['course']) ?></div>
                                        <span class="badge bg-light text-dark border small"><?= htmlspecialchars($app['year_level']) ?></span>
                                    </td>
                                    <td class="small text-muted">
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $app['status_badge'] ?> text-capitalize px-3 py-2">
                                            <?= htmlspecialchars($app['status_label']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-academic" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $app['id'] ?>">
                                            <i class="bi bi-clipboard-check me-1"></i> Review Candidate
                                        </button>
                                    </td>
                                </tr>

                                <!-- Comprehensive Candidate Review Modal -->
                                <div class="modal fade" id="reviewModal<?= $app['id'] ?>" tabindex="-1" aria-labelledby="reviewLabel<?= $app['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title fw-bold" id="reviewLabel<?= $app['id'] ?>">
                                                    <i class="bi bi-person-lines-fill me-1 text-warning"></i> Candidate Application: <?= htmlspecialchars($app['student_name']) ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                
                                                <!-- Top Applicant Card -->
                                                <div class="p-3 bg-light rounded-3 border mb-4">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <span class="text-muted small">Applicant Name:</span>
                                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($app['student_name']) ?></div>
                                                            <div class="small text-muted">Student ID: <strong><?= htmlspecialchars($app['student_number'] ?? '2024-00123') ?></strong></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="text-muted small">Target Position:</span>
                                                            <div class="fw-bold text-primary"><?= htmlspecialchars($app['job_title']) ?></div>
                                                            <div class="small text-muted">Department: <?= htmlspecialchars($app['department']) ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="text-muted small">Contact Phone & Email:</span>
                                                            <div class="small text-dark"><?= htmlspecialchars($app['phone'] ?? 'N/A') ?> &bull; <?= htmlspecialchars($app['student_email']) ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <span class="text-muted small">Degree & Year:</span>
                                                            <div class="small text-dark"><?= htmlspecialchars($app['course']) ?> (<?= htmlspecialchars($app['year_level']) ?>)</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Cover Letter -->
                                                <div class="mb-4">
                                                    <h6 class="fw-bold text-dark text-uppercase small"><i class="bi bi-file-text text-primary me-1"></i> Statement of Intent / Cover Letter</h6>
                                                    <div class="p-3 bg-white border rounded-3 text-secondary small lh-lg">
                                                        <?= nl2br(htmlspecialchars($app['cover_letter'])) ?>
                                                    </div>
                                                </div>

                                                <!-- Available Shifts -->
                                                <div class="mb-4">
                                                    <h6 class="fw-bold text-dark text-uppercase small"><i class="bi bi-calendar3 text-primary me-1"></i> Candidate Shift Availability (Vacant Periods)</h6>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php if (!empty($app['availability'])): ?>
                                                            <?php foreach ($app['availability'] as $av): ?>
                                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small"><?= htmlspecialchars($av) ?></span>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Flexible schedule</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Document Attachment Preview Mock -->
                                                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center mb-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                                        <div>
                                                            <div class="fw-semibold text-dark small"><?= htmlspecialchars($app['resume_file']) ?></div>
                                                            <span class="text-muted small">Official Student Resume & COR</span>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success small">Verified Enrollment</span>
                                                </div>

                                                <hr>

                                                <!-- Decision Action Form -->
                                                <h6 class="fw-bold text-dark text-uppercase small mb-3"><i class="bi bi-check2-circle text-primary me-1"></i> Hiring Decision & Status Controls</h6>
                                                
                                                <form action="applicants.php" method="POST">
                                                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">

                                                    <!-- Interview Scheduling Fields (collapsible/visible) -->
                                                    <div class="p-3 bg-light rounded-3 border mb-3">
                                                        <label class="form-label small fw-bold text-dark mb-2">Schedule Face-to-Face or Online Interview:</label>
                                                        <div class="row g-2">
                                                            <div class="col-md-4">
                                                                <label class="form-label small text-muted mb-0">Interview Date</label>
                                                                <input type="date" name="interview_date" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small text-muted mb-0">Interview Time</label>
                                                                <input type="text" name="interview_time" class="form-control form-control-sm" value="02:00 PM" placeholder="e.g. 10:00 AM">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small text-muted mb-0">Venue / Room / Link</label>
                                                                <input type="text" name="interview_venue" class="form-control form-control-sm" value="<?= htmlspecialchars($user['office_location'] ?? 'Admin Bldg Room 102') ?>">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-dark">Supervisor Remarks / Feedback to Student</label>
                                                        <textarea name="supervisor_notes" rows="2" class="form-control form-control-sm" placeholder="Add specific feedback or interview instructions..."><?= htmlspecialchars($app['supervisor_notes'] ?? '') ?></textarea>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-2 pt-2">
                                                        <button type="submit" name="action_type" value="interview_scheduled" class="btn btn-info text-white btn-sm fw-bold">
                                                            <i class="bi bi-calendar-check me-1"></i> Shortlist & Set Interview
                                                        </button>

                                                        <button type="submit" name="action_type" value="accepted" class="btn btn-success btn-sm fw-bold">
                                                            <i class="bi bi-person-check-fill me-1"></i> Accept & Hire Applicant
                                                        </button>

                                                        <button type="submit" name="action_type" value="declined" class="btn btn-outline-danger btn-sm">
                                                            <i class="bi bi-x-circle me-1"></i> Decline Application
                                                        </button>

                                                        <button type="button" class="btn btn-secondary btn-sm ms-auto" data-bs-dismiss="modal">Close</button>
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
