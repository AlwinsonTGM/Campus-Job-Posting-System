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
        'q' => 'Who is eligible to apply for on-campus jobs?',
        'a' => 'Any currently enrolled undergraduate or graduate student in good academic standing with no disciplinary records is eligible to apply.'
    ],
    [
        'q' => 'How many hours per week can a Student Assistant (SA) work?',
        'a' => 'SAs are permitted to work a maximum of <strong>20 hours per week</strong> during regular school terms and up to <strong>40 hours per week</strong> during semester breaks, ensuring academic priorities remain uncompromised.'
    ],
    [
        'q' => 'Can I apply for multiple campus jobs at the same time?',
        'a' => 'Yes, you can submit applications to multiple departments simultaneously. However, you can only hold <strong>one active on-campus contract</strong> at a time once officially hired.'
    ],
    [
        'q' => 'How do I know if my application was approved or shortlisted?',
        'a' => 'You can track real-time status updates (<em>Pending</em>, <em>Under Review</em>, <em>Interview Scheduled</em>, <em>Accepted</em>, <em>Rejected</em>) in your <a href="student/my-applications.php" class="text-ink fw-bold">Student Dashboard &gt; My Applications</a> tab.'
    ],
    [
        'q' => 'What documents are required when submitting an application?',
        'a' => 'Standard requirements include your updated Resume/CV, Certificate of Registration (COR) / Study Load, and an optional Cover Letter stating your available hours.'
    ],
    [
        'q' => 'How are student assistant stipends or salaries disbursed?',
        'a' => 'Stipends are processed semi-monthly or monthly through the University Accounting/Cashier Office or credited directly to your registered student bank/e-wallet account upon submission of signed Daily Time Records (DTR).'
    ],
    [
        'q' => 'How can campus departments or offices post new job openings?',
        'a' => 'Department heads and authorized office supervisors can register with an official university email, access the <a href="employer/dashboard.php" class="text-ink fw-bold">Employer Dashboard</a>, and submit a job requisition form via <strong>Create Job</strong>.'
    ],
    [
        'q' => 'What should I do if I forget my account password?',
        'a' => 'Navigate to the <a href="forgot-pass.php" class="text-ink fw-bold">Forgot Password</a> page, enter your registered institutional email address, and follow the password reset link or instructions sent to your inbox.'
    ],
    [
        'q' => 'Can I adjust my work schedule during midterm or final exam weeks?',
        'a' => 'Yes. University policy mandates campus employers to provide flexible adjustments to work shifts during designated examination periods.'
    ],
    [
        'q' => 'How is my personal academic and contact data protected?',
        'a' => 'All student and employer records are strictly managed in compliance with the <strong>Data Privacy Act of 2012 (RA 10173)</strong> and are solely utilized for internal campus recruitment purposes.'
    ]
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
                    '<i class="bi bi-question-circle-fill text-accent me-1"></i> Knowledge Base & Guidelines',
                    'Frequently Asked Questions',
                    'Everything you need to know about student assistant eligibility, work hour limits, application tracking, and payroll disbursements.'
                );
                ?>

                <!-- 10 FAQs Accordion inside Card Paper -->
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card-paper p-3 p-md-4 mb-5">
                            <div class="accordion accordion-flush" id="faqAccordion">
                                <?php foreach ($faqs as $idx => $item): ?>
                                    <div class="accordion-item bg-transparent border-0 mb-3">
                                        <h2 class="accordion-header" id="heading<?= $idx ?>">
                                            <button 
                                                class="accordion-button <?= $idx === 0 ? '' : 'collapsed' ?> card-paper card-hover p-3 d-flex gap-3 align-items-center" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?= $idx ?>" 
                                                aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>" 
                                                aria-controls="collapse<?= $idx ?>"
                                            >
                                                <span class="pill-badge pill-badge-ink flex-shrink-0" style="font-size: 11px; padding: 0.25rem 0.65rem;">
                                                    Q<?= $idx + 1 ?>
                                                </span>
                                                <span class="fw-bold text-ink fs-6 text-start flex-grow-1">
                                                    <?= htmlspecialchars($item['q']) ?>
                                                </span>
                                            </button>
                                        </h2>
                                        <div 
                                            id="collapse<?= $idx ?>" 
                                            class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" 
                                            aria-labelledby="heading<?= $idx ?>" 
                                            data-bs-parent="#faqAccordion"
                                        >
                                            <div class="accordion-body px-4 py-3 text-muted-custom bg-surface rounded-3 mt-1 border border-line">
                                                <p class="mb-0 fs-6 lh-base text-ink">
                                                    <?= $item['a'] ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Bottom Help CTA -->
                        <div class="card-paper bg-cream p-4 text-center">
                            <h3 class="card-paper-title mb-2">Still Have Questions?</h3>
                            <p class="text-muted-custom small mb-4">
                                Reach out to the Student Affairs & Career Services Office or connect with our student development team.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="student/jobs.php" class="btn-pill">
                                    <i class="bi bi-search"></i> Explore Campus Vacancies
                                </a>
                                <a href="about-us.php" class="btn-pill-outline">
                                    <i class="bi bi-people"></i> Meet The Developers
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
