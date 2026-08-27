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

<main class="py-4 bg-surface flex-grow-1">
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
                <h2 class="fw-bold text-ink mb-0">Application Tracker & Status</h2>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="jobs.php" class="btn-accent-pill py-2 px-3">
                    <i class="bi bi-plus-circle me-1"></i> Apply for More Vacancies
                </a>
            </div>
        </div>

        <?php if (empty($my_apps)): ?>
            <div class="card border-line shadow-sm rounded-4 p-5 text-center bg-white my-4">
                <div class="stat-icon bg-surface text-muted-custom mx-auto mb-3 fs-1">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <h4 class="fw-bold text-ink">No Submitted Applications Found</h4>
                <p class="text-muted-custom small mb-4">You have not applied for any campus student assistant positions yet.</p>
                <div>
                    <a href="jobs.php" class="btn-accent-pill">Explore Campus Jobs Now</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-line shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-cream border-bottom border-line">
                            <tr>
                                <th class="ps-4 text-ink fw-bold">Applied Vacancy</th>
                                <th class="text-ink fw-bold">Department</th>
                                <th class="text-ink fw-bold">Applied Date</th>
                                <th class="text-ink fw-bold">Status</th>
                                <th class="text-ink fw-bold">Interview / Remarks</th>
                                <th class="text-end pe-4 text-ink fw-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_apps as $app): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-ink"><?= htmlspecialchars($app['job_title']) ?></div>
                                        <span class="text-muted-custom small">App Ref: #APP-<?= str_pad($app['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold text-muted-custom"><?= htmlspecialchars($app['department']) ?></span>
                                    </td>
                                    <td class="small text-muted-custom">
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_pill = match($app['status']) {
                                            'accepted' => 'pill-badge',
                                            'rejected' => 'pill-badge pill-badge-ink',
                                            'interview_scheduled' => 'pill-badge',
                                            default => 'chip-tag'
                                        };
                                        ?>
                                        <span class="<?= $badge_pill ?>" style="font-size: 11px;">
                                            <?= htmlspecialchars($app['status_label']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($app['status'] === 'interview_scheduled' && !empty($app['interview_date'])): ?>
                                            <div class="small text-accent fw-semibold">
                                                <i class="bi bi-calendar-event me-1"></i> <?= htmlspecialchars($app['interview_date']) ?> (<?= htmlspecialchars($app['interview_time']) ?>)
                                                <div class="text-muted-custom small">Venue: <?= htmlspecialchars($app['interview_venue']) ?></div>
                                            </div>
                                        <?php elseif ($app['status'] === 'accepted'): ?>
                                            <span class="small text-accent fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Hired for Semester</span>
                                        <?php elseif (!empty($app['supervisor_notes'])): ?>
                                            <span class="small text-muted-custom text-truncate d-inline-block" style="max-width: 200px;">
                                                <?= htmlspecialchars($app['supervisor_notes']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="small text-muted-custom">In Queue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn-outline-pill py-1 px-3" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#appModal<?= $app['id'] ?>">
                                            <i class="bi bi-eye"></i> Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Application Detail Modal -->
                                <div class="modal fade" id="appModal<?= $app['id'] ?>" tabindex="-1" aria-labelledby="appModalLabel<?= $app['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                                        <div class="modal-content rounded-4 border-line shadow-lg">
                                            <div class="modal-header bg-kld-gradient text-white">
                                                <h5 class="modal-title fw-bold" id="appModalLabel<?= $app['id'] ?>">
                                                    <i class="bi bi-file-earmark-text me-1 text-accent"></i> Application Record: <?= htmlspecialchars($app['job_title']) ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 bg-surface">
                                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-line">
                                                    <div>
                                                        <span class="text-muted-custom small">Campus Department:</span>
                                                        <div class="fw-bold text-ink"><?= htmlspecialchars($app['department']) ?></div>
                                                    </div>
                                                    <div>
                                                        <span class="<?= $badge_pill ?>" style="font-size: 12px;">
                                                            <?= htmlspecialchars($app['status_label']) ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <?php if ($app['status'] === 'interview_scheduled' && !empty($app['interview_date'])): ?>
                                                    <div class="alert alert-light border-line bg-cream p-3 rounded-3 mb-3 text-ink">
                                                        <h6 class="fw-bold text-ink mb-1"><i class="bi bi-calendar-check-fill text-accent me-1"></i> Interview Notice</h6>
                                                        <div class="small text-muted-custom">
                                                            <strong class="text-ink">Date & Time:</strong> <?= htmlspecialchars($app['interview_date']) ?> at <?= htmlspecialchars($app['interview_time']) ?><br>
                                                            <strong class="text-ink">Location:</strong> <?= htmlspecialchars($app['interview_venue']) ?><br>
                                                            <em>Please bring your valid Student ID and study load printout.</em>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-ink text-uppercase">Statement of Intent / Cover Letter</label>
                                                    <div class="p-3 bg-white border-line border rounded-3 text-muted-custom small">
                                                        <?= nl2br(htmlspecialchars($app['cover_letter'])) ?>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-ink text-uppercase">Indicated Available Time Slots</label>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($app['availability'] as $av): ?>
                                                            <span class="chip-tag"><?= htmlspecialchars($av) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <?php if (!empty($app['supervisor_notes'])): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-ink text-uppercase">Supervisor Feedback & Remarks</label>
                                                        <div class="p-3 bg-white border-line border rounded-3 text-ink small border-start border-4" style="border-left-color: var(--accent) !important;">
                                                            <?= htmlspecialchars($app['supervisor_notes']) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="row g-2 small text-muted-custom pt-2 border-top border-line">
                                                    <div class="col-6">Submitted: <?= htmlspecialchars($app['applied_at']) ?></div>
                                                    <div class="col-6 text-end">Attached Document: <strong class="text-ink"><?= htmlspecialchars($app['resume_file']) ?></strong></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-cream border-top border-line">
                                                <?php if ($app['status'] === 'pending'): ?>
                                                    <form action="my-applications.php" method="POST" onsubmit="return confirm('Are you sure you want to withdraw this application?');">
                                                        <input type="hidden" name="withdraw_id" value="<?= $app['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                                            <i class="bi bi-trash me-1"></i> Withdraw Application
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <button type="button" class="btn-soft-pill py-1 px-3" data-bs-dismiss="modal" style="font-size: 12px;">Close Window</button>
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
