<?php
/**
 * Campus Job Posting System - Job Application Form
 * Archetype D: Application & Availability Matrix (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Ensure student auth - employers and admins cannot apply
if (!is_logged_in()) {
    set_flash('info', 'Please sign in with your student account to submit an application.');
    header('Location: ../login.php?demo=student');
    exit;
}

$user = get_logged_user();
if (($user['role'] ?? '') !== 'student') {
    set_flash('warning', 'Access Restricted: Only enrolled students can submit job applications. Employer accounts cannot apply for campus vacancies.');
    if (($user['role'] ?? '') === 'employer') {
        header('Location: ../employer/dashboard.php');
    } else {
        header('Location: ../admin/users.php');
    }
    exit;
}
$job_id = $_GET['id'] ?? ($_GET['job_id'] ?? null);
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The requested opportunity could not be found or has been closed.');
    header('Location: jobs.php');
    exit;
}

// Verification Check
if (($user['verification_status'] ?? 'verified') !== 'verified') {
    set_flash('warning', 'Account Verification Required: Your student registration is currently awaiting administrative review. The administrator must verify your account before you can submit applications.');
    header('Location: dashboard.php');
    exit;
}

// Gating Checks
if (strtolower($job['status'] ?? '') !== 'active') {
    set_flash('danger', 'This requisition has been closed or paused and is not accepting applications.');
    header('Location: job-details.php?id=' . $job['id']);
    exit;
}

if (!empty($job['deadline']) && strtotime($job['deadline']) < strtotime(date('Y-m-d'))) {
    set_flash('danger', 'The application deadline for this position has passed.');
    header('Location: job-details.php?id=' . $job['id']);
    exit;
}

$slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
$slots_filled = (int)($job['slots_filled'] ?? 0);
if ($slots_total > 0 && $slots_filled >= $slots_total) {
    set_flash('danger', 'All vacancy slots for this position have been filled.');
    header('Location: job-details.php?id=' . $job['id']);
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed (invalid CSRF session token). Please refresh and submit again.';
    } else {
        $cover_letter = trim($_POST['cover_letter'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $availability = $_POST['availability'] ?? [];
        
        // Handle resume file upload with failure detection
        $resume_name = ($user['name'] ?? 'Student') . '_Resume.pdf';
        $upload_failed = false;

        if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload failed. Please verify that your resume file is under 5MB.';
                $upload_failed = true;
            } else {
                $resume_path = save_uploaded_resume($_FILES['resume']);
                if (!$resume_path) {
                    $error = 'Invalid resume format or size. Accepted formats: PDF, DOC, DOCX (Max 5MB).';
                    $upload_failed = true;
                } else {
                    $resume_name = basename($resume_path);
                }
            }
        }

        if (!$upload_failed) {
            if (empty($cover_letter)) {
                $error = 'Please provide a brief statement of intent / cover letter.';
            } elseif (empty($availability) || count($availability) === 0) {
                $error = 'Candidate Shift Availability is required and cannot be empty. Please select at least one available weekly timeslot in the matrix.';
            } else {
                $res = create_application([
                    'job_id' => $job['id'],
                    'cover_letter' => $cover_letter,
                    'phone' => $phone,
                    'availability' => $availability,
                    'resume_file' => $resume_name
                ]);

                if ($res['success']) {
                    set_flash('success', "Application successfully submitted for {$job['title']}! You can track its review progress below.");
                    header('Location: my-applications.php');
                    exit;
                } else {
                    $error = $res['message'];
                }
            }
        }
    }
}

$default_availability = (isset($_POST['availability']) && is_array($_POST['availability']))
    ? $_POST['availability']
    : ((!empty($user['availability']) && is_array($user['availability'])) ? $user['availability'] : [
        'Mon - Morning (8AM–12NN)',
        'Wed - Morning (8AM–12NN)',
        'Fri - Afternoon (1PM–5PM)'
    ]);

$page_title = 'Apply for ' . $job['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Back Link & Page Head -->
                <div class="mb-4">
                    <a href="job-details.php?id=<?= $job['id'] ?>" class="text-ink fw-bold small text-decoration-none d-inline-flex align-items-center gap-1 mb-3">
                        <i class="bi bi-arrow-left"></i> Back to Requisition Details
                    </a>
                    
                    <?php
                    render_page_head(
                        '',
                        'Apply for ' . $job['title'],
                        $job['department'] . ' • ' . $job['pay_rate'] . ' • ' . ($job['work_setup'] ?? 'On-Campus')
                    );
                    ?>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        
                        <?php if ($error): ?>
                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card-paper p-4 p-md-5 mb-5">
                            
                            <form action="apply.php?id=<?= $job['id'] ?>&job_id=<?= $job['id'] ?>" method="POST" enctype="multipart/form-data" class="form-paper">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                
                                <!-- Section 1: Applicant Profile Confirmation -->
                                <div class="mb-4 pb-3 border-bottom border-line">
                                    <h3 class="card-paper-title fs-5 mb-3">
                                        <i class="bi bi-person-badge text-accent me-2"></i> 1. Applicant Profile Information
                                    </h3>
                                    
                                    <div class="row g-3 p-3 bg-cream rounded-4 border border-line">
                                        <div class="col-md-6">
                                            <span class="small text-muted-custom d-block mb-1">Student Full Name</span>
                                            <strong class="text-ink"><?= htmlspecialchars($user['name']) ?></strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="small text-muted-custom d-block mb-1">Student ID Number</span>
                                            <strong class="text-ink"><?= htmlspecialchars($user['student_id'] ?? '2024-00123') ?></strong>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="small text-muted-custom d-block mb-1">Enrolled Degree Program</span>
                                            <span class="text-ink"><?= htmlspecialchars($user['course'] ?? 'BS Information Systems') ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="small text-muted-custom d-block mb-1">Year Level & Standing</span>
                                            <span class="text-ink"><?= htmlspecialchars($user['year_level'] ?? '2nd Year') ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="small text-muted-custom d-block mb-1">Sex & Age</span>
                                            <span class="text-ink"><?= htmlspecialchars($user['sex'] ?? 'Male') ?> &bull; <?= htmlspecialchars((string)($user['age'] ?? (isset($user['birthdate']) ? calculate_age($user['birthdate']) : 20))) ?> yrs</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Contact Information -->
                                <div class="mb-4 pb-3 border-bottom border-line">
                                    <h3 class="card-paper-title fs-5 mb-3">
                                        <i class="bi bi-telephone text-accent me-2"></i> 2. Contact Phone Number
                                    </h3>
                                    <div>
                                        <label class="form-label" for="app-phone">Mobile Phone (for SMS interview notices) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="text" name="phone" id="app-phone" class="form-control" placeholder="+63 917 123 4567" value="<?= htmlspecialchars(!empty($user['phone']) ? $user['phone'] : '+63 917 555 0192') ?>" required>
                                        </div>
                                        <span class="small text-muted-custom mt-1 d-block" style="font-size: 12px;">
                                            Department supervisors use this contact to confirm interview dates and duty room assignments.
                                        </span>
                                    </div>
                                </div>

                                <!-- Section 3: Availability Matrix -->
                                <div class="mb-4 pb-3 border-bottom border-line">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h3 class="card-paper-title fs-5 mb-0">
                                            <i class="bi bi-calendar-week text-accent me-2"></i> 3. Weekly Shift Availability Matrix <span class="text-danger">*</span>
                                        </h3>
                                        <span class="badge-status--accepted" style="font-size: 10px;">&le; 20 hrs/week</span>
                                    </div>
                                    <p class="small text-muted-custom mb-3">
                                        Check all weekly time slots when you are free from academic lectures and can perform on-campus duty:
                                    </p>
                                    
                                    <div class="card-paper p-3 bg-surface border border-line position-relative" id="matrixContainer">
                                        <div id="availabilityErrorAlert" class="alert-paper alert-paper--danger mb-3" style="display: none;">
                                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                            <span>Candidate Shift Availability cannot be empty. Please select at least one weekly timeslot.</span>
                                        </div>
                                        <?php render_availability_matrix($default_availability, 'availability[]', false); ?>
                                    </div>
                                </div>

                                <!-- Section 4: Statement of Purpose / Cover Letter -->
                                <div class="mb-4 pb-3 border-bottom border-line">
                                    <h3 class="card-paper-title fs-5 mb-3">
                                        <i class="bi bi-card-text text-accent me-2"></i> 4. Statement of Intent / Cover Letter <span class="text-danger">*</span>
                                    </h3>
                                    <div>
                                        <label class="form-label" for="cover_letter">Statement of Interest</label>
                                        <textarea name="cover_letter" id="cover_letter" rows="4" class="form-control" placeholder="Briefly state your motivation, relevant coursework, and availability for this assistantship role..." required><?= isset($_POST['cover_letter']) ? htmlspecialchars($_POST['cover_letter']) : ("I am writing to express my strong interest in the " . htmlspecialchars($job['title']) . " position in " . htmlspecialchars($job['department']) . ". As a student in " . htmlspecialchars($user['course'] ?? 'BS Information Systems') . ", I have the requisite skills, organizational diligence, and vacant shift hours to fulfill the assigned duties reliably.") ?></textarea>
                                    </div>
                                </div>

                                <!-- Section 5: Resume Upload Mock -->
                                <div class="mb-4 pb-3 border-bottom border-line">
                                    <h3 class="card-paper-title fs-5 mb-3">
                                        <i class="bi bi-file-earmark-arrow-up text-accent me-2"></i> 5. Resume / Study Load Document
                                    </h3>
                                    <div>
                                        <label class="form-label">Attach Updated Resume or Study Load (PDF / DOCX)</label>
                                        <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                                        <span class="small text-muted-custom mt-1 d-block" style="font-size: 12px;">
                                            You may attach an updated PDF or leave blank to automatically link your stored student profile resume.
                                        </span>
                                    </div>
                                </div>

                                <!-- Academic Safeguard Declaration -->
                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="saComplianceCheck" required checked>
                                    <label class="form-check-label small text-ink" for="saComplianceCheck">
                                        I certify that my class attendance will not be compromised, and I agree to adhere strictly to the <strong>20-hour maximum weekly duty limit</strong> in accordance with campus work regulations.
                                    </label>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex flex-wrap gap-3 pt-2">
                                    <button type="submit" id="submitAppBtn" class="btn-pill px-4">
                                        <i class="bi bi-send-fill"></i> SUBMIT APPLICATION
                                    </button>
                                    <a href="job-details.php?id=<?= $job['id'] ?>" class="btn-pill-outline">
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.form-paper');
    const alertBox = document.getElementById('availabilityErrorAlert');
    const matrixContainer = document.getElementById('matrixContainer');

    if (form) {
        form.addEventListener('submit', function(e) {
            const checkedBoxes = form.querySelectorAll('input[name="availability[]"]:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                if (alertBox) {
                    alertBox.style.display = 'flex';
                }
                if (matrixContainer) {
                    matrixContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    matrixContainer.style.borderColor = 'var(--st-declined, #E5484D)';
                }
                return false;
            } else {
                if (alertBox) {
                    alertBox.style.display = 'none';
                }
                if (matrixContainer) {
                    matrixContainer.style.borderColor = '';
                }

                // Double submit prevention
                const submitBtn = document.getElementById('submitAppBtn');
                if (submitBtn) {
                    setTimeout(() => {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting Application...';
                    }, 10);
                }
            }
        });

        // Clear error on checkbox click
        form.querySelectorAll('input[name="availability[]"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                const checked = form.querySelectorAll('input[name="availability[]"]:checked');
                if (checked.length > 0) {
                    if (alertBox) alertBox.style.display = 'none';
                    if (matrixContainer) matrixContainer.style.borderColor = '';
                }
            });
        });
    }
});
</script>
