<?php
/**
 * Campus Job Posting System - Edit Job Opening Form
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['employer', 'admin']);
$job_id = $_GET['id'] ?? null;
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'Job posting not found.');
    header('Location: dashboard.php');
    exit;
}

$categories = get_categories();
$job_types = get_job_types();
$work_setups = get_work_setups();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? $job['category'];
    $job_type = $_POST['job_type'] ?? ($job['job_type'] ?? 'Student Assistant');
    $work_setup = $_POST['work_setup'] ?? ($job['work_setup'] ?? 'On-Campus');
    $location = trim($_POST['location'] ?? $job['location']);
    $pay_rate = trim($_POST['pay_rate'] ?? $job['pay_rate']);
    $hours_per_week = trim($_POST['hours_per_week'] ?? $job['hours_per_week']);
    $vacancies = (int)($_POST['vacancies'] ?? $job['vacancies']);
    $deadline = $_POST['deadline'] ?? $job['deadline'];
    $status = $_POST['status'] ?? $job['status'];
    $description = trim($_POST['description'] ?? $job['description']);

    if (empty($title) || empty($description)) {
        $error = 'Please fill in the job title and description.';
    } else {
        update_job($job['id'], [
            'title' => $title,
            'category' => $category,
            'job_type' => $job_type,
            'work_setup' => $work_setup,
            'location' => $location,
            'pay_rate' => $pay_rate,
            'hours_per_week' => $hours_per_week,
            'vacancies' => $vacancies,
            'deadline' => $deadline,
            'status' => $status,
            'description' => $description
        ]);

        set_flash('success', "Opportunity '{$title}' was updated successfully.");
        header('Location: dashboard.php');
        exit;
    }
}

$page_title = 'Edit ' . $job['title'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Employer Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Posting</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-line shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-line">
                        <div class="stat-icon bg-accent-soft text-ink fs-3">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-ink mb-0">Edit Opportunity Details</h3>
                            <span class="text-muted-custom small">Update parameters for <?= htmlspecialchars($job['title']) ?></span>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4 rounded-3">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="edit-job.php?id=<?= $job['id'] ?>" method="POST">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-ink">Vacancy Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['title']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Job Family Category</label>
                                <select name="category" class="form-select">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($job['category'] === $cat['name']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Opportunity Type & Workplace Arrangement -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Opportunity Type</label>
                                <select name="job_type" class="form-select">
                                    <?php foreach ($job_types as $k => $label): ?>
                                        <option value="<?= htmlspecialchars($k) ?>" <?= (($job['job_type'] ?? '') === $k) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Workplace Setup</label>
                                <select name="work_setup" class="form-select">
                                    <?php foreach ($work_setups as $k => $label): ?>
                                        <option value="<?= htmlspecialchars($k) ?>" <?= (($job['work_setup'] ?? '') === $k) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Physical / Reporting Location</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-ink">Vacancy Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>Active / Open for Applications</option>
                                    <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>Closed / Filled</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Stipend Rate</label>
                                <input type="text" name="pay_rate" class="form-control" value="<?= htmlspecialchars($job['pay_rate']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Weekly Hours</label>
                                <input type="text" name="hours_per_week" class="form-control" value="<?= htmlspecialchars($job['hours_per_week']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-ink">Total Slots</label>
                                <input type="number" name="vacancies" class="form-control" value="<?= $job['vacancies'] ?>" min="1" max="20" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-ink">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($job['deadline']) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-ink">Detailed Job Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($job['description']) ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-accent-pill py-2 px-4">
                                <i class="bi bi-check2-circle me-1"></i> SAVE CHANGES
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
