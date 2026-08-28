<?php
/**
 * Campus Job Posting System - Admin Career Updates & Dispatches Management
 * Archetype B: Administration & Bulletin Management (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Career Center Dispatches Management';

$error = null;
$action = $_POST['action'] ?? null;

// Handle Add / Edit / Delete POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Campus News');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $author_name = trim($_POST['author_name'] ?? $user['name']);
        $author_role = trim($_POST['author_role'] ?? 'System Administrator');
        $author_office = trim($_POST['author_office'] ?? 'KLD Career Development & Placement Office');

        if (empty($title) || empty($content)) {
            $error = 'Headline title and article content are required.';
        } else {
            add_career_update([
                'title' => $title,
                'category' => $category,
                'summary' => $summary,
                'content' => $content,
                'image' => $image,
                'author_name' => $author_name,
                'author_role' => $author_role,
                'author_office' => $author_office
            ]);
            set_flash('success', "Career dispatch '{$title}' was published successfully.");
            header('Location: updates.php');
            exit;
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Campus News');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $author_name = trim($_POST['author_name'] ?? '');
        $author_role = trim($_POST['author_role'] ?? '');
        $author_office = trim($_POST['author_office'] ?? '');

        if ($id <= 0 || empty($title) || empty($content)) {
            $error = 'Valid update ID, title, and content are required for editing.';
        } else {
            update_career_update($id, [
                'title' => $title,
                'category' => $category,
                'summary' => $summary,
                'content' => $content,
                'image' => $image,
                'author_name' => $author_name,
                'author_role' => $author_role,
                'author_office' => $author_office
            ]);
            set_flash('success', "Dispatch #{$id} was updated successfully.");
            header('Location: updates.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_career_update($id);
            set_flash('success', "Dispatch #{$id} was deleted permanently.");
            header('Location: updates.php');
            exit;
        }
    }
}

$all_updates = get_career_updates();

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
                    <button type="button" class="btn-pill" data-bs-toggle="modal" data-bs-target="#newDispatchModal">
                        <i class="bi bi-plus-circle-fill"></i> Post New Dispatch
                    </button>
                    <a href="../updates.php" class="btn-pill-outline" target="_blank">
                        <i class="bi bi-eye-fill"></i> View Public Hub
                    </a>
                ';
                render_page_head(
                    '',
                    'Career Dispatches & Bulletins',
                    'Publish, edit, and moderate official campus hiring announcements, workshops, and career news.',
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

                <!-- Dispatches Table Card -->
                <div class="card-paper p-0 overflow-hidden mb-5 reveal-fade-rise">
                    <div class="p-4 border-bottom border-line d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-paper-title h6 mb-1">Published Dispatches (<?= count($all_updates) ?>)</h3>
                            <p class="text-muted-custom small mb-0">Live on the homepage and public Career Center feed.</p>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">
                            <i class="bi bi-broadcast text-accent me-1"></i> Live Stream Active
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="bg-surface border-bottom border-line text-muted-custom small">
                                <tr>
                                    <th class="ps-4" style="width: 80px;">Thumbnail</th>
                                    <th>Title &amp; Summary</th>
                                    <th>Author / Office</th>
                                    <th>Published</th>
                                    <th class="text-end pe-4" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all_updates)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted-custom">
                                            <i class="bi bi-newspaper display-6 d-block mb-2 text-muted-custom"></i>
                                            No dispatches published yet. Click "Post New Dispatch" to create one.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_updates as $item): 
                                        $pub_time = strtotime($item['published_at'] ?? 'now');
                                        $pub_formatted = date('M j, Y • g:i A', $pub_time);
                                    ?>
                                        <tr>
                                            <td class="ps-4">
                                                <img src="<?= htmlspecialchars($item['image'] ?? '../assets/img/hero-office.jpg') ?>" class="rounded-3 border border-line" style="width: 54px; height: 42px; object-fit: cover;" alt="">
                                            </td>
                                            <td>
                                                <a href="../update-detail.php?id=<?= urlencode($item['id']) ?>" target="_blank" class="fw-bold text-ink text-decoration-none d-block">
                                                    <?= htmlspecialchars($item['title']) ?>
                                                </a>
                                                <span class="text-muted-custom small line-clamp-1" style="max-width: 480px;">
                                                    <?= htmlspecialchars($item['summary']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block fw-semibold text-ink small"><?= htmlspecialchars($item['author']['name'] ?? 'Career Center') ?></span>
                                                <span class="text-muted-custom small" style="font-size: 0.75rem;"><?= htmlspecialchars($item['author']['office'] ?? 'University') ?></span>
                                            </td>
                                            <td class="text-muted-custom small">
                                                <?= $pub_formatted ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="../update-detail.php?id=<?= urlencode($item['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="View Public Post">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-circle edit-dispatch-btn" 
                                                        data-id="<?= htmlspecialchars($item['id']) ?>"
                                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                                        data-category="<?= htmlspecialchars($item['category'] ?? 'Campus News') ?>"
                                                        data-summary="<?= htmlspecialchars($item['summary']) ?>"
                                                        data-content="<?= htmlspecialchars($item['content']) ?>"
                                                        data-image="<?= htmlspecialchars($item['image'] ?? '') ?>"
                                                        data-author-name="<?= htmlspecialchars($item['author']['name'] ?? '') ?>"
                                                        data-author-role="<?= htmlspecialchars($item['author']['role'] ?? '') ?>"
                                                        data-author-office="<?= htmlspecialchars($item['author']['office'] ?? '') ?>"
                                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Edit Dispatch">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="updates.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this dispatch? This cannot be undone.');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Delete Dispatch">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<!-- Modal: Post New Dispatch -->
<div class="modal fade" id="newDispatchModal" tabindex="-1" aria-labelledby="newDispatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-line rounded-4 shadow-lg">
            <div class="modal-header border-line bg-surface p-4">
                <h5 class="modal-title fw-bold text-ink" id="newDispatchModalLabel">
                    <i class="bi bi-megaphone-fill text-accent me-2"></i> Post New Career Dispatch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="updates.php">
                <input type="hidden" name="action" value="create">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Headline Title *</label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Fall Term Assistantship Applications Open" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Short Summary / Excerpt *</label>
                        <textarea name="summary" class="form-control rounded-3" rows="2" placeholder="Brief 1-2 sentence preview for cards..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Full Article Content (HTML allowed) *</label>
                        <textarea name="content" class="form-control rounded-3" rows="6" placeholder="<p>Full announcement paragraphs and details...</p>" required></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">Featured Photo URL</label>
                            <input type="url" name="image" class="form-control rounded-3" placeholder="https://images.unsplash.com/...">
                            <span class="text-muted-custom small" style="font-size: 0.75rem;">Leave blank for default campus hero photo.</span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">Topic / Category</label>
                            <input type="text" name="category" class="form-control rounded-3" value="Campus Bulletin" placeholder="e.g. Hiring Policy, Workshop">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Name</label>
                            <input type="text" name="author_name" class="form-control rounded-3" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Role</label>
                            <input type="text" name="author_role" class="form-control rounded-3" value="Administrator" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Issuing Office</label>
                            <input type="text" name="author_office" class="form-control rounded-3" value="KLD Career Development Office" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-line bg-surface p-3">
                    <button type="button" class="btn-paper-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-paper-primary btn-sm">
                        <i class="bi bi-send-fill me-1"></i> Publish Dispatch Live
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Existing Dispatch -->
<div class="modal fade" id="editDispatchModal" tabindex="-1" aria-labelledby="editDispatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-line rounded-4 shadow-lg">
            <div class="modal-header border-line bg-surface p-4">
                <h5 class="modal-title fw-bold text-ink" id="editDispatchModalLabel">
                    <i class="bi bi-pencil-square text-accent me-2"></i> Edit Career Dispatch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="updates.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Headline Title *</label>
                        <input type="text" name="title" id="edit_title" class="form-control rounded-3" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Short Summary / Excerpt *</label>
                        <textarea name="summary" id="edit_summary" class="form-control rounded-3" rows="2" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Full Article Content (HTML allowed) *</label>
                        <textarea name="content" id="edit_content" class="form-control rounded-3" rows="6" required></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">Featured Photo URL</label>
                            <input type="url" name="image" id="edit_image" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">Topic / Category</label>
                            <input type="text" name="category" id="edit_category" class="form-control rounded-3">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Name</label>
                            <input type="text" name="author_name" id="edit_author_name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Role</label>
                            <input type="text" name="author_role" id="edit_author_role" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Issuing Office</label>
                            <input type="text" name="author_office" id="edit_author_office" class="form-control rounded-3" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-line bg-surface p-3">
                    <button type="button" class="btn-paper-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-paper-primary btn-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editDispatchModal'));
    document.querySelectorAll('.edit-dispatch-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_title').value = this.dataset.title;
            document.getElementById('edit_category').value = this.dataset.category || 'Campus Bulletin';
            document.getElementById('edit_summary').value = this.dataset.summary;
            document.getElementById('edit_content').value = this.dataset.content;
            document.getElementById('edit_image').value = this.dataset.image;
            document.getElementById('edit_author_name').value = this.dataset.authorName;
            document.getElementById('edit_author_role').value = this.dataset.authorRole;
            document.getElementById('edit_author_office').value = this.dataset.authorOffice;
            editModal.show();
        });
    });
});
</script>
