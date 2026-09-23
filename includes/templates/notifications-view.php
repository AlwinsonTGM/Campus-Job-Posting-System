<?php
/**
 * View template for notifications.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../navbar.php'; ?>

        <main class="py-4 py-lg-5">
            <div class="container-paper">
                
                <!-- Page Title -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom border-line">
                    <div>
                        <h1 class="h2 fw-extrabold text-ink mb-1 tracking-tight">Notification Center</h1>
                        <p class="text-muted-custom small mb-0">Track verification progress, application evaluations, and campus career updates.</p>
                    </div>

                    <!-- Header Action Buttons -->
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($unread_count > 0): ?>
                            <form method="POST" action="notifications.php" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="mark_all_read">
                                <button type="submit" class="btn btn-paper-secondary btn-sm d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-check2-all text-accent"></i>
                                    <span>Mark All as Read</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabs Filter Row -->
                <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom border-line align-items-center justify-content-between">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a href="notifications.php?filter=all" class="chip chip-selectable text-decoration-none <?= ($filter !== 'unread') ? 'active' : '' ?>">
                            All
                            <span class="badge rounded-pill <?= ($filter !== 'unread') ? 'bg-white text-dark' : 'bg-cream text-ink border border-line' ?> px-2 ms-1" style="font-size: 11px;">
                                <?= $total_count ?>
                            </span>
                        </a>
                        <a href="notifications.php?filter=unread" class="chip chip-selectable text-decoration-none <?= ($filter === 'unread') ? 'active' : '' ?>">
                            <i class="bi bi-envelope-open me-1 <?= ($filter === 'unread') ? 'text-accent' : 'text-muted-custom' ?>"></i>
                            Unread
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger text-white rounded-pill px-2 ms-1" style="font-size: 11px;"><?= $unread_count ?></span>
                            <?php else: ?>
                                <span class="badge rounded-pill <?= ($filter === 'unread') ? 'bg-white text-dark' : 'bg-cream text-ink border border-line' ?> px-2 ms-1" style="font-size: 11px;">0</span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="text-muted-custom small">
                        Showing <?= count($display_notifications) ?> of <?= $total_count ?> total notifications
                    </div>
                </div>

                <!-- Notifications List -->
                <?php if (empty($display_notifications)): ?>
                    <div class="paper-card text-center py-5 px-4 my-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-cream rounded-circle p-4 mb-3 border border-line" style="width: 80px; height: 80px;">
                            <i class="bi <?= $unread_only ? 'bi-check2-circle text-accent' : 'bi-bell-slash text-muted' ?> fs-1"></i>
                        </div>
                        <h4 class="fw-extrabold text-ink mb-2">
                            <?= $unread_only ? 'You are all caught up!' : 'No notifications yet' ?>
                        </h4>
                        <p class="text-muted-custom small mx-auto mb-4" style="max-width: 440px;">
                            <?= $unread_only 
                                ? 'All updates, verification notices, and candidate alerts have been marked as read.' 
                                : 'When supervisors evaluate your applications or administrators review verification documents, notifications will appear here.' ?>
                        </p>
                        <?php if ($unread_only && $total_count > 0): ?>
                            <a href="notifications.php?filter=all" class="btn btn-paper-secondary btn-sm py-2 px-3">
                                <i class="bi bi-collection me-1"></i> View All Past Notifications
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3 mb-5">
                        <?php foreach ($display_notifications as $notif): 
                            $is_unread = empty($notif['is_read']);
                            $badge_color = $notif['badge_color'] ?? 'primary';
                            $icon_class = $notif['icon'] ?? 'bi-bell';
                            $target_link = !empty($notif['link']) ? ('api/notifications.php?action=click&id=' . $notif['id']) : null;
                        ?>
                            <div class="paper-card p-3 p-md-4 position-relative transition-all <?= $is_unread ? 'bg-cream-tint' : '' ?>">
                                <div class="d-flex align-items-start gap-3">
                                    <!-- Leading Status Icon -->
                                    <div class="notif-icon-circle bg-<?= htmlspecialchars($badge_color) ?>-subtle text-<?= htmlspecialchars($badge_color) ?> flex-shrink-0 mt-1" style="width: 44px; height: 44px; font-size: 1.25rem;">
                                        <i class="bi <?= htmlspecialchars($icon_class) ?>"></i>
                                    </div>

                                    <!-- Content Column -->
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-1 mb-2">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h5 class="h6 fw-bold text-ink mb-0"><?= htmlspecialchars($notif['title']) ?></h5>
                                                <?php if ($is_unread): ?>
                                                    <span class="badge bg-accent text-dark border border-line fw-bold" style="font-size: 10px;">NEW</span>
                                                <?php endif; ?>
                                                <span class="badge bg-cream text-muted-custom border border-line text-uppercase" style="font-size: 10px;">
                                                    <?= htmlspecialchars(str_replace('_', ' ', $notif['type'] ?? 'system')) ?>
                                                </span>
                                            </div>
                                            <div class="text-muted-custom small" title="<?= htmlspecialchars(format_display_date($notif['created_at'], true)) ?>">
                                                <i class="bi bi-clock me-1"></i><?= htmlspecialchars(time_ago_short($notif['created_at'])) ?>
                                            </div>
                                        </div>

                                        <p class="text-ink small mb-3 leading-relaxed">
                                            <?= htmlspecialchars($notif['message']) ?>
                                        </p>

                                        <!-- Footer Actions -->
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top border-line">
                                            <div>
                                                <?php if ($target_link): ?>
                                                    <a href="<?= $target_link ?>" class="btn btn-sm btn-paper-primary py-1 px-3 d-inline-flex align-items-center gap-1" style="height: 38px !important; font-size: 13px !important;">
                                                        <span>View Details</span>
                                                        <i class="bi bi-arrow-right ms-1"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <?php if ($is_unread): ?>
                                                    <form method="POST" action="notifications.php<?= $unread_only ? '?filter=unread' : '' ?>" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                        <input type="hidden" name="action" value="mark_read">
                                                        <input type="hidden" name="id" value="<?= (int)$notif['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-link text-muted-custom text-decoration-none py-0 px-2" title="Mark as read" style="font-size: 12px;">
                                                            <i class="bi bi-check2 me-1"></i>Mark Read
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="POST" action="notifications.php<?= $unread_only ? '?filter=unread' : '' ?>" class="d-inline" onsubmit="return confirm('Delete this notification?');">
                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= (int)$notif['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none py-0 px-2 opacity-75 hover-opacity-100" title="Delete notification" style="font-size: 12px;">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

