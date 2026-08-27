<?php
/**
 * Campus Job Posting System - Candidate Evaluation & Decision Drawer
 * Archetype C/D: Candidate Evaluation & Availability Matrix (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();

$app_id = $_GET['id'] ?? null;
$apps = $_SESSION['applications'] ?? load_json_file('applications.json');
$target_app = null;

foreach ($apps as $a) {
    if ($a['id'] == $app_id) {
        $target_app = $a;
        break;
    }
}

if (!$target_app) {
    set_flash('danger', 'The specified student application could not be found.');
    header('Location: applicants.php');
    exit;
}

if (!can_review_application($target_app, $user)) {
    set_flash('danger', 'Unauthorized: Candidate application belongs to another department.');
    header('Location: applicants.php');
    exit;
}

$job = get_job_by_id($target_app['job_id'] ?? 0);

// Handle status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? 'under_review';
    $notes = trim($_POST['supervisor_notes'] ?? '');

    $interview_data = [];
    if ($new_status === 'interview_scheduled') {
        $interview_data = [
            'date' => $_POST['interview_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'time' => $_POST['interview_time'] ?? '10:00 AM',
            'venue' => $_POST['interview_venue'] ?? ($user['office_location'] ?? 'Admin Building Room 102')
        ];
    }

    update_application_status($target_app['id'], $new_status, $notes, $interview_data);
    set_flash('success', "Candidate status for {$target_app['student_name']} updated to " . ucfirst(str_replace('_', ' ', $new_status)) . ".");
    header("Location: review-app.php?id={$target_app['id']}");
    exit;
}

$page_title = 'Evaluate: ' . $target_app['student_name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Back Link & Page Head -->
                <div class="mb-4">
                    <a href="applicants.php" class="text-ink fw-bold small text-decoration-none d-inline-flex align-items-center gap-1 mb-3">
                        <i class="bi bi-arrow-left"></i> Back to Applicant Roster
                    </a>
                    
                    <?php
                    render_page_head(
                        '',
                        'Review Applicant: ' . htmlspecialchars($target_app['student_name']),
                        'Vacancy: ' . htmlspecialchars($target_app['job_title']) . ' • Applied on ' . date('F d, Y', strtotime($target_app['applied_at'] ?? 'now'))
                    );
                    ?>
                </div>

                <div class="row g-4 mb-5">
                    
                    <!-- Left 7-col: Candidate Profile, Statement & Availability Matrix -->
                    <div class="col-lg-7">
                        
                        <!-- Candidate Academic Profile -->
                        <div class="card-paper p-4 mb-4">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-person-badge text-accent me-2"></i> 1. Academic & Contact Profile
                            </h3>

                            <div class="row g-3 p-3 bg-cream rounded-4 border border-line mb-3">
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Student Full Name</span>
                                    <strong class="text-ink fs-6"><?= htmlspecialchars($target_app['student_name']) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Student ID Number</span>
                                    <strong class="text-ink"><?= htmlspecialchars($target_app['student_number'] ?? '2024-00123') ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Degree Program & Standing</span>
                                    <span class="text-ink fw-semibold"><?= htmlspecialchars($target_app['course'] ?? 'BS Information Systems') ?></span>
                                    <span class="text-muted-custom">&bull; <?= htmlspecialchars($target_app['year_level'] ?? '2nd Year') ?></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Demographics (Sex & Age)</span>
                                    <span class="text-ink fw-semibold"><?= htmlspecialchars($target_app['sex'] ?? 'Male') ?> &bull; <?= htmlspecialchars((string)($target_app['age'] ?? 20)) ?> yrs old</span>
                                </div>
                                <div class="col-md-12">
                                    <span class="small text-muted-custom d-block mb-1">Contact Details</span>
                                    <span class="text-ink fw-semibold"><?= htmlspecialchars($target_app['phone'] ?? '+63 917 555 0192') ?></span>
                                    <span class="text-muted-custom mx-2">&bull;</span>
                                    <span class="small text-muted-custom"><?= htmlspecialchars($target_app['student_email']) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Statement of Purpose -->
                        <div class="card-paper p-4 mb-4">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-file-text text-accent me-2"></i> 2. Statement of Intent / Cover Letter
                            </h3>
                            <div class="p-3 bg-surface rounded-4 border border-line text-ink lh-lg small mb-0">
                                <?= nl2br(htmlspecialchars($target_app['cover_letter'])) ?>
                            </div>
                        </div>

                        <!-- Shift Availability Matrix (Readonly) -->
                        <div class="card-paper p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title fs-5 mb-0">
                                    <i class="bi bi-calendar-week text-accent me-2"></i> 3. Candidate Shift Availability
                                </h3>
                                <span class="badge-status--accepted" style="font-size: 10px;">&le; 20 hrs/week</span>
                            </div>
                            <p class="small text-muted-custom mb-3">
                                Periods when the student is free from academic lectures and can perform on-campus assistantship duty:
                            </p>
                            <div class="p-3 bg-surface rounded-4 border border-line">
                                <?php render_availability_matrix($target_app['availability'] ?? [], '', true); ?>
                            </div>
                        </div>

                        <!-- Attached Credentials -->
                        <div class="card-paper p-4">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-paperclip text-accent me-2"></i> 4. Attached Resume & Credentials
                            </h3>
                            <div class="p-3 bg-surface rounded-4 border border-line d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-2 flex-shrink-0"></i>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-ink small text-truncate"><?= htmlspecialchars($target_app['resume_file'] ?? 'Student_Resume.pdf') ?></div>
                                        <span class="small text-muted-custom d-block text-truncate">Official Student Resume & Credentials</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-nowrap gap-2 flex-shrink-0">
                                    <button type="button" class="btn-pill btn-pill-sm text-nowrap d-inline-flex align-items-center gap-1 py-1 px-3" data-bs-toggle="modal" data-bs-target="#resumeModal" style="font-size: 12px;">
                                        <i class="bi bi-eye-fill"></i> View PDF
                                    </button>
                                    <a href="../view-resume.php?app_id=<?= $target_app['id'] ?>" target="_blank" class="btn-pill-outline btn-pill-sm text-nowrap d-inline-flex align-items-center gap-1 py-1 px-3" style="font-size: 12px;" title="Open PDF in new tab">
                                        <i class="bi bi-box-arrow-up-right"></i> Open Tab
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right 5-col: Status Controls & Stepper Action Card -->
                    <div class="col-lg-5">
                        <div class="card-paper p-4 position-sticky" style="top: 90px;">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-sliders text-accent me-2"></i> Evaluation & Decision
                            </h3>

                            <!-- Current Status & Stepper -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small text-muted-custom">Current Stage:</span>
                                    <div><?= render_status_badge($target_app['status']) ?></div>
                                </div>
                                <div class="p-2">
                                    <?php render_stepper($target_app['status']); ?>
                                </div>
                            </div>

                            <!-- Status Transition Form -->
                            <form action="review-app.php?id=<?= $target_app['id'] ?>" method="POST" class="form-paper">
                                
                                <div class="mb-3">
                                    <label class="form-label" for="eval-status">Update Candidate Stage <span class="text-danger">*</span></label>
                                    <select name="status" id="eval-status" class="form-select" onchange="toggleInterviewFields(this.value)">
                                        <option value="pending" <?= in_array($target_app['status'], ['pending', 'Pending Review']) ? 'selected' : '' ?>>Pending Review</option>
                                        <option value="under_review" <?= in_array($target_app['status'], ['under_review', 'under review', 'Under Evaluation']) ? 'selected' : '' ?>>Under Evaluation</option>
                                        <option value="interview_scheduled" <?= in_array($target_app['status'], ['interview_scheduled', 'Interview Scheduled']) ? 'selected' : '' ?>>Shortlist & Schedule Interview</option>
                                        <option value="accepted" <?= in_array($target_app['status'], ['accepted', 'Accepted / Hired']) ? 'selected' : '' ?>>Accept & Officially Hire</option>
                                        <option value="declined" <?= in_array($target_app['status'], ['declined', 'Declined / Position Filled']) ? 'selected' : '' ?>>Decline / Position Filled</option>
                                    </select>
                                </div>

                                <!-- Dynamic Interview Details Box -->
                                <div id="interviewBox" class="p-3 bg-cream rounded-4 border border-line mb-3" style="<?= !in_array($target_app['status'], ['interview_scheduled', 'Interview Scheduled']) ? 'display:none;' : '' ?>">
                                    <h4 class="card-paper-title fs-6 mb-2">
                                        <i class="bi bi-calendar-event text-accent me-1"></i> Interview Logistics
                                    </h4>
                                    
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-1" for="int-date">Interview Date</label>
                                            <input type="date" name="interview_date" id="int-date" min="<?= date('Y-m-d') ?>" class="form-control" value="<?= htmlspecialchars($target_app['interview_date'] ?? date('Y-m-d', strtotime('+2 days'))) ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-1" for="int-time">Interview Time</label>
                                            <select name="interview_time" id="int-time" class="form-select">
                                                <?php
                                                $standard_times = [
                                                    '08:00 AM', '08:30 AM', '09:00 AM', '09:30 AM', 
                                                    '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
                                                    '01:00 PM', '01:30 PM', '02:00 PM', '02:30 PM', 
                                                    '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM', '05:00 PM'
                                                ];
                                                $cur_time = $target_app['interview_time'] ?? '10:00 AM';
                                                foreach ($standard_times as $t):
                                                ?>
                                                    <option value="<?= $t ?>" <?= (strcasecmp($cur_time, $t) === 0) ? 'selected' : '' ?>><?= $t ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small mb-1" for="int-venue">Venue / Room</label>
                                        <select name="interview_venue" id="int-venue" class="form-select">
                                            <?php
                                            $dept_office = $user['office_location'] ?? ($user['department'] ?? 'Campus Main Office');
                                            $standard_venues = [
                                                $dept_office,
                                                'KLD Admin Building, 1st Floor, Room 102 (HR Office)',
                                                'KLD Tech Building, 3rd Floor, MIS Center',
                                                'KLD Main Building, 2nd Floor (Dean\'s Conference Room)',
                                                'KLD Library, Multi-Media Presentation Room',
                                                'Student Affairs & Services Office (SASO) - Room 204',
                                                'University Student Pavilion - Meeting Hall B',
                                                'Online via Google Meet (Video Call Link to be emailed)'
                                            ];
                                            $standard_venues = array_unique(array_filter($standard_venues));
                                            $cur_venue = $target_app['interview_venue'] ?? $dept_office;
                                            if (!in_array($cur_venue, $standard_venues) && !empty($cur_venue)) {
                                                array_unshift($standard_venues, $cur_venue);
                                            }
                                            foreach ($standard_venues as $v):
                                            ?>
                                                <option value="<?= htmlspecialchars($v) ?>" <?= ($cur_venue === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="supervisor_notes">Supervisor Remarks / Notes</label>
                                    <textarea name="supervisor_notes" id="supervisor_notes" rows="3" class="form-control" placeholder="Add specific feedback, instructions, or internal notes..."><?= htmlspecialchars($target_app['supervisor_notes'] ?? '') ?></textarea>
                                </div>

                                <button type="submit" class="btn-pill w-100 mb-2">
                                    <i class="bi bi-check2-circle"></i> Save Evaluation Decision
                                </button>
                                <a href="applicants.php" class="btn-pill-outline btn-pill-sm w-100 text-center">
                                    Return to Roster
                                </a>
                            </form>

                        </div>
                    </div>

                </div>

            </div>
        </main>

        <!-- PDF Resume Preview Modal -->
        <div class="modal fade" id="resumeModal" tabindex="-1" aria-labelledby="resumeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content rounded-4 border-line shadow-lg overflow-hidden">
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 me-auto">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0 fs-6" id="resumeModalLabel">Candidate Resume: <?= htmlspecialchars($target_app['student_name']) ?></h5>
                                <span class="small text-muted-custom" style="font-size: 11.5px;"><?= htmlspecialchars($target_app['resume_file'] ?? 'Student_Resume.pdf') ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <a href="../view-resume.php?app_id=<?= $target_app['id'] ?>" target="_blank" class="btn-pill-outline btn-pill-sm py-1 px-3 text-nowrap d-inline-flex align-items-center gap-1" style="font-size: 12px;">
                                <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                            </a>
                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body p-0 bg-dark position-relative" style="min-height: 580px;">
                        <iframe src="../view-resume.php?app_id=<?= $target_app['id'] ?>" style="width: 100%; height: 600px; border: none;" title="PDF Resume Viewer"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<script>
function toggleInterviewFields(status) {
    const box = document.getElementById('interviewBox');
    if (box) {
        box.style.display = (status === 'interview_scheduled') ? 'block' : 'none';
    }
}
</script>
