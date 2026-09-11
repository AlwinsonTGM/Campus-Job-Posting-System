<?php
/**
 * Campus Job Posting System - Admin Category Management
 * Archetype B/C: Category Taxonomy Grid (COAL101 Blueprint)
 * Enhanced with Related Category Photography & Visual Profiles
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Job Family Category Taxonomy';

$error = null;

// Helper to resolve picture URL for both external Unsplash URLs and local uploads
function get_category_cover($cat) {
    $img = $cat['image'] ?? '';
    if (!empty($img)) {
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/')) {
            return $img;
        }
        return '../' . ltrim($img, '/');
    }

    // Smart curated fallbacks based on category title/slug keywords
    $haystack = strtolower(($cat['name'] ?? '') . ' ' . ($cat['slug'] ?? ''));
    if (str_contains($haystack, 'tech') || str_contains($haystack, 'it') || str_contains($haystack, 'computer')) {
        return '../assets/img/categories/cat-tech.jpg';
    }
    if (str_contains($haystack, 'lib') || str_contains($haystack, 'book')) {
        return '../assets/img/categories/cat-library.jpg';
    }
    if (str_contains($haystack, 'admin') || str_contains($haystack, 'clerk') || str_contains($haystack, 'office')) {
        return '../assets/img/categories/cat-admin.jpg';
    }
    if (str_contains($haystack, 'sci') || str_contains($haystack, 'lab') || str_contains($haystack, 'chem')) {
        return '../assets/img/categories/cat-lab.jpg';
    }
    if (str_contains($haystack, 'tutor') || str_contains($haystack, 'peer') || str_contains($haystack, 'mentor')) {
        return '../assets/img/categories/cat-tutor.jpg';
    }
    if (str_contains($haystack, 'sport') || str_contains($haystack, 'athletic') || str_contains($haystack, 'gym')) {
        return '../assets/img/categories/cat-sports.jpg';
    }
    if (str_contains($haystack, 'media') || str_contains($haystack, 'art') || str_contains($haystack, 'design')) {
        return '../assets/img/categories/cat-media.jpg';
    }
    if (str_contains($haystack, 'food') || str_contains($haystack, 'cafe') || str_contains($haystack, 'barista')) {
        return '../assets/img/categories/cat-cafe.jpg';
    }
    return '../assets/img/categories/cat-general.jpg';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'create';

        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon = $_POST['icon'] ?? 'bi-briefcase';
            if (!preg_match('/^bi-[a-z0-9-]+$/', $icon)) {
                $icon = 'bi-briefcase';
            }
            $color = $_POST['color'] ?? 'primary';
            if (!in_array($color, ['primary', 'accent', 'danger', 'info', 'warning', 'success', 'secondary'], true)) {
                $color = 'primary';
            }
            $badge_tag = trim($_POST['badge_tag'] ?? '');
            $badge_icon = trim($_POST['badge_icon'] ?? 'bi-star-fill');
            $hourly_range = trim($_POST['hourly_range'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $photo_file = $_FILES['category_photo'] ?? null;

            if (empty($name)) {
                $error = 'Category name cannot be empty.';
            } else {
                $cat_id = create_category([
                    'name'         => $name,
                    'description'  => $description,
                    'icon'         => $icon,
                    'color'        => $color,
                    'badge_tag'    => $badge_tag,
                    'badge_icon'   => $badge_icon,
                    'hourly_range' => $hourly_range,
                    'image'        => $image
                ], $photo_file);

                if ($cat_id > 0) {
                    set_flash('success', "New category '{$name}' with related visual profile was created successfully.");
                    header('Location: categories.php');
                    exit;
                } else {
                    $error = "Failed to create category '{$name}'. A category with this name or slug may already exist.";
                }
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon = $_POST['icon'] ?? 'bi-briefcase';
            if (!preg_match('/^bi-[a-z0-9-]+$/', $icon)) {
                $icon = 'bi-briefcase';
            }
            $color = $_POST['color'] ?? 'primary';
            if (!in_array($color, ['primary', 'accent', 'danger', 'info', 'warning', 'success', 'secondary'], true)) {
                $color = 'primary';
            }
            $badge_tag = trim($_POST['badge_tag'] ?? '');
            $badge_icon = trim($_POST['badge_icon'] ?? 'bi-star-fill');
            $hourly_range = trim($_POST['hourly_range'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $photo_file = $_FILES['category_photo'] ?? null;

            if ($id <= 0 || empty($name)) {
                $error = 'Valid category ID and name are required for updating.';
            } else {
                $ok = update_category($id, [
                    'name'         => $name,
                    'description'  => $description,
                    'icon'         => $icon,
                    'color'        => $color,
                    'badge_tag'    => $badge_tag,
                    'badge_icon'   => $badge_icon,
                    'hourly_range' => $hourly_range,
                    'image'        => $image
                ], $photo_file);

                if ($ok) {
                    set_flash('success', "Category '{$name}' and related picture updated successfully.");
                    header('Location: categories.php');
                    exit;
                } else {
                    $error = "Failed to update category '{$name}'.";
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                delete_category($id);
                set_flash('success', "Category #{$id} was deleted permanently.");
                header('Location: categories.php');
                exit;
            }
        }
    }
}

$categories = get_categories();
$all_jobs = get_jobs();

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.cat-hover-card {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.2s ease;
    border-radius: var(--radius-card, 16px);
}
.cat-hover-card:hover {
    transform: translateY(-4px);
    border-color: rgba(13, 59, 46, 0.35);
    box-shadow: 0 16px 36px rgba(13, 59, 46, 0.09) !important;
}
.cat-card-thumb {
    transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
}
.cat-hover-card:hover .cat-card-thumb {
    transform: scale(1.06);
}
.preset-pill-btn {
    cursor: pointer;
    transition: all 0.15s ease;
}
.preset-pill-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
</style>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">

                <?php if ($error): ?>
                    <div class="alert-paper alert-paper--danger mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                            <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Visual Hero Showcase Banner -->
                <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise border-line position-relative shadow-sm" style="background: linear-gradient(135deg, #0d3b2e 0%, #175343 55%, #1e6955 100%); color: #ffffff;">
                    <!-- Decorative Background Icon Accent -->
                    <div style="position: absolute; right: -30px; bottom: -30px; opacity: 0.08; pointer-events: none;">
                        <i class="bi bi-diagram-3-fill" style="font-size: 240px; line-height: 1;"></i>
                    </div>

                    <div class="p-4 p-md-5 position-relative">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); font-size: 11px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase;">
                                    <i class="bi bi-grid-3x3-gap-fill text-warning"></i> Institutional Taxonomy Catalog
                                </div>
                                <h1 class="display-6 fw-bold mb-2 text-white" style="letter-spacing: -0.02em;">Job Family Categories</h1>
                                <p class="mb-4 text-white-50 small" style="max-width: 580px; font-size: 14.5px; line-height: 1.6;">
                                    Organize student assistantship requisitions across university departments, academic laboratories, and accredited institutional partners with high-fidelity visual profiles and curated photography.
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn-pill" data-bs-toggle="modal" data-bs-target="#newCatModal" style="background: #ffffff; color: #0d3b2e; border: none; font-weight: 600;">
                                        <i class="bi bi-plus-circle-fill text-success"></i> Add Category with Picture
                                    </button>
                                    <a href="reports.php" class="btn-pill-outline" style="color: #ffffff; border-color: rgba(255, 255, 255, 0.4);">
                                        <i class="bi bi-bar-chart-fill"></i> Taxonomy Analytics
                                    </a>
                                    <a href="../student/jobs.php" target="_blank" class="btn-pill-outline" style="color: #ffffff; border-color: rgba(255, 255, 255, 0.4);">
                                        <i class="bi bi-box-arrow-up-right"></i> Student Portal View
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.15);">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-folder2-open text-warning fs-5"></i>
                                                <span class="small text-white-50 text-uppercase fw-semibold" style="font-size: 11px;">Active Families</span>
                                            </div>
                                            <div class="h3 fw-bold mb-0 text-white"><?= count($categories) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.15);">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-briefcase-fill text-info fs-5"></i>
                                                <span class="small text-white-50 text-uppercase fw-semibold" style="font-size: 11px;">Total Vacancies</span>
                                            </div>
                                            <div class="h3 fw-bold mb-0 text-white"><?= count($all_jobs) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.15);">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-image-fill text-success fs-5"></i>
                                                <span class="small text-white-50 text-uppercase fw-semibold" style="font-size: 11px;">Photo Profiles</span>
                                            </div>
                                            <div class="h3 fw-bold mb-0 text-white"><?= count(array_filter($categories, fn($c) => !empty($c['image']))) ?> / <?= count($categories) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.15);">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-cash-stack text-warning fs-5"></i>
                                                <span class="small text-white-50 text-uppercase fw-semibold" style="font-size: 11px;">Avg Hourly Pay</span>
                                            </div>
                                            <div class="h3 fw-bold mb-0 text-white">₱85.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Subheading -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h5 fw-bold text-ink mb-1">Configured Job Families &amp; Visual Profiles</h2>
                        <p class="text-muted-custom small mb-0">Each category is paired with a representative campus picture for student clarity.</p>
                    </div>
                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">
                        <i class="bi bi-check2-circle me-1"></i> <?= count($categories) ?> Active Classifications
                    </span>
                </div>

                <!-- Category Cards Grid -->
                <div class="row g-4 mb-5">
                    <?php if (empty($categories)): ?>
                        <div class="col-12">
                            <div class="card-paper p-5 text-center">
                                <i class="bi bi-tags text-muted-custom fs-1 d-block mb-3"></i>
                                <h4 class="fw-bold text-ink mb-1">No Categories Defined</h4>
                                <p class="text-muted-custom small mb-4">No job family categories have been configured in the taxonomy yet.</p>
                                <button type="button" class="btn-pill btn-pill-sm" data-bs-toggle="modal" data-bs-target="#newCatModal">
                                    <i class="bi bi-plus-circle-fill"></i> Add First Category with Picture
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): 
                            $cat_jobs = array_filter($all_jobs, fn($j) => ($j['category'] ?? '') === $cat['name']);
                            $count = count($cat_jobs);
                            $cover_image = get_category_cover($cat);
                        ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card-paper p-0 overflow-hidden h-100 d-flex flex-column border-line shadow-sm position-relative cat-hover-card reveal-fade-rise">
                                    
                                    <!-- Related Picture Container -->
                                    <div class="position-relative overflow-hidden" style="height: 180px; background-color: #f1f3f4;">
                                        <img src="<?= htmlspecialchars($cover_image) ?>" 
                                             alt="<?= htmlspecialchars($cat['name']) ?>" 
                                             class="w-100 h-100 cat-card-thumb" 
                                             loading="lazy"
                                             style="object-fit: cover; object-position: center;"
                                             onerror="this.src='../assets/img/categories/cat-general.jpg';">
                                        
                                        <!-- Gradient Overlay -->
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(17, 24, 39, 0.45) 0%, rgba(17, 24, 39, 0.05) 45%, rgba(17, 24, 39, 0.7) 100%);"></div>

                                        <!-- Top Badges Overlay -->
                                        <div class="position-absolute top-0 start-0 end-0 p-3 d-flex justify-content-between align-items-start">
                                            <?php if (!empty($cat['badge_tag'])): ?>
                                                <span class="badge rounded-pill shadow-sm border-0 d-inline-flex align-items-center gap-1 px-2 py-1 fw-bold" style="font-size: 11px; backdrop-filter: blur(8px); background: rgba(255, 255, 255, 0.94); color: #0d3b2e;">
                                                    <i class="bi <?= htmlspecialchars($cat['badge_icon'] ?? 'bi-star-fill') ?> text-accent"></i>
                                                    <?= htmlspecialchars($cat['badge_tag']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span></span>
                                            <?php endif; ?>

                                            <span class="badge rounded-pill shadow-sm border border-white-50 d-inline-flex align-items-center gap-1 px-2 py-1 fw-semibold text-white" style="font-size: 11px; backdrop-filter: blur(8px); background: rgba(17, 24, 39, 0.75);">
                                                <i class="bi bi-briefcase-fill text-white-50"></i>
                                                <?= $count ?> <?= $count === 1 ? 'Vacancy' : 'Vacancies' ?>
                                            </span>
                                        </div>

                                        <!-- Bottom Floating Icon and Hourly Rate -->
                                        <div class="position-absolute bottom-0 start-0 end-0 p-3 d-flex justify-content-between align-items-end">
                                            <div class="icon-circle icon-circle-success shadow-lg border border-2 border-white" style="width: 44px; height: 44px; font-size: 19px; transform: translateY(22px); z-index: 2; background-color: #ffffff;">
                                                <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-briefcase') ?> text-accent"></i>
                                            </div>
                                            <?php if (!empty($cat['hourly_range'])): ?>
                                                <span class="badge rounded-pill text-white border border-white-50 px-2 py-1" style="font-size: 11px; font-weight: 600; background: rgba(0, 0, 0, 0.72);">
                                                    <?= htmlspecialchars($cat['hourly_range']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Content Body -->
                                    <div class="p-4 pt-4 d-flex flex-column flex-grow-1" style="margin-top: 10px;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h3 class="card-paper-title fs-5 mb-0 fw-bold text-ink"><?= htmlspecialchars($cat['name']) ?></h3>
                                        </div>

                                        <p class="text-muted-custom small mb-3 flex-grow-1" style="line-height: 1.55; min-height: 44px;">
                                            <?= htmlspecialchars($cat['description'] ?? 'Campus assistantships and internships under this discipline.') ?>
                                        </p>

                                        <?php if (!empty($cat['popular_roles']) && is_array($cat['popular_roles'])): ?>
                                            <div class="mb-3 pt-2 border-top border-line">
                                                <span class="d-block text-muted-custom small fw-semibold mb-2" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px;">Representative Roles:</span>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach (array_slice($cat['popular_roles'], 0, 3) as $role_tag): ?>
                                                        <span class="badge bg-cream text-ink border border-line rounded-pill fw-normal" style="font-size: 11px; padding: 4px 8px;">
                                                            <?= htmlspecialchars($role_tag) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Footer Actions -->
                                        <div class="mt-auto pt-3 border-top border-line d-flex justify-content-between align-items-center gap-2">
                                            <div class="d-flex gap-1 align-items-center">
                                                <button type="button" class="btn-circle-icon edit-cat-btn" 
                                                    data-id="<?= htmlspecialchars($cat['id']) ?>"
                                                    data-name="<?= htmlspecialchars($cat['name']) ?>"
                                                    data-description="<?= htmlspecialchars($cat['description'] ?? '') ?>"
                                                    data-icon="<?= htmlspecialchars($cat['icon'] ?? 'bi-briefcase') ?>"
                                                    data-color="<?= htmlspecialchars($cat['color'] ?? 'primary') ?>"
                                                    data-badge-tag="<?= htmlspecialchars($cat['badge_tag'] ?? '') ?>"
                                                    data-badge-icon="<?= htmlspecialchars($cat['badge_icon'] ?? 'bi-star-fill') ?>"
                                                    data-hourly-range="<?= htmlspecialchars($cat['hourly_range'] ?? '') ?>"
                                                    data-image="<?= htmlspecialchars($cat['image'] ?? '') ?>"
                                                    style="width: 34px; height: 34px; font-size: 13px;"
                                                    title="Edit Category &amp; Picture">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                
                                                <?php if ($count === 0): ?>
                                                    <form method="POST" action="categories.php" class="d-inline" onsubmit="return confirm('Delete this category taxonomy permanently?');">
                                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars($cat['id']) ?>">
                                                        <button type="submit" class="btn-circle-icon text-danger" style="width: 34px; height: 34px; font-size: 13px;" title="Delete Category">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>

                                            <a href="../student/jobs.php?category=<?= urlencode($cat['name']) ?>" class="btn-pill-outline btn-pill-sm text-decoration-none">
                                                View Openings &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <!-- ============================================================
             Add Category Modal (with Related Picture Input)
             ============================================================ -->
        <div class="modal fade" id="newCatModal" tabindex="-1" aria-labelledby="newCatLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-tag-fill"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0" id="newCatLabel">Add Job Family Category</h5>
                                <span class="small text-muted-custom">Configure a new taxonomy with a related picture</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="categories.php" method="POST" enctype="multipart/form-data" class="form-paper" id="newCatForm">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="create">

                        <div class="modal-body p-4">
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label" for="cat-name">Category Title <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="cat-name" class="form-control" placeholder="e.g. Health & Clinical Services" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="cat-hourly">Hourly Rate Guidance</label>
                                    <input type="text" name="hourly_range" id="cat-hourly" class="form-control" placeholder="e.g. ₱85.00 / hr">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="cat-icon">Bootstrap Icon Class</label>
                                    <input type="text" name="icon" id="cat-icon" class="form-control" value="bi-briefcase" placeholder="bi-heart-pulse">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="cat-badge-tag">Badge Tag</label>
                                    <input type="text" name="badge_tag" id="cat-badge-tag" class="form-control" placeholder="e.g. Science & Health">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="cat-badge-icon">Badge Icon</label>
                                    <input type="text" name="badge_icon" id="cat-badge-icon" class="form-control" value="bi-star-fill" placeholder="bi-star-fill">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="cat-desc">Description</label>
                                <textarea name="description" id="cat-desc" rows="2" class="form-control" placeholder="Brief summary of assistantship duties under this taxonomy..."></textarea>
                            </div>

                            <!-- Related Picture Input Block -->
                            <div class="p-3 rounded-3 bg-cream border border-line mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 text-ink" for="new-cat-image-url">
                                        <i class="bi bi-image text-accent me-1"></i> Related Picture
                                    </label>
                                    <span class="small text-muted-custom">Web URL or File Upload</span>
                                </div>

                                <!-- Image URL Input -->
                                <div class="mb-2">
                                    <input type="text" name="image" id="new-cat-image-url" class="form-control" placeholder="assets/img/categories/cat-tech.jpg or image URL">
                                </div>

                                <!-- One-Click Picture Presets -->
                                <div class="mb-3">
                                    <span class="small text-muted-custom d-block mb-1" style="font-size: 11px; font-weight: 600;">One-Click Campus Presets:</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-tech.jpg" 
                                            data-icon="bi-laptop"><i class="bi bi-laptop me-1"></i>Tech / IT</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-library.jpg" 
                                            data-icon="bi-book"><i class="bi bi-book me-1"></i>Library</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-admin.jpg" 
                                            data-icon="bi-folder2-open"><i class="bi bi-folder2-open me-1"></i>Office / Admin</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-lab.jpg" 
                                            data-icon="bi-radioactive"><i class="bi bi-radioactive me-1"></i>Science Lab</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-tutor.jpg" 
                                            data-icon="bi-mortarboard"><i class="bi bi-mortarboard me-1"></i>Peer Tutor</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-sports.jpg" 
                                            data-icon="bi-trophy"><i class="bi bi-trophy me-1"></i>Athletics</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-media.jpg" 
                                            data-icon="bi-camera-reels"><i class="bi bi-camera-reels me-1"></i>Media / Arts</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="new" 
                                            data-url="assets/img/categories/cat-cafe.jpg" 
                                            data-icon="bi-cup-hot"><i class="bi bi-cup-hot me-1"></i>Cafe &amp; Dining</button>
                                    </div>
                                </div>

                                <!-- File Upload Option -->
                                <div class="mb-3">
                                    <label class="form-label small text-muted-custom mb-1" for="new-cat-photo-file">Or Upload Image File from Computer:</label>
                                    <input type="file" name="category_photo" id="new-cat-photo-file" class="form-control form-control-sm" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                                </div>

                                <!-- Live Picture Preview Container -->
                                <div class="text-center p-2 rounded-3 border border-line bg-white position-relative" style="height: 150px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <img id="new-cat-preview" src="" alt="Picture Preview" class="w-100 h-100 rounded-2 d-none" style="object-fit: cover;">
                                    <div id="new-cat-placeholder" class="text-muted-custom small">
                                        <i class="bi bi-card-image display-6 d-block mb-1 text-muted-custom"></i>
                                        <span>No picture selected. Paste a URL or pick a preset above to preview.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-cream border-top border-line py-3 px-4">
                            <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="btn-create-cat" class="btn-pill btn-pill-sm">Create Category with Picture</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================
             Edit Category Modal (with Picture & Taxonomy Controls)
             ============================================================ -->
        <div class="modal fade" id="editCatModal" tabindex="-1" aria-labelledby="editCatLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-line shadow-lg">
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-pencil-fill"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0" id="editCatLabel">Edit Category &amp; Picture</h5>
                                <span class="small text-muted-custom">Update taxonomy details, hourly rate, and representative image</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="categories.php" method="POST" enctype="multipart/form-data" class="form-paper" id="editCatForm">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit-cat-id" value="">

                        <div class="modal-body p-4">
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label" for="edit-cat-name">Category Title <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="edit-cat-name" class="form-control" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="edit-cat-hourly">Hourly Rate Guidance</label>
                                    <input type="text" name="hourly_range" id="edit-cat-hourly" class="form-control">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="edit-cat-icon">Bootstrap Icon Class</label>
                                    <input type="text" name="icon" id="edit-cat-icon" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="edit-cat-badge-tag">Badge Tag</label>
                                    <input type="text" name="badge_tag" id="edit-cat-badge-tag" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="edit-cat-badge-icon">Badge Icon</label>
                                    <input type="text" name="badge_icon" id="edit-cat-badge-icon" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="edit-cat-desc">Description</label>
                                <textarea name="description" id="edit-cat-desc" rows="2" class="form-control"></textarea>
                            </div>

                            <!-- Related Picture Input Block -->
                            <div class="p-3 rounded-3 bg-cream border border-line mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 text-ink" for="edit-cat-image-url">
                                        <i class="bi bi-image text-accent me-1"></i> Category Picture
                                    </label>
                                    <span class="small text-muted-custom">Web URL or File Upload</span>
                                </div>

                                <!-- Image URL Input -->
                                <div class="mb-2">
                                    <input type="text" name="image" id="edit-cat-image-url" class="form-control" placeholder="assets/img/categories/cat-tech.jpg or image URL">
                                </div>

                                <!-- One-Click Picture Presets -->
                                <div class="mb-3">
                                    <span class="small text-muted-custom d-block mb-1" style="font-size: 11px; font-weight: 600;">One-Click Campus Presets:</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-tech.jpg" 
                                            data-icon="bi-laptop"><i class="bi bi-laptop me-1"></i>Tech / IT</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-library.jpg" 
                                            data-icon="bi-book"><i class="bi bi-book me-1"></i>Library</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-admin.jpg" 
                                            data-icon="bi-folder2-open"><i class="bi bi-folder2-open me-1"></i>Office / Admin</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-lab.jpg" 
                                            data-icon="bi-radioactive"><i class="bi bi-radioactive me-1"></i>Science Lab</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-tutor.jpg" 
                                            data-icon="bi-mortarboard"><i class="bi bi-mortarboard me-1"></i>Peer Tutor</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-sports.jpg" 
                                            data-icon="bi-trophy"><i class="bi bi-trophy me-1"></i>Athletics</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-media.jpg" 
                                            data-icon="bi-camera-reels"><i class="bi bi-camera-reels me-1"></i>Media / Arts</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 preset-pill-btn" style="font-size: 11px;" 
                                            data-target="edit" 
                                            data-url="assets/img/categories/cat-cafe.jpg" 
                                            data-icon="bi-cup-hot"><i class="bi bi-cup-hot me-1"></i>Cafe &amp; Dining</button>
                                    </div>
                                </div>

                                <!-- File Upload Option -->
                                <div class="mb-3">
                                    <label class="form-label small text-muted-custom mb-1" for="edit-cat-photo-file">Or Replace with Uploaded Image File:</label>
                                    <input type="file" name="category_photo" id="edit-cat-photo-file" class="form-control form-control-sm" accept="image/png, image/jpeg, image/webp, image/svg+xml">
                                </div>

                                <!-- Live Picture Preview Container -->
                                <div class="text-center p-2 rounded-3 border border-line bg-white position-relative" style="height: 150px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <img id="edit-cat-preview" src="" alt="Picture Preview" class="w-100 h-100 rounded-2 d-none" style="object-fit: cover;">
                                    <div id="edit-cat-placeholder" class="text-muted-custom small">
                                        <i class="bi bi-card-image display-6 d-block mb-1 text-muted-custom"></i>
                                        <span>No picture selected. Paste a URL or pick a preset above.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-cream border-top border-line py-3 px-4">
                            <button type="button" class="btn-pill-outline btn-pill-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="btn-update-cat" class="btn-pill btn-pill-sm">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper to update image preview box
    function updatePreview(url, previewImgId, placeholderId) {
        const preview = document.getElementById(previewImgId);
        const placeholder = document.getElementById(placeholderId);
        if (!preview || !placeholder) return;

        if (url && url.trim() !== '') {
            preview.src = url.trim();
            preview.classList.remove('d-none');
            placeholder.classList.add('d-none');
        } else {
            preview.src = '';
            preview.classList.add('d-none');
            placeholder.classList.remove('d-none');
        }
    }

    // New Category Modal Picture Preview Listener
    const newImageUrlInput = document.getElementById('new-cat-image-url');
    const newFileInput = document.getElementById('new-cat-photo-file');
    if (newImageUrlInput) {
        newImageUrlInput.addEventListener('input', function() {
            updatePreview(this.value, 'new-cat-preview', 'new-cat-placeholder');
        });
    }
    if (newFileInput) {
        newFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    updatePreview(evt.target.result, 'new-cat-preview', 'new-cat-placeholder');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Edit Category Modal Picture Preview Listener
    const editImageUrlInput = document.getElementById('edit-cat-image-url');
    const editFileInput = document.getElementById('edit-cat-photo-file');
    if (editImageUrlInput) {
        editImageUrlInput.addEventListener('input', function() {
            updatePreview(this.value, 'edit-cat-preview', 'edit-cat-placeholder');
        });
    }
    if (editFileInput) {
        editFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    updatePreview(evt.target.result, 'edit-cat-preview', 'edit-cat-placeholder');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Preset Buttons Click Handler
    document.querySelectorAll('.preset-pill-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const url = this.getAttribute('data-url');
            const icon = this.getAttribute('data-icon');

            if (target === 'new') {
                if (newImageUrlInput) {
                    newImageUrlInput.value = url;
                    updatePreview(url, 'new-cat-preview', 'new-cat-placeholder');
                }
                const newIcon = document.getElementById('cat-icon');
                if (newIcon && icon) newIcon.value = icon;
            } else if (target === 'edit') {
                if (editImageUrlInput) {
                    editImageUrlInput.value = url;
                    updatePreview(url, 'edit-cat-preview', 'edit-cat-placeholder');
                }
                const editIcon = document.getElementById('edit-cat-icon');
                if (editIcon && icon) editIcon.value = icon;
            }
        });
    });

    // Populate Edit Category Modal
    const editModalEl = document.getElementById('editCatModal');
    const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;

    document.querySelectorAll('.edit-cat-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name') || '';
            const description = this.getAttribute('data-description') || '';
            const icon = this.getAttribute('data-icon') || 'bi-briefcase';
            const badgeTag = this.getAttribute('data-badge-tag') || '';
            const badgeIcon = this.getAttribute('data-badge-icon') || 'bi-star-fill';
            const hourlyRange = this.getAttribute('data-hourly-range') || '';
            const image = this.getAttribute('data-image') || '';

            document.getElementById('edit-cat-id').value = id;
            document.getElementById('edit-cat-name').value = name;
            document.getElementById('edit-cat-desc').value = description;
            document.getElementById('edit-cat-icon').value = icon;
            document.getElementById('edit-cat-badge-tag').value = badgeTag;
            document.getElementById('edit-cat-badge-icon').value = badgeIcon;
            document.getElementById('edit-cat-hourly').value = hourlyRange;
            document.getElementById('edit-cat-image-url').value = image;

            // Resolve preview
            let previewSrc = image;
            if (previewSrc && !previewSrc.startsWith('http') && !previewSrc.startsWith('/')) {
                previewSrc = '../' + previewSrc;
            }
            updatePreview(previewSrc, 'edit-cat-preview', 'edit-cat-placeholder');

            if (editModal) {
                editModal.show();
            }
        });
    });

    // Submit state for buttons
    const newForm = document.getElementById('newCatForm');
    if (newForm) {
        newForm.addEventListener('submit', function() {
            const btn = document.getElementById('btn-create-cat');
            if (btn) {
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
                }, 10);
            }
        });
    }

    const editForm = document.getElementById('editCatForm');
    if (editForm) {
        editForm.addEventListener('submit', function() {
            const btn = document.getElementById('btn-update-cat');
            if (btn) {
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating...';
                }, 10);
            }
        });
    }
});
</script>
