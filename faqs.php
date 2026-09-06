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
                                <button type="button" class="btn-accent-pill" data-bs-toggle="modal" data-bs-target="#faqChatbotModal" id="faqChatbotTriggerBtn">
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

        <!-- Campus AI FAQ Chatbot Modal -->
        <div class="modal fade" id="faqChatbotModal" tabindex="-1" aria-labelledby="faqChatbotModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content faq-chatbot-modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header faq-chatbot-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-3">
                            <div class="faq-chatbot-avatar">
                                <i class="bi bi-robot"></i>
                                <span class="faq-chatbot-status-dot" title="Online"></span>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="modal-title fw-bold text-ink mb-0" id="faqChatbotModalLabel">Campus AI Assistant</h5>
                                    <span class="badge-tag" style="font-size: 0.7rem; padding: 2px 8px; background: rgba(46, 204, 94, 0.15); color: #059669; border: 1px solid rgba(46, 204, 94, 0.3);">
                                        <i class="bi bi-stars me-1"></i> AI Powered
                                    </span>
                                </div>
                                <p class="text-muted-custom small mb-0 mt-1">Instant answers for student jobs, eligibility, shifts &amp; applications</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close faq-chatbot-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body: Chat History -->
                    <div class="modal-body faq-chatbot-body p-3 p-md-4">
                        <div id="faqChatMessages" class="faq-chat-messages-box mb-3">
                            <!-- Default Bot Greeting Message -->
                            <div class="faq-chat-msg faq-chat-msg-bot">
                                <div class="faq-chat-msg-avatar">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="faq-chat-msg-content">
                                    <div class="faq-chat-msg-bubble">
                                        👋 <strong>Hello!</strong> I'm your <strong>Campus AI Assistant</strong>. Still have questions about student assistantships, work-hour limits, stipend releases, or how to apply? Ask me anything below!
                                    </div>
                                    <span class="faq-chat-msg-time">Just now</span>
                                </div>
                            </div>
                        </div>

                        <!-- Thinking / Loading Indicator -->
                        <div id="faqChatThinking" class="faq-chat-thinking d-none mb-3">
                            <div class="d-flex gap-2 align-items-center">
                                <div class="faq-chat-msg-avatar">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="faq-chat-thinking-bubble">
                                    <span class="thinking-dots">
                                        <span class="tdot tdot-1"></span>
                                        <span class="tdot tdot-2"></span>
                                        <span class="tdot tdot-3"></span>
                                    </span>
                                    <span class="small text-muted-custom ms-2">Campus AI is generating answers...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Suggestion Chips -->
                        <div class="faq-chatbot-suggestions mb-3">
                            <div class="small text-muted-custom fw-semibold mb-2 d-flex align-items-center gap-1">
                                <i class="bi bi-lightbulb text-warning"></i> Suggested questions:
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="faq-chip-btn" data-question="Who is eligible to apply for on-campus student assistant jobs?">
                                    Who is eligible?
                                </button>
                                <button type="button" class="faq-chip-btn" data-question="How many hours per week can a Student Assistant work?">
                                    Work-hour limits (20 hrs)
                                </button>
                                <button type="button" class="faq-chip-btn" data-question="What documents are required when submitting an application?">
                                    Required documents
                                </button>
                                <button type="button" class="faq-chip-btn" data-question="How and when are student assistant stipends disbursed?">
                                    Stipend &amp; payroll schedule
                                </button>
                                <button type="button" class="faq-chip-btn" data-question="Can I adjust my work schedule during midterm or final exam weeks?">
                                    Exam schedule adjustments
                                </button>
                            </div>
                        </div>

                        <!-- Chat Input Form -->
                        <form id="faqChatForm" class="faq-chat-form" autocomplete="off">
                            <div class="faq-chat-input-wrap">
                                <input 
                                    type="text" 
                                    id="faqChatInput" 
                                    class="faq-chat-input" 
                                    placeholder="Ask a question about campus jobs, eligibility, requirements..." 
                                    maxlength="400"
                                    autocomplete="off"
                                    required
                                >
                                <button type="submit" id="faqChatSubmitBtn" class="faq-chat-send-btn" title="Send question">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </div>
                        </form>

                        <!-- Footer Hint -->
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-line">
                            <span class="small text-muted-custom" style="font-size: 0.78rem;">
                                <i class="bi bi-shield-check text-accent me-1"></i> Protected by Anti-Spam &bull; Official Campus AI
                            </span>
                            <a href="index.php" class="small text-ink fw-semibold text-decoration-none" style="font-size: 0.78rem;" target="_blank">
                                Launch 3D Companion Studio <i class="bi bi-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            'use strict';
            const modalEl = document.getElementById('faqChatbotModal');
            const messagesContainer = document.getElementById('faqChatMessages');
            const thinkingEl = document.getElementById('faqChatThinking');
            const chatForm = document.getElementById('faqChatForm');
            const chatInput = document.getElementById('faqChatInput');
            const submitBtn = document.getElementById('faqChatSubmitBtn');
            const chips = document.querySelectorAll('.faq-chip-btn');

            let isWaiting = false;

            // Auto-open modal if URL contains ?chat=1 or hash #chatbot
            if (window.location.search.indexOf('chat=1') !== -1 || window.location.hash === '#chatbot') {
                document.addEventListener('DOMContentLoaded', function () {
                    if (window.bootstrap && modalEl) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                });
            }

            // Auto-focus input when modal opens
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function () {
                    if (chatInput) chatInput.focus();
                });
            }

            function formatChatText(str) {
                if (!str) return '';
                let safe = str
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                safe = safe.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                safe = safe.replace(/(^|\n)[\t ]*(\d+)[\.\)][\t ]*([^\n]*)/g, '<br><strong>$2.</strong> $3');
                safe = safe.replace(/(^|\n)[\t ]*[\*\-•][\t ]*([^\n]*)/g, '<br>&bull; $2');
                safe = safe.replace(/(^|[^\s*])\*([^\n*]+?)\*/g, '$1<em>$2</em>');
                safe = safe.replace(/\n\s*\n/g, '<br><br>');
                safe = safe.replace(/\n/g, '<br>');
                if (safe.startsWith('<br>')) safe = safe.substring(4);
                return safe;
            }

            function appendMessage(role, text, modelName) {
                if (!messagesContainer) return;
                const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const wrapper = document.createElement('div');
                wrapper.className = 'faq-chat-msg ' + (role === 'user' ? 'faq-chat-msg-user' : 'faq-chat-msg-bot');

                if (role === 'user') {
                    wrapper.innerHTML = `
                        <div class="faq-chat-msg-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="faq-chat-msg-content">
                            <div class="faq-chat-msg-bubble">
                                ${formatChatText(text)}
                            </div>
                            <span class="faq-chat-msg-time">${timeStr}</span>
                        </div>
                    `;
                } else {
                    const badge = modelName ? `<div class="faq-chat-model-badge"><i class="bi bi-cpu-fill"></i> ${modelName}</div>` : '';
                    wrapper.innerHTML = `
                        <div class="faq-chat-msg-avatar">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="faq-chat-msg-content">
                            <div class="faq-chat-msg-bubble">
                                ${formatChatText(text)}
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="faq-chat-msg-time">${timeStr}</span>
                                ${badge}
                            </div>
                        </div>
                    `;
                }

                messagesContainer.appendChild(wrapper);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            function sendQuestion(questionText) {
                if (!questionText || isWaiting) return;
                const trimmed = questionText.trim();
                if (!trimmed) return;

                isWaiting = true;
                if (submitBtn) submitBtn.disabled = true;
                if (chatInput) {
                    chatInput.value = '';
                    chatInput.disabled = true;
                }

                appendMessage('user', trimmed);

                if (thinkingEl) {
                    thinkingEl.classList.remove('d-none');
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }

                fetch('<?= $base_url ?>api/robot-chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: trimmed, mode: 'faq' })
                })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, status: res.status, data: data };
                    }).catch(function () {
                        return { ok: res.ok, status: res.status, data: null };
                    });
                })
                .then(function (res) {
                    if (thinkingEl) thinkingEl.classList.add('d-none');
                    isWaiting = false;
                    if (submitBtn) submitBtn.disabled = false;
                    if (chatInput) {
                        chatInput.disabled = false;
                        chatInput.focus();
                    }

                    const data = res.data;
                    if (res.status === 429 || (data && data.error === 'RATE_LIMIT_EXCEEDED')) {
                        appendMessage('bot', data && data.message ? data.message : "🛡️ Anti-spam limit reached. Please wait a minute before asking another question.");
                        return;
                    }

                    if (data && data.status === 'success' && data.reply) {
                        appendMessage('bot', data.reply, data.model_name || data.model || 'Campus AI');
                    } else {
                        appendMessage('bot', "I'm having a little trouble connecting to my knowledge base right now. Feel free to reach out directly to the Student Affairs & Career Services Office or browse the FAQ questions above! 🏫");
                    }
                })
                .catch(function (err) {
                    console.error('[FAQChatbot] Error:', err);
                    if (thinkingEl) thinkingEl.classList.add('d-none');
                    isWaiting = false;
                    if (submitBtn) submitBtn.disabled = false;
                    if (chatInput) {
                        chatInput.disabled = false;
                        chatInput.focus();
                    }
                    appendMessage('bot', "Could not reach the Campus AI service at the moment. Please try again in a few moments or check our FAQs above! 💡");
                });
            }

            if (chatForm) {
                chatForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (chatInput) sendQuestion(chatInput.value);
                });
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    const q = chip.getAttribute('data-question');
                    if (q) sendQuestion(q);
                });
            });
        })();
        </script>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
