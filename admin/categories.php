<?php
/**
 * Campus Job Posting System - Admin Category Management
 */
require_once __DIR__ . '/../includes/data-helper.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Category Management';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = $_POST['icon'] ?? 'bi-briefcase';
    $color = $_POST['color'] ?? 'primary';

    if (empty($name)) {
        $error = 'Category name cannot be empty.';
    } else {
        create_category([
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'color' => $color
        ]);
        set_flash('success', "New category '{$name}' created successfully.");
        header('Location: categories.php');
        exit;
    }
}

$categories = get_categories();
$all_jobs = get_jobs();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4 bg-surface flex-grow-1">
    <div class="container">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="reports.php">Admin Control</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categories</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-ink mb-0">Job Categories Management</h2>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn-accent-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#newCatModal">
                    <i class="bi bi-plus-circle me-1"></i> Add New Category
                </button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-4 rounded-3">
                <i class="bi bi-exclamation-circle-fill fs-5"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($categories as $cat): 
                $cat_jobs = array_filter($all_jobs, fn($j) => $j['category'] === $cat['name']);
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-line shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stat-icon bg-accent-soft text-ink">
                                <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                            </div>
                            <span class="chip-tag" style="font-size: 11px;">
                                <?= count($cat_jobs) ?> Vacancies
                            </span>
                        </div>

                        <h5 class="fw-bold text-ink mb-2"><?= htmlspecialchars($cat['name']) ?></h5>
                        <p class="text-muted-custom small mb-3">
                            <?= htmlspecialchars($cat['description']) ?>
                        </p>

                        <div class="mt-auto border-top border-line pt-3 d-flex justify-content-between align-items-center">
                            <span class="pill-badge" style="font-size: 11px;">Active Status</span>
                            <a href="../student/jobs.php?cat=<?= urlencode($cat['name']) ?>" class="btn-outline-pill py-1 px-3" style="font-size: 12px;">
                                View Openings <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add Category Modal -->
        <div class="modal fade" id="newCatModal" tabindex="-1" aria-labelledby="newCatLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    <div class="modal-header bg-kld-gradient text-white">
                        <h5 class="modal-title fw-bold" id="newCatLabel">
                            <i class="bi bi-tag-fill me-1 text-accent"></i> Add New Job Category
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="categories.php" method="POST">
                        <div class="modal-body p-4 bg-surface">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-ink">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Health & Wellness Services" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-ink">Bootstrap Icon Class</label>
                                    <input type="text" name="icon" class="form-control" value="bi-briefcase" placeholder="bi-heart-pulse">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-ink">Accent Color</label>
                                    <select name="color" class="form-select">
                                        <option value="primary">Primary (Blue)</option>
                                        <option value="success">Success (Green)</option>
                                        <option value="warning">Warning (Gold)</option>
                                        <option value="info">Info (Cyan)</option>
                                        <option value="danger">Danger (Red)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-ink">Description</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Brief summary of duties under this job family..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-cream border-top border-line">
                            <button type="submit" class="btn-accent-pill py-2 px-4">Create Category</button>
                            <button type="button" class="btn-soft-pill py-2 px-3" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
