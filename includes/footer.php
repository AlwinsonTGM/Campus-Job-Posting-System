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

<?php
$current_data_mode = function_exists('get_system_data_mode') ? get_system_data_mode() : 'demo';
$is_real_mode = ($current_data_mode === 'real');
?>
<!-- System Dataset Switcher Modal -->
<div class="modal fade" id="dataModeModal" tabindex="-1" aria-labelledby="dataModeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-line shadow-lg rounded-4">
            <div class="modal-header border-line bg-surface">
                <h5 class="modal-title fs-5 fw-bold text-ink" id="dataModeModalLabel">
                    <i class="bi bi-database-fill-gear text-accent me-2"></i>System Dataset Switcher
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted-custom small mb-4">
                    Easily toggle between <strong>Demo / Placeholder Data</strong> (with pre-populated student assistantships and applicants) and <strong>Real Clean Slate Mode</strong> for live testing.
                </p>

                <div class="d-flex flex-column gap-3">
                    <!-- Real Mode Option -->
                    <form action="<?= $base_url ?>data-toggle.php" method="POST" class="data-mode-fab__form m-0">
                        <input type="hidden" name="action" value="switch_mode">
                        <input type="hidden" name="mode" value="real">
                        <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                        <button type="submit" class="btn btn-outline-success w-100 p-3 text-start d-flex align-items-center justify-content-between rounded-3 <?= $is_real_mode ? 'active border-2' : '' ?>">
                            <div>
                                <div class="fw-bold"><i class="bi bi-shield-check me-2"></i>Activate Real Clean Slate Mode</div>
                                <div class="small opacity-75">Wipes placeholder fixtures; allows real live job postings and applicant submissions.</div>
                            </div>
                            <i class="bi bi-arrow-right-circle fs-4"></i>
                        </button>
                    </form>

                    <!-- Demo Mode Option -->
                    <form action="<?= $base_url ?>data-toggle.php" method="POST" class="data-mode-fab__form m-0">
                        <input type="hidden" name="action" value="switch_mode">
                        <input type="hidden" name="mode" value="demo">
                        <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                        <button type="submit" class="btn btn-outline-warning w-100 p-3 text-start d-flex align-items-center justify-content-between rounded-3 <?= !$is_real_mode ? 'active border-2' : '' ?>">
                            <div>
                                <div class="fw-bold"><i class="bi bi-collection-play me-2"></i>Switch to Demo Placeholders</div>
                                <div class="small opacity-75">Restores standard demo jobs, pre-vetted departments, and test candidates.</div>
                            </div>
                            <i class="bi bi-arrow-right-circle fs-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
