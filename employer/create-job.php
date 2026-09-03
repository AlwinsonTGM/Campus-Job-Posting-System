<?php
/**
 * Campus Job Posting System - Create Job Requisition Form
 * Archetype C: Detail & Sidebar Action Form (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();

$is_partner = ($user['employer_type'] ?? '') === 'approved_partner';
$ver_status = $user['verification_status'] ?? 'verified';
if ($user['role'] === 'employer' && $is_partner && $ver_status !== 'verified') {
    set_flash('danger', 'Your partner organization account is currently awaiting administrative accreditation. Vacancies cannot be published until verified.');
    header('Location: dashboard.php');
    exit;
}

$categories = get_categories();
$job_types = get_job_types();
$work_setups = get_work_setups();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = $_POST['category'] ?? 'Administrative & Clerical';
        $job_type = $_POST['job_type'] ?? 'Student Assistant';
        $work_setup = $_POST['work_setup'] ?? 'On-Campus';
        $department = trim($_POST['department'] ?? ($user['organization_name'] ?? ($user['department'] ?? 'Campus Office')));
        $location = trim($_POST['location'] ?? 'Campus Main Office');
        $pay_rate = trim($_POST['pay_rate'] ?? '₱80.00 / hour');
        $hours_per_week = trim($_POST['hours_per_week'] ?? '10 - 20 hrs/week');
        $vacancies = (int)($_POST['vacancies'] ?? 1);
        $deadline = $_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days'));
        $description = trim($_POST['description'] ?? '');
        $responsibilities = trim($_POST['responsibilities'] ?? '');
        $qualifications = trim($_POST['qualifications'] ?? '');
        $tags = trim($_POST['tags'] ?? "{$job_type}, {$work_setup}");

        if (empty($title) || empty($description)) {
            $error = 'Please provide the vacancy title and detailed description.';
        } elseif ($vacancies < 1) {
            $error = 'Vacancy quota must be at least 1 position.';
        } else {
            $photo_file = $_FILES['job_photo'] ?? null;

            $new_id = create_job([
                'title' => $title,
                'category' => $category,
                'job_type' => $job_type,
                'work_setup' => $work_setup,
                'employer_type' => $user['employer_type'] ?? 'university_office',
                'organization_name' => $user['organization_name'] ?? $department,
                'department' => $department,
                'location' => $location,
                'pay_rate' => $pay_rate,
                'hours_per_week' => $hours_per_week,
                'vacancies' => $vacancies,
                'deadline' => $deadline,
                'description' => $description,
                'responsibilities' => $responsibilities,
                'qualifications' => $qualifications,
                'tags' => $tags
            ], $photo_file);

            if ($new_id > 0) {
                set_flash('success', "New vacancy '{$title}' posted successfully!");
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Failed to create vacancy. Please try again.';
            }
        }
    }
}

$page_title = 'Post New Opportunity';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Back Link & Page Head -->
                <div class="mb-4">
                    <a href="dashboard.php" class="text-ink fw-bold small text-decoration-none d-inline-flex align-items-center gap-1 mb-3">
                        <i class="bi bi-arrow-left"></i> Back to Employer Dashboard
                    </a>
                    
                    <?php
                    render_page_head(
                        '',
                        'Publish a Campus Vacancy',
                        'Deploy an approved student assistantship, library opening, or academic laboratory position.'
                    );
                    ?>
                </div>

                <?php if ($error): ?>
                    <div class="alert-paper alert-paper--danger mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                            <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="create-job.php" method="POST" enctype="multipart/form-data" class="form-paper">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <div class="row g-4 mb-5">
                        
                        <!-- Left 8-col: Role Overview & Responsibilities -->
                        <div class="col-lg-8">
                            <div class="card-paper p-4 p-md-4 mb-4">
                                <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                    <i class="bi bi-card-text text-accent me-2"></i> 1. Vacancy Information
                                </h3>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label" for="job-title">Vacancy Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="job-title" class="form-control" placeholder="e.g. Laboratory Student Assistant" required autofocus>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="job-category">Job Family Category <span class="text-danger">*</span></label>
                                        <select name="category" id="job-category" class="form-select" required>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="job-type">Opportunity Type <span class="text-danger">*</span></label>
                                        <select name="job_type" id="job-type" class="form-select" required>
                                            <?php foreach ($job_types as $k => $label): ?>
                                                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="work-setup">Workplace Setup <span class="text-danger">*</span></label>
                                        <select name="work_setup" id="work-setup" class="form-select" required>
                                            <?php foreach ($work_setups as $k => $label): ?>
                                                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="job-desc">Detailed Job Description <span class="text-danger">*</span></label>
                                    <textarea name="description" id="job-desc" rows="4" class="form-control" placeholder="Describe the purpose, daily responsibilities, and department environment..." required></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="job-resp">Key Duties & Responsibilities (1 per line)</label>
                                    <textarea name="responsibilities" id="job-resp" rows="3" class="form-control" placeholder="Assist student visitors with computer lab login&#10;Organize physical and digital department forms&#10;Coordinate Daily Time Records for assistantship cohort"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="job-qual">Qualifications & Requirements (1 per line)</label>
                                    <textarea name="qualifications" id="job-qual" rows="3" class="form-control" placeholder="Currently enrolled student in good academic standing&#10;GWA of 2.25 or better&#10;Proficiency with basic spreadsheet and office software"></textarea>
                                </div>
                            </div>

                            <!-- Photo / Hiring Banner (Optional -> Qualifies for Featured) -->
                            <div class="card-paper p-4 p-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-line">
                                    <h3 class="card-paper-title fs-5 mb-0">
                                        <i class="bi bi-image text-accent me-2"></i> 2. Hiring Flyer / Office Banner <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2">Optional</span>
                                    </h3>
                                </div>
                                
                                <div class="p-3 mb-3 rounded border" style="background-color: var(--surface); border-style: dashed !important; border-color: var(--line) !important;">
                                    <div class="d-flex gap-3 align-items-start">
                                        <div class="p-2 rounded bg-accent-subtle text-accent fs-4 flex-shrink-0">
                                            <i class="bi bi-stars"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-ink small mb-1">Qualify for Featured Homepage Spotlight</div>
                                            <p class="text-muted-custom small mb-0" style="font-size: 13px;">
                                                Uploading a hiring flyer or office photo is optional. Postings with an uploaded photo are <strong>automatically showcased in the "Featured Campus &amp; Partner Opportunities" carousel</strong> on the homepage!
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" for="job-photo">Upload Poster / Photo (JPG, PNG, WebP · Max 10MB)</label>
                                    <input type="file" name="job_photo" id="job-photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text text-muted-custom small mt-1">
                                        <i class="bi bi-info-circle me-1"></i> Recommended 16:9 or 4:3 landscape ratio (e.g. 1200×675px).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right 4-col: Scheduling & Compensation Sidebar -->
                        <div class="col-lg-4">
                            <div class="card-paper p-4 position-sticky" style="top: 90px;">
                                <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                    <i class="bi bi-sliders text-accent me-2"></i> 3. Terms &amp; Quota
                                </h3>

                                <div class="mb-3">
                                    <label class="form-label" for="job-dept">Hiring Department / Office <span class="text-danger">*</span></label>
                                    <input type="text" name="department" id="job-dept" class="form-control" value="<?= htmlspecialchars($user['organization_name'] ?? ($user['department'] ?? 'Office of the University Registrar')) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="job-loc">Physical / Office Location <span class="text-danger">*</span></label>
                                    <input type="text" name="location" id="job-loc" class="form-control" placeholder="Admin Bldg Room 102" value="<?= htmlspecialchars($user['office_location'] ?? 'Campus Main Office') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="job-pay">Stipend Rate / Compensation <span class="text-danger">*</span></label>
                                    <input type="text" name="pay_rate" id="job-pay" class="form-control" value="₱85.00 / hour" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="job-vac">Open Slots <span class="text-danger">*</span></label>
                                        <input type="number" name="vacancies" id="job-vac" class="form-control" value="2" min="1" max="20" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="job-hours">Weekly Limit</label>
                                        <input type="text" name="hours_per_week" id="job-hours" class="form-control" value="10 - 20 hrs/week" readonly style="background-color: var(--cream);">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="job-deadline">Application Deadline <span class="text-danger">*</span></label>
                                    <input type="date" name="deadline" id="job-deadline" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="job-tags">Tags (Comma-separated)</label>
                                    <input type="text" name="tags" id="job-tags" class="form-control" value="Flexible Schedule, Urgent">
                                </div>

                                <button type="submit" class="btn-pill w-100 mb-2">
                                    <i class="bi bi-check-circle-fill"></i> PUBLISH REQUISITION
                                </button>
                                <a href="dashboard.php" class="btn-pill-outline btn-pill-sm w-100 text-center">
                                    Cancel
                                </a>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
