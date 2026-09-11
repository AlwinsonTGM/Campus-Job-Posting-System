<?php
/**
 * Campus Job Posting System - Frequently Asked Questions (10 Q&As)
 * Archetype F: Help & Accordion (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Frequently Asked Questions (10 FAQs)';

// 10 Verbatim Blueprint Q&As
$faqs = [
    [
        'id' => 'eligibility',
        'category' => 'Eligibility & Rules',
        'q' => 'Who is eligible to apply for on-campus jobs?',
        'a' => 'Any currently enrolled undergraduate or graduate student carrying at least 12 academic units, <strong>maintaining a General Weighted Average (GWA) of 2.50 or better</strong>, and having <strong>no disciplinary records</strong> is eligible to apply for campus positions.'
    ],
    [
        'id' => 'work-limits',
        'category' => 'Work Hours & Academics',
        'q' => 'How many hours per week can a Student Assistant (SA) work?',
        'a' => 'To safeguard your academic studies, Student Assistants are restricted to a maximum of <strong>20 hours per week</strong> during regular school semesters. During official summer/semester breaks, working hours may extend up to 40 hours per week upon office approval.'
    ],
    [
        'id' => 'multiple-jobs',
        'category' => 'Applications',
        'q' => 'Can I apply for multiple campus jobs at the same time?',
        'a' => 'Yes, you may submit applications to multiple departments simultaneously to increase your chances. However, once officially hired and contracted by a department, you can only hold <strong>one active on-campus position</strong> per semester.'
    ],
    [
        'id' => 'track-status',
        'category' => 'Applications',
        'q' => 'How do I know if my application was approved or shortlisted for an interview?',
        'a' => 'You can track real-time status updates directly inside your <a href="student/my-applications.php" class="text-ink fw-bold">Student Dashboard &gt; My Applications</a> page. Whenever a supervisor updates your status (e.g. <em>Under Review</em>, <em>Interview Scheduled</em>, or <em>Accepted</em>), your portal dashboard reflects the change instantly.'
    ],
    [
        'id' => 'required-docs',
        'category' => 'Requirements',
        'q' => 'What documents are required when submitting an application?',
        'a' => 'Standard application requirements include: (1) An updated Student Resume / CV in PDF format, (2) Current Certificate of Registration (COR) / Study Load showing your class schedule, and (3) A brief Statement of Intent / Cover Letter indicating your available hours.'
    ],
    [
        'id' => 'stipend-payroll',
        'category' => 'Payroll & Stipends',
        'q' => 'How and when are student assistant stipends disbursed?',
        'a' => 'Stipends are computed based on approved Daily Time Records (DTR) and disbursed on a semi-monthly (every 15th and 30th) or monthly basis through the University Cashier Office or credited directly to registered student bank/e-wallet accounts.'
    ],
    [
        'id' => 'post-job',
        'category' => 'Employer / Office',
        'q' => 'How can campus departments or offices post new job openings?',
        'a' => 'Authorized institute deans, office supervisors, and laboratory custodians can register with their official KLD email (<code>@kld.edu.ph</code>), navigate to the <a href="employer/create-job.php" class="text-ink fw-bold">Employer Dashboard</a>, and fill out the vacancy requisition form.'
    ],
    [
        'id' => 'forgot-password',
        'category' => 'Account & Security',
        'q' => 'What should I do if I forget my account password?',
        'a' => 'Click on the <a href="forgot-pass.php" class="text-ink fw-bold">Forgot Password</a> link on the sign-in page, enter your registered institutional email address, and submit. You will receive instructions to reset your access credentials.'
    ],
    [
        'id' => 'exam-flexibility',
        'category' => 'Work Hours & Academics',
        'q' => 'Can I adjust my work schedule during midterm or final exam weeks?',
        'a' => 'Yes! Campus policy mandates that all hiring departments provide flexible work-shift adjustments during official midterm and final examination periods so student assistants can focus on their exams.'
    ],
    [
        'id' => 'data-privacy',
        'category' => 'Privacy & Security',
        'q' => 'How is my personal academic and contact data protected?',
        'a' => 'All student profiles, resumes, and contact numbers are managed in strict compliance with Republic Act No. 10173 (Philippine Data Privacy Act of 2012). Your data is accessed only by authorized hiring supervisors and is never shared with third parties.'
    ]
];

// Determine which FAQ is opened using native PHP $_GET parameters
$open_index = 0; // Default to Question 1

$target_faq = $_GET['open'] ?? $_GET['q'] ?? $_GET['topic'] ?? null;
if ($target_faq !== null) {
    $target_faq = trim((string)$target_faq);
    if (is_numeric($target_faq)) {
        $req_idx = (int)$target_faq - 1;
        if (isset($faqs[$req_idx])) {
            $open_index = $req_idx;
        }
    } else {
        $found = false;
        foreach ($faqs as $i => $item) {
            if (isset($item['id']) && $item['id'] === $target_faq) {
                $open_index = $i;
                $found = true;
                break;
            }
        }
        if (!$found && in_array($target_faq, ['work-regulation', 'work-hours', 'work-limits', '20-hour', 'regulation'])) {
            $open_index = 1; // 2nd question: 20-Hour Work Regulations
        }
    }
}

// Interactive 3D Companion Studio Scripts
$extra_js = [
    'assets/js/three.min.js',
    'assets/js/GLTFLoader.js',
    'assets/js/hero-robot.js?v=' . time()
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <!-- Page Head -->
                <?php
                render_page_head(
                    '',
                    'Frequently Asked Questions',
                    'Everything you need to know about student assistant eligibility, work hour limits, stipend payouts, and departmental application workflows.'
                );
                ?>

                <!-- 10 FAQs Accordion -->
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="accordion accordion-flush mb-5" id="faqAccordion">
                            <?php foreach ($faqs as $idx => $item): 
                                $is_open = ($idx === $open_index);
                                $q_num = $idx + 1;
                                $faq_id = $item['id'] ?? ('faq-' . $q_num);
                            ?>
                                <div class="faq-item-card mb-3 <?= $is_open ? 'is-active-item' : '' ?>" id="faq-<?= $q_num ?>" style="scroll-margin-top: 100px;">
                                    <?php if (!empty($item['id'])): ?>
                                        <span id="<?= htmlspecialchars($item['id']) ?>" style="display: block; position: relative; top: -100px; visibility: hidden;"></span>
                                    <?php endif; ?>
                                    <h2 class="accordion-header m-0" id="heading<?= $idx ?>">
                                        <button 
                                            class="faq-accordion-btn <?= $is_open ? '' : 'collapsed' ?>" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?= $idx ?>" 
                                            aria-expanded="<?= $is_open ? 'true' : 'false' ?>" 
                                            aria-controls="collapse<?= $idx ?>"
                                        >
                                            <span class="faq-q-pill">
                                                Q<?= $q_num ?>
                                            </span>
                                            <span class="faq-q-title">
                                                <?= htmlspecialchars($item['q']) ?>
                                            </span>
                                            <span class="faq-toggle-icon-wrap">
                                                <i class="bi bi-chevron-down"></i>
                                            </span>
                                        </button>
                                    </h2>
                                    <div 
                                        id="collapse<?= $idx ?>" 
                                        class="accordion-collapse collapse <?= $is_open ? 'show' : '' ?>" 
                                        aria-labelledby="heading<?= $idx ?>" 
                                        data-bs-parent="#faqAccordion"
                                    >
                                        <div class="faq-answer-body">
                                            <p class="mb-0">
                                                <?= $item['a'] ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Bottom Help CTA (Simple, Clean Card with AI Chatbot Integration) -->
                        <div class="faq-help-card" id="faq-help-section">
                            <div class="faq-help-icon-box">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <h3 class="h4 fw-bold text-ink mb-2">Still Have Unanswered Questions?</h3>
                            <p class="text-muted-custom small col-lg-8 mx-auto mb-4">
                                Reach out to the Student Affairs &amp; Career Services Office, connect with our student development team, or chat with our Campus AI Bot for immediate answers.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <button type="button" class="btn-accent-pill" id="faqChatbotTriggerBtn">
                                    <i class="bi bi-robot"></i> ASK OUR CHATBOT
                                </button>
                                <a href="student/jobs.php" class="btn-outline-pill">
                                    <i class="bi bi-search"></i> EXPLORE VACANCIES
                                </a>
                                <a href="about-us.php" class="btn-outline-pill">
                                    MEET THE TEAM <span class="btn-circle-arrow-accent"><i class="bi bi-arrow-up-right"></i></span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </main>

        <!-- Fullscreen Interactive 2-Column Campus AI Companion Studio Modal (Unified with index.php) -->
        <div id="campus-ai-fullscreen-modal" class="campus-ai-fullscreen-modal" role="dialog" aria-modal="true" aria-labelledby="fullscreen-modal-title" style="display: none;">
            <div class="fullscreen-modal-backdrop" id="fullscreen-backdrop"></div>
            <div class="fullscreen-modal-shell">
                <!-- Top Navigation / Status Bar -->
                <div class="fullscreen-modal-header">
                    <div class="fs-header-left d-flex align-items-center gap-3">
                        <div class="fs-bot-avatar">
                            <i class="bi bi-robot"></i>
                            <span class="fs-live-indicator" id="fs-live-dot"></span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 id="fullscreen-modal-title" class="fs-bot-title mb-0">Campus AI Assistant</h5>
                            </div>
                        </div>
                    </div>
                    <div class="fs-header-right d-flex align-items-center gap-2">
                        <div class="fs-rate-limit-badge" id="fs-rate-limit-badge" title="Hourly question quota">
                            <i class="bi bi-shield-check text-success" id="fs-rate-icon"></i>
                            <span id="fs-rate-count">10/10 requests left</span>
                        </div>
                        <button type="button" id="fullscreen-close-btn" class="fs-close-btn" title="Close fullscreen (Esc)" aria-label="Close fullscreen view">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Two-Column Studio Body -->
                <div class="fullscreen-modal-body">
                    <!-- Left Column: Spacious Chat Experience -->
                    <div class="fullscreen-chat-pane">
                        <!-- Scrollable Messages Stream -->
                        <div id="fullscreen-messages-container" class="fullscreen-messages-stream">
                            <!-- Populated dynamically via JS -->
                        </div>

                        <!-- Thinking Indicator for Fullscreen -->
                        <div id="fullscreen-thinking" class="fullscreen-thinking-indicator d-none">
                            <div class="fs-thinking-bubble">
                                <span class="thinking-dots">
                                    <span class="tdot tdot-1"></span>
                                    <span class="tdot tdot-2"></span>
                                    <span class="tdot tdot-3"></span>
                                </span>
                                <span class="fs-thinking-text ms-2">Campus AI is generating guidance...</span>
                            </div>
                        </div>

                        <!-- Floating Suggestion Chips -->
                        <div class="fullscreen-suggestions-bar">
                            <button type="button" class="fs-quick-chip" data-prompt="How do I apply for a student assistant role on this website?">
                                <i class="bi bi-file-earmark-text text-accent"></i> How to apply
                            </button>
                            <button type="button" class="fs-quick-chip" data-prompt="Can I adjust my student work shifts around my class lecture blocks?">
                                <i class="bi bi-calendar-range text-accent"></i> Flexible shifts
                            </button>
                            <button type="button" class="fs-quick-chip" data-prompt="What are the best interview tips for a student assistant role?">
                                <i class="bi bi-award text-accent"></i> Interview tips
                            </button>
                            <button type="button" class="fs-quick-chip" data-prompt="How do I highlight academic course projects on my campus resume?">
                                <i class="bi bi-journal-check text-accent"></i> Resume tips
                            </button>
                        </div>

                        <!-- Bottom Chat Input Bar -->
                        <form id="fullscreen-chat-form" class="fullscreen-chat-form" autocomplete="off">
                            <div class="fs-input-wrap">
                                <input type="text" id="fullscreen-chat-input" class="fs-chat-input" placeholder="Ask anything about student assistant roles, shift scheduling, or interview tips..." maxlength="400" autocomplete="off" aria-label="Ask Campus AI">
                                <button type="submit" id="fullscreen-submit-btn" class="fs-submit-btn" title="Send question">
                                    <i class="bi bi-arrow-up-circle-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Interactive 3D Robot Companion -->
                    <div class="fullscreen-robot-stage">
                        <div class="fs-robot-ambient-glow"></div>
                        <!-- 3D Canvas Reparent Target Slot -->
                        <div id="fullscreen-robot-stage-slot" class="fullscreen-robot-stage-slot">
                            <div id="hero-robot-canvas-container" class="hero-robot-canvas-container is-fullscreen" data-model-path="<?= $base_url ?>assets/models/cute_robot.glb" title="Click me to chat and see my animations!">
                                <!-- Dynamic Progress & Loading Skeleton -->
                                <div id="hero-robot-loader" class="hero-robot-loader">
                                    <div class="spinner-border text-success" role="status" style="width: 2rem; height: 2rem;">
                                        <span class="visually-hidden">Loading 3D Model...</span>
                                    </div>
                                    <div class="loader-text mt-2 small text-dark fw-bold">Waking Up 3D Assistant...</div>
                                    <div class="progress mt-2" style="width: 130px; height: 4px; background: #e2e8f0; border-radius: 99px;">
                                        <div id="hero-robot-progress" class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: 15%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fs-robot-hint">
                            <i class="bi bi-cursor-fill me-1 text-accent"></i>
                            <span>Move cursor to look around &bull; Click robot for animations</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
