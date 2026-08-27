<?php
/**
 * Campus Job Posting System - Job Application Form
 * Archetype D: Application & Availability Matrix (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

// Ensure student auth
if (!is_logged_in()) {
    set_flash('info', 'Please sign in with your student account to submit an application.');
    header('Location: ../login.php?demo=student');
    exit;
}

$user = get_logged_user();
$job_id = $_GET['id'] ?? ($_GET['job_id'] ?? null);
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The requested opportunity could not be found or has been closed.');
    header('Location: jobs.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $availability = $_POST['availability'] ?? [];
    $resume_name = $_FILES['resume']['name'] ?? ($user['name'] . '_Resume.pdf');

    if (empty($cover_letter)) {
        $error = 'Please provide a brief statement of intent / cover letter.';
    } elseif (empty($availability)) {
        $error = 'Please select at least one available weekly shift timeslot in the matrix.';
    } else {
        $res = create_application([
            'job_id' => $job['id'],
            'cover_letter' => $cover_letter,
            'phone' => $phone,
            'availability' => $availability,
            'resume_file' => !empty($_FILES['resume']['name']) ? $_FILES['resume']['name'] : ($user['name'] . '_Resume.pdf')
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

$default_availability = $user['availability'] ?? [
    'Mon - Morning (8AM–12NN)',
    'Wed - Morning (8AM–12NN)',
    'Fri - Afternoon (1PM–5PM)'
];

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
                        '<i class="bi bi-file-earmark-person-fill text-accent me-1"></i> Student Assistantship Application',
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
                                        <div class="col-md-6">
                                            <span class="small text-muted-custom d-block mb-1">Year Level & Standing</span>
                                            <span class="text-ink"><?= htmlspecialchars($user['year_level'] ?? '2nd Year') ?> &bull; Good Standing</span>
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
                                            <input type="text" name="phone" id="app-phone" class="form-control" placeholder="+63 917 123 4567" value="<?= htmlspecialchars($user['phone'] ?? '+63 917 555 0192') ?>" required>
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
                                    
                                    <div class="card-paper p-3 bg-surface border border-line">
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
                                        <textarea name="cover_letter" id="cover_letter" rows="4" class="form-control" placeholder="Briefly state your motivation, relevant coursework, and availability for this assistantship role..." required>I am writing to express my strong interest in the <?= htmlspecialchars($job['title']) ?> position in <?= htmlspecialchars($job['department']) ?>. As a student in <?= htmlspecialchars($user['course'] ?? 'BS Information Systems') ?>, I have the requisite skills, organizational diligence, and vacant shift hours to fulfill the assigned duties reliably.</textarea>
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
                                    <button type="submit" class="btn-pill px-4">
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
