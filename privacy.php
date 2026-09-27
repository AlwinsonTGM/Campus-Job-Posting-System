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

<style>
.prose, .prose p, .prose li {
    font-size: 1.0625rem;
    line-height: 1.85;
}
.prose p {
    text-align: justify;
    text-justify: inter-word;
    hyphens: auto;
    -webkit-hyphens: auto;
}
.prose p.lead {
    font-size: 1.15rem;
    line-height: 1.85;
    text-align: justify;
    text-justify: inter-word;
}
.prose .card-paper p {
    font-size: 1.02rem;
    line-height: 1.8;
    text-align: justify;
    text-justify: inter-word;
    hyphens: auto;
    -webkit-hyphens: auto;
}
.prose .card-paper h2,
.prose .card-paper h3,
.prose .card-paper h4 {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
</style>

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
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-line">
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

                <!-- Two-Column Layout Utilizing Workspace Free Space -->
                <div class="row g-4 g-xl-5">

                    <!-- Left Column: Privacy Policy Clauses -->
                    <div class="col-lg-8">
                        <div class="prose prose-wide">

                            <!-- Policy Introduction -->
                            <p class="lead text-muted-custom">
                                <strong>Kolehiyo ng Lungsod ng Dasmariñas (KLD)</strong>, operating through its Student Affairs and Services Office (SASO) in formal coordination with the Institutional Data Protection Office, affirms and enforces an uncompromising institutional commitment to protecting the privacy, confidentiality, integrity, and security of all personal, academic, and sensitive personal information entrusted to the College. This Policy applies comprehensively to all registered student applicants, active student assistants (SAs), faculty coordinators, academic deans, administrative division supervisors, and accredited campus partner employers who interact with the <strong>KLD Campus Job Posting System (Campus Hire)</strong>. Promulgated in strict compliance with the statutory provisions of <strong>Republic Act No. 10173</strong>, otherwise known as the <em>Data Privacy Act of 2012 (DPA)</em>, its Implementing Rules and Regulations (IRR), and all pertinent regulatory circulars and advisories promulgated by the National Privacy Commission (NPC), this document sets forth the comprehensive terms governing data acquisition, automated processing, cloud microservice integrations, retention schedules, and data subject rights enforcement across the institution.
                            </p>

                            <!-- Section 1: Statutory Mandate & Processing Principles -->
                            <div id="sec-1" class="pt-2">
                                <h2 class="h4">1. Statutory Mandate &amp; Core Data Processing Principles</h2>
                                <p>
                                    <strong>1.1 Statutory Authority, Legal Basis, and Institutional Scope.</strong> All personal data, sensitive personal information, and privileged communications collected, transmitted, processed, stored, or archived through the digital infrastructure of the Platform are subject to the governance and enforcement of Republic Act No. 10173, its Implementing Rules and Regulations, National Privacy Commission Advisory No. 2021-01 on automated processing systems, and applicable institutional circulars approved by the KLD Board of Trustees and the Office of the Vice President for Academic and Student Affairs (OVPASA). Under this statutory framework, Kolehiyo ng Lungsod ng Dasmariñas acts in the recognized capacity of a Personal Information Controller (PIC), exercising institutional oversight over data workflows, while designated third-party technical vendors and cloud infrastructure microservices operate strictly as Personal Information Processors (PIPs) under legally binding confidentiality covenants. Every user accessing, registering upon, or submitting digital documentation into the Platform falls within the protective scope of these statutory mandates, and any unauthorized processing, access, or data compromise shall be evaluated against both university administrative codes and national statutory penalties.
                                </p>
                                <p>
                                    <strong>1.2 Principle of Transparency and Prior Informed Consent.</strong> In adherence to Section 11(a) of Republic Act No. 10173, the University guarantees that all data subjects are provided with timely, lucid, and unambiguous disclosures concerning the precise nature, statutory purpose, institutional scope, storage boundaries, and retention limits of any personal data requested of them prior to or at the immediate moment of collection. Consent is procured through explicit, affirmative, and voluntary actions, including mandatory digital acknowledgments during account registration and distinct notification gates prior to document uploads or cloud-assisted interactions. The University refrains from deploying ambiguous blanket consents, pre-checked opt-in boxes, or opaque automated tracking mechanisms. Data subjects maintain the right to inspect how their records are routed within the institutional hierarchy, receive clarifications regarding data sharing with administrative payroll offices, and receive advance formal notice whenever this Privacy Policy or its underlying processing architecture undergoes substantive administrative revision.
                                </p>
                                <p>
                                    <strong>1.3 Principle of Legitimate Purpose and Secondary Use Restrictions.</strong> All collection, analysis, matching, and retention of user data within the Platform is legally anchored to declared, specified, and legitimate university functions directly related to the administration of student assistantships, student financial welfare grants, academic timetable coordination, and municipal work-study compliance. Personal data collected for assistantship placement, such as enrolled unit counts, General Weighted Averages, and daily attendance logs, shall never be repurposed, commercialized, monetized, sold, leased, or utilized for secondary activities incompatible with these recognized educational and administrative objectives. The University explicitly bars the exploitation of student dossiers for external advertising, commercial demographic aggregation, unsolicited third-party marketing, or unauthorized institutional profiling. Any secondary processing that lies outside the initial scope of assistantship administration requires the independent, written, and freely given consent of the affected data subject.
                                </p>
                                <p>
                                    <strong>1.4 Principle of Proportionality and Data Minimization.</strong> The processing of personal information across all modules of the Platform is strictly governed by the doctrine of proportionality, which mandates that only the minimum necessary information required to evaluate applicant qualifications, execute contractual agreements, resolve schedule conflicts, and endorse municipal allowance disbursements shall be requested and retained. The University strictly instructs hiring departments, academic deans, and administrative supervisors never to solicit, record, or demand excessive, intrusive, or irrelevant personal data, including but not limited to religious beliefs, political affiliations, sensitive personal medical histories unrelated to occupational health clearances, or non-educational familial assets. Whenever an administrative or verification objective can be reasonably accomplished through anonymized metrics, summarized statistical aggregates, or redacted documentation, the Platform enforces such minimization techniques as the institutional default.
                                </p>
                            </div>

                            <!-- Section 2: Categories of Personal & Academic Data Collected -->
                            <div id="sec-2" class="pt-3">
                                <h2 class="h4">2. Categories of Personal &amp; Academic Data Collected</h2>
                                <p>
                                    <strong>2.1 Personal Identification and Demographic Records.</strong> To establish institutional account identity, authenticate user authority, and facilitate legitimate communication between campus hiring units and candidates, the Platform collects fundamental biographical and contact markers. These markers encompass the student's full legal surname, given names, and middle name; official university-assigned Student Identification Number; institutional Google Workspace email address ending exclusively in <code>@kld.edu.ph</code>; active personal mobile contact numbers; secondary emergency contact names and phone numbers; residential municipality of residence for geographical logistics planning; and optional digital profile photographs uploaded by the user to personalize candidate portfolios and generate verified assistantship badges. For external accredited employers and faculty supervisors, the Platform similarly records corporate email addresses, physical office workstations, and professional designation titles to maintain an authenticated institutional trust environment.
                                </p>
                                <p>
                                    <strong>2.2 Academic Standing, Curriculum Enrollment, and Study Load.</strong> Because on-campus student assistantships are legislated as experiential work-study opportunities strictly subordinate to degree progression, the Platform collects detailed academic metrics to verify applicant eligibility and maintain compliance with KLD academic scholarship standards. This data includes the student's enrolled Academic Institute (such as the Institute of Computing Studies, Institute of Engineering, or Institute of Business and Public Administration); enrolled degree program and current curriculum year level; cumulative and preceding-semester General Weighted Average (GWA); active academic status (regular matriculation, irregular standing, or graduating status); and digital copies of official Certificate of Registration (COR) and study load documents. These records undergo automated validation to ensure applicants satisfy the prerequisite minimum twelve (12) enrolled academic unit threshold and have not incurred disqualifying academic deficiencies.
                                </p>
                                <p>
                                    <strong>2.3 Employment Application Dossiers and Curricular Portfolios.</strong> To enable campus hiring units, laboratory managers, and administrative office supervisors to evaluate candidate qualifications against specific job requisitions, the Platform stores formal employment application dossiers submitted voluntarily by students. These dossiers consist of comprehensive Curriculum Vitae (CV) and resumes submitted exclusively in Portable Document Format (PDF); customized statements of qualification and formal cover letters addressed to department heads; declared technical and interpersonal skillsets; language proficiencies; specialized software proficiencies; summaries of prior campus leadership or community service; digital portfolio links demonstrating creative, administrative, or programming capabilities; and supervisor letters of recommendation endorsing the student's work ethic and integrity.
                                </p>
                                <p>
                                    <strong>2.4 Work-Study Availability Matrices and Timetable Data.</strong> To rigorously enforce university policies against lecture overlap and protect students from academic compromise, the Platform captures structured schedule availability data via an interactive eighteen-slot weekly matrix. This matrix maps the student's available non-instructional hours from Monday through Saturday partitioned across Morning (8:00 AM to 12:00 PM), Afternoon (1:00 PM to 5:00 PM), and Evening (5:00 PM to 8:00 PM) intervals. The system dynamically cross-references these availability inputs against enrolled class blocks detailed on the student's submitted study load, ensuring that proposed departmental work shifts are scheduled strictly during authentic free periods, upholding the statutory twenty (20) hour weekly work cap, and preventing any conflict with lecture or laboratory obligations.
                                </p>
                                <p>
                                    <strong>2.5 Technical Telemetry, Cryptographic Identifiers, and System Audit Logs.</strong> Whenever any user accesses the Platform via mobile or desktop web browsers, system security daemons automatically log technical diagnostic and security telemetry necessary to prevent malicious activity, detect unauthorized intrusions, and preserve transactional non-repudiation. Logged parameters include originating public Internet Protocol (IP) addresses; browser user agent headers detailing device type, browser engine, and operating system versions; session initiation, activity, and expiration timestamps; cryptographic session identifiers; randomized anti-CSRF token verification nonces; failed login attempts; password reset dispatch histories; and detailed audit logs of critical administrative actions, including job posting approvals, applicant stage progressions, and Daily Time Record endorsements.
                                </p>
                            </div>

                            <!-- Section 3: Lawful Basis & Purposes of Data Processing -->
                            <div id="sec-3" class="pt-3">
                                <h2 class="h4">3. Lawful Basis &amp; Purposes of Data Processing</h2>
                                <p>
                                    Under Sections 12 and 13 of Republic Act No. 10173, the processing of personal information and sensitive personal data across this portal is recognized as lawful based on contractual necessity (the execution and performance of student assistantship work-study agreements), compliance with legal obligations imposed by local government auditing codes and CHED memoranda, the fulfillment of institutional statutory mandates of Kolehiyo ng Lungsod ng Dasmariñas, and explicit, freely given user consent. Data collected is applied specifically to the following administrative workflows:
                                </p>
                                <p>
                                    <strong>3.1 Assistantship Eligibility Verification and Standing Audit.</strong> The primary objective of data collection is the rigorous verification of student assistantship eligibility before any candidate is endorsed to a hiring department. The Student Affairs and Services Office (SASO) processes academic records to verify that applicants maintain active full-time matriculation with at least twelve (12) enrolled lecture units, satisfy the minimum institutional benchmark of a 2.50 cumulative GWA without unresolved failing grades, and possess clearance of good moral standing from the Office of Student Conduct. This evaluation ensures that public municipal educational assistance funds, institutional stipends, and student labor privileges are distributed fairly, transparently, and exclusively to qualified scholars in good standing.
                                </p>
                                <p>
                                    <strong>3.2 Department Requisition Matching and Supervisor Dossier Review.</strong> The Platform processes application records to empower authorized campus departments, including academic institutes, administrative offices, library divisions, and research centers, to review candidate profiles and fulfill approved student assistantship vacancies. Hiring supervisors utilize candidate dossiers, resumes, and declared skill matrices within a protected administrative drawer to evaluate applicant suitability, rank qualified applicants, coordinate on-campus interview schedules, and officially issue acceptance or declination decisions. This workflow eliminates disorganized physical resume handling, prevents accidental dossier loss, and guarantees that applicant personal data remains restricted strictly to supervisors with an authenticated institutional need-to-know.
                                </p>
                                <p>
                                    <strong>3.3 Lecture Conflict Prevention and Overwork Mitigation.</strong> Student academic progression remains the foundational priority of Kolehiyo ng Lungsod ng Dasmariñas. The Platform actively analyzes student availability matrices against department shift requisitions to programmatically enforce the statutory twenty (20) hour weekly labor cap during regular instructional semesters. By algorithmically disallowing shift assignments that intersect with enrolled classroom hours or laboratory blocks, the system prevents student fatigue, eliminates absenteeism caused by employment obligations, and ensures that student assistants maintain adequate time for study, research, examinations, and personal rest throughout the academic term.
                                </p>
                                <p>
                                    <strong>3.4 Timekeeping Authentication, DTR Audit, and Stipend Endorsement.</strong> To satisfy municipal accounting standards and Commission on Audit (COA) compliance regulations governing public stipend releases, the Platform facilitates the digital logging, supervisor authentication, and administrative audit of student Daily Time Records (DTR). Hours physically rendered by student assistants are recorded, verified by immediate department supervisors, and compiled into semi-monthly certified attendance summaries. These audited summaries are formally endorsed by the SASO Director to the University Cashier and the Dasmariñas City Accounting Office, serving as the sole legal basis for the computation, release, and audit reconciliation of student financial allowances and tuition grant stipends.
                                </p>
                                <p>
                                    <strong>3.5 Institutional Credentialing, Alumni Records, and Service Verification.</strong> Upon the satisfactory conclusion of an academic semester and verification that all rendered hours meet program standards, the Platform aggregates service data to generate official, verifiable SASO Certificates of Service. These documents are archived within permanent institutional records, providing student assistants with recognized co-curricular credentials, official service transcripts, and formal pre-graduation professional references that can be verified by future employers, graduate admissions committees, and civil service accrediting bodies upon the student's authorized request.
                                </p>
                            </div>

                            <!-- Section 4: AI Cloud Integration, NVIDIA NIM Services & Model Training Notice -->
                            <div id="sec-4" class="pt-3">
                                <div class="card-paper bg-cream p-4 border border-line">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="bi bi-cpu-fill text-accent fs-4 flex-shrink-0" style="line-height: 1; display: inline-flex; align-items: center;"></i>
                                        <h2 class="h4 text-ink mb-0 mt-0" style="margin-top: 0 !important; margin-bottom: 0 !important; line-height: 1.25;">4. Artificial Intelligence Assistant &amp; NVIDIA Cloud Training Disclosure</h2>
                                    </div>

                                    <p class="text-muted-custom mb-3">
                                        To deliver real-time interactive navigation, job discovery support, policy clarification, and mock interview coaching, the Platform integrates an interactive conversational AI Assistant (&ldquo;Campus AI&rdquo; or &ldquo;KLD AI Robot&rdquo;). In strict compliance with National Privacy Commission regulations on artificial intelligence systems and automated decision-making transparency, all users must review, understand, and abide by the following technical architecture specifications, data boundaries, and third-party cloud processing conditions:
                                    </p>

                                    <div class="card-paper p-3 mb-3 border border-line bg-surface">
                                        <h5 class="card-paper-title fw-bold text-ink mb-2" style="font-size: 1.05rem;">
                                            <i class="bi bi-cloud-arrow-up text-accent me-2"></i>4.1 Third-Party Cloud Infrastructure &amp; Foundation Model Architecture
                                        </h5>
                                        <p class="text-muted-custom mb-2">
                                            The Campus AI Assistant utilizes cloud-hosted large language model (LLM) inference microservices provisioned by <strong>NVIDIA Corporation</strong> via secure NVIDIA NIM (NVIDIA Inference Microservice) application programming interface (API) endpoints located at <code>https://integrate.api.nvidia.com/v1</code>. The platform interacts with advanced open-access foundation models, including architectures developed by Meta Platforms, Mistral AI, DeepSeek AI, and Google LLC, operating under NVIDIA's developer, research, and evaluation service tiers. Network communication between the university web server and the NVIDIA cloud gateway is conducted strictly through high-grade Transport Layer Security (TLS 1.3) cryptographic channels.
                                        </p>
                                        <p class="text-muted-custom mb-0">
                                            <strong>Model Training, Telemetry Logging, and Data Retention Disclosures:</strong> All users are formally notified that under the commercial and developer terms of service governing NVIDIA's free, developer, and evaluation API tiers, <strong>conversational prompts, user queries, follow-up messages, system responses, and interaction telemetry transmitted to the AI inference endpoints may be monitored, captured, logged, and retained by NVIDIA Corporation and its foundation model research partners</strong>. This retained conversational data may be processed for research and safety monitoring, system performance benchmarking, safety filter refinement, and ongoing algorithmic training, including reinforcement learning from human feedback (RLHF), for future generations of artificial intelligence foundation models.
                                        </p>
                                    </div>

                                    <div class="card-paper p-3 mb-3 border border-line bg-surface">
                                        <h5 class="card-paper-title fw-bold text-danger mb-2" style="font-size: 1.05rem;">
                                            <i class="bi bi-shield-slash text-danger me-2"></i>4.2 Absolute Prohibition on Submission of Sensitive Personal and Confidential Information
                                        </h5>
                                        <p class="text-muted-custom mb-0">
                                            In light of the external cloud processing and potential model training telemetry described above, <strong>all users are strictly, unconditionally, and categorically prohibited from submitting any personal, academic, financial, or confidential information into the AI chat interface</strong>. Users shall never type, paste, or disclose their Student Identification Numbers, account passwords, bank account details, GCash or digital wallet identifiers, grades, semester GWA records, home addresses, personal mobile numbers, medical notes, disciplinary records, or confidential university administrative memoranda into the chatbot. The AI Assistant is engineered and deployed exclusively for general public inquiries regarding campus hiring policies, work hour limitations, application steps, cover letter drafting principles, and simulated interview practice.
                                        </p>
                                    </div>

                                    <div class="card-paper p-3 mb-3 border border-line bg-surface">
                                        <h5 class="card-paper-title fw-bold text-ink mb-2" style="font-size: 1.05rem;">
                                            <i class="bi bi-layers-half text-accent me-2"></i>4.3 Institutional Data Boundaries &amp; Local Server Isolation
                                        </h5>
                                        <p class="text-muted-custom mb-2">
                                            <strong>Local Document and Profile Isolation:</strong> The University maintains an impermeable architectural boundary between official platform datastores and external AI cloud services. Official student profiles, uploaded PDF resumes, academic transcripts, Certificates of Registration (COR), Daily Time Records, supervisor evaluation sheets, and job application records are stored exclusively within secure local university datastores and protected MySQL databases. These records are <strong>never transmitted, exported, synchronized, or exposed to NVIDIA, OpenAI, Google, or any external AI service provider</strong> under any circumstances.
                                        </p>
                                        <p class="text-muted-custom mb-2">
                                            <strong>Isolated Text Payload Scope:</strong> The network payload dispatched to the NVIDIA cloud inference endpoint is strictly isolated and confined to the specific text characters typed directly into the active chat input field by the user, augmented only by a localized pre-prompt defining the assistant's helpful campus persona and recent conversational turns within the active session. No persistent background profile attributes, session cookies, student numbers, or database records are appended to the cloud inference request.
                                        </p>
                                        <p class="text-muted-custom mb-2">
                                            <strong>Automated Server-Side Heuristic Fallback Engine:</strong> In the event of external cloud API latency, rate-limiting, upstream service disruption, or lack of external Internet connectivity, the Platform automatically disengages cloud transmission and shifts execution to an internal <em>Local Guided Mode</em>. In this mode, conversational queries are analyzed and answered entirely on the local university server using pre-compiled deterministic regex heuristics, knowledge dictionaries, and hardcoded campus work FAQs, resulting in zero external network packet transmission.
                                        </p>
                                        <p class="text-muted-custom mb-0">
                                            <strong>Voluntary Utilization and Non-Discrimination Policy:</strong> Interacting with the AI Assistant is entirely voluntary. No student, applicant, or supervisor is required or coerced to utilize the AI assistant to browse job postings, submit formal job applications, monitor candidate progress, or manage work assistantship hours. Choosing not to engage with the AI assistant carries zero penalty, academic prejudice, or impediment to full platform functionality.
                                        </p>
                                    </div>

                                    <div class="text-muted-custom" style="font-size: 0.95rem;">
                                        For detailed statutory disclosures regarding NVIDIA's enterprise data handling, telemetry retention, and international data transfer frameworks, data subjects are encouraged to review the official <a href="https://www.nvidia.com/en-us/about-nvidia/privacy-policy/" target="_blank" rel="noopener noreferrer" class="text-ink fw-bold text-decoration-underline">NVIDIA Privacy Policy</a>.
                                    </div>
                                </div>
                            </div>

                            <!-- Section 5: Data Protection & Security Controls -->
                            <div id="sec-5" class="pt-3">
                                <h2 class="h4">5. Data Protection, Storage Security, &amp; Access Controls</h2>
                                <p>
                                    To safeguard personal information against unlawful destruction, accidental loss, unauthorized access, fraudulent alteration, or unlawful dissemination, Kolehiyo ng Lungsod ng Dasmariñas enforces comprehensive technical, physical, and organizational security countermeasures in accordance with NPC Circular 16-01 on government data security:
                                </p>
                                <p>
                                    <strong>5.1 Role-Based Access Control (RBAC) and Administrative Partitioning.</strong> Direct access to applicant dossiers, academic load schedules, resumes, and hiring remarks is strictly partitioned through hierarchical, role-based authorization gates. Information is segmented among four distinct operational roles: Student Applicants, Department Hiring Supervisors, SASO Program Coordinators, and System Super Administrators. A department supervisor may only view candidate submissions directed specifically to their department's active requisitions and is barred from viewing applicants to other offices. Peer student applicants are strictly prohibited from viewing or discovering another student's profile, application status, resume, or contact details. All administrative data queries require authenticated session validation, preventing unauthorized data harvesting.
                                </p>
                                <p>
                                    <strong>5.2 Cryptographic Safeguards and Credential Storage.</strong> User authentication credentials are protected using industry-standard cryptographic hashing mechanisms. Passwords submitted during registration or profile updates are salted with cryptographically secure pseudorandom nonces and hashed using the <strong>Bcrypt algorithm (<code>PASSWORD_DEFAULT</code>)</strong> before persistence in database records. Plaintext passwords are never stored, transmitted, cached, or accessible to system developers or university administrative staff. Password reset mechanisms utilize time-limited, single-use cryptographic tokens dispatched exclusively to the verified <code>@kld.edu.ph</code> institutional email inbox of the registered account owner.
                                </p>
                                <p>
                                    <strong>5.3 Transmission Security and Attack Mitigation Architecture.</strong> All web traffic traversing the Platform is forced over encrypted Hypertext Transfer Protocol Secure (HTTPS) connections utilizing modern Transport Layer Security (TLS 1.3) ciphers, safeguarding data in transit against eavesdropping and packet inspection. To defend against Cross-Site Request Forgery (CSRF), every state-modifying HTTP POST request requires cryptographic session tokens validated before execution. Furthermore, underlying server directories housing JSON flat files, uploaded PDF documents, and system audit logs are protected with server-level access barriers (<code>.htaccess</code> directives) that forbid direct public HTTP access and prevent arbitrary file execution.
                                </p>
                            </div>

                            <!-- Section 6: Data Retention & Disposal Policies -->
                            <div id="sec-6" class="pt-3">
                                <h2 class="h4">6. Data Retention &amp; Disposal Policies</h2>
                                <p>
                                    <strong>6.1 Active Retention Lifecycles and Audit Windows.</strong> In alignment with institutional governance circulars and state audit requirements, personal records, application dossiers, and verified timekeeping logs are retained within active system storage only for as long as necessary to achieve the declared purposes of student assistantship administration. Application dossiers and resumes submitted for a specific semester are preserved for the active academic term plus one (1) succeeding semester to accommodate late grade reconciliations, stipend audit discrepancies, and student grievances. Certified Daily Time Records and payroll endorsement manifests are preserved in read-only administrative archives for a period of five (5) academic years to comply with statutory Commission on Audit (COA) post-audit review mandates governing municipal disbursements.
                                </p>
                                <p>
                                    <strong>6.2 Archival and Permanent Disposal Protocols.</strong> Upon the expiration of the mandatory retention lifecycle, inactive student records, rejected or withdrawn application files, expired Certificates of Registration, and temporary availability matrices are systematically scheduled for permanent disposal or irreversible anonymization. In accordance with National Archives of the Philippines (NAP) General Circular No. 1 and KLD Records Management Office circulars, digital files marked for deletion undergo secure electronic overwriting and file-level purging that prevents forensic data recovery. Paper records, if any were generated for offline clearance, are shredded using cross-cut mechanical destruction certified by the University Records Custodian.
                                </p>
                            </div>

                            <!-- Section 7: Statutory Rights of the Data Subject -->
                            <div id="sec-7" class="pt-3">
                                <h2 class="h4">7. Statutory Rights of the Data Subject (RA 10173)</h2>
                                <p>
                                    In accordance with Chapter IV, Section 16 of Republic Act No. 10173, every registered student, faculty member, administrator, and accredited partner employer is recognized as an autonomous Data Subject and possesses full statutory authority to exercise the following legally enforceable rights:
                                </p>
                                <p>
                                    <strong>7.1 Right to be Informed (Section 16(a)).</strong> Every data subject has the right to be provided with clear, unambiguous, and intelligible explanations regarding whether personal data entering the system is being processed, the specific categories of data solicited, the operational and statutory purposes for which it is handled, the automated processing logic applied, the identity of third-party cloud recipients, and the identity and contact channels of the designated University Data Protection Officer. This right is guaranteed through this comprehensive policy, granular registration notices, and real-time warnings embedded throughout the user interface.
                                </p>
                                <p>
                                    <strong>7.2 Right of Access (Section 16(c)).</strong> Data subjects possess the unrestricted right to demand and receive reasonable access to their personal data held within the Platform. Registered students may directly view, inspect, verify, and export digital copies of their personal profile information, curriculum records, uploaded PDF resumes, application history logs, interview evaluations, and weekly availability schedules directly through their secure, self-service Student Dashboard at any time without administrative fees.
                                </p>
                                <p>
                                    <strong>7.3 Right to Rectification (Section 16(d)).</strong> If a data subject identifies inaccuracies, clerical errors, outdated information, or omissions within their recorded data, such as an updated General Weighted Average, corrected contact number, or updated Certificate of Registration reflecting curriculum load adjustments, they possess the right to dispute the inaccurate record and request immediate correction. Students may execute real-time updates to editable profile fields or file an official Academic Profile Change Request accompanied by supporting documentation for verified administrative fields.
                                </p>
                                <p>
                                    <strong>7.4 Right to Erasure or Blocking (Section 16(e)).</strong> Data subjects maintain the right to suspend, withdraw, or order the removal of their personal data from system records upon showing that the information is incomplete, outdated, false, unlawfully obtained, used for unauthorized purposes, or no longer necessary to achieve the declared objectives of student assistantship administration. Under this provision, students retain the unencumbered right to withdraw pending job applications prior to formal supervisor review, which immediately restricts hiring supervisors from viewing or accessing the submitted application dossier.
                                </p>
                                <p>
                                    <strong>7.5 Right to Object (Section 16(b)).</strong> A data subject has the right to withhold or withdraw consent regarding data processing activities that exceed statutory educational requirements or contractual assistantship agreements. Data subjects may object to automated profile evaluation, decline participation in optional institutional research surveys, and unconditionally refuse to engage with the AI Assistant chatbot without fear of administrative reprisal, reduction in student standing, or disqualification from campus job opportunities.
                                </p>
                                <p>
                                    <strong>7.6 Right to File a Complaint and Seek Indemnification (Section 16(f)).</strong> If a student, faculty member, or supervisor has reasonable grounds to believe that their personal data has been unlawfully processed, mishandled, compromised, or disclosed in violation of Republic Act No. 10173 or the security directives of this Policy, they possess the statutory right to file a formal complaint before the KLD Data Protection Officer or directly before the <strong>National Privacy Commission (NPC)</strong> via <code>complaints@privacy.gov.ph</code>. Furthermore, data subjects retain the legal right to seek indemnification for any damages sustained due to inaccurate, false, unlawfully obtained, or unauthorized use of their personal information.
                                </p>
                            </div>

                            <!-- Section 8: Security Incident & Data Breach Management -->
                            <div id="sec-8" class="pt-3">
                                <h2 class="h4">8. Security Incident &amp; Data Breach Management</h2>
                                <p>
                                    <strong>8.1 Institutional Incident Response Protocol.</strong> In the event of a confirmed or suspected information security incident, unauthorized server penetration, credential exposure, or personal data breach affecting platform users, the University Computer Emergency Response Team (CERT) in close coordination with the Data Protection Officer shall immediately invoke the KLD Statutory Incident Response Protocol. This protocol mandates the immediate containment of affected technical subsystems, temporary suspension of vulnerable network endpoints, forensic logging of attack vectors, preservation of digital audit trails, and execution of comprehensive remediation countermeasures to restore system integrity and prevent recurrent compromises.
                                </p>
                                <p>
                                    <strong>8.2 Mandatory Regulatory and Subject Notification Protocol.</strong> In strict adherence to Section 20(f) of Republic Act No. 10173 and NPC Circular No. 16-03 on Personal Data Breach Management, whenever a confirmed security breach involves sensitive personal information or records that give rise to a real risk of identity fraud, financial harm, or serious damage to affected data subjects, the University shall officially notify the National Privacy Commission and all impacted data subjects within <strong>seventy-two (72) hours</strong> of breach confirmation. The formal notification shall delineate the nature of the breach, the specific data categories compromised, remedial security actions executed by the university, and actionable security recommendations to assist affected users in protecting their digital identity.
                                </p>
                            </div>

                            <!-- Section 9: Data Protection Officer (DPO) Contact Information -->
                            <div id="sec-9" class="pt-3">
                                <h2 class="h4">9. Data Protection Officer (DPO) &amp; Compliance Office</h2>
                                <p>
                                    <strong>9.1 Formal Inquiries, Rights Invocations, and Compliance Requests.</strong> For formal inquiries regarding the interpretation or enforcement of this Policy, official requests to exercise statutory data subject rights, reports of suspected security vulnerabilities, or mediation of privacy disputes concerning assistantship records or AI chat telemetry, data subjects may communicate directly with the designated University Data Protection Officer through the official administrative compliance channels detailed below:
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
                                        <div><i class="bi bi-shield-check text-accent me-2"></i><strong>Office Hours:</strong> Monday to Friday, 8:00 AM to 5:00 PM (PHT)</div>
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
                                        <div><strong class="text-ink d-block">AI Model Training Notice</strong> Free-tier AI queries may be logged by NVIDIA for safety and evaluation. Do not input personal data.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-file-earmark-lock text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Local Document Protection</strong> Resumes and study loads remain securely on university infrastructure and are never transmitted to AI endpoints.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check2-circle text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Zero Commercial Exploitation</strong> Student dossiers and contact details are strictly restricted from commercial marketing or third-party sale.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-person-check text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Strict Access Partitioning</strong> Candidate portfolios are accessible only to verified supervisors of the recruiting department.</div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-shield-shaded text-accent fs-6 mt-1"></i>
                                        <div><strong class="text-ink d-block">Full Statutory Protection</strong> Data subjects hold enforceable rights of access, correction, objection, and withdrawal under RA 10173.</div>
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
