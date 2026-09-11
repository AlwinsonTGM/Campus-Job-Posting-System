<?php
/**
 * Campus Job Posting System - Data Privacy Policy
 * Archetype F: Prose (COAL101 Blueprint)
 * Compliant with Philippine Republic Act No. 10173 (Data Privacy Act of 2012)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Data Privacy Policy (RA 10173)';
$from_register = isset($_GET['from']) && $_GET['from'] === 'register';
$fallback_url = $from_register ? 'register.php' : 'index.php';

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-4 py-lg-5">
            <div class="container-paper">

                <?php if ($from_register): ?>
                <!-- Contextual Registration Notice -->
                <div class="card-paper bg-cream p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border border-line">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle icon-circle-dark flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-shield-check text-accent"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-ink small">Account Registration in Progress</div>
                            <div class="text-muted-custom small">Review the Data Privacy Policy under RA 10173 below. When ready, return to complete your account submission.</div>
                        </div>
                    </div>
                    <button type="button" onclick="smartGoBack('register.php')" class="btn-pill btn-sm d-inline-flex align-items-center gap-2" style="padding: 0.45rem 1.15rem; font-size: 0.85rem;">
                        <i class="bi bi-arrow-left"></i> Return to Registration
                    </button>
                </div>
                <?php endif; ?>

                <!-- Page Header with Smart Return Action -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-line">
                    <div>
                        <h1 class="page-head-title mb-1">Data Privacy Policy</h1>
                        <p class="text-muted-custom small mb-0">
                            <strong>Last updated:</strong> August 27, 2026 &bull; Official Statutory Compliance Statement (Republic Act No. 10173)
                        </p>
                    </div>
                    <div>
                        <button type="button" onclick="smartGoBack('<?= $fallback_url ?>')" class="btn-pill-outline d-inline-flex align-items-center gap-2" style="padding: 0.45rem 1.15rem; font-size: 0.9rem;">
                            <i class="bi bi-arrow-left"></i> <?= $from_register ? 'Return to Registration' : 'Go Back' ?>
                        </button>
                    </div>
                </div>

                <!-- Two-Column Layout Utilizing Workspace Free Space -->
                <div class="row g-4 g-xl-5">

                    <!-- Left Column: Privacy Policy Clauses -->
                    <div class="col-lg-8">
                        <div class="prose prose-wide">

                            <!-- Policy Introduction -->
                            <p class="lead text-muted-custom">
                                The Campus Job Posting System is committed to safeguarding personal, academic, and sensitive information submitted by students and campus department representatives in strict compliance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> of the Philippines.
                            </p>

                            <!-- Section 1: Collected Information -->
                            <div id="sec-1" class="pt-2">
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
                            </div>

                            <!-- Section 2: Purpose of Collection -->
                            <div id="sec-2" class="pt-3">
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
                            </div>

                            <!-- Section 3: Data Protection & Mock Session Lifecycle -->
                            <div id="sec-3" class="pt-3">
                                <h2 class="h4">3. Data Protection &amp; Platform Security</h2>
                                <p>
                                    Under no circumstances will student resumes, contact numbers, or academic credentials be sold, shared, or disclosed to third-party commercial marketing firms. Access is restricted exclusively to authorized university department supervisors, campus hiring managers, and Career Services administrators.
                                </p>
                                <p>
                                    Within this platform environment, runtime state is managed securely via encrypted PHP Session lifecycles (<code>$_SESSION</code>) and structured JSON datastores (<code>data/*.json</code>) protected against unauthorized direct file access.
                                </p>
                            </div>

                            <!-- Section 4: Rights of the Data Subject -->
                            <div id="sec-4" class="pt-3">
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
                            </div>

                            <!-- Section 5: Data Protection Officer Contact -->
                            <div id="sec-5" class="pt-3">
                                <h2 class="h4">5. Data Protection Officer (DPO) Contact</h2>
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
                                            <span class="small text-muted-custom">Campus Career Services &amp; Compliance Division</span>
                                        </div>
                                    </div>
                                    <hr class="border-line my-2">
                                    <div class="small text-muted-custom d-flex flex-column gap-1">
                                        <div><i class="bi bi-geo-alt text-accent me-2"></i><strong>Location:</strong> Room 201, Student Affairs &amp; Administration Building</div>
                                        <div><i class="bi bi-envelope text-accent me-2"></i><strong>Official Email:</strong> dataprivacy@kld.edu.ph</div>
                                        <div><i class="bi bi-telephone text-accent me-2"></i><strong>Campus Trunkline:</strong> (02) 8920-1000 loc. 105</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Return Action -->
                            <div class="pt-4 mt-4 border-top border-line d-flex flex-wrap justify-content-center gap-3">
                                <button type="button" onclick="smartGoBack('<?= $fallback_url ?>')" class="btn-pill d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-arrow-left"></i> <?= $from_register ? 'Return to Registration' : 'Go Back to Previous Page' ?>
                                </button>
                                <a href="index.php" class="btn-pill-outline d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-house"></i> Home
                                </a>
                            </div>

                        </div>
                    </div>

                    <!-- Right Column: Sticky Sidebar -->
                    <div class="col-lg-4">
                        <div class="d-flex flex-column gap-4" style="position: sticky; top: 96px;">

                            <!-- Table of Contents Card -->
                            <div class="card-paper p-4 border border-line">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-line">
                                    <i class="bi bi-list-nested text-accent fs-5"></i>
                                    <h5 class="card-paper-title mb-0" style="font-size: 1.05rem;">Table of Contents</h5>
                                </div>
                                <nav class="toc-nav">
                                    <a href="#sec-1" class="toc-link"><i class="bi bi-chevron-right"></i> 1. Collected Information</a>
                                    <a href="#sec-2" class="toc-link"><i class="bi bi-chevron-right"></i> 2. Purpose of Collection</a>
                                    <a href="#sec-3" class="toc-link"><i class="bi bi-chevron-right"></i> 3. Data Protection</a>
                                    <a href="#sec-4" class="toc-link"><i class="bi bi-chevron-right"></i> 4. Data Subject Rights</a>
                                    <a href="#sec-5" class="toc-link"><i class="bi bi-chevron-right"></i> 5. DPO Contact Office</a>
                                </nav>
                            </div>

                            <!-- Privacy Safeguards Card -->
                            <div class="card-paper bg-cream p-4 border border-line">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-line">
                                    <i class="bi bi-shield-check text-accent fs-5"></i>
                                    <h5 class="card-paper-title mb-0" style="font-size: 1.05rem;">Privacy Commitments</h5>
                                </div>
                                <div class="d-flex flex-column gap-3 small text-muted-custom">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check2-circle text-accent fs-6 mt-1"></i>
                                        <div><strong>Zero Third-Party Sharing:</strong> Resumes and contact info are never sold to external marketing entities.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check2-circle text-accent fs-6 mt-1"></i>
                                        <div><strong>Role-Based Access:</strong> Only designated department hiring supervisors can view applicant dossiers.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check2-circle text-accent fs-6 mt-1"></i>
                                        <div><strong>Full Statutory Rights:</strong> You can request data updates or withdraw applications anytime.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick DPO Help Card -->
                            <div class="card-paper p-4 border border-line">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-envelope-check text-accent fs-5"></i>
                                    <h5 class="card-paper-title mb-0" style="font-size: 1.05rem;">Need Privacy Help?</h5>
                                </div>
                                <p class="small text-muted-custom mb-3">
                                    Our Data Protection Officer handles statutory inquiries, corrections, and consent management.
                                </p>
                                <a href="mailto:dataprivacy@kld.edu.ph" class="btn-pill-outline w-100 d-inline-flex align-items-center justify-content-center gap-2 small">
                                    <i class="bi bi-envelope"></i> Email DPO Team
                                </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
