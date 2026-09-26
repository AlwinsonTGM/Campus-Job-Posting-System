<?php
/**
 * Campus Job Posting System - Data Privacy Policy
 * Archetype F: Prose (COAL101 Blueprint)
 * Compliant with Philippine Republic Act No. 10173 (Data Privacy Act of 2012)
 * Comprehensive Statutory Statement & Third-Party AI Data Processing Disclosure
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Data Privacy Policy & AI Disclosure (RA 10173)';
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
                            <div class="text-muted-custom small">Review the statutory privacy terms and AI processing disclosures below before completing registration.</div>
                        </div>
                    </div>
                    <button type="button" onclick="smartGoBack('register.php')" class="btn-pill btn-sm d-inline-flex align-items-center gap-2" style="padding: 0.45rem 1.15rem; font-size: 0.85rem;">
                        <i class="bi bi-arrow-left"></i> Return to Registration
                    </button>
                </div>
                <?php endif; ?>

                <!-- Page Header with Smart Return Action -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom border-line">
                    <div>
                        <h1 class="page-head-title mb-1">Data Privacy Policy &amp; AI Processing Disclosure</h1>
                        <p class="text-muted-custom small mb-0">
                            <strong>Effective Date:</strong> September 26, 2026 &bull; Official Statutory Compliance Statement under Republic Act No. 10173
                        </p>
                    </div>
                    <div>
                        <button type="button" onclick="smartGoBack('<?= $fallback_url ?>')" class="btn-pill-outline d-inline-flex align-items-center gap-2" style="padding: 0.45rem 1.15rem; font-size: 0.9rem;">
                            <i class="bi bi-arrow-left"></i> <?= $from_register ? 'Return to Registration' : 'Go Back' ?>
                        </button>
                    </div>
                </div>

                <!-- Structured Versioning & Statutory Metadata Bar -->
                <div class="card-paper bg-cream p-3 mb-4 border border-line">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <span class="badge bg-ink text-white px-2 py-1 small">
                                <i class="bi bi-shield-lock-fill text-accent me-1"></i> Privacy Version 2.4
                            </span>
                            <span class="small text-muted-custom">
                                <i class="bi bi-calendar2-check text-accent me-1"></i><strong>Last Updated:</strong> September 26, 2026
                            </span>
                            <span class="small text-muted-custom">
                                <i class="bi bi-bank text-accent me-1"></i><strong>Statutory Authority:</strong> RA 10173 &bull; NPC AI Processing Advisory
                            </span>
                        </div>
                        <span class="small text-muted-custom">
                            <i class="bi bi-patch-check-fill text-accent me-1"></i>KLD Institutional DPO Registered
                        </span>
                    </div>
                </div>

                <!-- Two-Column Layout Utilizing Workspace Free Space -->
                <div class="row g-4 g-xl-5">

                    <!-- Left Column: Privacy Policy Clauses -->
                    <div class="col-lg-8">
                        <div class="prose prose-wide">

                            <!-- Policy Introduction -->
                            <p class="lead text-muted-custom">
                                <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong>, through its Student Affairs and Services Office (SASO) and Data Protection Office, is strictly committed to protecting the privacy, confidentiality, and security of personal and academic data submitted by students, faculty coordinators, administrative supervisors, and accredited partner employers in full adherence with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, its Implementing Rules and Regulations (IRR), and National Privacy Commission (NPC) advisories on automated systems and artificial intelligence.
                            </p>

                            <!-- Section 1: Statutory Mandate & Processing Principles -->
                            <div id="sec-1" class="pt-2">
                                <h2 class="h4">1. Statutory Mandate &amp; Core Data Processing Principles</h2>
                                <p>
                                    All personal and sensitive personal data collected and processed through the KLD Campus Job Posting System (Campus Hire) is strictly governed by the foundational principles of:
                                </p>
                                <ul>
                                    <li><strong>Transparency:</strong> Data subjects are fully informed of the specific nature, purpose, extent, and third-party processors involved in the handling of their information before collection.</li>
                                    <li><strong>Legitimate Purpose:</strong> Data is collected and utilized solely for bona fide university student assistantship hiring, academic schedule conflict resolution, Daily Time Record (DTR) verification, and stipend disbursement.</li>
                                    <li><strong>Proportionality:</strong> Only the minimum necessary information required to evaluate student assistantship eligibility and facilitate department placements is collected and processed.</li>
                                </ul>
                            </div>

                            <!-- Section 2: Categories of Personal & Academic Data Collected -->
                            <div id="sec-2" class="pt-3">
                                <h2 class="h4">2. Categories of Personal &amp; Academic Data Collected</h2>
                                <p>
                                    Depending on your user role (Student Applicant, Department Supervisor, or Accredited Employer), we collect and store the following categories of information:
                                </p>
                                <ul>
                                    <li><strong>Personal Identification Data:</strong> Full legal name, official Student Identification Number, institutional email address (<code>@kld.edu.ph</code>), contact telephone numbers, and profile avatars.</li>
                                    <li><strong>Academic Standing &amp; Curriculum Records:</strong> Enrolled Academic Institute (e.g., Institute of Computing Studies, Institute of Engineering), degree program, current curriculum year level, General Weighted Average (GWA), and official Certificate of Registration (COR) / Study Load documents.</li>
                                    <li><strong>Employment Application Dossiers:</strong> Digital Curriculum Vitae (CV) / Resumes (PDF), formal cover letters, statements of qualifications, and emergency contact details.</li>
                                    <li><strong>Weekly Availability Schedule:</strong> 18-slot availability schedule matrices (Monday–Saturday across Morning, Afternoon, and Evening blocks) used to ensure work shifts do not overlap with lectures.</li>
                                    <li><strong>Technical Telemetry &amp; Audit Logs:</strong> IP addresses, browser user agent strings, session timestamps, and encrypted anti-CSRF token verification identifiers.</li>
                                </ul>
                            </div>

                            <!-- Section 3: Lawful Basis & Purposes of Data Processing -->
                            <div id="sec-3" class="pt-3">
                                <h2 class="h4">3. Lawful Basis &amp; Purposes of Data Processing</h2>
                                <p>
                                    Under Sections 12 and 13 of Republic Act No. 10173, the processing of personal data on this platform is lawful based on contractual necessity (work-study agreements), university statutory mandates, and explicit user consent:
                                </p>
                                <ol>
                                    <li><strong>Assistantship Eligibility Screening:</strong> Verifying minimum 12-unit active enrollment, minimum 2.50 GWA, and good moral standing with SASO.</li>
                                    <li><strong>Department Requisition Matching:</strong> Facilitating transparent applicant screening, review drawers, and candidate dossier evaluation by authorized campus hiring supervisors.</li>
                                    <li><strong>Academic Lecture Conflict Prevention:</strong> Cross-referencing student weekly schedules with department work shifts to enforce the <strong>20-hour weekly cap</strong> and guarantee zero classroom absence.</li>
                                    <li><strong>Timekeeping &amp; Payroll Disbursement:</strong> Endorsing verified Daily Time Records (DTR) to the University Cashier and City Accounting Office for student stipend and tuition allowance releases.</li>
                                    <li><strong>Official Credentialing:</strong> Generating verifiable SASO-certified Certificates of Service upon semester completion.</li>
                                </ol>
                            </div>

                            <!-- Section 4: AI Cloud Integration, NVIDIA NIM Services & Model Training Notice -->
                            <div id="sec-4" class="pt-3">
                                <div class="card-paper bg-cream p-4 border border-line">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-cpu-fill text-accent fs-4"></i>
                                        <h2 class="h4 mb-0 text-ink">4. Artificial Intelligence Assistant &amp; NVIDIA Cloud Training Disclosure</h2>
                                    </div>
                                    <span class="badge bg-warning text-dark px-2 py-1 small fw-bold mb-3 d-inline-block">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Mandatory Third-Party AI Data Processing Notice
                                    </span>

                                    <p class="small text-muted-custom mb-3">
                                        To provide real-time student guidance, job browsing assistance, FAQ navigation, and mock interview tips, the portal incorporates an interactive conversational AI Assistant ("Campus AI" / "KLD AI Robot"). Users must take careful note of the following technical architecture and data processing conditions:
                                    </p>

                                    <div class="card-paper p-3 mb-3 border border-line bg-surface">
                                        <h5 class="card-paper-title small fw-bold text-ink mb-2">
                                            <i class="bi bi-cloud-arrow-up text-accent me-2"></i>Third-Party Cloud Provider &amp; Model Training Disclosure
                                        </h5>
                                        <p class="small text-muted-custom mb-2">
                                            The Campus AI Assistant utilizes cloud inference microservices hosted by <strong>NVIDIA Corporation</strong> (via NVIDIA NIM endpoints at <code>https://integrate.api.nvidia.com/v1</code>) utilizing free-tier developer/evaluation API access.
                                        </p>
                                        <p class="small text-muted-custom mb-0">
                                            <strong>AI Model Training Notice:</strong> Under the standard developer terms of service governing free and evaluation AI API tiers, <strong>conversations, queries, text prompts, and model interactions submitted to the AI assistant may be logged, monitored, retained, and used by NVIDIA and its foundation model partners (such as Meta, Mistral AI, DeepSeek, and Google) for model research, safety monitoring, performance evaluation, reinforcement learning, and ongoing artificial intelligence training.</strong>
                                        </p>
                                    </div>

                                    <div class="card-paper p-3 mb-3 border border-line bg-surface">
                                        <h5 class="card-paper-title small fw-bold text-danger mb-2">
                                            <i class="bi bi-shield-slash text-danger me-2"></i>Strict Prohibition: Do Not Submit Sensitive Personal Data
                                        </h5>
                                        <p class="small text-muted-custom mb-0">
                                            Users are <strong>strictly instructed and warned NOT to enter any personal, academic, or sensitive confidential information</strong> into the AI chat interface. Do not input Student ID numbers, passwords, bank account details, GWA slips, home addresses, phone numbers, or confidential university documents into the chat. The assistant is intended solely for general public FAQs, work regulations, and general interview advice.
                                        </p>
                                    </div>

                                    <h5 class="card-paper-title small fw-bold text-ink mb-2">
                                        <i class="bi bi-layers-half text-accent me-2"></i>Data Boundary: What Stays on KLD University Servers
                                    </h5>
                                    <ul class="small text-muted-custom mb-3">
                                        <li><strong>Resumes &amp; Documents:</strong> Official student profiles, uploaded PDF resumes, Certificates of Registration (COR), and job application submissions are stored strictly in protected local JSON and server datastores and are <strong>NEVER transmitted to NVIDIA or external AI models</strong>.</li>
                                        <li><strong>Isolated Payload:</strong> Only the specific message text typed into the chat input bar and recent conversation turns are dispatched over encrypted TLS/HTTPS to the cloud AI gateway.</li>
                                        <li><strong>Local Heuristic Fallback:</strong> If offline, rate-limited, or when cloud endpoints are unreachable, the platform activates a <em>Local Guided Mode</em> on the university server, resolving questions entirely via local regex rules with zero external network transmission.</li>
                                        <li><strong>Voluntary Use:</strong> Interacting with the AI Assistant is completely voluntary. You are never required to use the chatbot to search, apply for, or track campus job applications.</li>
                                    </ul>

                                    <div class="small text-muted-custom">
                                        For more information regarding NVIDIA's cloud privacy practices, you may review the official <a href="https://www.nvidia.com/en-us/about-nvidia/privacy-policy/" target="_blank" rel="noopener noreferrer" class="text-ink fw-bold text-decoration-underline">NVIDIA Privacy Policy</a>.
                                    </div>
                                </div>
                            </div>

                            <!-- Section 5: Data Protection & Security Controls -->
                            <div id="sec-5" class="pt-3">
                                <h2 class="h4">5. Data Protection, Storage Security, &amp; Access Controls</h2>
                                <p>
                                    The university implements rigorous technical, organizational, and physical security measures to protect personal data from unauthorized access, accidental loss, alteration, or unlawful disclosure:
                                </p>
                                <ul>
                                    <li><strong>Role-Based Access Control (RBAC):</strong> Student application dossiers and resumes are visible strictly to verified supervisors of the specific hiring department where the application was submitted, SASO officers, and system administrators. Cross-department or peer student access is barred.</li>
                                    <li><strong>Cryptographic Safeguards:</strong> Passwords are cryptographically salted and hashed using <strong>Bcrypt (<code>PASSWORD_DEFAULT</code>)</strong>. Plaintext passwords are never recorded in database stores or system logs.</li>
                                    <li><strong>Anti-CSRF &amp; Secure Sessions:</strong> Every state-altering HTTP POST request requires cryptographic session tokens to prevent Cross-Site Request Forgery. Direct file access to system datastores is restricted via web server security directives (<code>.htaccess</code>).</li>
                                </ul>
                            </div>

                            <!-- Section 6: Data Retention & Disposal Policies -->
                            <div id="sec-6" class="pt-3">
                                <h2 class="h4">6. Data Retention &amp; Disposal Policies</h2>
                                <ul>
                                    <li><strong>Active Application Lifecycles:</strong> Student assistantship applications and hiring dossiers are retained on active university datastores for the duration of the active academic term plus one (1) succeeding semester for audit reconciliation.</li>
                                    <li><strong>Archival &amp; Disposal:</strong> Following the expiration of the retention window, application documents and withdrawn submissions are securely anonymized or permanently purged from system datastores in accordance with National Archives of the Philippines (NAP) standards and KLD Records Management Office circulars.</li>
                                </ul>
                            </div>

                            <!-- Section 7: Statutory Rights of the Data Subject -->
                            <div id="sec-7" class="pt-3">
                                <h2 class="h4">7. Statutory Rights of the Data Subject (RA 10173)</h2>
                                <p>
                                    Under Chapter IV, Section 16 of Republic Act No. 10173, every registered student, faculty member, and employer retains full statutory rights over their personal information:
                                </p>
                                <ul>
                                    <li><strong>Right to be Informed:</strong> To know whether personal data pertaining to you is being processed, the categories of data collected, and the third parties with whom data may be shared.</li>
                                    <li><strong>Right to Access:</strong> To inspect and obtain a digital copy of your personal data, application histories, and recorded availability matrices directly via your Student Dashboard.</li>
                                    <li><strong>Right to Rectification:</strong> To dispute inaccuracies in your profile data and submit an official Academic Profile Change Request with supporting COR documentation.</li>
                                    <li><strong>Right to Erasure / Blocking:</strong> To withdraw pending job applications prior to supervisor review or request the suspension or removal of non-mandatory account information.</li>
                                    <li><strong>Right to Object:</strong> To object to data processing for purposes beyond mandatory assistantship administration, or to decline interacting with the AI chat feature without penalty.</li>
                                    <li><strong>Right to File a Complaint:</strong> To lodge a formal grievance before the KLD Data Protection Officer or the <strong>National Privacy Commission (NPC)</strong> at <code>complaints@privacy.gov.ph</code> if your data privacy rights have been violated.</li>
                                </ul>
                            </div>

                            <!-- Section 8: Security Incident & Data Breach Management -->
                            <div id="sec-8" class="pt-3">
                                <h2 class="h4">8. Security Incident &amp; Data Breach Management</h2>
                                <p>
                                    In the event of a confirmed security incident, unauthorized intrusion, or personal data breach affecting platform users, the university's Computer Emergency Response Team (CERT) and DPO will execute the statutory Incident Response Protocol.
                                </p>
                                <p>
                                    In accordance with NPC Circular 16-03, when a breach involves sensitive personal information that poses a real risk of serious harm to data subjects, the university will notify the National Privacy Commission and the affected users within <strong>seventy-two (72) hours</strong> of breach confirmation, detailing remedial actions and protective guidance.
                                </p>
                            </div>

                            <!-- Section 9: Data Protection Officer (DPO) Contact Information -->
                            <div id="sec-9" class="pt-3">
                                <h2 class="h4">9. Data Protection Officer (DPO) &amp; Compliance Office</h2>
                                <p>
                                    For inquiries, formal requests to exercise data subject rights, or concerns regarding the processing of your personal data or AI assistant telemetry, you may contact the designated University Data Protection Officer:
                                </p>

                                <div class="card-paper bg-cream p-4 mt-3 border border-line">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="icon-circle icon-circle-dark">
                                            <i class="bi bi-person-badge text-white"></i>
                                        </div>
                                        <div>
                                            <h4 class="card-paper-title mb-0">University Data Protection Office</h4>
                                            <span class="small text-muted-custom">Compliance &amp; Legal Division &bull; Kolehiyo ng Lungsod ng Dasmariñas</span>
                                        </div>
                                    </div>
                                    <hr class="border-line my-2">
                                    <div class="small text-muted-custom d-flex flex-column gap-1">
                                        <div><i class="bi bi-geo-alt text-accent me-2"></i><strong>Office Location:</strong> Room 201, Student Affairs &amp; Administration Building, KLD Main Campus</div>
                                        <div><i class="bi bi-envelope text-accent me-2"></i><strong>Official DPO Email:</strong> <a href="mailto:dataprivacy@kld.edu.ph" class="text-ink fw-bold">dataprivacy@kld.edu.ph</a></div>
                                        <div><i class="bi bi-telephone text-accent me-2"></i><strong>Campus Trunkline:</strong> (02) 8920-1000 loc. 105 / 402</div>
                                        <div><i class="bi bi-shield-check text-accent me-2"></i><strong>Office Hours:</strong> Monday &ndash; Friday, 8:00 AM &ndash; 5:00 PM (PHT)</div>
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
                                <nav class="toc-nav d-flex flex-column gap-1" style="max-height: 480px; overflow-y: auto; padding-right: 4px;">
                                    <a href="#sec-1" class="toc-link small"><i class="bi bi-chevron-right"></i> 1. Statutory Mandate</a>
                                    <a href="#sec-2" class="toc-link small"><i class="bi bi-chevron-right"></i> 2. Information Collected</a>
                                    <a href="#sec-3" class="toc-link small"><i class="bi bi-chevron-right"></i> 3. Purposes of Processing</a>
                                    <a href="#sec-4" class="toc-link small fw-bold text-accent"><i class="bi bi-cpu"></i> 4. AI &amp; NVIDIA Training Disclosure</a>
                                    <a href="#sec-5" class="toc-link small"><i class="bi bi-chevron-right"></i> 5. Data Security Controls</a>
                                    <a href="#sec-6" class="toc-link small"><i class="bi bi-chevron-right"></i> 6. Data Retention Policies</a>
                                    <a href="#sec-7" class="toc-link small"><i class="bi bi-chevron-right"></i> 7. Data Subject Rights</a>
                                    <a href="#sec-8" class="toc-link small"><i class="bi bi-chevron-right"></i> 8. Breach Management</a>
                                    <a href="#sec-9" class="toc-link small"><i class="bi bi-chevron-right"></i> 9. DPO Contact Office</a>
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
                                        <i class="bi bi-cpu text-accent fs-6 mt-1"></i>
                                        <div><strong>NVIDIA AI Cloud Notice:</strong> Free-tier AI chats may be logged &amp; used by NVIDIA for model training. Do not input personal data.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-file-earmark-lock text-accent fs-6 mt-1"></i>
                                        <div><strong>Resumes Kept Local:</strong> Resumes and student CORs are never sent to external AI services.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check2-circle text-accent fs-6 mt-1"></i>
                                        <div><strong>Zero Commercial Sale:</strong> Resumes and contact info are never sold to external marketing entities.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-person-check text-accent fs-6 mt-1"></i>
                                        <div><strong>Strict RBAC:</strong> Only designated department hiring supervisors can view candidate dossiers.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-shield-shaded text-accent fs-6 mt-1"></i>
                                        <div><strong>Full Statutory Rights:</strong> You can request data updates or withdraw applications anytime under RA 10173.</div>
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
