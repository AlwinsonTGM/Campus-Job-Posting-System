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
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? $job['category'];
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
            'location' => $location,
            'pay_rate' => $pay_rate,
            'hours_per_week' => $hours_per_week,
            'vacancies' => $vacancies,
            'deadline' => $deadline,
            'status' => $status,
            'description' => $description
        ]);

        set_flash('success', "Job posting '{$title}' was updated successfully.");
        header('Location: dashboard.php');
        exit;
    }
}

$page_title = 'Edit ' . $job['title'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-light flex-grow-1">
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
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="stat-icon bg-primary text-white fs-3">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0">Edit Vacancy Requisition</h3>
                            <span class="text-muted small">Update details for <?= htmlspecialchars($job['title']) ?></span>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="edit-job.php?id=<?= $job['id'] ?>" method="POST">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-dark">Job Vacancy Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['title']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Category</label>
                                <select name="category" class="form-select">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($job['category'] === $cat['name']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Physical Campus Location</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Vacancy Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $job['status'] === 'active' ? 'selected' : '' ?>>Active / Open for Applications</option>
                                    <option value="closed" <?= $job['status'] === 'closed' ? 'selected' : '' ?>>Closed / Filled</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Stipend Rate</label>
                                <input type="text" name="pay_rate" class="form-control" value="<?= htmlspecialchars($job['pay_rate']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Weekly Hours</label>
                                <input type="text" name="hours_per_week" class="form-control" value="<?= htmlspecialchars($job['hours_per_week']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Total Slots</label>
                                <input type="number" name="vacancies" class="form-control" value="<?= $job['vacancies'] ?>" min="1" max="20" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($job['deadline']) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Detailed Job Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4" class="form-control" required><?= htmlspecialchars($job['description']) ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-academic py-2 px-4 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Save Changes
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
