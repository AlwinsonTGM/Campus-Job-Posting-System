<?php
/** Employer announcements; controller retains publishing, editing, and archive handling. */
require_once __DIR__ . '/../header.php';
?>
<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../navbar.php'; ?>
        <main class="py-5 employer-workspace">
            <div class="container-paper">
                <?php
                $head_actions = '<button type="button" class="employer-button employer-button--primary" data-bs-toggle="modal" data-bs-target="#newEmployerDispatchModal"><i class="bi bi-plus-lg" aria-hidden="true"></i> Post Announcement</button><a href="../updates.php" class="employer-button" target="_blank" rel="noopener"><i class="bi bi-eye" aria-hidden="true"></i> View Public Hub</a>';
                render_page_head('', 'Department announcements', $org_name . ' • Publish hiring notices and office bulletins.', $head_actions);
                ?>
                <?php if ($error): ?>
                    <div class="alert-paper alert-paper--danger mb-4" role="alert"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <section class="employer-panel" aria-labelledby="bulletins-title">
                    <div class="employer-panel-heading"><div><h2 id="bulletins-title">Campus bulletins (<?= count($dept_updates) ?>)</h2><p>Announcements published by your department.</p></div></div>
                    <?php if (empty($dept_updates)): ?>
                        <div class="employer-empty">
                            <i class="bi bi-megaphone" aria-hidden="true"></i>
                            <h3>Your first announcement starts here</h3>
                            <p>Share a hiring notice or an office update with the campus community.</p>
                            <button type="button" class="employer-text-button" data-bs-toggle="modal" data-bs-target="#newEmployerDispatchModal">Create an announcement <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    <?php else: ?>
                        <div class="employer-table-scroll">
                            <table class="employer-table">
                                <caption class="visually-hidden">Published department announcements</caption>
                                <thead><tr><th scope="col">Announcement</th><th scope="col">Title &amp; summary</th><th scope="col">Author / office</th><th scope="col">Published</th><th scope="col" class="employer-actions-cell">Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($dept_updates as $item):
                                        $upd_img = $item['image'] ?? 'assets/img/hero-office.jpg';
                                        $upd_src = (str_starts_with($upd_img, 'http://') || str_starts_with($upd_img, 'https://') || str_starts_with($upd_img, '/')) ? $upd_img : '../' . ltrim($upd_img, '/');
                                    ?>
                                        <tr>
                                            <td data-label="Announcement"><img src="<?= htmlspecialchars($upd_src) ?>" class="employer-bulletin-image" alt="" onerror="this.src='../assets/img/hero-office.jpg';this.onerror=null;"></td>
                                            <td data-label="Title &amp; summary"><a href="../update-detail.php?id=<?= urlencode($item['id']) ?>" target="_blank" rel="noopener" class="text-ink text-decoration-none"><strong><?= htmlspecialchars($item['title']) ?></strong></a><small class="employer-bulletin-summary"><?= htmlspecialchars($item['summary']) ?></small></td>
                                            <td data-label="Author / office"><strong><?= htmlspecialchars($item['author']['name'] ?? $user['name']) ?></strong><small><?= htmlspecialchars($item['author']['office'] ?? $org_name) ?></small></td>
                                            <td data-label="Published" class="employer-date"><?= format_display_date($item['published_at'] ?? 'now', true) ?></td>
                                            <td data-label="Actions" class="employer-actions-cell">
                                                <div class="employer-actions employer-actions--bulletins">
                                                    <a href="../update-detail.php?id=<?= urlencode($item['id']) ?>" target="_blank" rel="noopener" class="employer-button employer-button--icon" title="View public post" aria-label="View <?= htmlspecialchars($item['title'], ENT_QUOTES) ?> (opens in new tab)"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                                    <button type="button" class="employer-button employer-button--icon edit-emp-dispatch-btn"
                                                        data-id="<?= htmlspecialchars($item['id']) ?>"
                                                        data-title="<?= htmlspecialchars($item['title']) ?>"
                                                        data-summary="<?= htmlspecialchars($item['summary']) ?>"
                                                        data-content="<?= htmlspecialchars($item['content']) ?>"
                                                        data-image="<?= htmlspecialchars($item['image'] ?? '') ?>"
                                                        data-author-name="<?= htmlspecialchars($item['author']['name'] ?? $user['name']) ?>"
                                                        data-author-role="<?= htmlspecialchars($item['author']['role'] ?? 'Department Supervisor') ?>"
                                                        data-author-office="<?= htmlspecialchars($item['author']['office'] ?? $org_name) ?>"
                                                        title="Edit announcement" aria-label="Edit <?= htmlspecialchars($item['title'], ENT_QUOTES) ?>"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                                    <form method="POST" action="updates.php" onsubmit="return confirm('Archive this announcement? Campus records will be preserved.');">
                                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                        <input type="hidden" name="action" value="archive">
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                                                        <button type="submit" class="employer-button employer-button--icon" title="Archive announcement" aria-label="Archive <?= htmlspecialchars($item['title'], ENT_QUOTES) ?>"><i class="bi bi-archive" aria-hidden="true"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

<!-- Modal: Post Department Dispatch -->
<div class="modal fade employer-workspace" id="newEmployerDispatchModal" tabindex="-1" aria-labelledby="newEmployerDispatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-line rounded-4 shadow-lg">
            <div class="modal-header border-line bg-surface p-4">
                <h5 class="modal-title fw-bold text-ink" id="newEmployerDispatchModalLabel">
                    <i class="bi bi-megaphone me-2"></i> Post Department Announcement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="updates.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="create">
                <div class="modal-body p-4">
                    <?php if ($error && $action === 'create'): ?>
                        <div class="alert-paper alert-paper--danger mb-3" role="alert"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Headline Title *</label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. <?= htmlspecialchars($org_name) ?> Announces SA Openings" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Short Summary / Excerpt *</label>
                        <textarea name="summary" class="form-control rounded-3" rows="2" placeholder="Brief preview for the campus updates feed..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Full Article Content (HTML allowed) *</label>
                        <textarea name="content" class="form-control rounded-3" rows="6" placeholder="<p>Full announcement details, shift qualifications, and application instructions...</p>" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink" for="dispatch-photo">Upload Featured Photo</label>
                        <input type="file" name="photo" id="dispatch-photo" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp" aria-describedby="dispatch-photo-help">
                        <div id="dispatch-photo-help" class="form-text text-muted-custom">JPG, PNG, or WebP, up to 5 MB. An uploaded photo takes priority over the URL below.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink" for="dispatch-image">Featured Photo URL (optional)</label>
                        <input type="text" name="image" id="dispatch-image" class="form-control rounded-3" placeholder="assets/img/updates/... or photo URL">
                        <div class="form-text text-muted-custom">Leave both blank for the default campus photo.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Name</label>
                            <input type="text" name="author_name" class="form-control rounded-3" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Role</label>
                            <input type="text" name="author_role" class="form-control rounded-3" value="Department Supervisor" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Department / Office</label>
                            <input type="text" name="author_office" class="form-control rounded-3" value="<?= htmlspecialchars($org_name) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-cream border-top border-line py-3 px-4">
                    <button type="button" class="employer-button employer-button--compact" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="employer-button employer-button--primary employer-button--compact">
                        <i class="bi bi-send"></i> Publish Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Department Dispatch -->
<div class="modal fade employer-workspace" id="editEmployerDispatchModal" tabindex="-1" aria-labelledby="editEmployerDispatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-line rounded-4 shadow-lg">
            <div class="modal-header border-line bg-surface p-4">
                <h5 class="modal-title fw-bold text-ink" id="editEmployerDispatchModalLabel">
                    <i class="bi bi-pencil-square text-accent me-2"></i> Edit Department Dispatch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="updates.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_emp_id">
                <div class="modal-body p-4">
                    <?php if ($error && $action === 'edit'): ?>
                        <div class="alert-paper alert-paper--danger mb-3" role="alert"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Headline Title *</label>
                        <input type="text" name="title" id="edit_emp_title" class="form-control rounded-3" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Short Summary / Excerpt *</label>
                        <textarea name="summary" id="edit_emp_summary" class="form-control rounded-3" rows="2" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Full Article Content (HTML allowed) *</label>
                        <textarea name="content" id="edit_emp_content" class="form-control rounded-3" rows="6" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink" for="edit-dispatch-photo">Replace Featured Photo</label>
                        <input type="file" name="photo" id="edit-dispatch-photo" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp" aria-describedby="edit-dispatch-photo-help">
                        <div id="edit-dispatch-photo-help" class="form-text text-muted-custom">JPG, PNG, or WebP, up to 5 MB. Leave blank to keep the current photo.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink" for="edit_emp_image">Featured Photo URL (optional)</label>
                        <input type="text" name="image" id="edit_emp_image" class="form-control rounded-3">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Name</label>
                            <input type="text" name="author_name" id="edit_emp_author_name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Author Role</label>
                            <input type="text" name="author_role" id="edit_emp_author_role" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-ink">Department / Office</label>
                            <input type="text" name="author_office" id="edit_emp_author_office" class="form-control rounded-3" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-cream border-top border-line py-3 px-4">
                    <button type="button" class="employer-button employer-button--compact" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="employer-button employer-button--primary employer-button--compact">
                        <i class="bi bi-check2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editEmployerDispatchModal'));
    document.querySelectorAll('.edit-emp-dispatch-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelector('#editEmployerDispatchModal form').reset();
            document.getElementById('edit_emp_id').value = this.dataset.id;
            document.getElementById('edit_emp_title').value = this.dataset.title;
            document.getElementById('edit_emp_summary').value = this.dataset.summary;
            document.getElementById('edit_emp_content').value = this.dataset.content;
            document.getElementById('edit_emp_image').value = this.dataset.image;
            document.getElementById('edit_emp_author_name').value = this.dataset.authorName;
            document.getElementById('edit_emp_author_role').value = this.dataset.authorRole;
            document.getElementById('edit_emp_author_office').value = this.dataset.authorOffice;
            editModal.show();
        });
    });
    <?php if ($error && in_array($action ?? '', ['create', 'edit'], true)): ?>
    const failedModal = document.getElementById(<?= json_encode($action === 'edit' ? 'editEmployerDispatchModal' : 'newEmployerDispatchModal') ?>);
    const failedForm = failedModal.querySelector('form');
    const values = <?= json_encode($dispatch_form ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    Object.entries(values).forEach(([name, value]) => {
        const field = failedForm.elements.namedItem(name);
        if (field && field.type !== 'file' && name !== 'csrf_token') field.value = value;
    });
    bootstrap.Modal.getOrCreateInstance(failedModal).show();
    <?php endif; ?>
});
</script>

