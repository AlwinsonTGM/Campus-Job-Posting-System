<?php
/**
 * Campus Job Posting System - Create Job Opening Form
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$categories = get_categories();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'Administrative & Clerical';
    $department = trim($_POST['department'] ?? ($user['department'] ?? 'Campus Office'));
    $location = trim($_POST['location'] ?? 'Campus Main Office');
    $pay_rate = trim($_POST['pay_rate'] ?? '₱80.00 / hour');
    $hours_per_week = trim($_POST['hours_per_week'] ?? '10 - 20 hrs/week');
    $vacancies = (int)($_POST['vacancies'] ?? 1);
    $deadline = $_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days'));
    $description = trim($_POST['description'] ?? '');
    $responsibilities = trim($_POST['responsibilities'] ?? '');
    $qualifications = trim($_POST['qualifications'] ?? '');
    $tags = trim($_POST['tags'] ?? 'Student Assistant, Flexible Shift');

    if (empty($title) || empty($description)) {
        $error = 'Please provide the job title and detailed description.';
    } else {
        $new_id = create_job([
            'title' => $title,
            'category' => $category,
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

        set_flash('success', "Job vacancy '{$title}' was successfully published!");
        header('Location: dashboard.php');
        exit;
    }
}

$page_title = 'Post New Department Vacancy';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-light flex-grow-1">
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
                
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="stat-icon bg-success text-white fs-3">
                            <i class="bi bi-plus-circle-fill"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0">Publish New Job Requisition</h3>
                            <span class="text-muted small">Post a student assistantship or department employment opportunity</span>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="create-job.php" method="POST">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-dark">Job Vacancy Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Student Library Assistant" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Hiring Department / Office <span class="text-danger">*</span></label>
                                <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department'] ?? 'Office of the University Registrar') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Physical Campus Location <span class="text-danger">*</span></label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Admin Bldg, Room 102" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Stipend / Pay Rate <span class="text-danger">*</span></label>
                                <input type="text" name="pay_rate" class="form-control" placeholder="₱85.00 / hour" value="₱80.00 / hour" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Expected Weekly Hours <span class="text-danger">*</span></label>
                                <input type="text" name="hours_per_week" class="form-control" placeholder="10 - 20 hrs/week" value="10 - 15 hrs/week" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Open Slots (Vacancies) <span class="text-danger">*</span></label>
                                <input type="number" name="vacancies" class="form-control" value="2" min="1" max="20" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Application Deadline <span class="text-danger">*</span></label>
                                <input type="date" name="deadline" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Job Tags (Comma-separated)</label>
                                <input type="text" name="tags" class="form-control" placeholder="Flexible, Urgent, Office Work" value="Student Assistant, Flexible Shift">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Detailed Job Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Describe the day-to-day duties and purpose of this student assistant position..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Key Duties & Responsibilities (1 per line)</label>
                            <textarea name="responsibilities" rows="3" class="form-control" placeholder="Assist in document archiving&#10;Organize physical files&#10;Manage frontline visitor logbook"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Qualifications & Eligibility Requirements (1 per line)</label>
                            <textarea name="qualifications" rows="3" class="form-control" placeholder="Enrolled undergraduate student&#10;GWA of 2.25 or better&#10;Proficient in Microsoft Office"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-academic py-2 px-4 fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Publish Opportunity
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary py-2 px-3">
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
