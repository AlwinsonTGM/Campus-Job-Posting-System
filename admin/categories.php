<?php
/**
 * Campus Job Posting System - Admin Category Management
 * Archetype B/C: Category Taxonomy Grid (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Job Family Category Taxonomy';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = $_POST['icon'] ?? 'bi-briefcase';
        $color = $_POST['color'] ?? 'primary';

        if (empty($name)) {
            $error = 'Category name cannot be empty.';
        } else {
            $cat_id = create_category([
                'name' => $name,
                'description' => $description,
                'icon' => $icon,
                'color' => $color
            ]);
            if ($cat_id > 0) {
                set_flash('success', "New category '{$name}' was created successfully.");
                header('Location: categories.php');
                exit;
            } else {
                $error = "Failed to create category '{$name}'. A category with this name or slug may already exist.";
            }
        }
    }
}

$categories = get_categories();
$all_jobs = get_jobs();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                $head_actions = '
                    <button type="button" class="btn-pill" data-bs-toggle="modal" data-bs-target="#newCatModal">
                        <i class="bi bi-plus-circle-fill"></i> Add Category
                    </button>
                    <a href="reports.php" class="btn-pill-outline">
                        <i class="bi bi-bar-chart-fill"></i> Analytics
                    </a>
                ';
                render_page_head(
                    '',
                    'Job Family Categories',
                    'Organize student assistantship requisitions by institutional domain and discipline family.',
                    $head_actions
                );
                ?>

                <?php if ($error): ?>
                    <div class="alert-paper alert-paper--danger mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                            <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Category Cards Grid -->
                <div class="row g-4 mb-5">
                    <?php foreach ($categories as $cat): 
                        $cat_jobs = array_filter($all_jobs, fn($j) => ($j['category'] ?? '') === $cat['name']);
                        $count = count($cat_jobs);
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card-paper p-4 h-100 d-flex flex-column reveal-fade-rise">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="icon-circle icon-circle-success">
                                        <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-briefcase') ?>"></i>
                                    </div>
                                    <span class="chip">
                                        <?= $count ?> <?= $count === 1 ? 'Vacancy' : 'Vacancies' ?>
                                    </span>
                                </div>

                                <h3 class="card-paper-title fs-5 mb-2"><?= htmlspecialchars($cat['name']) ?></h3>
                                <p class="text-muted-custom small mb-4 flex-grow-1">
                                    <?= htmlspecialchars($cat['description'] ?? 'Campus assistantships and internships under this discipline.') ?>
                                </p>

                                <div class="mt-auto pt-3 border-top border-line d-flex justify-content-between align-items-center">
                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle" style="font-size: 11px;">Active Category</span>
                                    <a href="../student/jobs.php?category=<?= urlencode($cat['name']) ?>" class="btn-pill-outline btn-pill-sm">
                                        View Openings &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </main>

        <!-- Add Category Modal -->
        <div class="modal fade" id="newCatModal" tabindex="-1" aria-labelledby="newCatLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-tag-fill"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0" id="newCatLabel">Add Job Family Category</h5>
                                <span class="small text-muted-custom">Define a new category taxonomy</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="categories.php" method="POST" class="form-paper">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label" for="cat-name">Category Title <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="cat-name" class="form-control" placeholder="e.g. Health & Clinical Services" required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label" for="cat-icon">Bootstrap Icon Class</label>
                                    <input type="text" name="icon" id="cat-icon" class="form-control" value="bi-briefcase" placeholder="bi-heart-pulse">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="cat-color">Color Theme</label>
                                    <select name="color" id="cat-color" class="form-select">
                                        <option value="primary">Emerald Accent (Primary)</option>
                                        <option value="success">Success Green</option>
                                        <option value="info">Review Blue</option>
                                        <option value="warning">Warning Yellow</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" for="cat-desc">Description</label>
                                <textarea name="description" id="cat-desc" rows="3" class="form-control" placeholder="Brief summary of assistantship duties under this taxonomy..."></textarea>
                            </div>
                        </div>

                        <div class="modal-footer bg-cream border-top border-line py-3 px-4">
                            <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-pill btn-pill-sm">Create Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
