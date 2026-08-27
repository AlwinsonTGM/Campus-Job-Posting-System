<?php
/**
 * Campus Job Posting System - Edit Job Requisition Form
 * Archetype C: Detail & Sidebar Action Form (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$job_id = $_GET['id'] ?? null;
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The specified job requisition could not be found.');
    header('Location: dashboard.php');
    exit;
}

if (!can_manage_job($job, $user)) {
    set_flash('danger', 'Unauthorized: You can only edit requisitions posted by your office.');
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
    $responsibilities = trim($_POST['responsibilities'] ?? '');
    $qualifications = trim($_POST['qualifications'] ?? '');

    if (empty($title) || empty($description)) {
        $error = 'Please provide the vacancy title and detailed description.';
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
            'description' => $description,
            'responsibilities' => !empty($responsibilities) ? array_filter(array_map('trim', explode("\n", $responsibilities))) : ($job['responsibilities'] ?? []),
            'qualifications' => !empty($qualifications) ? array_filter(array_map('trim', explode("\n", $qualifications))) : ($job['qualifications'] ?? [])
        ]);

        set_flash('success', "Vacancy '{$title}' has been updated successfully.");
        header('Location: dashboard.php');
        exit;
    }
}

$resp_str = is_array($job['responsibilities'] ?? null) ? implode("\n", $job['responsibilities']) : ($job['responsibilities'] ?? '');
$qual_str = is_array($job['qualifications'] ?? null) ? implode("\n", $job['qualifications']) : ($job['qualifications'] ?? '');

$page_title = 'Edit ' . $job['title'];
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
                        '<i class="bi bi-pencil-square text-accent me-1"></i> Edit Requisition',
                        'Edit Vacancy: ' . $job['title'],
                        'Update duties, requirements, quota, or application deadlines for this opening.'
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

                <form action="edit-job.php?id=<?= $job['id'] ?>" method="POST" class="form-paper">
                    <div class="row g-4 mb-5">
                        
                        <!-- Left 8-col: Role Overview & Responsibilities -->
                        <div class="col-lg-8">
                            <div class="card-paper p-4 p-md-4">
                                <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                    <i class="bi bi-card-text text-accent me-2"></i> 1. Vacancy Information
                                </h3>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label" for="edit-title">Vacancy Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="edit-title" class="form-control" value="<?= htmlspecialchars($job['title']) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="edit-cat">Job Family Category</label>
                                        <select name="category" id="edit-cat" class="form-select">
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
                                        <label class="form-label" for="edit-type">Opportunity Type</label>
                                        <select name="job_type" id="edit-type" class="form-select">
                                            <?php foreach ($job_types as $k => $label): ?>
                                                <option value="<?= htmlspecialchars($k) ?>" <?= (($job['job_type'] ?? '') === $k) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="edit-setup">Workplace Setup</label>
                                        <select name="work_setup" id="edit-setup" class="form-select">
                                            <?php foreach ($work_setups as $k => $label): ?>
                                                <option value="<?= htmlspecialchars($k) ?>" <?= (($job['work_setup'] ?? '') === $k) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-desc">Detailed Job Description <span class="text-danger">*</span></label>
                                    <textarea name="description" id="edit-desc" rows="4" class="form-control" required><?= htmlspecialchars($job['description']) ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-resp">Key Duties & Responsibilities (1 per line)</label>
                                    <textarea name="responsibilities" id="edit-resp" rows="3" class="form-control"><?= htmlspecialchars($resp_str) ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit-qual">Qualifications & Requirements (1 per line)</label>
                                    <textarea name="qualifications" id="edit-qual" rows="3" class="form-control"><?= htmlspecialchars($qual_str) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Right 4-col: Scheduling & Status Controls Sidebar -->
                        <div class="col-lg-4">
                            <div class="card-paper p-4 position-sticky" style="top: 90px;">
                                <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                    <i class="bi bi-sliders text-accent me-2"></i> 2. Terms & Controls
                                </h3>

                                <div class="mb-3">
                                    <label class="form-label" for="edit-status">Vacancy Status</label>
                                    <select name="status" id="edit-status" class="form-select">
                                        <option value="active" <?= ($job['status'] === 'active' || $job['status'] === 'Active') ? 'selected' : '' ?>>Active / Open for Applications</option>
                                        <option value="closed" <?= ($job['status'] === 'closed' || $job['status'] === 'Closed') ? 'selected' : '' ?>>Closed / Position Filled</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit-loc">Physical Location</label>
                                    <input type="text" name="location" id="edit-loc" class="form-control" value="<?= htmlspecialchars($job['location']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit-pay">Stipend Rate</label>
                                    <input type="text" name="pay_rate" id="edit-pay" class="form-control" value="<?= htmlspecialchars($job['pay_rate']) ?>" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="edit-vac">Open Slots</label>
                                        <input type="number" name="vacancies" id="edit-vac" class="form-control" value="<?= $job['vacancies'] ?>" min="1" max="20" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="edit-hours">Weekly Limit</label>
                                        <input type="text" name="hours_per_week" id="edit-hours" class="form-control" value="<?= htmlspecialchars($job['hours_per_week']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-deadline">Application Deadline</label>
                                    <input type="date" name="deadline" id="edit-deadline" class="form-control" value="<?= htmlspecialchars($job['deadline']) ?>" required>
                                </div>

                                <button type="submit" class="btn-pill w-100 mb-2">
                                    <i class="bi bi-check2-circle"></i> SAVE CHANGES
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
