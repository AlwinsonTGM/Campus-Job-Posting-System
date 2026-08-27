<?php
/**
 * Campus Job Posting System - Create Job Opening Form
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$categories = get_categories();
$job_types = get_job_types();
$work_setups = get_work_setups();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error = 'Please provide the job title and detailed description.';
    } else {
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
            'tags' => array_filter(array_map('trim', explode(',', $tags)))
        ]);

        set_flash('success', "Vacancy '{$title}' ({$job_type}) was successfully published!");
        header('Location: dashboard.php');
        exit;
    }
}

$page_title = 'Post New Opportunity';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Employer Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Post Vacancy</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="card border-line shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-line">
                        <div class="stat-icon bg-accent-soft text-ink fs-3">
                            <i class="bi bi-plus-circle-fill"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-ink mb-0">Publish New Opportunity</h3>
                            <span class="text-muted-custom small">Deploy an approved student assistantship, part-time position, or internship</span>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4 rounded-3">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="create-job.php" method="POST">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-ink">Vacancy Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Student Library Assistant or Junior Web Intern" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Job Family Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Opportunity Type & Workplace Arrangement -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Opportunity Type <span class="text-danger">*</span></label>
                                <select name="job_type" class="form-select" required>
                                    <?php foreach ($job_types as $k => $label): ?>
                                        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Workplace Setup <span class="text-danger">*</span></label>
                                <select name="work_setup" class="form-select" required>
                                    <?php foreach ($work_setups as $k => $label): ?>
                                        <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Hiring Organization / Office <span class="text-danger">*</span></label>
                                <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['organization_name'] ?? ($user['department'] ?? 'Office of the University Registrar')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Physical / Reporting Location <span class="text-danger">*</span></label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Admin Bldg Room 102 or Tech Park Suite B" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Stipend / Pay Rate <span class="text-danger">*</span></label>
                                <input type="text" name="pay_rate" class="form-control" placeholder="₱85.00 / hour" value="₱85.00 / hour" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Weekly Hours <span class="text-danger">*</span></label>
                                <input type="text" name="hours_per_week" class="form-control" placeholder="10 - 20 hrs/week" value="10 - 15 hrs/week" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Open Slots (Vacancies) <span class="text-danger">*</span></label>
                                <input type="number" name="vacancies" class="form-control" value="2" min="1" max="20" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Application Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="deadline" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Job Tags (Comma-separated)</label>
                                <input type="text" name="tags" class="form-control" placeholder="Flexible, Urgent, Office Work" value="Flexible Schedule, Urgent">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">Detailed Job Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Describe the day-to-day duties and purpose of this position..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">Key Duties & Responsibilities (1 per line)</label>
                            <textarea name="responsibilities" rows="3" class="form-control" placeholder="Assist in document archiving&#10;Organize physical files&#10;Manage frontline visitor logbook"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-ink">Qualifications & Eligibility Requirements (1 per line)</label>
                            <textarea name="qualifications" rows="3" class="form-control" placeholder="Enrolled undergraduate student&#10;GWA of 2.25 or better&#10;Proficient in computer skills"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-accent-pill py-2 px-4">
                                <i class="bi bi-check-circle me-1"></i> PUBLISH OPPORTUNITY
                            </button>
                            <a href="dashboard.php" class="btn-outline-pill py-2 px-3">
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
