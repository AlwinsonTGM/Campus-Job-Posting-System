<?php
/**
 * View template for login.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Archetype A: Auth Split Card Shell (max-w 960) -->
                <div class="auth-shell">
                    
                    <!-- Left Brand Panel (42% on desktop) -->
                    <div class="auth-brand-panel">
                        <div>
                            <!-- Brand Mark -->
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <!-- D4 Antenna Helper brand mark (theme-aware: light <-> dark pairing) -->
                                <script>
                                (function(){
                                  try {
                                    var t = localStorage.getItem('campus_hire_theme');
                                    if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                                    document.write(t === 'dark'
                                      ? '<img class="brand-logo-img" data-brand-logo src="<?= $base_url ?>assets/img/logo-d4-dark-64.png?v=d4" width="42" height="42" style="width:42px;height:42px;object-fit:cover;border-radius:10px;" alt="Campus Hire logo">'
                                      : '<img class="brand-logo-img" data-brand-logo src="<?= $base_url ?>assets/img/logo-d4-light-64.png?v=d4" width="42" height="42" style="width:42px;height:42px;object-fit:cover;border-radius:10px;" alt="Campus Hire logo">');
                                  } catch (e) {
                                    document.write('<img class="brand-logo-img" data-brand-logo src="<?= $base_url ?>assets/img/logo-d4-light-64.png?v=d4" width="42" height="42" style="width:42px;height:42px;object-fit:cover;border-radius:10px;" alt="Campus Hire logo">');
                                  }
                                })();
                                </script>
                                <noscript><img class="brand-logo-img" src="<?= $base_url ?>assets/img/logo-d4-light-64.png?v=d4" width="42" height="42" style="width:42px;height:42px;object-fit:cover;border-radius:10px;" alt="Campus Hire logo"></noscript>
                                <span class="fw-extrabold text-ink fs-5 tracking-tight"><?= htmlspecialchars(SITE_NAME) ?></span>
                            </div>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Empowering Student Talent &amp; Campus Opportunities
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Sign in to manage your student applications, explore departmental assistantships, and track your institutional employment.
                            </p>

                            <!-- Feature List -->
                            <div class="d-flex flex-column gap-3 mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; aspect-ratio: 1 / 1; font-size: 0.95rem;">
                                        <i class="bi bi-mortarboard-fill"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Verified On-Campus Assistantships</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; aspect-ratio: 1 / 1; font-size: 0.95rem;">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">Strict 20 hrs/week Academic Safeguards</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="faq-help-icon-box m-0 flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; aspect-ratio: 1 / 1; font-size: 0.95rem;">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <span class="small fw-semibold text-ink">RA 10173 Data Privacy Compliance</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Form Panel (58% on desktop) -->
                    <div class="auth-form-panel">
                        
                        <div class="mb-4">
                            <h2 class="card-paper-title fs-4 mb-1">Student Sign In</h2>
                            <p class="text-muted-custom small mb-0">Enter your student credentials to apply for campus assistantships and track your applications</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5 flex-shrink-0"></i>
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                </div>
                                <?php if (!empty($portal_redirect_hint)): ?>
                                    <div class="mt-3 pt-2 border-top border-line">
                                        <a href="portal-login.php?email=<?= urlencode($email ?? '') ?><?= $safe_next !== '' ? '&next=' . urlencode($safe_next) : '' ?>" class="btn-pill btn-pill-sm text-decoration-none d-inline-flex align-items-center gap-1">
                                            <span>Go to Partner &amp; Staff Portal</span> <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" class="form-paper">
                            <?php if ($safe_next !== ''): ?>
                                <input type="hidden" name="next" value="<?= htmlspecialchars($safe_next) ?>">
                            <?php endif; ?>
                            
                            <!-- Email Input -->
                            <div class="mb-3">
                                <label class="form-label" for="login-email">Institutional Email Address</label>
                                <div class="input-group input-group-integrated">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="login-email" class="form-control" placeholder="username@kld.edu.ph" value="<?= htmlspecialchars($email ?? '') ?>" required <?= empty($email) ? 'autofocus' : '' ?>>
                                </div>
                            </div>

                            <!-- Password Input with Show/Hide Toggle -->
                            <div class="mb-3">
                                <label class="form-label" for="login-password">Account Password</label>
                                <div class="input-group input-group-integrated">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password" id="login-password" class="form-control" placeholder="Password" required <?= !empty($email) ? 'autofocus' : '' ?>>
                                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('login-password', 'toggle-pw-icon')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="toggle-pw-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Remember Me Checkbox & Forgot Password Link -->
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                                <div class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input" id="rememberMe" checked>
                                    <label class="form-check-label small text-muted-custom" for="rememberMe">
                                        Keep me signed in on this device
                                    </label>
                                </div>
                                <a href="forgot-pass.php" class="small fw-bold text-ink text-decoration-none">
                                    Forgot Password?
                                </a>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-pill w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> SIGN IN AS STUDENT
                            </button>
                        </form>

                        <!-- Alternate Links -->
                        <div class="border-top border-line pt-3 text-center">
                            <span class="text-muted-custom small">Don't have an account yet?</span>
                            <a href="register.php?role=student" class="text-ink fw-bold small ms-1 text-decoration-none">
                                Create an Account <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Partner & Staff Callout Card -->
                        <div class="mt-4 p-3 rounded-3 bg-cream border border-line text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1 text-muted-custom small">
                                <i class="bi bi-building-lock text-accent"></i>
                                <span>Department Office, Industry Partner, or Administrator?</span>
                            </div>
                            <div>
                                <a href="portal-login.php<?= $safe_next !== '' ? '?next=' . urlencode($safe_next) : '' ?>" id="link-portal-login" class="fw-bold text-accent text-decoration-none">Sign in to Partner &amp; Staff Portal <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

<script src="assets/js/password-strength.js"></script>
