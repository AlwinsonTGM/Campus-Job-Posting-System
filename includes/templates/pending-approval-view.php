<?php
/** Account review presentation; authorization and resubmission remain in the controller. */
$auth_partner = $user_role !== 'student';
$auth_story_title = $is_pending ? 'Your next chapter is almost here.' : ($is_revoked ? 'Let’s get you the right support.' : 'A small update. A fresh review.');
$auth_story_description = $is_pending ? 'Your email is verified. Our administrators will review your registration and documents before opening your workspace.' : ($is_revoked ? 'Career Services can help you understand the decision and the next steps for your account.' : 'Review the administrator’s feedback, update your documents, and send your registration back for review.');
$auth_story_benefits = [['envelope-check', 'Email address verified'], [$is_pending ? 'hourglass-split' : 'clipboard-check', $is_pending ? 'Administrator review in progress' : 'Administrator review completed'], ['door-open', 'Portal access follows approval']];
$auth_story_footnote = 'Questions? support@kld.edu.ph';
$review_flash = get_flash();
require __DIR__ . '/auth-start.php';
?>
<div class="approval-review">
    <div class="review-heading">
        <div class="review-symbol" aria-hidden="true"><i class="bi bi-<?= $is_pending ? 'clock-history' : ($is_revoked ? 'shield-x' : 'file-earmark-arrow-up') ?>"></i></div>
        <h1 id="form-title"><?= $is_pending ? 'Registration submitted.' : ($is_revoked ? 'Verification revoked.' : 'A few updates needed.') ?></h1>
    </div>
    <p class="intro">Hi <strong><?= htmlspecialchars($user_name) ?></strong>, <?= $is_pending ? 'your account is waiting for administrator approval.' : ($is_revoked ? 'your account verification has been withdrawn. Please contact Career Services for help.' : 'please make the requested corrections so we can review your registration again.') ?></p>
    <?php if ($review_flash): ?><div class="verification-notice" role="status"><?= htmlspecialchars($review_flash['message']) ?></div><?php endif; ?>
    <?php if ($is_pending): ?>
        <div class="review-address"><i class="bi bi-envelope" aria-hidden="true"></i><span>We’ll send the decision to <strong><?= htmlspecialchars($user_email) ?></strong></span></div>
        <ol class="review-timeline" aria-label="Account approval progress">
            <li class="complete"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><div><strong>Email verified</strong><p>Your email address is confirmed.</p></div></li>
            <li aria-current="step"><i class="bi bi-hourglass-split" aria-hidden="true"></i><div><strong>Administrator review</strong><p>Your registration details and documents are being checked. This typically takes 1–2 business days.</p></div></li>
            <li><i class="bi bi-door-open" aria-hidden="true"></i><div><strong>Ready to sign in</strong><p>Once approved, sign in to access your campus portal.</p></div></li>
        </ol>
    <?php else: ?>
        <?php if ($rejection_reason !== ''): ?><div class="review-feedback"><strong>Administrator feedback</strong><p><?= nl2br(htmlspecialchars($rejection_reason)) ?></p></div><?php endif; ?>
        <?php if ($is_revoked): ?>
            <p class="review-help">A revoked verification cannot be resubmitted here. Contact <a href="mailto:support@kld.edu.ph">support@kld.edu.ph</a> or visit the Office of Student Affairs so an administrator can review your account.</p>
        <?php else: ?>
            <?php if ($resubmit_error !== null): ?><div class="error-summary" role="alert"><?= htmlspecialchars($resubmit_error) ?></div><?php endif; ?>
            <form action="pending-approval.php" method="POST" enctype="multipart/form-data" class="review-resubmit">
                <input type="hidden" name="csrf_token" value="<?= !empty($design_preview) ? '' : generate_csrf_token() ?>">
                <input type="hidden" name="action" value="resubmit">
                <?php if ($user_role === 'employer'): ?>
                    <div class="field"><label for="pa-org">Organization / Office name</label><input id="pa-org" name="organization_name" value="<?= htmlspecialchars($account['organization_name'] ?? '') ?>" required></div>
                    <div class="field"><label for="pa-rep">Authorized representative</label><input id="pa-rep" name="name" value="<?= htmlspecialchars($account['name'] ?? '') ?>" required></div>
                <?php endif; ?>
                <?php $is_employer = $user_role === 'employer'; $existing_document = $is_employer ? $existing_permit : $existing_proof; ?>
                <div class="field">
                    <label for="pa-document"><?= $is_employer ? 'Business Permit / Mayor’s Permit / BIR 2303' : 'Updated COR / Student ID' ?></label>
                    <input type="file" id="pa-document" name="<?= $is_employer ? 'permit_file' : 'proof_file' ?>" accept="image/*,application/pdf" aria-describedby="document-hint" <?= $existing_document === '' ? 'required' : '' ?>>
                    <p class="hint" id="document-hint">Clear, readable PDF, JPG, or PNG. Maximum 5 MB.<?= $existing_document !== '' ? ' Optional if unchanged.' : '' ?></p>
                    <?php if ($existing_document !== ''): ?><a href="<?= htmlspecialchars($existing_document) ?>" target="_blank" rel="noopener noreferrer">View document on file</a><?php endif; ?>
                </div>
                <div class="field"><label for="pa-note">What did you change? <span class="optional">Optional</span></label><textarea id="pa-note" name="note" rows="3" placeholder="Tell the administrator what you updated."></textarea></div>
                <button class="primary" type="submit">Submit for review <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                <p class="hint">Submitting returns your account to the review queue. We’ll email you when it has been reviewed.</p>
            </form>
        <?php endif; ?>
    <?php endif; ?>
    <div class="review-actions"><a class="<?= $is_rejected ? 'text-button' : 'primary' ?>" href="<?= $auth_login_url ?>">Back to sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></a><a href="index.php">Return to home</a></div>
    <?php if ($is_pending): ?><p class="hint review-help">Need help? Contact <a href="mailto:support@kld.edu.ph">support@kld.edu.ph</a> or visit the Office of Student Affairs.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/auth-end.php'; ?>
