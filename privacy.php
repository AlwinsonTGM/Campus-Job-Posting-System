<?php
/**
 * Campus Job Posting System - Data Privacy Policy
 * Archetype F: Prose (COAL101 Blueprint)
 * Compliant with Philippine Republic Act No. 10173 (Data Privacy Act of 2012)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Data Privacy Policy (RA 10173)';

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
                        <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle mb-2">
                            <i class="bi bi-shield-check text-accent"></i> Republic Act No. 10173
                        </span>
                        <h1 class="page-head-title">Data Privacy Policy</h1>
                        <p class="text-muted-custom small mb-0">
                            <strong>Last updated:</strong> August 27, 2026 &bull; Official Compliance Statement
                        </p>
                    </div>

                    <!-- Policy Introduction -->
                    <p class="lead text-muted-custom">
                        The Campus Job Posting System is committed to safeguarding personal, academic, and sensitive information submitted by students and campus department representatives in strict compliance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> of the Philippines.
                    </p>

                    <!-- Section 1: Collected Information -->
                    <h2 class="h4">1. Collected Information</h2>
                    <p>
                        We collect and process only the minimum necessary information required to assess eligibility for campus student assistantships and office staffing requisitions:
                    </p>
                    <ul>
                        <li><strong>Student Identification:</strong> Student ID Number, Full Legal Name, and Institutional Email (<code>@kld.edu.ph</code>).</li>
                        <li><strong>Academic Information:</strong> Enrolled Course/Degree Program, Academic Year Level, and Academic Standing.</li>
                        <li><strong>Application Credentials:</strong> Digital Resume / Curriculum Vitae, statements of interest, and Cover Letters.</li>
                        <li><strong>Schedule Availability:</strong> Class schedule timetable and weekly vacant shift hours.</li>
                    </ul>

                    <!-- Section 2: Purpose of Collection -->
                    <h2 class="h4">2. Purpose of Collection</h2>
                    <p>
                        All collected personal data is processed strictly for the following legitimate university recruitment objectives:
                    </p>
                    <ol>
                        <li>Evaluating student eligibility for on-campus student assistantships and laboratory assignments.</li>
                        <li>Facilitating recruitment matching between campus academic offices, administrative divisions, and accredited partner employers.</li>
                        <li>Scheduling technical interviews and coordinating student work shifts to prevent conflict with lecture hours.</li>
                        <li>Submitting verified Daily Time Records (DTR) for student stipend and tuition allowance processing.</li>
                    </ol>

                    <!-- Section 3: Data Protection & Mock Session Lifecycle -->
                    <h2 class="h4">3. Data Protection & Mock Session Lifecycle</h2>
                    <p>
                        Under no circumstances will student resumes, contact numbers, or academic credentials be sold, shared, or disclosed to third-party commercial marketing firms. Access is restricted exclusively to authorized university department supervisors, campus hiring managers, and Career Services administrators.
                    </p>
                    <p>
                        Within this prototype platform environment, runtime state is managed securely via encrypted PHP Session lifecycles (<code>$_SESSION</code>) and structured JSON datastores (<code>data/*.json</code>) without permanent external database exposure.
                    </p>

                    <!-- Section 4: Rights of the Data Subject -->
                    <h2 class="h4">4. Rights of the Data Subject</h2>
                    <p>
                        In accordance with Republic Act No. 10173, student applicants and registered employers retain full statutory rights over their personal information:
                    </p>
                    <ul>
                        <li><strong>Right to Access:</strong> View all active application submissions and recorded profile data directly on the Student Dashboard.</li>
                        <li><strong>Right to Rectification:</strong> Request corrections or updates to inaccurate contact numbers or uploaded documents.</li>
                        <li><strong>Right to Erasure / Withdrawal:</strong> Withdraw submitted job applications at any time while the status remains <em>Pending Review</em>.</li>
                        <li><strong>Right to Object:</strong> Withhold non-mandatory demographic details without compromising baseline assistantship evaluation.</li>
                    </ul>

                    <!-- Section 5: Data Protection Officer Contact -->
                    <h2 class="h4">5. Data Protection Officer (DPO) Contact Block</h2>
                    <p>
                        For inquiries, concerns, or requests regarding the processing of your personal data under RA 10173, you may contact the designated University Data Protection Officer:
                    </p>

                    <div class="card-paper bg-cream p-4 mt-3">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="icon-circle icon-circle-dark">
                                <i class="bi bi-person-badge text-white"></i>
                            </div>
                            <div>
                                <h4 class="card-paper-title mb-0">University Data Protection Office</h4>
                                <span class="small text-muted-custom">Campus Career Services & Compliance Division</span>
                            </div>
                        </div>
                        <hr class="border-line my-2">
                        <div class="small text-muted-custom d-flex flex-column gap-1">
                            <div><i class="bi bi-geo-alt text-accent me-2"></i><strong>Location:</strong> Room 201, Student Affairs & Administration Building</div>
                            <div><i class="bi bi-envelope text-accent me-2"></i><strong>Official Email:</strong> dataprivacy@campus-hire.edu</div>
                            <div><i class="bi bi-telephone text-accent me-2"></i><strong>Campus Trunkline:</strong> (02) 8920-1000 loc. 105</div>
                        </div>
                    </div>

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
