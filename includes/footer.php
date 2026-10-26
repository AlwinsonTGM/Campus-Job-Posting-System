<?php
/** Shared footer content and page scripts. */
require __DIR__ . '/templates/includes-footer-view.php';
?>

<!-- Shared Floating Spotlight Search Modal -->
<?php require_once __DIR__ . '/search-modal.php'; ?>

<!-- Back-to-top floating button (all pages) -->
<button type="button" id="back-to-top" class="back-to-top" aria-label="Back to top" title="Back to top">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Bootstrap 5 Bundle JS (Local Offline Vendor) -->
<script src="<?= $base_url ?>assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>

<!-- Theme Toggle Controller (dark/light mode + localStorage persistence) -->
<script src="<?= $base_url ?>assets/js/theme-toggle.js?v=<?= time() ?>"></script>

<!-- Global Main JS (Paper Sheet 3D Tilt & Interaction Engine) -->
<script src="<?= $base_url ?>assets/js/main.js?v=<?= time() ?>"></script>

<?php if (function_exists('get_logged_user') && get_logged_user()): ?>
<!-- Real-time Notifications Client -->
<script src="<?= $base_url ?>assets/js/notifications.js?v=<?= time() ?>"></script>
<?php endif; ?>

<?php if (isset($extra_js) && is_array($extra_js)): ?>
    <?php foreach ($extra_js as $script): ?>
        <script src="<?= $base_url ?><?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
