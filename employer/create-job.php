<?php
/**
 * Campus Job Posting System - Create Job Requisition Form
 * Archetype A/C: Multi-Stage Split Card Requisition Wizard (COAL101 Blueprint)
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
$initial_step = 1;
$responsibilities = [];
$qualifications = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $job_type = trim($_POST['job_type'] ?? '');
        $work_setup = trim($_POST['work_setup'] ?? '');
        $department = trim($_POST['department'] ?? ($user['organization_name'] ?? ($user['department'] ?? '')));
        $location = trim($_POST['location'] ?? ($user['office_location'] ?? ''));

        // Resolve category_id from category name
        $category_id = 3;
        foreach ($categories as $cat) {
            if (strcasecmp($cat['name'], $category) === 0) {
                $category_id = (int)$cat['id'];
                break;
            }
        }

        // Separated stipend rate & compensation
        $pay_amount = trim($_POST['pay_amount'] ?? '');
        $pay_period = trim($_POST['pay_period'] ?? '/ hour');

        if (is_numeric($pay_amount) && (float)$pay_amount > 0) {
            $formatted_amt = number_format((float)$pay_amount, 2);
            if ($pay_period === 'fixed stipend') {
                $pay_rate = '₱' . $formatted_amt . ' fixed stipend';
                $pay_type = 'Fixed Stipend';
            } else {
                $pay_rate = '₱' . $formatted_amt . ' ' . $pay_period;
                if (stripos($pay_period, 'hour') !== false) {
                    $pay_type = 'Hourly';
                } elseif (stripos($pay_period, 'month') !== false) {
                    $pay_type = 'Monthly';
                } elseif (stripos($pay_period, 'day') !== false) {
                    $pay_type = 'Daily';
                } elseif (stripos($pay_period, 'sem') !== false) {
                    $pay_type = 'Per Semester';
                } else {
                    $pay_type = 'Stipend';
                }
            }
        } elseif (!empty($_POST['pay_rate'])) {
            $pay_rate = trim($_POST['pay_rate']);
            $pay_type = 'Hourly';
        } else {
            $pay_rate = '₱80.00 / hour';
            $pay_type = 'Hourly';
        }

        $hours_per_week = trim($_POST['hours_per_week'] ?? '10 - 20 hrs/week');
        $vacancies = (int)($_POST['vacancies'] ?? 1);
        $deadline = trim($_POST['deadline'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Dynamic lines handling for duties & qualifications
        $raw_resp = $_POST['responsibilities'] ?? [];
        $responsibilities = is_array($raw_resp)
            ? array_values(array_filter(array_map('trim', $raw_resp), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_resp)), fn($v) => $v !== ''));

        $raw_qual = $_POST['qualifications'] ?? [];
        $qualifications = is_array($raw_qual)
            ? array_values(array_filter(array_map('trim', $raw_qual), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_qual)), fn($v) => $v !== ''));

        $tags = trim($_POST['tags'] ?? '');
        if (empty($tags) && (!empty($job_type) || !empty($work_setup))) {
            $tags = implode(', ', array_filter([$job_type, $work_setup]));
        }

        // Server-side step validation
        if (empty($title) || empty($category) || empty($job_type) || empty($work_setup) || empty($description)) {
            $error = 'Please complete all required fields in Step 1 (Vacancy Information).';
            $initial_step = 1;
        } elseif (empty($department) || empty($location) || empty($pay_amount) || empty($deadline)) {
            $error = 'Please complete all required terms & quota fields in Step 3.';
            $initial_step = 3;
        } elseif (!is_numeric($pay_amount) || (float)$pay_amount <= 0) {
            $error = 'Please provide a valid positive stipend rate.';
            $initial_step = 3;
        } elseif ($vacancies < 1) {
            $error = 'Vacancy quota must be at least 1 position.';
            $initial_step = 3;
        } else {
            $photo_file = $_FILES['job_photo'] ?? null;

            $new_id = create_job([
                'title' => $title,
                'category' => $category,
                'category_id' => $category_id,
                'job_type' => $job_type,
                'work_setup' => $work_setup,
                'employer_type' => $user['employer_type'] ?? 'university_office',
                'organization_name' => $user['organization_name'] ?? $department,
                'department' => $department,
                'location' => $location,
                'pay_rate' => $pay_rate,
                'pay_type' => $pay_type,
                'hours_per_week' => $hours_per_week,
                'vacancies' => $vacancies,
                'deadline' => $deadline,
                'description' => $description,
                'responsibilities' => $responsibilities,
                'qualifications' => $qualifications,
                'tags' => $tags
            ], $photo_file);

            if ($new_id > 0) {
                set_flash('success', "New vacancy '{$title}' published successfully!");
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Failed to create vacancy. Please try again.';
                $initial_step = 3;
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
                        'Publish an approved student assistantship or academic opening.'
                    );
                    ?>
                </div>

                <?php if ($error): ?>
                    <div class="alert-paper alert-paper--danger mb-4" id="server-error-alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                            <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Client-Side Step Validation Alert Box -->
                <div class="alert-paper alert-paper--danger mb-4 d-none" id="step-error-alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <div class="small fw-semibold text-ink" id="step-error-message">Please fill out all required fields to proceed.</div>
                    </div>
                </div>

                <div class="row g-4 mb-5">

                    <!-- Left Column: Stepper Track & Dynamic Context Card -->
                    <div class="col-lg-4 col-xl-4">
                        <div class="card-paper p-4 position-sticky" style="top: 90px;">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-line">
                                <span class="badge bg-ink text-white fw-bold px-2 py-1 small">Requisition Steps</span>
                                <span class="small text-muted-custom">COAL101 Multi-Stage</span>
                            </div>

                            <!-- Desktop Vertical Stepper -->
                            <div class="reg-stepper" id="job-desktop-stepper">
                                <div class="reg-stepper-track"></div>

                                <!-- Step Item 1 -->
                                <div class="reg-step-item is-active is-clickable" id="step-nav-1" onclick="handleStepNavClick(1)">
                                    <div class="reg-step-badge" id="step-badge-1">1</div>
                                    <div class="reg-step-text">
                                        <div class="reg-step-title">
                                            <span>Vacancy Information</span>
                                            <i class="bi bi-check-circle-fill text-accent d-none" id="step-check-1"></i>
                                        </div>
                                        <div class="reg-step-desc">Title, category, setup & duties</div>
                                    </div>
                                </div>

                                <!-- Step Item 2 -->
                                <div class="reg-step-item" id="step-nav-2" onclick="handleStepNavClick(2)">
                                    <div class="reg-step-badge" id="step-badge-2">2</div>
                                    <div class="reg-step-text">
                                        <div class="reg-step-title">
                                            <span>Hiring Flyer / Media</span>
                                            <i class="bi bi-check-circle-fill text-accent d-none" id="step-check-2"></i>
                                        </div>
                                        <div class="reg-step-desc">Optional homepage spotlight flyer</div>
                                    </div>
                                </div>

                                <!-- Step Item 3 -->
                                <div class="reg-step-item" id="step-nav-3" onclick="handleStepNavClick(3)">
                                    <div class="reg-step-badge" id="step-badge-3">3</div>
                                    <div class="reg-step-text">
                                        <div class="reg-step-title">
                                            <span>Terms & Quota</span>
                                            <i class="bi bi-check-circle-fill text-accent d-none" id="step-check-3"></i>
                                        </div>
                                        <div class="reg-step-desc">Stipend, slots & deadline</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Contextual Hint Box -->
                            <div class="reg-hint-box mt-3" id="job-hint-box">
                                <div class="reg-hint-badge" id="job-hint-step-label">
                                    <i class="bi bi-lightbulb-fill"></i> <span>Step 1 Tip</span>
                                </div>
                                <p class="reg-hint-text" id="job-hint-text">
                                    Define day-to-day duties clearly using the addable line inputs so applicants can easily verify their qualifications.
                                </p>
                            </div>

                            <!-- Real-time Live Summary Box -->
                            <div class="p-3 bg-surface rounded border border-line mt-3">
                                <div class="small fw-bold text-ink mb-2 d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-eye me-1 text-accent"></i> Posting Preview</span>
                                    <span class="badge bg-white text-muted border" id="preview-badge-type">Draft</span>
                                </div>
                                <div class="fw-bold text-ink text-truncate mb-1" id="preview-title" style="font-size: 13.5px;">Untitled Vacancy</div>
                                <div class="small text-muted-custom mb-2 text-truncate" id="preview-dept">Department / Office</div>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-line small">
                                    <span class="fw-bold text-accent" id="preview-pay">₱0.00 / hr</span>
                                    <span class="text-muted-custom" id="preview-slots">0 slots open</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right Column: Step Form Panes -->
                    <div class="col-lg-8 col-xl-8">

                        <!-- Mobile Mini Stepper (shown only < 992px) -->
                        <div class="reg-mobile-stepper" id="job-mobile-stepper">
                            <div class="reg-mobile-step is-active" id="mob-step-1" onclick="handleStepNavClick(1)">
                                <div class="reg-mobile-dot" id="mob-dot-1">1</div>
                                <span>Vacancy Info</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted-custom small"></i>
                            <div class="reg-mobile-step" id="mob-step-2" onclick="handleStepNavClick(2)">
                                <div class="reg-mobile-dot" id="mob-dot-2">2</div>
                                <span>Flyer</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted-custom small"></i>
                            <div class="reg-mobile-step" id="mob-step-3" onclick="handleStepNavClick(3)">
                                <div class="reg-mobile-dot" id="mob-dot-3">3</div>
                                <span>Terms & Quota</span>
                            </div>
                        </div>

                        <!-- Progress Meta Header -->
                        <div class="reg-progress-wrap mb-4">
                            <div class="reg-progress-meta">
                                <div>
                                    <span class="reg-progress-step-tag" id="progress-step-tag">Step 1 of 3</span>
                                    <h2 class="reg-progress-title" id="progress-step-title">Vacancy Information</h2>
                                </div>
                                <div>
                                    <span class="badge bg-surface text-ink border border-line fw-semibold py-2 px-3">
                                        <i class="bi bi-building me-1 text-accent"></i>
                                        <?= htmlspecialchars($user['organization_name'] ?? ($user['department'] ?? 'Campus Requisition')) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="reg-progress-track">
                                <div class="reg-progress-fill" id="job-progress-fill" style="width: 33.33%;"></div>
                            </div>
                        </div>

                        <form action="create-job.php" method="POST" id="job-wizard-form" enctype="multipart/form-data" class="form-paper" data-initial-step="<?= $initial_step ?>" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                            <!-- ========================================== -->
                            <!-- STEP PANE 1: VACANCY INFORMATION           -->
                            <!-- ========================================== -->
                            <div class="reg-step-pane is-visible" id="step-pane-1">
                                <div class="card-paper p-4 p-md-4">
                                    <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                        <i class="bi bi-card-text text-accent me-2"></i> 1. Vacancy Information
                                    </h3>

                                    <!-- Title -->
                                    <div class="mb-3">
                                        <label class="form-label" for="job-title">Vacancy Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="job-title" class="form-control" placeholder="e.g. Laboratory Student Assistant" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required autofocus>
                                    </div>

                                    <!-- Category, Type, Setup -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label" for="job-category">Job Family Category <span class="text-danger">*</span></label>
                                            <select name="category" id="job-category" class="form-select" required>
                                                <option value="" disabled <?= empty($_POST['category']) ? 'selected' : '' ?>>Select Job Category...</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= htmlspecialchars($cat['name']) ?>" <?= (isset($_POST['category']) && $_POST['category'] === $cat['name']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="job-type">Opportunity Type <span class="text-danger">*</span></label>
                                            <select name="job_type" id="job-type" class="form-select" required>
                                                <option value="" disabled <?= empty($_POST['job_type']) ? 'selected' : '' ?>>Select Opportunity Type...</option>
                                                <?php foreach ($job_types as $k => $label): ?>
                                                    <option value="<?= htmlspecialchars($k) ?>" <?= (isset($_POST['job_type']) && $_POST['job_type'] === $k) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="work-setup">Workplace Setup <span class="text-danger">*</span></label>
                                            <select name="work_setup" id="work-setup" class="form-select" required>
                                                <option value="" disabled <?= empty($_POST['work_setup']) ? 'selected' : '' ?>>Select Workplace Setup...</option>
                                                <?php foreach ($work_setups as $k => $label): ?>
                                                    <option value="<?= htmlspecialchars($k) ?>" <?= (isset($_POST['work_setup']) && $_POST['work_setup'] === $k) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="mb-4">
                                        <label class="form-label" for="job-desc">Detailed Job Description <span class="text-danger">*</span></label>
                                        <textarea name="description" id="job-desc" rows="4" class="form-control" placeholder="Describe the overall purpose, daily duties, schedule flexibility, and office working environment..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    </div>

                                    <!-- Key Duties & Responsibilities (Addable Input Bar) -->
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label class="form-label mb-0 fw-bold">Key Duties & Responsibilities</label>
                                            <span class="small text-muted-custom">Add lines one by one</span>
                                        </div>
                                        <div class="small text-muted-custom mb-2">Type a specific task and click <strong class="text-ink">+</strong> (or press Enter) to add it to the list.</div>

                                        <!-- Addable Input Bar -->
                                        <div class="input-group mb-2">
                                            <input type="text" id="resp-input-bar" class="form-control" placeholder="e.g. Assist student visitors with computer lab login...">
                                            <button type="button" class="btn btn-accent px-3 fw-bold" id="btn-add-resp" title="Add Duty">
                                                <i class="bi bi-plus-lg me-1"></i> Add Line
                                            </button>
                                        </div>

                                        <!-- Dynamic Lines Container -->
                                        <div class="dynamic-lines-wrapper" id="resp-items-container">
                                            <div class="dynamic-empty-hint" id="resp-empty-hint" <?= !empty($responsibilities) ? 'style="display:none;"' : '' ?>>
                                                <i class="bi bi-info-circle me-1"></i> No duties added yet. Use the bar above and click <strong>+ Add Line</strong> to add duties.
                                            </div>
                                            <?php if (!empty($responsibilities)): ?>
                                                <?php foreach ($responsibilities as $idx => $duty): ?>
                                                    <div class="dynamic-line-item d-flex align-items-center gap-2 mb-2">
                                                        <span class="dynamic-line-badge"><?= $idx + 1 ?></span>
                                                        <input type="text" name="responsibilities[]" class="form-control form-control-sm border-0 bg-transparent px-2" placeholder="Key duty or responsibility..." value="<?= htmlspecialchars($duty) ?>">
                                                        <button type="button" class="btn btn-outline-danger btn-sm dynamic-line-btn-del" title="Remove line">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Qualifications & Requirements (Addable Input Bar) -->
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label class="form-label mb-0 fw-bold">Qualifications & Requirements</label>
                                            <span class="small text-muted-custom">Add lines one by one</span>
                                        </div>
                                        <div class="small text-muted-custom mb-2">Type a requirement or qualification and click <strong class="text-ink">+</strong> (or press Enter) to add.</div>

                                        <!-- Addable Input Bar -->
                                        <div class="input-group mb-2">
                                            <input type="text" id="qual-input-bar" class="form-control" placeholder="e.g. GWA of 2.25 or better...">
                                            <button type="button" class="btn btn-accent px-3 fw-bold" id="btn-add-qual" title="Add Qualification">
                                                <i class="bi bi-plus-lg me-1"></i> Add Line
                                            </button>
                                        </div>

                                        <!-- Dynamic Lines Container -->
                                        <div class="dynamic-lines-wrapper" id="qual-items-container">
                                            <div class="dynamic-empty-hint" id="qual-empty-hint" <?= !empty($qualifications) ? 'style="display:none;"' : '' ?>>
                                                <i class="bi bi-info-circle me-1"></i> No qualifications added yet. Use the bar above and click <strong>+ Add Line</strong> to add requirements.
                                            </div>
                                            <?php if (!empty($qualifications)): ?>
                                                <?php foreach ($qualifications as $idx => $qual): ?>
                                                    <div class="dynamic-line-item d-flex align-items-center gap-2 mb-2">
                                                        <span class="dynamic-line-badge"><?= $idx + 1 ?></span>
                                                        <input type="text" name="qualifications[]" class="form-control form-control-sm border-0 bg-transparent px-2" placeholder="Qualification or requirement..." value="<?= htmlspecialchars($qual) ?>">
                                                        <button type="button" class="btn btn-outline-danger btn-sm dynamic-line-btn-del" title="Remove line">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Step 1 Navigation Actions -->
                                    <div class="reg-step-actions">
                                        <a href="dashboard.php" class="btn-step-prev text-decoration-none">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </a>
                                        <button type="button" class="btn-step-next" onclick="nextStep()">
                                            Next: Hiring Flyer <i class="bi bi-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ========================================== -->
                            <!-- STEP PANE 2: HIRING FLYER / OFFICE BANNER  -->
                            <!-- ========================================== -->
                            <div class="reg-step-pane" id="step-pane-2">
                                <div class="card-paper p-4 p-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-line">
                                        <h3 class="card-paper-title fs-5 mb-0">
                                            <i class="bi bi-image text-accent me-2"></i> 2. Hiring Flyer / Office Banner
                                            <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2">Optional</span>
                                        </h3>
                                    </div>

                                    <div class="p-3 mb-4 rounded border" style="background-color: var(--surface); border-style: dashed !important; border-color: var(--line) !important;">
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

                                    <div class="mb-4">
                                        <label class="form-label" for="job-photo">Upload Poster / Photo (JPG, PNG, WebP · Max 10MB)</label>
                                        <input type="file" name="job_photo" id="job-photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                                        <div class="form-text text-muted-custom small mt-1">
                                            <i class="bi bi-info-circle me-1"></i> Recommended 16:9 or 4:3 landscape ratio (e.g. 1200×675px).
                                        </div>
                                    </div>

                                    <!-- Live Image Preview Container -->
                                    <div id="flyer-preview-wrapper" class="d-none mb-4">
                                        <label class="form-label fw-bold small text-ink">Selected Flyer Preview</label>
                                        <div class="flyer-preview-container p-2 border rounded bg-white position-relative">
                                            <img id="flyer-preview-img" class="flyer-preview-img rounded" src="#" alt="Flyer Preview">
                                            <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                                <span class="small text-muted-custom text-truncate" id="flyer-file-info" style="max-width: 250px;"></span>
                                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" id="btn-remove-flyer">
                                                    <i class="bi bi-trash me-1"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2 Navigation Actions -->
                                    <div class="reg-step-actions">
                                        <button type="button" class="btn-step-prev" onclick="prevStep()">
                                            <i class="bi bi-arrow-left"></i> Back: Vacancy Info
                                        </button>
                                        <button type="button" class="btn-step-next" onclick="nextStep()">
                                            Next: Terms & Quota <i class="bi bi-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ========================================== -->
                            <!-- STEP PANE 3: TERMS & QUOTA                 -->
                            <!-- ========================================== -->
                            <div class="reg-step-pane" id="step-pane-3">
                                <div class="card-paper p-4 p-md-4">
                                    <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                        <i class="bi bi-sliders text-accent me-2"></i> 3. Terms & Quota
                                    </h3>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="job-dept">Hiring Department / Office <span class="text-danger">*</span></label>
                                            <input type="text" name="department" id="job-dept" class="form-control" placeholder="e.g. Office of the University Registrar" value="<?= htmlspecialchars($_POST['department'] ?? ($user['organization_name'] ?? ($user['department'] ?? ''))) ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="job-loc">Physical / Office Location <span class="text-danger">*</span></label>
                                            <input type="text" name="location" id="job-loc" class="form-control" placeholder="e.g. KLD Admin Building, 1st Floor, Room 1" value="<?= htmlspecialchars($_POST['location'] ?? ($user['office_location'] ?? '')) ?>" required>
                                        </div>
                                    </div>

                                    <!-- Separated Stipend Rate & Compensation -->
                                    <div class="mb-3">
                                        <label class="form-label" for="job-pay-amount">Stipend Rate &amp; Payment Frequency <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light fw-bold text-muted">₱</span>
                                            <input type="number" name="pay_amount" id="job-pay-amount" class="form-control" placeholder="85.00" step="0.50" min="0" value="<?= htmlspecialchars($_POST['pay_amount'] ?? '') ?>" required>
                                            <?php $cur_period = $_POST['pay_period'] ?? '/ hour'; ?>
                                            <select name="pay_period" id="job-pay-period" class="form-select" style="max-width: 175px;" required>
                                                <option value="/ hour" <?= $cur_period === '/ hour' ? 'selected' : '' ?>>/ hour</option>
                                                <option value="/ day" <?= $cur_period === '/ day' ? 'selected' : '' ?>>/ day</option>
                                                <option value="/ week" <?= $cur_period === '/ week' ? 'selected' : '' ?>>/ week</option>
                                                <option value="/ month" <?= $cur_period === '/ month' ? 'selected' : '' ?>>/ month</option>
                                                <option value="/ semester" <?= $cur_period === '/ semester' ? 'selected' : '' ?>>/ semester</option>
                                                <option value="fixed stipend" <?= $cur_period === 'fixed stipend' ? 'selected' : '' ?>>fixed stipend</option>
                                            </select>
                                        </div>
                                        <div class="form-text text-muted-custom small mt-1">
                                            <i class="bi bi-info-circle me-1"></i> Amount is in Philippine Peso (PHP). Choose the frequency rate from the dropdown.
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="job-vac">Open Slots <span class="text-danger">*</span></label>
                                            <input type="number" name="vacancies" id="job-vac" class="form-control" placeholder="e.g. 2" min="1" max="50" value="<?= htmlspecialchars($_POST['vacancies'] ?? '') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="job-hours">Weekly Limit <span class="text-danger">*</span></label>
                                            <?php $cur_hours = $_POST['hours_per_week'] ?? '10 - 20 hrs/week'; ?>
                                            <select name="hours_per_week" id="job-hours" class="form-select" required>
                                                <option value="10 - 20 hrs/week" <?= $cur_hours === '10 - 20 hrs/week' ? 'selected' : '' ?>>10 - 20 hrs/week (Standard Student Assistant)</option>
                                                <option value="Up to 15 hrs/week" <?= $cur_hours === 'Up to 15 hrs/week' ? 'selected' : '' ?>>Up to 15 hrs/week</option>
                                                <option value="Up to 20 hrs/week" <?= $cur_hours === 'Up to 20 hrs/week' ? 'selected' : '' ?>>Up to 20 hrs/week</option>
                                                <option value="Up to 30 hrs/week" <?= $cur_hours === 'Up to 30 hrs/week' ? 'selected' : '' ?>>Up to 30 hrs/week (Partner Placement)</option>
                                                <option value="Flexible Schedule" <?= $cur_hours === 'Flexible Schedule' ? 'selected' : '' ?>>Flexible Schedule</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label" for="job-deadline">Application Deadline <span class="text-danger">*</span></label>
                                            <input type="date" name="deadline" id="job-deadline" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="job-tags">Tags (Comma-separated)</label>
                                            <input type="text" name="tags" id="job-tags" class="form-control" placeholder="e.g. Flexible Schedule, Urgent, IT Support" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <!-- Requisition Confirmation Banner -->
                                    <div class="p-3 bg-surface rounded border border-line mb-4">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-shield-check text-accent fs-5 flex-shrink-0 mt-1"></i>
                                            <div class="small text-muted-custom">
                                                By publishing this requisition, you confirm that this opening adheres to institutional student assistantship compensation standards and campus labor policies.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3 Navigation Actions -->
                                    <div class="reg-step-actions">
                                        <button type="button" class="btn-step-prev" onclick="prevStep()">
                                            <i class="bi bi-arrow-left"></i> Back: Hiring Flyer
                                        </button>
                                        <button type="submit" class="btn-step-next" id="btn-publish-job" style="background-color: var(--accent); color: var(--ink); border: none;">
                                            <i class="bi bi-check-circle-fill"></i> PUBLISH REQUISITION
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<script>
let currentStep = 1;
let maxVisitedStep = 1;

const stepTitles = {
    1: 'Vacancy Information',
    2: 'Hiring Flyer / Office Banner',
    3: 'Terms & Quota'
};

const stepHints = {
    1: {
        label: 'Step 1 Tip',
        text: 'Define day-to-day duties clearly using the addable line inputs so applicants can easily verify their qualifications.'
    },
    2: {
        label: 'Step 2 Tip',
        text: 'Attaching an office flyer automatically showcases your opening in the Featured Carousel on the landing page!'
    },
    3: {
        label: 'Step 3 Tip',
        text: 'Set accurate stipend amounts and weekly limits in accordance with campus student assistantship guidelines.'
    }
};

function showStepError(msg) {
    const alertBox = document.getElementById('step-error-alert');
    const msgBox = document.getElementById('step-error-message');
    if (alertBox && msgBox) {
        msgBox.textContent = msg;
        alertBox.classList.remove('d-none');
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function hideStepError() {
    const alertBox = document.getElementById('step-error-alert');
    if (alertBox) alertBox.classList.add('d-none');
}

function validateCurrentStep() {
    hideStepError();

    if (currentStep === 1) {
        const title = document.getElementById('job-title');
        const cat = document.getElementById('job-category');
        const type = document.getElementById('job-type');
        const setup = document.getElementById('work-setup');
        const desc = document.getElementById('job-desc');

        if (!title || !title.value.trim()) {
            showStepError('Please provide a Vacancy Title.');
            if (title) title.focus();
            return false;
        }
        if (!cat || !cat.value) {
            showStepError('Please select a Job Family Category.');
            if (cat) cat.focus();
            return false;
        }
        if (!type || !type.value) {
            showStepError('Please select an Opportunity Type.');
            if (type) type.focus();
            return false;
        }
        if (!setup || !setup.value) {
            showStepError('Please select a Workplace Setup.');
            if (setup) setup.focus();
            return false;
        }
        if (!desc || !desc.value.trim()) {
            showStepError('Please enter a Detailed Job Description.');
            if (desc) desc.focus();
            return false;
        }
        return true;
    }

    if (currentStep === 2) {
        // Flyer upload is optional
        return true;
    }

    if (currentStep === 3) {
        const dept = document.getElementById('job-dept');
        const loc = document.getElementById('job-loc');
        const payAmt = document.getElementById('job-pay-amount');
        const vac = document.getElementById('job-vac');
        const deadline = document.getElementById('job-deadline');

        if (!dept || !dept.value.trim()) {
            showStepError('Please specify the Hiring Department / Office.');
            if (dept) dept.focus();
            return false;
        }
        if (!loc || !loc.value.trim()) {
            showStepError('Please provide the Physical / Office Location.');
            if (loc) loc.focus();
            return false;
        }
        if (!payAmt || !payAmt.value || parseFloat(payAmt.value) <= 0) {
            showStepError('Please provide a valid Stipend Rate amount.');
            if (payAmt) payAmt.focus();
            return false;
        }
        if (!vac || !vac.value || parseInt(vac.value, 10) < 1) {
            showStepError('Open slots quota must be at least 1 position.');
            if (vac) vac.focus();
            return false;
        }
        if (!deadline || !deadline.value) {
            showStepError('Please choose an Application Deadline.');
            if (deadline) deadline.focus();
            return false;
        }
        return true;
    }

    return true;
}

function goToStep(targetStep) {
    if (targetStep < 1 || targetStep > 3) return;
    currentStep = targetStep;
    if (currentStep > maxVisitedStep) {
        maxVisitedStep = currentStep;
    }
    updateWizardUI();
    hideStepError();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextStep() {
    if (validateCurrentStep()) {
        goToStep(currentStep + 1);
    }
}

function prevStep() {
    hideStepError();
    goToStep(currentStep - 1);
}

function handleStepNavClick(step) {
    if (step <= maxVisitedStep || step === currentStep) {
        goToStep(step);
    } else if (step === currentStep + 1) {
        nextStep();
    }
}

function updateWizardUI() {
    // 1. Panes
    for (let s = 1; s <= 3; s++) {
        const pane = document.getElementById(`step-pane-${s}`);
        if (pane) {
            if (s === currentStep) {
                pane.classList.add('is-visible');
            } else {
                pane.classList.remove('is-visible');
            }
        }
    }

    // 2. Desktop Stepper
    for (let s = 1; s <= 3; s++) {
        const navItem = document.getElementById(`step-nav-${s}`);
        const badge = document.getElementById(`step-badge-${s}`);
        const checkIcon = document.getElementById(`step-check-${s}`);

        if (navItem && badge) {
            navItem.classList.remove('is-active', 'is-completed', 'is-clickable');

            if (s === currentStep) {
                navItem.classList.add('is-active', 'is-clickable');
                badge.innerHTML = s;
                if (checkIcon) checkIcon.classList.add('d-none');
            } else if (s < currentStep) {
                navItem.classList.add('is-completed', 'is-clickable');
                badge.innerHTML = '<i class="bi bi-check-lg"></i>';
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                if (s <= maxVisitedStep) {
                    navItem.classList.add('is-clickable');
                }
                badge.innerHTML = s;
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        }
    }

    // 3. Mobile Mini Stepper
    for (let s = 1; s <= 3; s++) {
        const mobStep = document.getElementById(`mob-step-${s}`);
        const mobDot = document.getElementById(`mob-dot-${s}`);

        if (mobStep && mobDot) {
            mobStep.classList.remove('is-active', 'is-completed');
            if (s === currentStep) {
                mobStep.classList.add('is-active');
                mobDot.innerHTML = s;
            } else if (s < currentStep) {
                mobStep.classList.add('is-completed');
                mobDot.innerHTML = '<i class="bi bi-check"></i>';
            } else {
                mobDot.innerHTML = s;
            }
        }
    }

    // 4. Progress Header & Bar
    const progTag = document.getElementById('progress-step-tag');
    const progTitle = document.getElementById('progress-step-title');
    const progFill = document.getElementById('job-progress-fill');

    if (progTag) progTag.textContent = `Step ${currentStep} of 3`;
    if (progTitle) progTitle.textContent = stepTitles[currentStep] || 'Requisition Details';
    if (progFill) {
        const pct = currentStep === 1 ? '33.33%' : (currentStep === 2 ? '66.66%' : '100%');
        progFill.style.width = pct;
    }

    // 5. Contextual Hint Box
    const hint = stepHints[currentStep] || { label: `Step ${currentStep} Tip`, text: '' };
    const hintLabel = document.getElementById('job-hint-step-label');
    const hintText = document.getElementById('job-hint-text');
    if (hintLabel) hintLabel.innerHTML = `<i class="bi bi-lightbulb-fill"></i> <span>${hint.label}</span>`;
    if (hintText) hintText.textContent = hint.text;

    // 6. Update Live Preview Box
    updateLivePreview();
}

function updateLivePreview() {
    const titleVal = document.getElementById('job-title')?.value.trim();
    const deptVal = document.getElementById('job-dept')?.value.trim();
    const typeVal = document.getElementById('job-type')?.value;
    const payAmtVal = document.getElementById('job-pay-amount')?.value.trim();
    const payPeriodVal = document.getElementById('job-pay-period')?.value || '/ hour';
    const vacVal = document.getElementById('job-vac')?.value.trim();

    const previewTitle = document.getElementById('preview-title');
    const previewDept = document.getElementById('preview-dept');
    const previewBadge = document.getElementById('preview-badge-type');
    const previewPay = document.getElementById('preview-pay');
    const previewSlots = document.getElementById('preview-slots');

    if (previewTitle) previewTitle.textContent = titleVal || 'Untitled Vacancy';
    if (previewDept) previewDept.textContent = deptVal || 'Department / Office';
    if (previewBadge) previewBadge.textContent = typeVal || 'Draft';
    if (previewPay) {
        if (payAmtVal && parseFloat(payAmtVal) > 0) {
            previewPay.textContent = `₱${parseFloat(payAmtVal).toFixed(2)} ${payPeriodVal}`;
        } else {
            previewPay.textContent = '₱-- / hr';
        }
    }
    if (previewSlots) {
        previewSlots.textContent = vacVal ? `${vacVal} slot(s)` : '0 slots open';
    }
}

// Dynamic Line Items Manager (Duties & Qualifications)
function setupDynamicList(inputBarId, btnAddId, containerId, emptyHintId, fieldName, placeholderText) {
    const inputBar = document.getElementById(inputBarId);
    const btnAdd = document.getElementById(btnAddId);
    const container = document.getElementById(containerId);
    const emptyHint = document.getElementById(emptyHintId);

    function renumber() {
        const rows = container.querySelectorAll('.dynamic-line-item');
        if (rows.length === 0) {
            if (emptyHint) emptyHint.style.display = 'block';
        } else {
            if (emptyHint) emptyHint.style.display = 'none';
            rows.forEach((row, idx) => {
                const badge = row.querySelector('.dynamic-line-badge');
                if (badge) badge.textContent = idx + 1;
            });
        }
    }

    function addRow(valueText = '') {
        const row = document.createElement('div');
        row.className = 'dynamic-line-item d-flex align-items-center gap-2 mb-2';
        row.innerHTML = `
            <span class="dynamic-line-badge">1</span>
            <input type="text" name="${fieldName}[]" class="form-control form-control-sm border-0 bg-transparent px-2" placeholder="${placeholderText}" value="${valueText.replace(/"/g, '&quot;')}">
            <button type="button" class="btn btn-outline-danger btn-sm dynamic-line-btn-del" title="Remove line">
                <i class="bi bi-x-lg"></i>
            </button>
        `;

        row.querySelector('.dynamic-line-btn-del').addEventListener('click', function() {
            row.remove();
            renumber();
        });

        // Enter key in the line input creates next line
        row.querySelector('input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                inputBar.focus();
            }
        });

        container.appendChild(row);
        renumber();
        return row;
    }

    function handleAdd() {
        const text = inputBar.value.trim();
        if (text) {
            addRow(text);
            inputBar.value = '';
            inputBar.focus();
        }
    }

    if (btnAdd && inputBar) {
        btnAdd.addEventListener('click', handleAdd);
        inputBar.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleAdd();
            }
        });
    }

    // Wire up existing rows (e.g. from server validation reload)
    container.querySelectorAll('.dynamic-line-item').forEach(row => {
        row.querySelector('.dynamic-line-btn-del')?.addEventListener('click', function() {
            row.remove();
            renumber();
        });
        row.querySelector('input')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                inputBar.focus();
            }
        });
    });
    renumber();

    return { addRow, renumber };
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Setup Dynamic Lists
    setupDynamicList(
        'resp-input-bar',
        'btn-add-resp',
        'resp-items-container',
        'resp-empty-hint',
        'responsibilities',
        'Key duty or responsibility...'
    );

    setupDynamicList(
        'qual-input-bar',
        'btn-add-qual',
        'qual-items-container',
        'qual-empty-hint',
        'qualifications',
        'Qualification or requirement...'
    );

    // 2. Setup Live Flyer Preview
    const flyerInput = document.getElementById('job-photo');
    const flyerWrapper = document.getElementById('flyer-preview-wrapper');
    const flyerImg = document.getElementById('flyer-preview-img');
    const flyerInfo = document.getElementById('flyer-file-info');
    const btnRemoveFlyer = document.getElementById('btn-remove-flyer');

    if (flyerInput) {
        flyerInput.addEventListener('change', function(e) {
            const file = e.target.files && e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    if (flyerImg) flyerImg.src = evt.target.result;
                    if (flyerInfo) flyerInfo.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                    if (flyerWrapper) flyerWrapper.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoveFlyer && flyerInput) {
        btnRemoveFlyer.addEventListener('click', function() {
            flyerInput.value = '';
            if (flyerWrapper) flyerWrapper.classList.add('d-none');
            if (flyerImg) flyerImg.src = '#';
        });
    }

    // 3. Live Preview Listeners
    ['job-title', 'job-dept', 'job-type', 'job-pay-amount', 'job-pay-period', 'job-vac'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', updateLivePreview);
            el.addEventListener('change', updateLivePreview);
        }
    });

    // 4. Form Submit & Keydown Validation
    const form = document.getElementById('job-wizard-form');
    if (form) {
        // Prevent accidental enter key on text inputs from skipping steps or prematurely submitting
        form.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'submit' && e.target.id !== 'resp-input-bar' && e.target.id !== 'qual-input-bar') {
                e.preventDefault();
                if (currentStep < 3) {
                    nextStep();
                }
                return false;
            }
        });

        form.addEventListener('submit', function(e) {
            if (currentStep < 3) {
                e.preventDefault();
                nextStep();
                return false;
            }
            if (!validateCurrentStep()) {
                e.preventDefault();
                return false;
            }

            // Double submit prevention
            const publishBtn = document.getElementById('btn-publish-job');
            if (publishBtn) {
                setTimeout(() => {
                    publishBtn.disabled = true;
                    publishBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Publishing...';
                }, 10);
            }
        });
    }

    // 5. Initial Step
    const initialStep = form ? parseInt(form.getAttribute('data-initial-step') || '1', 10) : 1;
    goToStep(initialStep);
});
</script>
