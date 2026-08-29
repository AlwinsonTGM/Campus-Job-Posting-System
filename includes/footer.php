<?php
/**
 * Campus Job Posting System - Shared Footer Partial
 * Paper Sheet Aesthetic (COAL101 Blueprint)
 */
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}
?>
<footer class="paper-footer mt-auto">
    <div class="container-fluid px-lg-4">
        <div class="row g-4 pb-4">
            <!-- Brand & Mission Column -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-3 p-2 shadow-sm" style="width: 34px; height: 34px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#2ECC5E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h5 class="fw-extrabold text-ink mb-0 tracking-tight"><?= htmlspecialchars(SITE_NAME) ?></h5>
                </div>
                <p class="text-muted-custom small mb-4 pe-lg-3">
                    Dedicated campus employment network empowering undergraduate students to find flexible on-campus assistantships while supporting university departments and academic laboratories.
                </p>
                <!-- Social Icon Circles -->
                <div class="d-flex gap-2">
                    <a href="https://www.facebook.com/alwinson.bustamante" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="Facebook" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://x.com/alwinson1000" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="X (formerly Twitter)" title="X (formerly Twitter)"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.linkedin.com/in/AlwinsonTGM" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="LinkedIn" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="https://github.com/AlwinsonTGM" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="GitHub" title="GitHub"><i class="bi bi-github"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="eyebrow-badge text-ink mb-3">Quick Links</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= $base_url ?>student/jobs.php">Find Jobs</a></li>
                    <li><a href="<?= $base_url ?>employer/dashboard.php">For Employers</a></li>
                    <li><a href="<?= $base_url ?>updates.php">Career Updates</a></li>
                    <li><a href="<?= $base_url ?>faqs.php">FAQs</a></li>
                    <li><a href="<?= $base_url ?>about-us.php">About Us</a></li>
                    <li><a href="<?= $base_url ?>login.php">Login / Portal</a></li>
                </ul>
            </div>

            <!-- Legal & Governance -->
            <div class="col-lg-3 col-md-3 col-6">
                <h6 class="eyebrow-badge text-ink mb-3">Legal & Guidelines</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= $base_url ?>privacy.php">Data Privacy Policy (RA 10173)</a></li>
                    <li><a href="<?= $base_url ?>terms.php">Terms of Service</a></li>
                    <li><a href="<?= $base_url ?>faqs.php?open=2#faq-2">20-Hour Work Regulations</a></li>
                    <li><a href="<?= $base_url ?>about-us.php#developers">COAL101 Dev Team</a></li>
                </ul>
            </div>

            <!-- Support & Office -->
            <div class="col-lg-3 col-md-6">
                <h6 class="eyebrow-badge text-ink mb-3">Campus Career Center</h6>
                <p class="text-muted-custom small mb-2">
                    <i class="bi bi-geo-alt me-2 text-accent"></i> Student Affairs & Career Services Office<br>
                    Main Academic Building, Room 201
                </p>
                <p class="text-muted-custom small mb-2">
                    <i class="bi bi-envelope me-2 text-accent"></i> careers@campus-hire.edu
                </p>
                <p class="text-muted-custom small mb-0">
                    <i class="bi bi-telephone me-2 text-accent"></i> (02) 8920-1000 loc. 402
                </p>
            </div>
        </div>

        <!-- Accreditation note line -->
        <div class="border-top border-line pt-3 pb-2 text-center text-muted-custom small">
            <span class="d-inline-flex align-items-center gap-1">
                <i class="bi bi-shield-check text-accent"></i> Official University Student Employment Portal · Approved for Academic Year 2026–2027
            </span>
        </div>

        <!-- Bottom Bar -->
        <div class="border-top border-line pt-3 mt-2 d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start small text-muted-custom">
            <div>
                &copy; <?= date('Y') ?> <strong><?= htmlspecialchars(SITE_NAME) ?></strong>. All Rights Reserved.
            </div>
            <div class="mt-2 mt-md-0 d-flex gap-3">
                <a href="<?= $base_url ?>privacy.php" class="text-muted-custom">Data Privacy Policy</a>
                <span>·</span>
                <a href="<?= $base_url ?>terms.php" class="text-muted-custom">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>


<!-- ═══════════════════════════════════════════════════════════════════
     FLOATING DATA MODE TOGGLE (Position: Fixed — Outside Layout Flow)
     ═══════════════════════════════════════════════════════════════════ -->
<?php
$_current_data_mode = function_exists('get_system_data_mode') ? get_system_data_mode() : 'demo';
$_is_demo = ($_current_data_mode === 'demo');
$_toggle_target = $_is_demo ? 'real' : 'demo';
$_csrf = function_exists('generate_csrf_token') ? generate_csrf_token() : '';
?>
<div id="dataModeFAB" class="data-mode-fab" data-mode="<?= htmlspecialchars($_current_data_mode) ?>">
    <!-- Collapsed: Small pill showing current mode -->
    <button type="button" class="data-mode-fab__trigger" onclick="document.getElementById('dataModeFAB').classList.toggle('data-mode-fab--open')" title="Data Mode: <?= $_is_demo ? 'Demo (Placeholder)' : 'Real (Live)' ?>">
        <span class="data-mode-fab__icon"><?= $_is_demo ? '📋' : '🧪' ?></span>
        <span class="data-mode-fab__label"><?= $_is_demo ? 'DEMO' : 'REAL' ?></span>
    </button>

    <!-- Expanded: Card with mode info and switch action -->
    <div class="data-mode-fab__panel">
        <div class="data-mode-fab__panel-header">
            <span class="data-mode-fab__panel-title">Dataset Mode</span>
            <button type="button" class="data-mode-fab__close" onclick="document.getElementById('dataModeFAB').classList.remove('data-mode-fab--open')" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="data-mode-fab__panel-body">
            <!-- Current mode indicator -->
            <div class="data-mode-fab__current <?= $_is_demo ? 'data-mode-fab__current--demo' : 'data-mode-fab__current--real' ?>">
                <div class="data-mode-fab__current-icon"><?= $_is_demo ? '📋' : '🧪' ?></div>
                <div>
                    <div class="data-mode-fab__current-label"><?= $_is_demo ? 'Demo / Placeholder' : 'Real / Clean Slate' ?></div>
                    <div class="data-mode-fab__current-desc"><?= $_is_demo
                        ? 'Sample data loaded — students, jobs, applications are all mock placeholders.'
                        : 'Clean slate — everything is empty. Register and create your own data.' ?></div>
                </div>
            </div>

            <!-- Switch action -->
            <form method="GET" action="<?= $base_url ?>data-toggle.php" class="data-mode-fab__form">
                <input type="hidden" name="action" value="switch_mode">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($_toggle_target) ?>">
                <button type="submit" class="data-mode-fab__switch-btn <?= $_is_demo ? 'data-mode-fab__switch-btn--to-real' : 'data-mode-fab__switch-btn--to-demo' ?>">
                    <i class="bi <?= $_is_demo ? 'bi-rocket-takeoff' : 'bi-arrow-counterclockwise' ?>"></i>
                    Switch to <?= $_is_demo ? 'Real Mode' : 'Demo Mode' ?>
                </button>
            </form>

            <?php if (!$_is_demo): ?>
            <!-- Wipe & Reset button — only visible in Real mode -->
            <form method="GET" action="<?= $base_url ?>data-toggle.php" class="data-mode-fab__form data-mode-fab__form--wipe" onsubmit="return confirm('⚠️ This will wipe ALL real data — registered users, jobs, and applications — back to a clean slate.\n\nOnly the admin account will remain.\n\nContinue?');">
                <input type="hidden" name="action" value="wipe_real">
                <button type="submit" class="data-mode-fab__switch-btn data-mode-fab__switch-btn--wipe">
                    <i class="bi bi-trash3"></i>
                    Wipe &amp; Start Fresh
                </button>
            </form>
            <?php endif; ?>

            <div class="data-mode-fab__hint">
                <?= $_is_demo
                    ? '<i class="bi bi-info-circle"></i> Switching to Real clears all placeholder data. You fill everything yourself.'
                    : '<i class="bi bi-info-circle"></i> Switching to Demo restores sample accounts, jobs, and applications.' ?>
            </div>
        </div>
    </div>
</div>

<style>
/* ─── Data Mode FAB: Fixed Position Outside All Layout ─── */
.data-mode-fab {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    user-select: none;
    -webkit-user-select: none;
}

.data-mode-fab__trigger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px 7px 10px;
    border: 1.5px solid rgba(0, 0, 0, 0.1);
    border-radius: 100px;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.03);
    cursor: pointer;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.6px;
    color: #1a1a2e;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
}

.data-mode-fab__trigger:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.06);
    transform: translateY(-1px);
}

.data-mode-fab__icon {
    font-size: 15px;
    line-height: 1;
}

.data-mode-fab__label {
    font-size: 10.5px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Demo mode = warm amber/orange tint */
.data-mode-fab[data-mode="demo"] .data-mode-fab__trigger {
    border-color: rgba(245, 158, 11, 0.3);
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

/* Real mode = cool teal/green tint */
.data-mode-fab[data-mode="real"] .data-mode-fab__trigger {
    border-color: rgba(16, 185, 129, 0.3);
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
}

/* ─── Expanded Panel ─── */
.data-mode-fab__panel {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 300px;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.02);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-6px) scale(0.97);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.data-mode-fab--open .data-mode-fab__panel {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.data-mode-fab__panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px 10px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.data-mode-fab__panel-title {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #6b7280;
}

.data-mode-fab__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: #9ca3af;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.15s ease;
}

.data-mode-fab__close:hover {
    background: #f3f4f6;
    color: #374151;
}

.data-mode-fab__panel-body {
    padding: 14px 16px 16px;
}

.data-mode-fab__current {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 12px;
}

.data-mode-fab__current--demo {
    background: #fffbeb;
    border: 1px solid rgba(245, 158, 11, 0.15);
}

.data-mode-fab__current--real {
    background: #ecfdf5;
    border: 1px solid rgba(16, 185, 129, 0.15);
}

.data-mode-fab__current-icon {
    font-size: 22px;
    line-height: 1;
    flex-shrink: 0;
    margin-top: 1px;
}

.data-mode-fab__current-label {
    font-size: 13px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 3px;
}

.data-mode-fab__current-desc {
    font-size: 11.5px;
    line-height: 1.45;
    color: #6b7280;
}

.data-mode-fab__form {
    margin-bottom: 10px;
}

.data-mode-fab__switch-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    letter-spacing: 0.3px;
}

.data-mode-fab__switch-btn--to-real {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
}

.data-mode-fab__switch-btn--to-real:hover {
    background: linear-gradient(135deg, #059669, #047857);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
    transform: translateY(-1px);
}

.data-mode-fab__switch-btn--to-demo {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}

.data-mode-fab__switch-btn--to-demo:hover {
    background: linear-gradient(135deg, #d97706, #b45309);
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
    transform: translateY(-1px);
}

.data-mode-fab__form--wipe {
    margin-top: -4px;
}

.data-mode-fab__switch-btn--wipe {
    background: transparent;
    border: 1.5px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    font-size: 11.5px;
}

.data-mode-fab__switch-btn--wipe:hover {
    background: #fef2f2;
    border-color: rgba(239, 68, 68, 0.5);
    color: #dc2626;
    box-shadow: 0 2px 10px rgba(239, 68, 68, 0.15);
    transform: translateY(-1px);
}

.data-mode-fab__hint {
    font-size: 10.5px;
    line-height: 1.5;
    color: #9ca3af;
    display: flex;
    align-items: flex-start;
    gap: 5px;
}

.data-mode-fab__hint .bi {
    flex-shrink: 0;
    margin-top: 1px;
    font-size: 11px;
}

/* ─── Close panel when clicking outside ─── */
@media (max-width: 575.98px) {
    .data-mode-fab {
        top: auto;
        bottom: 20px;
        right: 16px;
    }
    .data-mode-fab__panel {
        top: auto;
        bottom: calc(100% + 8px);
        right: 0;
        width: 280px;
    }
}
</style>

<script>
// Close FAB panel when clicking outside
document.addEventListener('click', function(e) {
    var fab = document.getElementById('dataModeFAB');
    if (fab && !fab.contains(e.target)) {
        fab.classList.remove('data-mode-fab--open');
    }
});
</script>

<!-- Shared Floating Spotlight Search Modal -->
<?php require_once __DIR__ . '/search-modal.php'; ?>

<!-- Bootstrap 5 Bundle JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Global Main JS (Paper Sheet 3D Tilt & Interaction Engine) -->
<script src="<?= $base_url ?>assets/js/main.js"></script>

<?php if (isset($extra_js) && is_array($extra_js)): ?>
    <?php foreach ($extra_js as $script): ?>
        <script src="<?= $base_url ?><?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
