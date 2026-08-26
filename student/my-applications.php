<?php
/**
 * Campus Job Posting System - My Applications (Status Tracker)
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['student', 'admin']);
$user = get_logged_user();
$page_title = 'My Job Applications';

// Handle withdrawal
if (isset($_POST['withdraw_id'])) {
    $withdraw_id = $_POST['withdraw_id'];
    $apps = $_SESSION['applications'] ?? load_json_file('applications.json');
    $filtered = array_filter($apps, function($a) use ($withdraw_id, $user) {
        return !($a['id'] == $withdraw_id && $a['student_id'] == $user['id']);
    });
    $_SESSION['applications'] = array_values($filtered);
    save_json_file('applications.json', $_SESSION['applications']);
    set_flash('info', 'Application was successfully withdrawn.');
    header('Location: my-applications.php');
    exit;
}

$my_apps = get_applications($user['id']);

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
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Applications</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-dark mb-0">Application Tracker & Status</h2>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="jobs.php" class="btn btn-academic btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Apply for More Vacancies
                </a>
            </div>
        </div>

        <?php if (empty($my_apps)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
                <div class="stat-icon bg-light text-muted mx-auto mb-3 fs-1">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <h4 class="fw-bold text-dark">No Submitted Applications Found</h4>
                <p class="text-muted small mb-3">You have not applied for any campus student assistant positions yet.</p>
                <div>
                    <a href="jobs.php" class="btn btn-gold btn-sm px-4 fw-bold">Explore Campus Jobs Now</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Applied Vacancy</th>
                                <th>Department</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Interview / Remarks</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_apps as $app): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($app['job_title']) ?></div>
                                        <span class="text-muted small">App Ref: #APP-<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold text-secondary"><?= htmlspecialchars($app['department']) ?></span>
                                    </td>
                                    <td class="small text-muted">
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $app['status_badge'] ?> text-capitalize px-3 py-2">
                                            <?= htmlspecialchars($app['status_label']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'interview_scheduled' && !empty($app['interview_date'])): ?>
                                            <div class="small text-primary fw-semibold">
                                                <i class="bi bi-calendar-event me-1"></i> <?= htmlspecialchars($app['interview_date']) ?> (<?= htmlspecialchars($app['interview_time']) ?>)
                                                <div class="text-muted small">Venue: <?= htmlspecialchars($app['interview_venue']) ?></div>
                                            </div>
                                        <?php elseif ($app['status'] === 'accepted'): ?>
                                            <span class="small text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Hired for Semester</span>
                                        <?php elseif (!empty($app['supervisor_notes'])): ?>
                                            <span class="small text-muted text-truncate d-inline-block" style="max-width: 200px;">
                                                <?= htmlspecialchars($app['supervisor_notes']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="small text-muted">In Queue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#appModal<?= $app['id'] ?>">
                                            <i class="bi bi-eye"></i> View Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Application Detail Modal -->
                                <div class="modal fade" id="appModal<?= $app['id'] ?>" tabindex="-1" aria-labelledby="appModalLabel<?= $app['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title fw-bold" id="appModalLabel<?= $app['id'] ?>">
                                                    <i class="bi bi-file-earmark-text me-1 text-warning"></i> Application Record: <?= htmlspecialchars($app['job_title']) ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                    <div>
                                                        <span class="text-muted small">Campus Department:</span>
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($app['department']) ?></div>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-<?= $app['status_badge'] ?> fs-6 px-3 py-2">
                                                            <?= htmlspecialchars($app['status_label']) ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <?php if ($app['status'] === 'interview_scheduled' && !empty($app['interview_date'])): ?>
                                                    <div class="alert alert-info p-3 rounded-3 mb-3">
                                                        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-calendar-check-fill text-info me-1"></i> Interview Notice</h6>
                                                        <div class="small text-secondary">
                                                            <strong>Date & Time:</strong> <?= htmlspecialchars($app['interview_date']) ?> at <?= htmlspecialchars($app['interview_time']) ?><br>
                                                            <strong>Location:</strong> <?= htmlspecialchars($app['interview_venue']) ?><br>
                                                            <em>Please bring your valid Student ID and study load printout.</em>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Statement of Intent / Cover Letter</label>
                                                    <div class="p-3 bg-light rounded-3 text-secondary small">
                                                        <?= nl2br(htmlspecialchars($app['cover_letter'])) ?>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Indicated Available Time Slots</label>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($app['availability'] as $av): ?>
                                                            <span class="badge bg-secondary-subtle text-dark border small"><?= htmlspecialchars($av) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <?php if (!empty($app['supervisor_notes'])): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted text-uppercase">Supervisor Feedback & Remarks</label>
                                                        <div class="p-3 bg-light rounded-3 text-dark small border-start border-4 border-primary">
                                                            <?= htmlspecialchars($app['supervisor_notes']) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="row g-2 small text-muted pt-2 border-top">
                                                    <div class="col-6">Submitted: <?= htmlspecialchars($app['applied_at']) ?></div>
                                                    <div class="col-6 text-end">Attached Document: <strong><?= htmlspecialchars($app['resume_file']) ?></strong></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <?php if ($app['status'] === 'pending'): ?>
                                                    <form action="my-applications.php" method="POST" onsubmit="return confirm('Are you sure you want to withdraw this application?');">
                                                        <input type="hidden" name="withdraw_id" value="<?= $app['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="bi bi-trash me-1"></i> Withdraw Application
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close Window</button>
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
