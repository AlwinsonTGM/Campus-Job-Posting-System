<?php
/**
 * Campus Job Posting System - Fast-Track Verification Queue
 * Clean paper-sheet design adhering to system tokens, keyboard shortcuts, and centered viewport geometry.
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Verification Queue';

// Query pending accounts
$pdo = get_db_connection();
$sql = "
    SELECT 
        u.`id`,
        u.`name`,
        u.`email`,
        u.`role`,
        u.`phone`,
        u.`created_at`,
        u.`verification_status`,
        sp.`student_id`,
        sp.`department`,
        sp.`course`,
        sp.`year_level`,
        sp.`rejection_reason`,
        sp.`registration_proof`,
        ep.`organization_name`,
        ep.`employer_type`,
        ep.`business_permit`,
        ep.`office_location`,
        ep.`accreditation_number`
    FROM `users` u
    LEFT JOIN `student_profiles` sp ON u.`id` = sp.`user_id` AND u.`role` = 'student'
    LEFT JOIN `employer_profiles` ep ON u.`id` = ep.`user_id` AND u.`role` = 'employer'
    WHERE (u.`verification_status` = 'pending_approval' OR sp.`verification_status` = 'pending_approval' OR ep.`verification_status` = 'pending_approval')
      AND (u.`status` IS NULL OR u.`status` = 'active')
      AND (u.`is_archived` = 0 OR u.`is_archived` IS NULL)
    ORDER BY u.`created_at` DESC
";
$raw_cards = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$deck_data = [];
foreach ($raw_cards as $r) {
    $email = strtolower($r['email'] ?? '');
    $domain = substr(strrchr($email, "@"), 1) ?: '';
    $is_kld = ($domain === 'kld.edu.ph' || str_ends_with($domain, '.kld.edu.ph'));
    $is_gmail = ($domain === 'gmail.com');

    $raw_proof = ($r['role'] === 'employer')
        ? ($r['business_permit'] ?? '')
        : ($r['registration_proof'] ?? '');

    $proof_url = '';
    $proof_is_pdf = false;
    if (!empty($raw_proof)) {
        $proof_url = '../' . ltrim(str_replace('\\', '/', $raw_proof), '/');
        $proof_is_pdf = (bool)preg_match('/\.pdf$/i', $raw_proof);
    }

    $id_num = ($r['role'] === 'employer')
        ? ($r['accreditation_number'] ?: 'PARTNER-' . $r['id'])
        : ($r['student_id'] ?: 'STUDENT-' . $r['id']);

    $deck_data[] = [
        'id'              => (int)$r['id'],
        'name'            => $r['name'] ?? 'Applicant',
        'email'           => $r['email'] ?? '',
        'domain'          => $domain,
        'is_kld'          => $is_kld,
        'is_gmail'        => $is_gmail,
        'role'            => $r['role'],
        'phone'           => $r['phone'] ?? 'N/A',
        'identifier'      => $id_num,
        'department'      => $r['department'] ?? ($r['organization_name'] ?? 'General'),
        'course'          => $r['course'] ?? '',
        'year_level'      => $r['year_level'] ?? '',
        'org_name'        => $r['organization_name'] ?? '',
        'employer_type'   => $r['employer_type'] ?? '',
        'proof_url'       => $proof_url,
        'proof_is_pdf'    => $proof_is_pdf,
        'rejection_notes' => $r['rejection_reason'] ?? '',
        'created_at'      => date('M d, Y g:i A', strtotime($r['created_at'] ?? 'now')),
        'created_ago'     => time_ago($r['created_at'] ?? 'now'),
    ];
}

$initial_count = count($deck_data);
require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ==========================================================================
   VERIFICATION QUEUE — SYSTEM TOKEN ALIGNED (Paper Sheet Aesthetic)
   ========================================================================== */
.workbench-viewport {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 250px);
    width: 100%;
}

.deck-stage-wrapper {
    position: relative;
    width: 100%;
    max-width: 920px;
    margin: 0 auto;
    user-select: none;
    touch-action: pan-y;
}

.card-deck-stack {
    position: relative;
    width: 100%;
    height: 520px;
    margin: 0 auto;
}

.deck-card {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--surface, #ffffff);
    border: 1px solid var(--line, #e5e0d4);
    border-radius: var(--radius-card, 18px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
    display: flex;
    flex-direction: row;
    overflow: hidden;
    cursor: grab;
    will-change: transform, opacity;
}

.deck-card:active {
    cursor: grabbing;
}

/* Layering depth */
.deck-card[data-layer="0"] {
    z-index: 10;
    transform: translate3d(0, 0, 0) scale(1);
    opacity: 1;
}

.deck-card[data-layer="1"] {
    z-index: 9;
    transform: translate3d(0, 8px, 0) scale(0.97);
    opacity: 0.88;
    pointer-events: none;
}

.deck-card[data-layer="2"] {
    z-index: 8;
    transform: translate3d(0, 16px, 0) scale(0.94);
    opacity: 0.7;
    pointer-events: none;
}

/* Minimalist Stamps */
.stamp {
    position: absolute;
    padding: 6px 18px;
    font-size: 1.35rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: var(--radius-badge, 8px);
    opacity: 0;
    pointer-events: none;
    z-index: 30;
    transition: opacity 0.1s ease;
    background: var(--surface, #ffffff);
}

.stamp-approve {
    top: 24px;
    left: 24px;
    transform: rotate(-10deg);
    border: 2px solid var(--ink, #161616);
    color: var(--ink, #161616);
}

.stamp-reject {
    top: 24px;
    right: 24px;
    transform: rotate(10deg);
    border: 2px solid var(--ink, #161616);
    color: var(--ink, #161616);
}

.stamp-revision {
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%);
    border: 2px solid var(--ink, #161616);
    color: var(--ink, #161616);
}

/* Split Card Layout: Left Proof Stage, Right Full Dossier */
.deck-card-preview {
    flex: 1 1 54%;
    min-width: 0;
    height: 100%;
    position: relative;
    background: #181818;
    overflow: hidden;
}

.deck-preview-viewport {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    user-select: none;
    touch-action: none;
    cursor: default;
}

.deck-preview-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transform-origin: 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    will-change: transform;
}

.deck-preview-target {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    max-width: 96%;
    max-height: 96%;
    transition: transform 0.15s ease-out;
}

.deck-proof-img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.45);
    border-radius: 4px;
}

.deck-proof-tools {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 20;
}

.deck-zoom-indicator {
    background: rgba(22, 22, 22, 0.85);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-size: 0.72rem;
    font-family: monospace;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 999px;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
}

.deck-zoom-indicator:hover {
    background: rgba(0, 0, 0, 0.95);
    border-color: rgba(255, 255, 255, 0.45);
}

.deck-tool-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.7);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    transition: background 0.15s ease;
}

.deck-tool-btn:hover {
    background: rgba(0, 0, 0, 0.95);
    color: #ffffff;
}

.deck-preview-pill {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.72);
    backdrop-filter: blur(6px);
    color: #ffffff;
    font-size: 0.72rem;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    pointer-events: none;
    white-space: nowrap;
    z-index: 15;
}

.deck-card-dossier {
    flex: 1 1 46%;
    min-width: 0;
    height: 100%;
    background: var(--surface, #ffffff);
    border-left: 1px solid var(--line, #e5e0d4);
    display: flex;
    flex-direction: column;
    padding: 1.25rem 1.4rem;
    overflow-y: auto;
}

.dossier-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem 0.9rem;
    margin-bottom: 0.75rem;
}

.dossier-item {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.dossier-label {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted, #6e6a61);
    margin-bottom: 2px;
}

.dossier-val {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--ink, #161616);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dossier-check-card {
    background: var(--cream, #f1ebdc);
    border: 1px solid var(--line, #e5e0d4);
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
    margin-top: auto;
}

@media (max-width: 767.98px) {
    .deck-stage-wrapper {
        max-width: 100%;
    }
    .card-deck-stack {
        height: 640px;
    }
    .deck-card {
        flex-direction: column;
    }
    .deck-card-preview {
        flex: 0 0 240px;
        height: 240px;
    }
    .deck-card-dossier {
        flex: 1 1 auto;
        border-left: none;
        border-top: 1px solid var(--line, #e5e0d4);
        padding: 1rem;
    }
}

/* Pan-Zoom Document Inspection Stage */
.pan-zoom-stage {
    position: relative;
    width: 100%;
    height: 75vh;
    min-height: 540px;
    background: #141414;
    overflow: hidden;
    cursor: grab;
    user-select: none;
    touch-action: none;
}

.pan-zoom-stage:active {
    cursor: grabbing;
}

.pan-zoom-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transform-origin: 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    will-change: transform;
}

.pan-zoom-target {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    transition: transform 0.15s ease-out;
}

.zoom-controls-overlay {
    position: absolute;
    top: 14px;
    right: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 25;
}

.zoom-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(22, 22, 22, 0.85);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}

.zoom-btn:hover {
    background: rgba(0, 0, 0, 0.95);
    border-color: rgba(255, 255, 255, 0.4);
    transform: scale(1.05);
    color: #ffffff;
}

.zoom-pill {
    background: rgba(22, 22, 22, 0.85);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    font-size: 0.74rem;
    font-family: monospace;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 999px;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
}

.zoom-pill:hover {
    background: rgba(0, 0, 0, 0.95);
    border-color: rgba(255, 255, 255, 0.4);
}

.zoom-hint-pill {
    position: absolute;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(18, 18, 18, 0.85);
    backdrop-filter: blur(6px);
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.74rem;
    padding: 5px 14px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    pointer-events: none;
    white-space: nowrap;
    z-index: 20;
}

/* Action Controls Dock (Non-colorful, subtle, adhering to tokens) */
.action-dock {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 18px;
}

.action-btn-circle {
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--line, #e5e0d4);
    background: var(--surface, #ffffff);
    color: var(--ink, #161616);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: all 0.15s ease;
}

.action-btn-circle:hover {
    transform: translateY(-1px);
    background: var(--cream, #f1ebdc);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.action-btn-circle:active {
    transform: translateY(0);
}

.btn-decision {
    width: 48px;
    height: 48px;
    font-size: 1.15rem;
}

.btn-utility {
    width: 40px;
    height: 40px;
    font-size: 0.95rem;
    color: var(--muted, #6e6a61);
}

/* Floating Undo Bar */
.floating-undo-toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: var(--ink, #161616);
    border: 1px solid var(--line, #e5e0d4);
    color: var(--surface, #ffffff);
    padding: 8px 16px;
    border-radius: var(--radius-pill, 999px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    z-index: 1050;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.84rem;
    transition: transform 0.25s ease, opacity 0.25s ease;
    opacity: 0;
    pointer-events: none;
}

.floating-undo-toast.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
    pointer-events: auto;
}

.undo-progress-bar {
    position: absolute;
    bottom: 0;
    left: 16px;
    right: 16px;
    height: 2px;
    background: var(--muted, #9b978f);
    border-radius: 2px;
    transform-origin: left;
    transition: transform 8s linear;
}

/* Empty State Card (Properly Centered in Middle) */
.celebration-box {
    display: none;
    width: 100%;
    max-width: 460px;
    margin: 0 auto;
    text-align: center;
    padding: 40px 28px;
    border-radius: var(--radius-card, 20px);
    background: var(--surface, #ffffff);
    border: 1px solid var(--line, #e5e0d4);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

/* Keyboard Guide Pill (Monochromatic, Paper-Sheet) */
.shortcut-guide-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    font-size: 0.74rem;
    color: var(--muted, #6e6a61);
}

.shortcut-guide-pill kbd {
    background: var(--surface, #ffffff);
    color: var(--ink, #161616);
    border: 1px solid var(--line, #e5e0d4);
    font-family: monospace;
    font-size: 0.7rem;
    padding: 1px 4px;
    border-radius: 4px;
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.08);
}

/* Preset Chips */
.quick-chip {
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid var(--line, #e5e0d4);
    background: var(--surface, #ffffff);
    color: var(--muted, #6e6a61);
    border-radius: var(--radius-pill, 999px);
    padding: 5px 12px;
    font-size: 0.76rem;
    display: inline-block;
}

.quick-chip:hover {
    border-color: var(--ink, #161616);
    color: var(--ink, #161616);
    background: var(--cream, #f1ebdc);
}

/* Symmetrical Page Head Spacing */
.page-head {
    margin-top: 0 !important;
    padding-top: 1.75rem !important;
    padding-bottom: 1.35rem !important;
    margin-bottom: 1.5rem !important;
    border-bottom: 1px solid var(--line, #e5e0d4) !important;
    align-items: center !important;
}

.page-head-content {
    position: relative !important;
    top: -6px !important;
}

.page-head-title {
    margin-top: 0 !important;
    margin-bottom: 0.35rem !important;
    line-height: 1.15 !important;
}

.page-head-lead {
    margin-bottom: 0 !important;
    line-height: 1.35 !important;
}
</style>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="pt-0 pb-4">
            <div class="container-paper">
                
                <!-- Standard Canonical Page Head -->
                <?php
                $head_actions = '
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="d-none d-md-flex align-items-center gap-1 p-1 bg-cream rounded-pill border border-line">
                            <span class="shortcut-guide-pill"><kbd>D</kbd> Approve</span>
                            <span class="shortcut-guide-pill"><kbd>A</kbd> Reject</span>
                            <span class="shortcut-guide-pill"><kbd>C</kbd> Comment</span>
                            <span class="shortcut-guide-pill"><kbd>Ctrl+Z</kbd> Undo</span>
                            <span class="shortcut-guide-pill"><kbd>Space</kbd> Zoom</span>
                        </div>
                        <a href="users.php" class="btn-pill-outline btn-pill-sm">
                            <i class="bi bi-arrow-left me-1"></i> User Directory
                        </a>
                    </div>
                ';

                render_page_head(
                    '',
                    'Verification Queue',
                    'Screen applicant credentials, student IDs, and registration documents.',
                    $head_actions
                );
                ?>

                <!-- Viewport Container: Centered Vertically in Screen -->
                <div class="workbench-viewport">
                    <div class="deck-stage-wrapper">

                        <!-- Active Stack of Cards -->
                        <div id="cardDeckStack" class="card-deck-stack">
                            <!-- Populated dynamically by JavaScript -->
                        </div>

                        <!-- Empty State (Positioned Dead-Center) -->
                        <div id="celebrationScreen" class="celebration-box">
                            <div class="icon-circle icon-circle-lg mx-auto mb-3" style="width: 52px; height: 52px; border-radius: 50%; background: var(--cream); color: var(--ink); border: 1px solid var(--line); display: inline-flex; align-items: center; justify-content: center;">
                                <i class="bi bi-shield-check fs-2 text-ink"></i>
                            </div>
                            <h2 class="h5 fw-bold text-ink mb-2">Queue Completed</h2>
                            <p class="text-muted-custom small mb-4" style="font-size: 0.86rem; max-width: 360px; margin: 0 auto;">
                                All registration accounts have been reviewed. There are no submissions awaiting verification.
                            </p>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="users.php" class="btn-pill btn-pill-sm py-2 px-3">
                                    <i class="bi bi-people me-1"></i> User Directory
                                </a>
                                <button type="button" class="btn-pill-outline btn-pill-sm py-2 px-3" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Queue
                                </button>
                            </div>
                        </div>

                        <!-- Action Controls Dock -->
                        <div id="actionDockControls" class="action-dock">
                            <!-- Undo -->
                            <button type="button" id="btnDeckUndo" class="action-btn-circle btn-utility" title="Undo (Ctrl+Z)" onclick="deckEngine.undo()">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>

                            <!-- Reject -->
                            <button type="button" id="btnDeckReject" class="action-btn-circle btn-decision" title="Reject (A)" onclick="deckEngine.swipe('reject')">
                                <i class="bi bi-x-lg"></i>
                            </button>

                            <!-- Comment -->
                            <button type="button" id="btnDeckComment" class="action-btn-circle btn-utility" title="Comment (C)" onclick="deckEngine.openCommentModal()">
                                <i class="bi bi-chat-text"></i>
                            </button>

                            <!-- Approve -->
                            <button type="button" id="btnDeckApprove" class="action-btn-circle btn-decision" title="Approve (D)" onclick="deckEngine.swipe('approve')">
                                <i class="bi bi-check-lg"></i>
                            </button>

                            <!-- Zoom -->
                            <button type="button" id="btnDeckZoom" class="action-btn-circle btn-utility" title="Inspect Document (Space)" onclick="deckEngine.openProofZoom()">
                                <i class="bi bi-arrows-fullscreen"></i>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<!-- ==========================================================================
     COMMENT & REVISION MODAL
     ========================================================================== -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border border-line shadow-lg" style="background: var(--surface); color: var(--ink);">
            <div class="modal-header bg-cream border-bottom border-line py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-circle icon-circle-sm" style="background: var(--surface); color: var(--ink); border: 1px solid var(--line);">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-ink fs-6 mb-0">Request Document Revision</h5>
                        <span id="commentApplicantName" class="small text-muted-custom" style="font-size: 0.78rem;">Applicant</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label small fw-bold text-ink mb-2">Preset Guidance (Click to insert):</label>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="quick-chip" onclick="deckEngine.insertReason('Document image is blurry or unreadable. Please provide a clear, well-lit photo.')">
                        Blurry or unreadable photo
                    </span>
                    <span class="quick-chip" onclick="deckEngine.insertReason('Invalid document type. Please upload your official Diploma, Alumni ID, or Certificate of Registration (COR).')">
                        Invalid document type
                    </span>
                    <span class="quick-chip" onclick="deckEngine.insertReason('The name shown on the uploaded document does not match your registered name.')">
                        Name mismatch
                    </span>
                    <span class="quick-chip" onclick="deckEngine.insertReason('Student ID number provided does not correspond to institutional records.')">
                        Student ID mismatch
                    </span>
                    <span class="quick-chip" onclick="deckEngine.insertReason('Document is expired. Please upload proof reflecting your current graduation or enrollment status.')">
                        Expired proof document
                    </span>
                </div>

                <div class="mb-2">
                    <label for="revisionNotesInput" class="form-label small fw-bold text-ink">Administrative Note / Instructions:</label>
                    <textarea id="revisionNotesInput" class="form-control" rows="3" placeholder="Specify what document or correction is required..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-cream border-top border-line py-2 px-4 d-flex justify-content-between">
                <button type="button" class="btn-pill-outline btn-pill-sm py-1 px-3" data-bs-dismiss="modal">Cancel (Esc)</button>
                <button type="button" id="btnSubmitRevisionModal" class="btn-pill btn-pill-sm py-1 px-3" onclick="deckEngine.submitRevision()">
                    Send Revision Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     PROOF DOCUMENT INSPECTION MODAL
     ========================================================================== -->
<div class="modal fade" id="zoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border border-line shadow-2xl" style="background: var(--surface); color: var(--ink);">
            <div class="modal-header bg-cream border-bottom border-line py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <div class="icon-circle icon-circle-sm flex-shrink-0" style="background: var(--surface); color: var(--ink); border: 1px solid var(--line);">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="text-truncate">
                        <h6 class="modal-title fs-6 fw-bold mb-0 text-ink text-truncate" id="zoomModalTitle">Document Inspection</h6>
                        <span class="small text-muted-custom" style="font-size: 0.76rem;">Submitted proof for verification</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn-pill-outline btn-pill-sm py-1 px-3" onclick="deckEngine.rotateZoomModal()" title="Rotate 90°">
                        <i class="bi bi-arrow-clockwise me-1"></i> Rotate
                    </button>
                    <a id="zoomDownloadBtn" href="#" target="_blank" class="btn-pill-outline btn-pill-sm py-1 px-3" title="Open File in New Tab">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open
                    </a>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 text-center d-flex align-items-center justify-content-center" style="min-height: 560px; background: #141414; overflow: hidden;">
                <div id="zoomModalContent" class="w-100 h-100 position-relative"></div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     FLOATING UNDO TOAST
     ========================================================================== -->
<div id="floatingUndoToast" class="floating-undo-toast">
    <span id="undoToastMsg">Decision recorded</span>
    <button type="button" class="btn btn-sm py-0 px-2 rounded-pill fw-semibold text-white" style="background: var(--muted); border: none; font-size: 0.78rem;" onclick="deckEngine.undo()">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Undo <kbd style="background: var(--ink); color: #ffffff; font-size: 0.68rem;">Ctrl+Z</kbd>
    </button>
    <div id="undoProgressBar" class="undo-progress-bar"></div>
</div>

<!-- Offline Self-Contained PDF.js Engine -->
<script src="../assets/vendor/pdfjs/pdf.min.js"></script>

<!-- ==========================================================================
     QUEUE ENGINE CONTROLLER
     ========================================================================== -->
<script>
const INITIAL_DECK = <?= json_encode($deck_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

class VerificationDeckEngine {
    constructor(cards) {
        this.deck = [...cards];
        this.undoStack = [];
        this.isDragging = false;
        this.startX = 0;
        this.startY = 0;
        this.currentX = 0;
        this.currentY = 0;
        this.undoTimer = null;
        this.currentRotation = 0;
        this.zoomScale = 1;
        this.zoomPointX = 0;
        this.zoomPointY = 0;
        this.isZoomPanning = false;
        this.zoomStartX = 0;
        this.zoomStartY = 0;

        // In-Card Zoom State
        this.topCardScale = 1;
        this.topCardPointX = 0;
        this.topCardPointY = 0;
        this.isCardZoomPanning = false;
        this.cardPanStartX = 0;
        this.cardPanStartY = 0;

        this.stackEl = document.getElementById('cardDeckStack');
        this.celebrationEl = document.getElementById('celebrationScreen');
        this.controlsEl = document.getElementById('actionDockControls');
        this.undoToastEl = document.getElementById('floatingUndoToast');
        this.undoProgressBar = document.getElementById('undoProgressBar');

        this.commentModal = new bootstrap.Modal(document.getElementById('commentModal'));
        this.zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));

        this.init();
    }

    init() {
        this.renderStack();
        this.bindGlobalKeyboard();
        this.preloadNextImages();
    }

    renderStack() {
        this.stackEl.innerHTML = '';

        if (this.deck.length === 0) {
            this.showCelebration();
            return;
        }

        // Active items present
        this.celebrationEl.style.display = 'none';
        this.stackEl.style.display = 'block';
        this.controlsEl.style.display = 'flex';

        // Render top 3 visible cards
        const visibleSlice = this.deck.slice(0, 3);
        visibleSlice.reverse().forEach((cardData, idx) => {
            const forwardLayer = visibleSlice.length - 1 - idx;
            const cardEl = this.createCardElement(cardData, forwardLayer);
            this.stackEl.appendChild(cardEl);
        });

        this.renderVisiblePdfs();
        this.bindTopCardGestures();
    }

    renderVisiblePdfs() {
        this.stackEl.querySelectorAll('canvas[data-pdf]').forEach(canvas => {
            if (!canvas.getAttribute('data-pdf-rendered')) {
                canvas.setAttribute('data-pdf-rendered', 'true');
                this.renderPdfToCanvas(canvas.getAttribute('data-pdf'), canvas, 1.8);
            }
        });
    }

    renderPdfToCanvas(url, canvas, scale = 1.75) {
        if (!window.pdfjsLib) return;
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = '../assets/vendor/pdfjs/pdf.worker.min.js';

        window.pdfjsLib.getDocument(url).promise.then(pdf => {
            return pdf.getPage(1);
        }).then(page => {
            const viewport = page.getViewport({ scale: scale });
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const ctx = canvas.getContext('2d');
            return page.render({ canvasContext: ctx, viewport: viewport }).promise;
        }).catch(err => {
            console.error('PDF render error:', err);
        });
    }

    createCardElement(data, layer) {
        const card = document.createElement('div');
        card.className = 'deck-card';
        card.setAttribute('data-id', data.id);
        card.setAttribute('data-layer', layer);

        let domainBadge = '';
        if (data.is_kld) {
            domainBadge = `<span class="badge border border-success-subtle bg-success-subtle text-success font-monospace px-2 py-1" style="font-size: 0.72rem;"><i class="bi bi-shield-check me-1"></i>${this.escapeHtml(data.domain)}</span>`;
        } else {
            domainBadge = `<span class="badge border border-line bg-cream text-muted font-monospace px-2 py-1" style="font-size: 0.72rem;"><i class="bi bi-person-badge me-1"></i>${this.escapeHtml(data.domain || 'External')} &bull; Manual Review</span>`;
        }

        let roleBadge = data.role === 'employer'
            ? `<span class="badge border border-line bg-surface text-ink fw-semibold px-2 py-1" style="font-size: 0.72rem;">Campus Partner</span>`
            : `<span class="badge border border-line bg-surface text-ink fw-semibold px-2 py-1" style="font-size: 0.72rem;">Student Applicant</span>`;

        let proofHtml = '';
        if (!data.proof_url) {
            proofHtml = `
                <div class="p-4 text-center d-flex flex-column align-items-center justify-content-center h-100" style="background: var(--surface);">
                    <i class="bi bi-file-earmark-x fs-1 mb-2 text-muted opacity-50"></i>
                    <strong class="text-ink small mb-1">No Document Attached</strong>
                    <span class="text-muted-custom small" style="font-size: 0.74rem;">Registered via standard personal email flow</span>
                </div>
            `;
        } else {
            const docElement = data.proof_is_pdf
                ? `<canvas class="deck-proof-img" id="imgProof${data.id}" data-pdf="${this.escapeHtml(data.proof_url)}" draggable="false"></canvas>`
                : `<img src="${data.proof_url}" alt="Proof Document" class="deck-proof-img" id="imgProof${data.id}" loading="eager" draggable="false">`;

            const pillIcon = data.proof_is_pdf ? 'bi-file-earmark-pdf' : 'bi-mouse';

            proofHtml = `
                <div class="deck-preview-viewport" id="previewViewport${data.id}">
                    <div class="deck-preview-layer" id="previewLayer${data.id}">
                        <div class="deck-preview-target" id="previewTarget${data.id}">
                            ${docElement}
                        </div>
                    </div>
                </div>
                <div class="deck-proof-tools">
                    <span class="deck-zoom-indicator" id="cardZoomBadge${data.id}" style="display: none;" onclick="event.stopPropagation(); deckEngine.resetTopCardZoom();" title="Click to Reset (100%)">100%</span>
                    <button type="button" class="deck-tool-btn" title="Rotate Document" onclick="event.stopPropagation(); deckEngine.rotateCardImage(${data.id})">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <a href="${data.proof_url}" target="_blank" class="deck-tool-btn" title="Open File in New Tab" onclick="event.stopPropagation();">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <button type="button" class="deck-tool-btn" title="Fullscreen Zoom (Space)" onclick="event.stopPropagation(); deckEngine.openProofZoom()">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
                <div class="deck-preview-pill">
                    <i class="bi ${pillIcon} me-1"></i> Wheel to Zoom &bull; Drag to Pan &bull; Space to Expand
                </div>
            `;
        }

        let detailsHtml = '';
        if (data.role === 'employer') {
            detailsHtml = `
                <div class="dossier-grid">
                    <div class="dossier-item" style="grid-column: span 2;">
                        <span class="dossier-label">Organization Name</span>
                        <span class="dossier-val" title="${this.escapeHtml(data.org_name || data.department)}">${this.escapeHtml(data.org_name || data.department)}</span>
                    </div>
                    <div class="dossier-item">
                        <span class="dossier-label">Accreditation / ID</span>
                        <span class="dossier-val font-monospace">${this.escapeHtml(data.identifier)}</span>
                    </div>
                    <div class="dossier-item">
                        <span class="dossier-label">Partner Classification</span>
                        <span class="dossier-val">${this.escapeHtml(data.employer_type || 'Private Organization')}</span>
                    </div>
                    <div class="dossier-item">
                        <span class="dossier-label">Mobile / Contact</span>
                        <span class="dossier-val">${this.escapeHtml(data.phone || 'N/A')}</span>
                    </div>
                    <div class="dossier-item">
                        <span class="dossier-label">Official Email</span>
                        <span class="dossier-val text-truncate" title="${this.escapeHtml(data.email)}">${this.escapeHtml(data.email)}</span>
                    </div>
                </div>
            `;
        } else {
            detailsHtml = `
                <div class="dossier-grid">
                    <div class="dossier-item">
                        <span class="dossier-label">Student ID</span>
                        <span class="dossier-val font-monospace text-ink fw-bold">${this.escapeHtml(data.identifier)}</span>
                    </div>
                    <div class="dossier-item">
                        <span class="dossier-label">Year Standing</span>
                        <span class="dossier-val">${this.escapeHtml(data.year_level || 'Undergraduate')}</span>
                    </div>
                    <div class="dossier-item" style="grid-column: span 2;">
                        <span class="dossier-label">College / Department</span>
                        <span class="dossier-val text-truncate" title="${this.escapeHtml(data.department || 'General')}">${this.escapeHtml(data.department || 'General')}</span>
                    </div>
                    <div class="dossier-item" style="grid-column: span 2;">
                        <span class="dossier-label">Academic Program / Degree</span>
                        <span class="dossier-val text-truncate" title="${this.escapeHtml(data.course || 'Unassigned Program')}">${this.escapeHtml(data.course || 'Unassigned Program')}</span>
                    </div>
                    <div class="dossier-item">
                        <span class="dossier-label">Registered Email</span>
                        <span class="dossier-val text-truncate" title="${this.escapeHtml(data.email)}">${this.escapeHtml(data.email)}</span>
                    </div>
                    <div class="dossier-item">
                        <span class="dossier-label">Mobile Contact</span>
                        <span class="dossier-val">${this.escapeHtml(data.phone || 'N/A')}</span>
                    </div>
                </div>
            `;
        }

        let feedbackHtml = '';
        if (data.rejection_notes) {
            feedbackHtml = `
                <div class="alert alert-warning py-1 px-2 mb-2 d-flex align-items-center gap-2" style="font-size: 0.74rem;">
                    <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0"></i>
                    <div class="text-truncate">
                        <strong>Previous Revision Note:</strong> ${this.escapeHtml(data.rejection_notes)}
                    </div>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="stamp stamp-approve">APPROVED</div>
            <div class="stamp stamp-reject">REJECTED</div>
            <div class="stamp stamp-revision">REVISE</div>

            <!-- Left: Document Preview Stage -->
            <div class="deck-card-preview">
                ${proofHtml}
            </div>

            <!-- Right: Full Applicant Dossier Panel -->
            <div class="deck-card-dossier">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2 pb-2 border-bottom border-line">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="icon-circle icon-circle-sm fw-bold flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.92rem; background: var(--cream); color: var(--ink); border: 1px solid var(--line);">
                            ${this.escapeHtml(data.name.charAt(0).toUpperCase())}
                        </div>
                        <div class="text-truncate">
                            <h3 class="h6 fw-bold text-ink mb-1 text-truncate" style="font-size: 0.96rem; letter-spacing: -0.01em;">${this.escapeHtml(data.name)}</h3>
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                ${roleBadge}
                                ${domainBadge}
                            </div>
                        </div>
                    </div>
                </div>

                ${feedbackHtml}

                <div class="mb-2">
                    <span class="text-muted-custom fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Applicant Credentials</span>
                </div>

                ${detailsHtml}

                <div class="dossier-check-card mt-auto">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-bold text-ink" style="font-size: 0.76rem;"><i class="bi bi-card-checklist me-1"></i> Cross-Check Proof Document:</span>
                        <span class="badge border border-line bg-surface text-muted" style="font-size: 0.65rem;">Match Check</span>
                    </div>
                    <div class="small text-muted-custom" style="font-size: 0.74rem; line-height: 1.4;">
                        Ensure the student name (<strong class="text-ink">${this.escapeHtml(data.name)}</strong>) and Student ID (<strong class="text-ink font-monospace">${this.escapeHtml(data.identifier)}</strong>) match the attached document.
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-top border-line" style="font-size: 0.72rem;">
                    <span class="text-muted-custom">
                        <i class="bi bi-clock me-1"></i> Submitted ${this.escapeHtml(data.created_ago)}
                    </span>
                    <span class="text-muted-custom font-monospace">
                        Ref #${data.id}
                    </span>
                </div>
            </div>
        `;

        return card;
    }

    bindTopCardGestures() {
        const topCard = this.stackEl.querySelector('.deck-card[data-layer="0"]');
        if (!topCard) return;

        const cardId = topCard.getAttribute('data-id');
        const viewportEl = topCard.querySelector('.deck-preview-viewport');
        const layerEl = document.getElementById(`previewLayer${cardId}`);
        const badgeEl = document.getElementById(`cardZoomBadge${cardId}`);

        this.topCardScale = 1;
        this.topCardPointX = 0;
        this.topCardPointY = 0;
        this.isCardZoomPanning = false;

        const applyCardZoom = () => {
            if (layerEl) {
                layerEl.style.transform = `translate(${this.topCardPointX}px, ${this.topCardPointY}px) scale(${this.topCardScale})`;
            }
            if (badgeEl) {
                if (this.topCardScale > 1.02) {
                    badgeEl.style.display = 'inline-block';
                    badgeEl.innerText = `${Math.round(this.topCardScale * 100)}%`;
                } else {
                    badgeEl.style.display = 'none';
                }
            }
            if (viewportEl) {
                viewportEl.style.cursor = this.topCardScale > 1 ? (this.isCardZoomPanning ? 'grabbing' : 'grab') : 'default';
            }
        };

        if (viewportEl && layerEl) {
            // Wheel event: cursor-focal zoom on card preview
            viewportEl.addEventListener('wheel', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const rect = viewportEl.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;

                const factor = e.deltaY < 0 ? 1.2 : 0.833;
                const newScale = Math.min(Math.max(this.topCardScale * factor, 1), 6);

                if (newScale === this.topCardScale) return;

                if (newScale === 1) {
                    this.topCardPointX = 0;
                    this.topCardPointY = 0;
                } else {
                    this.topCardPointX = mouseX - (mouseX - this.topCardPointX) * (newScale / this.topCardScale);
                    this.topCardPointY = mouseY - (mouseY - this.topCardPointY) * (newScale / this.topCardScale);
                }
                this.topCardScale = newScale;
                applyCardZoom();
            }, { passive: false });

            // Double click: reset zoom
            viewportEl.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                this.resetTopCardZoom();
            });
        }

        const onPointerDown = (e) => {
            if (e.target.closest('.deck-tool-btn') || e.target.closest('.deck-zoom-indicator') || e.target.closest('a')) return;

            // If zoomed in (>1) and interacting with preview viewport, pan the document
            if (viewportEl && (e.target === viewportEl || viewportEl.contains(e.target)) && this.topCardScale > 1) {
                this.isCardZoomPanning = true;
                this.cardPanStartX = e.clientX - this.topCardPointX;
                this.cardPanStartY = e.clientY - this.topCardPointY;
                viewportEl.setPointerCapture(e.pointerId);
                applyCardZoom();

                const onZoomPanMove = (pe) => {
                    if (!this.isCardZoomPanning) return;
                    this.topCardPointX = pe.clientX - this.cardPanStartX;
                    this.topCardPointY = pe.clientY - this.cardPanStartY;
                    applyCardZoom();
                };

                const onZoomPanEnd = (pe) => {
                    this.isCardZoomPanning = false;
                    viewportEl.releasePointerCapture(pe.pointerId);
                    viewportEl.removeEventListener('pointermove', onZoomPanMove);
                    viewportEl.removeEventListener('pointerup', onZoomPanEnd);
                    viewportEl.removeEventListener('pointercancel', onZoomPanEnd);
                    applyCardZoom();
                };

                viewportEl.addEventListener('pointermove', onZoomPanMove);
                viewportEl.addEventListener('pointerup', onZoomPanEnd);
                viewportEl.addEventListener('pointercancel', onZoomPanEnd);
                return;
            }

            // Normal Card Swipe Handling
            this.isDragging = true;
            this.startX = e.clientX;
            this.startY = e.clientY;
            this.currentX = e.clientX;
            this.currentY = e.clientY;

            topCard.style.transition = 'none';
            topCard.setPointerCapture(e.pointerId);

            topCard.addEventListener('pointermove', onPointerMove);
            topCard.addEventListener('pointerup', onPointerUp);
            topCard.addEventListener('pointercancel', onPointerUp);
        };

        const onPointerMove = (e) => {
            if (!this.isDragging) return;
            this.currentX = e.clientX;
            this.currentY = e.clientY;

            const deltaX = this.currentX - this.startX;
            const deltaY = this.currentY - this.startY;
            const rot = deltaX * 0.07;

            topCard.style.transform = `translate3d(${deltaX}px, ${deltaY}px, 0) rotate(${rot}deg)`;

            const stampApprove = topCard.querySelector('.stamp-approve');
            const stampReject = topCard.querySelector('.stamp-reject');
            const stampRevise = topCard.querySelector('.stamp-revision');

            if (deltaX > 20) {
                const opacity = Math.min((deltaX - 20) / 70, 1);
                stampApprove.style.opacity = opacity;
                stampReject.style.opacity = 0;
                stampRevise.style.opacity = 0;
            } else if (deltaX < -20) {
                const opacity = Math.min((-deltaX - 20) / 70, 1);
                stampReject.style.opacity = opacity;
                stampApprove.style.opacity = 0;
                stampRevise.style.opacity = 0;
            } else if (deltaY < -30 && Math.abs(deltaX) < 30) {
                const opacity = Math.min((-deltaY - 30) / 50, 1);
                stampRevise.style.opacity = opacity;
                stampApprove.style.opacity = 0;
                stampReject.style.opacity = 0;
            } else {
                stampApprove.style.opacity = 0;
                stampReject.style.opacity = 0;
                stampRevise.style.opacity = 0;
            }
        };

        const onPointerUp = (e) => {
            if (!this.isDragging) return;
            this.isDragging = false;
            topCard.releasePointerCapture(e.pointerId);

            topCard.removeEventListener('pointermove', onPointerMove);
            topCard.removeEventListener('pointerup', onPointerUp);
            topCard.removeEventListener('pointercancel', onPointerUp);

            const deltaX = this.currentX - this.startX;
            const deltaY = this.currentY - this.startY;

            if (deltaX > 90) {
                this.swipe('approve');
            } else if (deltaX < -90) {
                this.swipe('reject');
            } else if (deltaY < -70 && Math.abs(deltaX) < 50) {
                this.springBack(topCard, () => {
                    this.openCommentModal();
                });
            } else {
                this.springBack(topCard);
            }
        };

        topCard.addEventListener('pointerdown', onPointerDown);
    }

    springBack(card, callback = null) {
        card.style.transition = 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        card.style.transform = 'translate3d(0, 0, 0) rotate(0deg)';
        card.querySelectorAll('.stamp').forEach(s => s.style.opacity = 0);
        if (callback) setTimeout(callback, 180);
    }

    swipe(action, customNotes = '') {
        if (this.deck.length === 0) return;
        const topCard = this.stackEl.querySelector('.deck-card[data-layer="0"]');
        if (!topCard) return;

        const currentApplicant = this.deck[0];

        topCard.style.transition = 'transform 0.3s cubic-bezier(0.2, 0.9, 0.3, 1.2), opacity 0.3s';

        const stampApprove = topCard.querySelector('.stamp-approve');
        const stampReject = topCard.querySelector('.stamp-reject');
        const stampRevise = topCard.querySelector('.stamp-revision');

        if (action === 'approve') {
            stampApprove.style.opacity = 1;
            topCard.style.transform = 'translate3d(850px, 30px, 0) rotate(30deg)';
        } else if (action === 'reject') {
            stampReject.style.opacity = 1;
            topCard.style.transform = 'translate3d(-850px, 30px, 0) rotate(-30deg)';
        } else if (action === 'request_revision') {
            stampRevise.style.opacity = 1;
            topCard.style.transform = 'translate3d(0, -750px, 0) scale(0.9)';
        }

        topCard.style.opacity = '0';
        this.resetTopCardZoom();

        this.undoStack.push({
            data: currentApplicant,
            action: action,
            notes: customNotes
        });

        this.dispatchApiAction(action, currentApplicant.id, customNotes);
        this.showUndoToast(currentApplicant.name, action);

        setTimeout(() => {
            this.deck.shift();
            this.renderStack();
        }, 180);
    }

    dispatchApiAction(action, id, notes = '') {
        fetch('../api/verification-card-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: action, id: id, notes: notes })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) console.error('Failed:', data.message);
        })
        .catch(err => console.error('Error:', err));
    }

    undo() {
        if (this.undoStack.length === 0) return;

        const lastAction = this.undoStack.pop();
        const applicant = lastAction.data;

        fetch('../api/verification-card-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'rollback', id: applicant.id })
        })
        .catch(err => console.error('Rollback error:', err));

        this.deck.unshift(applicant);
        this.renderStack();

        const restoredTopCard = this.stackEl.querySelector('.deck-card[data-layer="0"]');
        if (restoredTopCard) {
            const startX = (lastAction.action === 'approve') ? 500 : (lastAction.action === 'reject' ? -500 : 0);
            const startY = (lastAction.action === 'request_revision') ? -500 : 0;
            restoredTopCard.style.transition = 'none';
            restoredTopCard.style.transform = `translate3d(${startX}px, ${startY}px, 0) scale(0.92)`;
            restoredTopCard.style.opacity = '0';

            requestAnimationFrame(() => {
                restoredTopCard.style.transition = 'transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.35s';
                restoredTopCard.style.transform = 'translate3d(0, 0, 0) scale(1)';
                restoredTopCard.style.opacity = '1';
            });
        }

        this.hideUndoToast();
    }

    showUndoToast(applicantName, action) {
        clearTimeout(this.undoTimer);

        let verb = 'Approved';
        if (action === 'reject') {
            verb = 'Rejected';
        } else if (action === 'request_revision') {
            verb = 'Revision requested:';
        }

        document.getElementById('undoToastMsg').innerHTML = `<strong>${verb}</strong> ${this.escapeHtml(applicantName)}`;

        this.undoProgressBar.style.transition = 'none';
        this.undoProgressBar.style.transform = 'scaleX(1)';

        this.undoToastEl.classList.add('show');

        requestAnimationFrame(() => {
            this.undoProgressBar.style.transition = 'transform 8s linear';
            this.undoProgressBar.style.transform = 'scaleX(0)';
        });

        this.undoTimer = setTimeout(() => {
            this.hideUndoToast();
        }, 8000);
    }

    hideUndoToast() {
        this.undoToastEl.classList.remove('show');
        clearTimeout(this.undoTimer);
    }

    openCommentModal() {
        if (this.deck.length === 0) return;
        const current = this.deck[0];
        document.getElementById('commentApplicantName').innerText = `Applicant: ${current.name} (${current.identifier})`;
        document.getElementById('revisionNotesInput').value = '';
        this.commentModal.show();
        setTimeout(() => {
            document.getElementById('revisionNotesInput').focus();
        }, 250);
    }

    insertReason(text) {
        const area = document.getElementById('revisionNotesInput');
        area.value = area.value ? area.value + ' ' + text : text;
        area.focus();
    }

    submitRevision() {
        const notes = document.getElementById('revisionNotesInput').value.trim();
        if (!notes) {
            alert('Please specify the guidance or document requirement.');
            return;
        }
        this.commentModal.hide();
        this.swipe('request_revision', notes);
    }

    openProofZoom() {
        if (this.deck.length === 0) return;
        const current = this.deck[0];
        if (!current.proof_url) return;

        document.getElementById('zoomModalTitle').innerText = `Document: ${current.name} (${current.identifier})`;
        document.getElementById('zoomDownloadBtn').href = current.proof_url;

        const contentEl = document.getElementById('zoomModalContent');
        this.currentRotation = 0;
        this.zoomScale = 1;
        this.zoomPointX = 0;
        this.zoomPointY = 0;

        const targetHtml = current.proof_is_pdf
            ? `<canvas id="modalZoomCanvas" class="rounded-2 shadow-2xl" style="max-width: 90vw; max-height: 80vh; pointer-events: none;" draggable="false"></canvas>`
            : `<img src="${current.proof_url}" class="rounded-2 shadow-2xl" style="max-width: 90vw; max-height: 80vh; object-fit: contain; pointer-events: none;" alt="Document">`;

        contentEl.innerHTML = `
            <div id="zoomStageContainer" class="pan-zoom-stage">
                <div id="zoomPanLayer" class="pan-zoom-layer">
                    <div id="zoomInnerTarget" class="pan-zoom-target">
                        ${targetHtml}
                    </div>
                </div>
                <div class="zoom-controls-overlay">
                    <span id="zoomPercentPill" class="zoom-pill" onclick="deckEngine.resetModalZoom()" title="Click to Reset (100%)">100%</span>
                    <button type="button" class="zoom-btn" onclick="deckEngine.stepModalZoom(1.25)" title="Zoom In (+)">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    <button type="button" class="zoom-btn" onclick="deckEngine.stepModalZoom(0.8)" title="Zoom Out (-)">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button type="button" class="zoom-btn" onclick="deckEngine.resetModalZoom()" title="Reset (Double-Click)">
                        <i class="bi bi-arrows-angle-contract"></i>
                    </button>
                </div>
                <div class="zoom-hint-pill">
                    <i class="bi bi-mouse me-1"></i> Mouse wheel to zoom at cursor &bull; Drag to pan &bull; Double-click to reset
                </div>
            </div>
        `;

        if (current.proof_is_pdf) {
            const modalCanvas = document.getElementById('modalZoomCanvas');
            if (modalCanvas) {
                this.renderPdfToCanvas(current.proof_url, modalCanvas, 2.2);
            }
        }

        this.initModalPanZoom();
        this.zoomModal.show();
    }

    initModalPanZoom() {
        const stage = document.getElementById('zoomStageContainer');
        if (!stage) return;

        // Mouse Wheel: Zoom in/out specifically at the cursor point
        stage.addEventListener('wheel', (e) => {
            e.preventDefault();
            const rect = stage.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            const factor = e.deltaY < 0 ? 1.2 : 0.833;
            const newScale = Math.min(Math.max(this.zoomScale * factor, 0.75), 8);

            if (newScale === this.zoomScale) return;

            // Invariant: keep the document point under the mouse cursor stationary
            this.zoomPointX = mouseX - (mouseX - this.zoomPointX) * (newScale / this.zoomScale);
            this.zoomPointY = mouseY - (mouseY - this.zoomPointY) * (newScale / this.zoomScale);
            this.zoomScale = newScale;

            this.applyModalZoomTransform();
        }, { passive: false });

        // Left-click Drag: Pan around the zoomed document
        stage.addEventListener('pointerdown', (e) => {
            if (e.target.closest('.zoom-btn') || e.target.closest('.zoom-pill')) return;
            if (e.button !== 0) return;

            this.isZoomPanning = true;
            this.zoomStartX = e.clientX - this.zoomPointX;
            this.zoomStartY = e.clientY - this.zoomPointY;
            stage.setPointerCapture(e.pointerId);
            stage.style.cursor = 'grabbing';
        });

        stage.addEventListener('pointermove', (e) => {
            if (!this.isZoomPanning) return;
            this.zoomPointX = e.clientX - this.zoomStartX;
            this.zoomPointY = e.clientY - this.zoomStartY;
            this.applyModalZoomTransform();
        });

        const endPan = () => {
            if (!this.isZoomPanning) return;
            this.isZoomPanning = false;
            stage.style.cursor = 'grab';
        };

        stage.addEventListener('pointerup', endPan);
        stage.addEventListener('pointercancel', endPan);

        // Double-click to snap back to 100%
        stage.addEventListener('dblclick', (e) => {
            if (e.target.closest('.zoom-btn') || e.target.closest('.zoom-pill')) return;
            this.resetModalZoom();
        });
    }

    applyModalZoomTransform() {
        const layer = document.getElementById('zoomPanLayer');
        const pill = document.getElementById('zoomPercentPill');
        if (layer) {
            layer.style.transform = `translate(${this.zoomPointX}px, ${this.zoomPointY}px) scale(${this.zoomScale})`;
        }
        if (pill) {
            pill.innerText = `${Math.round(this.zoomScale * 100)}%`;
        }
    }

    resetModalZoom() {
        this.zoomScale = 1;
        this.zoomPointX = 0;
        this.zoomPointY = 0;
        this.applyModalZoomTransform();
    }

    stepModalZoom(factor) {
        const stage = document.getElementById('zoomStageContainer');
        if (!stage) return;
        const rect = stage.getBoundingClientRect();
        const mouseX = rect.width / 2;
        const mouseY = rect.height / 2;

        const newScale = Math.min(Math.max(this.zoomScale * factor, 0.75), 8);
        if (newScale === this.zoomScale) return;

        this.zoomPointX = mouseX - (mouseX - this.zoomPointX) * (newScale / this.zoomScale);
        this.zoomPointY = mouseY - (mouseY - this.zoomPointY) * (newScale / this.zoomScale);
        this.zoomScale = newScale;

        this.applyModalZoomTransform();
    }

    rotateZoomModal() {
        const target = document.getElementById('zoomInnerTarget');
        if (!target) return;
        this.currentRotation = (this.currentRotation + 90) % 360;
        target.style.transform = `rotate(${this.currentRotation}deg)`;
    }

    rotateCardImage(id) {
        const target = document.getElementById(`previewTarget${id}`) || document.getElementById(`imgProof${id}`);
        if (!target) return;
        let rot = parseInt(target.getAttribute('data-rotation') || '0', 10);
        rot = (rot + 90) % 360;
        target.setAttribute('data-rotation', rot);
        target.style.transform = `rotate(${rot}deg)`;
    }

    resetTopCardZoom() {
        const topCard = this.stackEl.querySelector('.deck-card[data-layer="0"]');
        if (!topCard) return;
        const cardId = topCard.getAttribute('data-id');
        const layerEl = document.getElementById(`previewLayer${cardId}`);
        const badgeEl = document.getElementById(`cardZoomBadge${cardId}`);
        const viewportEl = topCard.querySelector('.deck-preview-viewport');
        this.topCardScale = 1;
        this.topCardPointX = 0;
        this.topCardPointY = 0;
        this.isCardZoomPanning = false;
        if (layerEl) {
            layerEl.style.transform = 'translate(0px, 0px) scale(1)';
        }
        if (badgeEl) {
            badgeEl.style.display = 'none';
        }
        if (viewportEl) {
            viewportEl.style.cursor = 'default';
        }
    }

    showCelebration() {
        this.stackEl.style.display = 'none';
        this.controlsEl.style.display = 'none';
        this.celebrationEl.style.display = 'block';
    }

    preloadNextImages() {
        this.deck.slice(0, 4).forEach(item => {
            if (item.proof_url && !item.proof_is_pdf) {
                const img = new Image();
                img.src = item.proof_url;
            }
        });
    }

    bindGlobalKeyboard() {
        document.addEventListener('keydown', (e) => {
            const isInputActive = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);

            if (document.getElementById('commentModal').classList.contains('show')) {
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    this.submitRevision();
                }
                return;
            }

            if (document.getElementById('zoomModal').classList.contains('show')) return;
            if (isInputActive) return;

            if ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'z')) {
                e.preventDefault();
                this.undo();
                return;
            }

            switch (e.key.toLowerCase()) {
                case 'd':
                case 'arrowright':
                    e.preventDefault();
                    this.swipe('approve');
                    break;
                case 'a':
                case 'arrowleft':
                    e.preventDefault();
                    this.swipe('reject');
                    break;
                case 'c':
                case 'arrowup':
                    e.preventDefault();
                    this.openCommentModal();
                    break;
                case 'u':
                    e.preventDefault();
                    this.undo();
                    break;
                case ' ':
                    e.preventDefault();
                    this.openProofZoom();
                    break;
            }
        });
    }

    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}

let deckEngine = null;
document.addEventListener('DOMContentLoaded', () => {
    deckEngine = new VerificationDeckEngine(INITIAL_DECK);
});
</script>
