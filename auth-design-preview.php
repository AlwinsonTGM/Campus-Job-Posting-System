<?php
/** Local, presentation-only demo. Never loads account controllers or changes session data. */
if (PHP_SAPI !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    exit;
}
require __DIR__ . '/includes/services/common-service.php';
$design_preview = true;
$partner = ($_GET['role'] ?? '') === 'partner';
$screen = in_array($_GET['screen'] ?? '', ['pending', 'rejected', 'revoked'], true) ? $_GET['screen'] : 'verify';
$page_title = 'Account flow — Design preview';
$existing_user = ['role' => $partner ? 'employer' : 'student'];
$pending_email = 'alex@example.com';
$smtp_ready = true;
$remaining_seconds = 0;
$error = null;
$user_role = $existing_user['role'];
$is_pending = $screen === 'pending';
$is_rejected = $screen === 'rejected';
$is_revoked = $screen === 'revoked';
$user_name = 'Alex Rivera';
$user_email = $pending_email;
$account = ['name' => $user_name, 'organization_name' => 'Sample organization'];
$existing_proof = $existing_permit = '';
$rejection_reason = 'Please upload a clearer copy of your registration document. Make sure all details are readable.';
$resubmit_error = null;
ob_start();
require __DIR__ . '/includes/templates/' . ($screen === 'verify' ? 'verify-email-view.php' : 'pending-approval-view.php');
$preview_html = ob_get_clean();
ob_start();
?>
<aside class="review-bar preview-toolbar" aria-label="Design preview controls">
    <div><strong>Design preview</strong><span class="review-description">Sample data · Any 6 digits work · No account changes</span></div>
    <div class="review-controls">
        <label class="sr-only" for="preview-role">Account type</label><select id="preview-role"><option value="student" <?= !$partner ? 'selected' : '' ?>>Student</option><option value="partner" <?= $partner ? 'selected' : '' ?>>Partner</option></select>
        <label class="sr-only" for="preview-screen">Preview screen</label><select id="preview-screen">
            <?php foreach (['verify' => 'Email verification', 'pending' => 'Pending approval', 'rejected' => 'Revisions requested', 'revoked' => 'Verification revoked'] as $key => $label): ?><option value="<?= $key ?>" <?= $key === $screen ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
        </select>
        <button class="text-button" type="button" id="preview-play"><i class="bi bi-play-fill" aria-hidden="true"></i> Play animation</button>
    </div>
</aside>
<script src="assets/js/auth-design-preview.js"></script>
<?php
$preview_toolbar = ob_get_clean();
echo str_replace('<div class="paper">', $preview_toolbar . '<div class="paper">', $preview_html);
