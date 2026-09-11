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
        <div class="row g-4">
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
                <div class="d-flex gap-2 mb-3">
                    <a href="https://www.facebook.com/alwinson.bustamante" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="Facebook" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://x.com/alwinson1000" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="X (formerly Twitter)" title="X (formerly Twitter)"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.linkedin.com/in/AlwinsonTGM" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="LinkedIn" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="https://github.com/AlwinsonTGM" target="_blank" rel="noopener noreferrer" class="social-circle-link" aria-label="GitHub" title="GitHub"><i class="bi bi-github"></i></a>
                </div>
                <div class="text-muted-custom small">
                    &copy; <?= date('Y') ?> <strong><?= htmlspecialchars(SITE_NAME) ?></strong>. All Rights Reserved.
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="fw-bold text-ink mb-3 small text-uppercase">Quick Links</h6>
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
                <h6 class="fw-bold text-ink mb-3 small text-uppercase">Legal & Guidelines</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?= $base_url ?>privacy.php">Data Privacy Policy (RA 10173)</a></li>
                    <li><a href="<?= $base_url ?>terms.php">Terms of Service</a></li>
                    <li><a href="<?= $base_url ?>faqs.php?open=2#faq-2">20-Hour Work Regulations</a></li>
                    <li><a href="<?= $base_url ?>about-us.php#developers">COAL101 Dev Team</a></li>
                </ul>
            </div>

            <!-- Support & Office -->
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold text-ink mb-3 small text-uppercase">Campus Career Center</h6>
                <p class="text-muted-custom small mb-2">
                    <i class="bi bi-geo-alt me-2 text-accent"></i> Student Affairs & Career Services Office<br>
                    Main Academic Building, Room 201
                </p>
                <p class="text-muted-custom small mb-2">
                    <i class="bi bi-envelope me-2 text-accent"></i> careers@kld.edu.ph
                </p>
                <p class="text-muted-custom small mb-0">
                    <i class="bi bi-telephone me-2 text-accent"></i> (02) 8920-1000 loc. 402
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Shared Floating Spotlight Search Modal -->
<?php require_once __DIR__ . '/search-modal.php'; ?>

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
