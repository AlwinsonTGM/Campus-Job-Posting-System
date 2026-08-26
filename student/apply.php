<?php
/**
 * Campus Job Posting System - Job Application Form
 */
require_once __DIR__ . '/../includes/data-helper.php';

// Ensure user is logged in as student (or auto-switch if needed)
if (!is_logged_in()) {
    set_flash('info', 'Please sign in with your student account to submit an application.');
    header('Location: ../login.php?demo=student');
    exit;
}

$user = get_logged_user();
$job_id = $_GET['job_id'] ?? null;
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'Job opening not found.');
    header('Location: jobs.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $availability = $_POST['availability'] ?? [];
    $resume_name = $_FILES['resume']['name'] ?? 'Juan_Dela_Cruz_Resume.pdf';

    if (empty($cover_letter)) {
        $error = 'Please provide a brief statement of intent / cover letter.';
    } elseif (empty($availability)) {
        $error = 'Please select at least one available weekly shift slot.';
    } else {
        $res = create_application([
            'job_id' => $job['id'],
            'cover_letter' => $cover_letter,
            'phone' => $phone,
            'availability' => $availability,
            'resume_file' => !empty($_FILES['resume']['name']) ? $_FILES['resume']['name'] : ($user['name'] . '_Resume.pdf')
        ]);

        if ($res['success']) {
            set_flash('success', "Application successfully submitted for {$job['title']}! You can track its status below.");
            header('Location: my-applications.php');
            exit;
        } else {
            $error = $res['message'];
        }
    }
}

$page_title = 'Apply for ' . $job['title'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-light flex-grow-1">
    <div class="container">
        
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="jobs.php">Job Vacancies</a></li>
                <li class="breadcrumb-item"><a href="job-details.php?id=<?= $job['id'] ?>"><?= htmlspecialchars($job['title']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Application Form</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="stat-icon bg-warning text-dark fs-3">
                            <i class="bi bi-file-earmark-person-fill"></i>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary border px-2 py-1 small">Student Assistantship Application</span>
                            <h3 class="fw-bold text-dark mb-0"><?= htmlspecialchars($job['title']) ?></h3>
                            <div class="text-muted small"><i class="bi bi-building me-1"></i><?= htmlspecialchars($job['department']) ?> &bull; <strong class="text-success"><?= htmlspecialchars($job['pay_rate']) ?></strong></div>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="apply.php?job_id=<?= $job['id'] ?>" method="POST" enctype="multipart/form-data">
                        
                        <!-- Applicant Pre-filled Profile -->
                        <h6 class="fw-bold text-dark mb-3 text-uppercase small"><i class="bi bi-person-badge text-primary me-1"></i> 1. Applicant Profile (From Account)</h6>
                        <div class="row g-3 p-3 bg-light rounded-3 mb-4 border">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-0">Student Full Name</label>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($user['name']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-0">Student ID Number</label>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($user['student_id'] ?? '2024-00123') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-0">Course / Year Level</label>
                                <div class="text-dark"><?= htmlspecialchars($user['course'] ?? 'BS Information Systems') ?> &bull; <?= htmlspecialchars($user['year_level'] ?? '2nd Year') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-0">Institutional Email</label>
                                <div class="text-dark"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </div>

                        <!-- Contact Phone -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Active Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="+63 917 123 4567" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            <div class="form-text small">Used by department supervisors to SMS interview notices.</div>
                        </div>

                        <!-- Weekly Availability Checklist -->
                        <h6 class="fw-bold text-dark mb-2 text-uppercase small"><i class="bi bi-calendar-check text-primary me-1"></i> 2. Available Free Class Hours / Shift Timeslots <span class="text-danger">*</span></h6>
                        <p class="text-muted small mb-2">Check all periods during the week when you are free from classes:</p>
                        
                        <div class="row g-2 mb-4 p-3 bg-light rounded-3 border">
                            <?php 
                            $slots = [
                                'Mon AM (8:00 AM - 12:00 PM)',
                                'Mon PM (1:00 PM - 5:00 PM)',
                                'Tue AM (8:00 AM - 12:00 PM)',
                                'Tue PM (1:00 PM - 5:00 PM)',
                                'Wed AM (8:00 AM - 12:00 PM)',
                                'Wed PM (1:00 PM - 5:00 PM)',
                                'Thu AM (8:00 AM - 12:00 PM)',
                                'Thu PM (1:00 PM - 5:00 PM)',
                                'Fri AM (8:00 AM - 12:00 PM)',
                                'Fri PM (1:00 PM - 5:00 PM)',
                                'Sat AM (8:00 AM - 12:00 PM)',
                                'Sat PM (1:00 PM - 5:00 PM)',
                            ];
                            foreach ($slots as $idx => $slot): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="availability[]" value="<?= htmlspecialchars($slot) ?>" id="slot_<?= $idx ?>" <?= $idx < 3 ? 'checked' : '' ?>>
                                        <label class="form-check-label small text-dark" for="slot_<?= $idx ?>">
                                            <?= htmlspecialchars($slot) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Statement of Intent / Cover Letter -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Statement of Intent / Cover Letter <span class="text-danger">*</span></label>
                            <textarea name="cover_letter" rows="4" class="form-control" placeholder="Briefly explain why you are interested in this position and how your skills or study load match the department's needs..." required>I am writing to express my eager interest in the <?= htmlspecialchars($job['title']) ?> position. As a <?= htmlspecialchars($user['course'] ?? 'BS Information Systems') ?> student, I have the required skills and vacant periods to commit reliably to this role.</textarea>
                        </div>

                        <!-- Mock Resume Upload -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Upload Updated Resume / Study Load (PDF / DOCX)</label>
                            <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                            <div class="form-text small">Demo accepts any sample PDF file or uses the default student resume profile.</div>
                        </div>

                        <div class="alert alert-light border border-warning-subtle small text-secondary mb-4 p-3 rounded-3">
                            <i class="bi bi-shield-check text-warning me-1"></i>
                            By submitting this application, you declare that you satisfy the campus GWA requirements and agree to comply with the 20-hour weekly student assistant work limit.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-academic py-2 px-4 fw-bold shadow-sm">
                                <i class="bi bi-send-fill me-1"></i> Submit Application
                            </button>
                            <a href="job-details.php?id=<?= $job['id'] ?>" class="btn btn-outline-secondary py-2 px-3">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>

            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
