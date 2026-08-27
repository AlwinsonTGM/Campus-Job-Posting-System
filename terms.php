<?php
/**
 * Campus Job Posting System - Terms of Service
 * Archetype F: Prose (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Terms of Service & Campus Work Regulations';

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <div class="prose">
                    <!-- Page Head & Last Updated Line -->
                    <div class="mb-4 pb-3 border-bottom border-line">
                        <span class="pill-badge mb-2">
                            <i class="bi bi-file-earmark-text text-accent"></i> Campus Employment Guidelines
                        </span>
                        <h1 class="page-head-title">Terms of Service</h1>
                        <p class="text-muted-custom small mb-0">
                            <strong>Last updated:</strong> August 27, 2026 &bull; Institutional Work Policy
                        </p>
                    </div>

                    <!-- Policy Introduction -->
                    <p class="lead text-muted-custom">
                        These Terms of Service govern the usage of the Campus Job Posting System by students, university departments, academic laboratories, and accredited partner employers. By accessing the platform, all parties agree to comply with the institutional guidelines detailed below.
                    </p>

                    <!-- Section 1: Eligibility Clause -->
                    <h2 class="h4">1. Eligibility Clause</h2>
                    <p>
                        Only currently enrolled bona fide undergraduate or graduate students in good academic standing carrying at least 12 academic units and with no active disciplinary records may apply for student assistantships. A valid Certificate of Registration (COR) / Study Load must be presented upon request.
                    </p>

                    <!-- Section 2: Work Hour Regulations (20-Hour Rule) -->
                    <h2 class="h4">2. Work Hour Regulations (20-Hour Rule)</h2>
                    <p>
                        To ensure academic priorities remain uncompromised, student assistants are strictly restricted to working a maximum of <strong>20 hours per week</strong> during regular academic semesters.
                    </p>
                    <div class="card-paper bg-cream p-3 mb-3 border border-line">
                        <div class="d-flex align-items-center gap-2 text-ink fw-bold small mb-1">
                            <i class="bi bi-clock-history text-accent fs-5"></i>
                            <span>Academic Safeguard Policy</span>
                        </div>
                        <p class="small text-muted-custom mb-0">
                            During official semester breaks or summer terms, working hours may expand up to a maximum of <strong>40 hours per week</strong>, subject to departmental budget allocation and supervisor approval.
                        </p>
                    </div>

                    <!-- Section 3: Employer & Office Obligations -->
                    <h2 class="h4">3. Employer & Office Obligations</h2>
                    <p>
                        Participating campus departments, laboratories, and accredited partner organizations agree to:
                    </p>
                    <ul>
                        <li>Provide accurate job descriptions, clear duty expectations, and ethical workplace environments.</li>
                        <li>Disburse fair and timely stipend compensation in accordance with university hourly rate schedules.</li>
                        <li>Mandate schedule flexibility and duty adjustments during designated midterm and final examination weeks.</li>
                        <li>Provide signed Daily Time Records (DTR) promptly to avoid stipend disbursement delays.</li>
                    </ul>

                    <!-- Section 4: Code of Conduct -->
                    <h2 class="h4">4. Code of Conduct & Zero Tolerance Policy</h2>
                    <p>
                        The university enforces a strict zero-tolerance policy against:
                    </p>
                    <ul>
                        <li><strong>Fraudulent Credentials:</strong> Submitting falsified academic grades, altered study loads, or forged identification.</li>
                        <li><strong>Ghost Attendance:</strong> Falsification of Daily Time Records (DTR) or clocking hours without performing assigned office duties.</li>
                        <li><strong>Harassment & Misconduct:</strong> Any form of verbal, physical, or discriminatory harassment in the campus workplace.</li>
                    </ul>
                    <p>
                        Violations will result in immediate termination of the assistantship contract, forfeiture of pending stipends, and referral to the University Disciplinary Board.
                    </p>

                    <!-- Section 5: Disclaimer & Termination -->
                    <h2 class="h4">5. Disclaimer & Termination</h2>
                    <p>
                        The University Administration reserves the right to suspend or revoke posting privileges of any employer or applying privileges of any student for non-compliance with these Terms. Either party may terminate an assistantship arrangement upon providing one (1) week prior written notice to the department supervisor and Career Services Office.
                    </p>

                    <!-- Return CTA -->
                    <div class="pt-4 mt-4 border-top border-line text-center">
                        <a href="index.php" class="btn-pill-outline">
                            <i class="bi bi-arrow-left"></i> Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
