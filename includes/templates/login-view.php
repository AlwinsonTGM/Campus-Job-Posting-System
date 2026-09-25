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
                                <span class="faq-help-icon-box m-0 flex-shrink-0" style="width: 42px; height: 42px; min-width: 42px; min-height: 42px; aspect-ratio: 1 / 1; font-size: 1.15rem;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="var(--ink)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 17L12 22L22 17" stroke="var(--ink)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M2 12L12 17L22 12" stroke="var(--ink)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="fw-extrabold text-ink fs-5 tracking-tight"><?= htmlspecialchars(SITE_NAME) ?></span>
                            </div>

                            <h2 class="h3 fw-bold text-ink mb-3">
                                Empowering Student Talent &amp; Campus Opportunities
                            </h2>
                            <p class="text-muted-custom small mb-4">
                                Sign in to manage your student applications, explore departmental assistantships, or evaluate student candidates.
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

                        <!-- 1-Click Instant Demo Login Selector -->
                        <div class="p-3 bg-white rounded-4 border border-line mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-ink text-uppercase" style="font-size: 11px;">
                                    <i class="bi bi-lightning-charge-fill text-accent"></i> 1-Click Evaluation Accounts
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" id="tab-student" class="chip chip-selectable" data-demo-email="student@kld.edu.ph">
                                    <i class="bi bi-mortarboard text-accent"></i> Student
                                </button>
                                <button type="button" id="tab-employer" class="chip chip-selectable" data-demo-email="registrar@kld.edu.ph">
                                    <i class="bi bi-bank text-accent"></i> Campus Office
                                </button>
                                <button type="button" id="tab-partner" class="chip chip-selectable" data-demo-email="techvanguard@partner.kld.edu.ph">
                                    <i class="bi bi-patch-check-fill text-accent"></i> Partner
                                </button>
                                <button type="button" id="tab-admin" class="chip chip-selectable" data-demo-email="admin@kld.edu.ph">
                                    <i class="bi bi-shield-lock text-accent"></i> Admin
                                </button>
                            </div>

                            <!-- Dropdown for All 30 Realistic Campus Personas -->
                            <div class="mt-2 pt-2 border-top border-line">
                                <label for="quickPersonaSelect" class="form-label text-muted-custom small mb-1" style="font-size: 11px;">
                                    <i class="bi bi-people me-1 text-accent"></i> Quick Persona Switcher:
                                </label>
                                <select id="quickPersonaSelect" class="form-select form-select-sm" style="font-size: 11.5px;">
                                    <option value="" selected disabled>Choose a realistic persona to test...</option>
                                    <optgroup label="🎓 Students (Real-World Situations)">
                                        <option value="student@kld.edu.ph" data-pass="Password123!">Juan Dela Cruz (BSIS · 2 Active Interviews Scheduled)</option>
                                        <option value="maria.santos@kld.edu.ph" data-pass="Password123!">Maria Santos (BSCS · Hired at KLD Library)</option>
                                        <option value="bea.alonzo@kld.edu.ph" data-pass="Password123!">Bea Alonzo (BSN · Hired in Nursing Lab & Interview in Clinic)</option>
                                        <option value="camille.delrosario@kld.edu.ph" data-pass="Password123!">Camille Del Rosario (BSA · Hired at Registrar & Scholarship Office)</option>
                                        <option value="joshua.garcia@kld.edu.ph" data-pass="Password123!">Joshua Garcia (BSIT 1st Yr · Fresh Applicant)</option>
                                        <option value="sofia.mendoza@kld.edu.ph" data-pass="Password123!">Sofia Mendoza (BACOMM · Hired Creative Media Specialist)</option>
                                        <option value="mark.bautista@kld.edu.ph" data-pass="Password123!">Mark Dave Bautista (BSHM · Hired Barista, Rejected Event)</option>
                                        <option value="angela.cruz@kld.edu.ph" data-pass="Password123!">Angela Nicole Cruz (BSP · Hired Guidance Peer Facilitator)</option>
                                        <option value="gabriel.tan@kld.edu.ph" data-pass="Password123!">Gabriel Tan (BSCS 4th Yr · DSA Peer Tutor & TechVanguard Intern)</option>
                                        <option value="chloe.perez@kld.edu.ph" data-pass="Password123!">Chloe Danielle Perez (BEEd · Library Aide Applicant)</option>
                                        <option value="ralph.santos@kld.edu.ph" data-pass="Password123!">Ralph Matthew Santos (BSIS · Pending COR Verification)</option>
                                        <option value="kimberly.ramos@kld.edu.ph" data-pass="Password123!">Kimberly Joy Ramos (Midwifery · Rejected COR Proof)</option>
                                        <option value="abustamante@kld.edu.ph" data-pass="@Alwinson100">Alwinson Bustamante (Lead Student Lab Tech)</option>
                                    </optgroup>
                                    <optgroup label="🏛️ Campus Offices (Internal Supervisors)">
                                        <option value="registrar@kld.edu.ph" data-pass="Password123!">Prof. Roberto Hernandez (University Registrar)</option>
                                        <option value="itdept@kld.edu.ph" data-pass="Password123!">Engr. Clara Villanueva (Management Information Systems)</option>
                                        <option value="guidance@kld.edu.ph" data-pass="Password123!">Dr. Evelyn Ramos (Guidance & Counseling Center)</option>
                                        <option value="library@kld.edu.ph" data-pass="Password123!">Ms. Teresa Cruz (University Library Services)</option>
                                        <option value="sciencelab@kld.edu.ph" data-pass="Password123!">Prof. Corazon Navarro (Science & Nursing Skills Labs)</option>
                                        <option value="athletics@kld.edu.ph" data-pass="Password123!">Coach Gary Belmonte (Athletics & Sports Development)</option>
                                        <option value="scholarships@kld.edu.ph" data-pass="Password123!">Mr. Arnold Santos (Admissions & Scholarship Office)</option>
                                        <option value="bursar@kld.edu.ph" data-pass="Password123!">Mr. Fernando Ocampo (University Cashier & Bursar)</option>
                                    </optgroup>
                                    <optgroup label="🤝 Accredited Partners (External Employers)">
                                        <option value="techvanguard@partner.kld.edu.ph" data-pass="Password123!">Atty. Katrina Alcantara (TechVanguard Solutions Inc.)</option>
                                        <option value="mediahub@partner.kld.edu.ph" data-pass="Password123!">Marco V. Domingo (Dasma Creative Media Studio)</option>
                                        <option value="campuscafe@partner.kld.edu.ph" data-pass="Password123!">Chef Patricia Reyes (Campus Cafe & Co.)</option>
                                        <option value="greenleaf@partner.kld.edu.ph" data-pass="Password123!">Ms. Hazel Dimaculangan (GreenLeaf Academic Books)</option>
                                        <option value="cavitebpo@partner.kld.edu.ph" data-pass="Password123!">Roland Dela Torre (Cavite BPO Solutions Hub)</option>
                                        <option value="apexrobotics@partner.kld.edu.ph" data-pass="Password123!">Engr. Dennis Ramirez (Apex Robotics · Pending Approval)</option>
                                        <option value="codecrafted@partner.kld.edu.ph" data-pass="Password123!">Michelle Anne Soriano (CodeCrafted · Rejected)</option>
                                    </optgroup>
                                    <optgroup label="🛡️ Administration">
                                        <option value="admin@kld.edu.ph" data-pass="Password123!">KLD Campus System Admin (Full System Oversight)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2 d-flex align-items-center justify-content-between py-1 px-2 text-decoration-none" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#modalAllPersonas">
                                <span><i class="bi bi-person-lines-fill me-1 text-accent"></i> Persona Directory & Scenarios</span>
                                <i class="bi bi-chevron-right text-muted" style="font-size: 10px;"></i>
                            </button>
                        </div>

                    </div>

                    <!-- Right Form Panel (58% on desktop) -->
                    <div class="auth-form-panel">
                        
                        <div class="mb-4">
                            <h2 class="card-paper-title fs-4 mb-1">Sign In to Your Account</h2>
                            <p class="text-muted-custom small mb-0">Enter your institutional credentials to proceed</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert-paper alert-paper--danger mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                                    <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                                </div>
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
                                    <input type="email" name="email" id="login-email" class="form-control" placeholder="username@kld.edu.ph" required autofocus>
                                </div>
                            </div>

                            <!-- Password Input with Show/Hide Toggle -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0" for="login-password">Account Password</label>
                                    <a href="forgot-pass.php" class="small fw-bold text-ink text-decoration-none">
                                        Forgot Password?
                                    </a>
                                </div>
                                <div class="input-group input-group-integrated">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('login-password', 'toggle-pw-icon')" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye" id="toggle-pw-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Remember Me Checkbox -->
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" checked>
                                <label class="form-check-label small text-muted-custom" for="rememberMe">
                                    Keep me signed in on this device
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-pill w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> SIGN IN TO PORTAL
                            </button>
                        </form>

                        <!-- Alternate Links -->
                        <div class="border-top border-line pt-3 text-center">
                            <span class="text-muted-custom small">Don't have an account yet?</span>
                            <a href="register.php" class="text-ink fw-bold small ms-1 text-decoration-none">
                                Create an Account <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </main>

        <!-- Modal: All 30 Realistic Campus Personas Directory -->
        <div class="modal fade" id="modalAllPersonas" tabindex="-1" aria-labelledby="modalAllPersonasLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom border-line px-4 pt-4 pb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-success text-white px-2 py-1 small fw-semibold">30 Active Personas</span>
                                <span class="text-muted-custom small">· Realistic Campus Ecosystem</span>
                            </div>
                            <h5 class="modal-title fw-bold text-ink" id="modalAllPersonasLabel">Campus Hire Persona Directory &amp; Scenarios</h5>
                            <p class="text-muted-custom small mb-0">Select any realistic account to instantly test applicant journeys, hiring workflows, or admin governance.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-pills nav-fill gap-2 mb-4" id="personaTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-3 py-2 small fw-semibold" id="tab-p-students" data-bs-toggle="tab" data-bs-target="#pane-p-students" type="button" role="tab">
                                    <i class="bi bi-mortarboard-fill me-1"></i> Students (13)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-3 py-2 small fw-semibold" id="tab-p-offices" data-bs-toggle="tab" data-bs-target="#pane-p-offices" type="button" role="tab">
                                    <i class="bi bi-bank2 me-1"></i> Campus Offices (8)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-3 py-2 small fw-semibold" id="tab-p-partners" data-bs-toggle="tab" data-bs-target="#pane-p-partners" type="button" role="tab">
                                    <i class="bi bi-patch-check-fill me-1"></i> Partners (7)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-3 py-2 small fw-semibold" id="tab-p-admin" data-bs-toggle="tab" data-bs-target="#pane-p-admin" type="button" role="tab">
                                    <i class="bi bi-shield-lock-fill me-1"></i> Admin (1)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="personaTabsContent">
                            <!-- TAB 1: STUDENTS -->
                            <div class="tab-pane fade show active" id="pane-p-students" role="tabpanel">
                                <div class="row g-3">
                                    <!-- Juan Dela Cruz -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Juan Dela Cruz</span>
                                                    <span class="badge bg-info text-dark" style="font-size: 10px;">2 Interviews Scheduled</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">student@kld.edu.ph · BSIS 2nd Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Active candidate with interviews scheduled at MIS Lab &amp; Registrar, plus a pending Athletics application.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="student@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Juan
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Maria Santos -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Maria Santos</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Hired at Library</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">maria.santos@kld.edu.ph · BSCS 3rd Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Approved student assistant performing digital book cataloging and circulation desk duties.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="maria.santos@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Maria
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Bea Alonzo -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Bea Alonzo</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Hired in Nursing Lab</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">bea.alonzo@kld.edu.ph · BS Nursing 3rd Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Assigned to Clinical Simulation Ward 202; also holds a scheduled interview for Health Clinic Support Aide.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="bea.alonzo@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Bea
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Camille Del Rosario -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Camille Del Rosario</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">Dual-Hired · Pending Profile</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">camille.delrosario@kld.edu.ph · BS Accountancy 3rd Yr</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Hired in Registrar Archival &amp; Scholarship Office. Has a pending 3rd Year profile change request awaiting Admin approval.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="camille.delrosario@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Camille
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Joshua Garcia -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Joshua Garcia</span>
                                                    <span class="badge bg-warning text-dark" style="font-size: 10px;">Fresh Applicant</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">joshua.garcia@kld.edu.ph · BSIT 1st Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">1st-year student exploring entry-level roles; submitted pending applications to MIS Lab and Bookstore.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="joshua.garcia@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Joshua
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Sofia Mendoza -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Sofia Mendoza</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Creative Specialist</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">sofia.mendoza@kld.edu.ph · BACOMM 2nd Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Hired by Dasma Creative Media Studio to produce video reels, infographics, and promotional media.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="sofia.mendoza@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Sofia
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Mark Dave Bautista -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Mark Dave Bautista</span>
                                                    <span class="badge bg-secondary text-white" style="font-size: 10px;">Barista · 1 Rejected Flow</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">mark.bautista@kld.edu.ph · BSHM 2nd Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Hired Barista at Campus Cafe &amp; Co.; demonstrates realistic constructive rejection note on Intramurals.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="mark.bautista@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Mark
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Angela Nicole Cruz -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Angela Nicole Cruz</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Guidance Peer</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">angela.cruz@kld.edu.ph · BS Psychology 3rd Yr</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Hired peer facilitator handling frontdesk appointments and testing packets at the Guidance Center.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="angela.cruz@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Angela
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Gabriel Tan -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Gabriel Tan</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Lead DSA Tutor &amp; Tech Intern</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">gabriel.tan@kld.edu.ph · BSCS 4th Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Hired peer tutor in Algorithms (₱100/hr) with an upcoming technical interview at TechVanguard Solutions.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="gabriel.tan@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Gabriel
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Ralph Matthew Santos -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Ralph Matthew Santos</span>
                                                    <span class="badge bg-warning text-dark" style="font-size: 10px;">Pending Document Review</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">ralph.santos@kld.edu.ph · BSIS 1st Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Newly registered student whose COR verification is queued for review by the Administrator.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="ralph.santos@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Ralph
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Kimberly Joy Ramos -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Kimberly Joy Ramos</span>
                                                    <span class="badge bg-danger text-white" style="font-size: 10px;">Rejected COR Proof</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">kimberly.ramos@kld.edu.ph · Midwifery 2nd Yr</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Demonstrates the rejection feedback loop: uploaded blurry proof, with guidance to re-upload clear COR.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="kimberly.ramos@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Kimberly
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Alwinson Bustamante -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Alwinson Bustamante</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Lead Lab Tech</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">abustamante@kld.edu.ph · BSIS 2nd Year</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Lead Student Systems Developer &amp; Senior Laboratory Tech Assistant (Password: @Alwinson100).</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="abustamante@kld.edu.ph" data-pass="@Alwinson100">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Alwinson
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: CAMPUS OFFICES -->
                            <div class="tab-pane fade" id="pane-p-offices" role="tabpanel">
                                <div class="row g-3">
                                    <!-- Registrar -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Office of the University Registrar</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">registrar@kld.edu.ph · Prof. Roberto Hernandez</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Admin Building Room 102. Managing Student Records Assistant postings (4 total slots, 2 filled, 2 active applicants).</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="registrar@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Registrar
                                            </button>
                                        </div>
                                    </div>
                                    <!-- MIS -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Management Information Systems (MIS)</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">itdept@kld.edu.ph · Engr. Clara Villanueva</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Tech Building 3rd Floor. Managing Computer Lab Technical Assistant &amp; Peer Tutor DSA requisitions.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="itdept@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as MIS Director
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Guidance -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Guidance &amp; Counseling Center</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">guidance@kld.edu.ph · Dr. Evelyn Ramos</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Student Center Room 204. Overseeing student peer facilitators, testing materials, and mental wellness initiatives.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="guidance@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Guidance Head
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Library Services -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">University Library Services</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">library@kld.edu.ph · Ms. Teresa Cruz</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">KLD Main Library 2nd Floor. Overseeing book cataloging, Dewey Decimal sorting, and student study hub assistants.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="library@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Chief Librarian
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Science Labs -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Science &amp; Nursing Skills Labs</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">sciencelab@kld.edu.ph · Prof. Corazon Navarro</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Science Building Lab 301. Managing Nursing Simulation Ward, Chemistry apparatus prep, and Clinic support aides.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="sciencelab@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Labs Head
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Athletics -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Athletics &amp; Sports Development</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">athletics@kld.edu.ph · Coach Gary Belmonte</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Sports Complex Office 101. Managing Gymnasium Custodians, Tournament Scorekeepers, and Track Oval Field Marshals.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="athletics@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Athletics Director
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Admissions & Scholarships -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Admissions &amp; Scholarship Office</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">scholarships@kld.edu.ph · Mr. Arnold Santos</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Admin Building Window 4. Managing student assistant encoders for city LGU scholarship grants and payrolls.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="scholarships@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Scholarship Head
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Cashier & Bursar -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">University Cashier &amp; Bursar</span>
                                                    <span class="badge bg-primary text-white" style="font-size: 10px;">University Office</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">bursar@kld.edu.ph · Mr. Fernando Ocampo</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Admin Building Cashier Wing. Oversight of student assistant payroll processing and official disbursement.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="bursar@kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Head Cashier
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: ACCREDITED PARTNERS -->
                            <div class="tab-pane fade" id="pane-p-partners" role="tabpanel">
                                <div class="row g-3">
                                    <!-- TechVanguard -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">TechVanguard Solutions Inc.</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Verified Partner</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">techvanguard@partner.kld.edu.ph · Atty. Alcantara</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Dasma Tech Park Suite 401. Software engineering firm hiring Junior Web Devs &amp; React Frontend Interns (₱120/hr).</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="techvanguard@partner.kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as TechVanguard
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Creative Media -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Dasma Creative Media Studio</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Verified Partner</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">mediahub@partner.kld.edu.ph · Marco V. Domingo</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Creative Hub Suite 201. Multimedia production agency employing student video editors and graphic designers (₱95/hr).</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="mediahub@partner.kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Creative Media
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Campus Cafe -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Campus Cafe &amp; Co.</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Verified Concessionaire</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">campuscafe@partner.kld.edu.ph · Chef Patricia Reyes</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Student Pavilion Food Arcade. Student coffee concessionaire providing paid on-site barista training (₱90/hr).</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="campuscafe@partner.kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Campus Cafe
                                            </button>
                                        </div>
                                    </div>
                                    <!-- GreenLeaf Bookstore -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">GreenLeaf Academic Books</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Verified Partner</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">greenleaf@partner.kld.edu.ph · Ms. Dimaculangan</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Commercial Arcade Unit 105. Campus bookstore hiring inventory stock clerks and POS counter cashiers (₱85/hr).</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="greenleaf@partner.kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as GreenLeaf
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Cavite BPO -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Cavite BPO Solutions Hub</span>
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">Verified Partner</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">cavitebpo@partner.kld.edu.ph · Roland Dela Torre</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Robinsons Dasma Tech Wing. Customer support center offering flexible evening &amp; weekend shifts (₱110/hr).</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="cavitebpo@partner.kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Cavite BPO
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Apex Robotics (Pending) -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">Apex Robotics PH Corp.</span>
                                                    <span class="badge bg-warning text-dark" style="font-size: 10px;">Pending Accreditation</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">apexrobotics@partner.kld.edu.ph · Engr. Ramirez</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Innovation Tower 4th Flr. Demonstrates the pending partner approval flow in Admin Users directory.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="apexrobotics@partner.kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as Apex Robotics
                                            </button>
                                        </div>
                                    </div>
                                    <!-- CodeCrafted (Rejected) -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <span class="fw-bold text-ink">CodeCrafted Interactive Labs</span>
                                                    <span class="badge bg-danger text-white" style="font-size: 10px;">Rejected · Missing Permit</span>
                                                </div>
                                                <div class="text-muted-custom small mb-2 font-monospace" style="font-size: 11px;">codecrafted@partner.kld.edu.ph · Michelle Soriano</div>
                                                <p class="text-muted-custom small mb-3" style="font-size: 12px;">Demonstrates the partner rejection flow (missing Mayor's Permit / BIR Form 2303) and re-upload guidance.</p>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success w-100" data-select-persona="codecrafted@partner.kld.edu.ph" data-pass="Password123!">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In as CodeCrafted
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: ADMIN -->
                            <div class="tab-pane fade" id="pane-p-admin" role="tabpanel">
                                <div class="p-4 bg-light rounded-3 border border-line text-center">
                                    <div class="mb-3">
                                        <span class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle p-3 shadow-sm" style="width: 54px; height: 54px;">
                                            <i class="bi bi-shield-lock-fill text-success fs-3"></i>
                                        </span>
                                    </div>
                                    <h5 class="fw-bold text-ink mb-1">KLD Campus System Administrator</h5>
                                    <div class="text-muted-custom font-monospace small mb-3">admin@kld.edu.ph · Student Affairs &amp; Services (SASO)</div>
                                    <p class="text-muted-custom small mx-auto mb-4" style="max-width: 520px;">
                                        Comprehensive administrative oversight across all 30 user accounts, 20 active requisitions, 22 applications, document verification queues, student profile requests, and career updates.
                                    </p>
                                    <button type="button" class="btn btn-pill px-4" data-select-persona="admin@kld.edu.ph" data-pass="Password123!">
                                        <i class="bi bi-box-arrow-in-right me-2"></i> SIGN IN AS CAMPUS ADMINISTRATOR
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-line px-4 py-3 bg-cream">
                        <div class="small text-muted-custom me-auto">
                            <i class="bi bi-info-circle me-1"></i> All demo accounts share password: <code>Password123!</code> (Alwinson: <code>@Alwinson100</code>).
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

<script src="assets/js/password-strength.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('login-email');
    const passInput = document.getElementById('login-password');
    const demoBtns = document.querySelectorAll('[data-demo-email]');
    const quickSelect = document.getElementById('quickPersonaSelect');
    const modalElement = document.getElementById('modalAllPersonas');

    function populateCredentials(email, password) {
        if (emailInput && passInput) {
            emailInput.value = email;
            passInput.value = password || 'Password123!';
            emailInput.focus();
        }
    }

    demoBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const email = this.getAttribute('data-demo-email');
            const pass = this.getAttribute('data-demo-pass') || 'Password123!';
            populateCredentials(email, pass);
        });
    });

    if (quickSelect) {
        quickSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                populateCredentials(selectedOpt.value, selectedOpt.getAttribute('data-pass') || 'Password123!');
            }
        });
    }

    document.querySelectorAll('[data-select-persona]').forEach(btn => {
        btn.addEventListener('click', function() {
            const email = this.getAttribute('data-select-persona');
            const pass = this.getAttribute('data-pass') || 'Password123!';
            populateCredentials(email, pass);
            if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modalInstance.hide();
            }
        });
    });
});
</script>

