<?php
/**
 * View template for employer/review-app.php
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
                
                <!-- Back Link & Page Head -->
                <div class="mb-4">
                    <a href="applicants.php" class="text-ink fw-bold small text-decoration-none d-inline-flex align-items-center gap-1 mb-3">
                        <i class="bi bi-arrow-left"></i> Back to Applicant Roster
                    </a>
                    
                    <?php
                    render_page_head(
                        '',
                        'Review Applicant: ' . htmlspecialchars($target_app['student_name']),
                        'Vacancy: ' . htmlspecialchars($target_app['job_title']) . ' • Applied on ' . format_display_date($target_app['applied_at'] ?? 'now')
                    );
                    ?>
                </div>

                <div class="row g-4 mb-5">
                    
                    <!-- Left 7-col: Candidate Profile, Statement & Availability Matrix -->
                    <div class="col-lg-7">
                        
                        <!-- Candidate Academic Profile -->
                        <div class="card-paper p-4 mb-4">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-person-badge text-accent me-2"></i> 1. Academic & Contact Profile
                            </h3>

                            <div class="row g-3 p-3 bg-cream rounded-4 border border-line mb-3">
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Student Full Name</span>
                                    <strong class="text-ink fs-6"><?= htmlspecialchars($target_app['student_name']) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Student ID Number</span>
                                    <strong class="text-ink"><?= htmlspecialchars($target_app['student_number'] ?? '2024-00123') ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Degree Program & Standing</span>
                                    <span class="text-ink fw-semibold"><?= htmlspecialchars($target_app['course'] ?? 'BS Information Systems') ?></span>
                                    <span class="text-muted-custom">&bull; <?= htmlspecialchars($target_app['year_level'] ?? '2nd Year') ?></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="small text-muted-custom d-block mb-1">Demographics (Sex & Age)</span>
                                    <span class="text-ink fw-semibold"><?= htmlspecialchars($target_app['sex'] ?? 'Male') ?> &bull; <?= htmlspecialchars((string)($target_app['age'] ?? 20)) ?> yrs old</span>
                                </div>
                                <div class="col-md-12">
                                    <span class="small text-muted-custom d-block mb-1">Contact Details</span>
                                    <span class="text-ink fw-semibold"><?= htmlspecialchars($target_app['phone'] ?? '+63 917 555 0192') ?></span>
                                    <span class="text-muted-custom mx-2">&bull;</span>
                                    <span class="small text-muted-custom"><?= htmlspecialchars($target_app['student_email']) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Statement of Purpose -->
                        <div class="card-paper p-4 mb-4">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-file-text text-accent me-2"></i> 2. Statement of Intent / Cover Letter
                            </h3>
                            <div class="p-3 bg-surface rounded-4 border border-line text-ink lh-lg small mb-0">
                                <?= nl2br(htmlspecialchars($target_app['cover_letter'])) ?>
                            </div>
                        </div>

                        <!-- Shift Availability Matrix (Readonly) -->
                        <div class="card-paper p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-line">
                                <h3 class="card-paper-title fs-5 mb-0">
                                    <i class="bi bi-calendar-week text-accent me-2"></i> 3. Candidate Shift Availability
                                </h3>
                                <?php
                                    $app_norm_slots = function_exists('normalize_availability_slots') 
                                        ? normalize_availability_slots($target_app['availability'] ?? []) 
                                        : ($target_app['availability'] ?? []);
                                    $slot_count = count($app_norm_slots);
                                ?>
                                <?php if ($slot_count > 0): ?>
                                    <span class="chip" style="font-size: 10px;"><?= $slot_count ?> slot<?= $slot_count > 1 ? 's' : '' ?> (~<?= $slot_count * 4 ?> hrs/wk)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 10px;">
                                        <i class="bi bi-exclamation-triangle-fill"></i> 0 Slots Declared
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="small text-muted-custom mb-3">
                                Periods when the student is free from academic lectures and can perform on-campus assistantship duty:
                            </p>
                            <div class="p-3 bg-surface rounded-4 border border-line">
                                <?php render_availability_matrix($target_app['availability'] ?? [], '', true); ?>
                            </div>
                        </div>

                        <!-- Attached Credentials -->
                        <div class="card-paper p-4">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-paperclip text-accent me-2"></i> 4. Attached Resume & Credentials
                            </h3>
                            <div class="p-3 bg-surface rounded-4 border border-line d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-2 flex-shrink-0"></i>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-ink small text-truncate"><?= htmlspecialchars($target_app['resume_file'] ?? 'Student_Resume.pdf') ?></div>
                                        <span class="small text-muted-custom d-block text-truncate">Official Student Resume & Credentials</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-nowrap gap-2 flex-shrink-0">
                                    <button type="button" class="btn-pill btn-pill-sm text-nowrap d-inline-flex align-items-center gap-1 py-1 px-3" data-bs-toggle="modal" data-bs-target="#resumeModal" style="font-size: 12px;">
                                        <i class="bi bi-eye-fill"></i> View PDF
                                    </button>
                                    <a href="../view-resume.php?app_id=<?= $target_app['id'] ?>" target="_blank" class="btn-pill-outline btn-pill-sm text-nowrap d-inline-flex align-items-center gap-1 py-1 px-3" style="font-size: 12px;" title="Open PDF in new tab">
                                        <i class="bi bi-box-arrow-up-right"></i> Open Tab
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right 5-col: Status Controls & Stepper Action Card -->
                    <div class="col-lg-5">
                        <div class="card-paper p-4 position-sticky" style="top: 90px;">
                            <h3 class="card-paper-title fs-5 mb-3 pb-2 border-bottom border-line">
                                <i class="bi bi-sliders text-accent me-2"></i> Evaluation & Decision
                            </h3>

                            <!-- Current Status & Stepper -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small text-muted-custom">Current Stage:</span>
                                    <div><?= render_status_badge($target_app['status']) ?></div>
                                </div>
                                <div class="p-2">
                                    <?php render_stepper($target_app['status']); ?>
                                </div>
                            </div>

                            <!-- Supervisor Advisory Guide (Laya System 1 Engine) -->
                            <div class="p-3 bg-cream rounded-4 border border-line mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-ink small d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-compass text-accent"></i> Supervisor Advisory Dossier
                                    </span>
                                    <?php if (!empty($laya_available)): ?>
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-link p-0 text-muted-custom text-decoration-none d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#layaMetricGuideModal" title="Explain Laya Percentages & Confidence" style="font-size: 10px;">
                                                <i class="bi bi-question-circle"></i> Metric Guide
                                            </button>
                                            <?php if (!empty($laya_guidance['evaluated_at'])): ?>
                                                <span class="text-muted-custom" style="font-size: 10px;" title="Timestamp of last neural evaluation">Live: <?= htmlspecialchars($laya_guidance['evaluated_at']) ?></span>
                                            <?php endif; ?>
                                            <a href="review-app.php?id=<?= (int)$target_app['id'] ?>&refresh_guidance=1" class="text-muted-custom small text-decoration-none d-inline-flex align-items-center gap-1 border border-line rounded px-1" title="Bust cache and re-query Laya ModernBERT neural daemon" style="font-size: 10px;">
                                                <i class="bi bi-arrow-clockwise"></i> Refresh
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($laya_guidance)): ?>
                                    <?php
                                        $sched_status = $laya_guidance['schedule_assessment']['status'] ?? 'success';
                                        $sched_text_class = ($sched_status === 'danger') ? 'text-danger' : (($sched_status === 'warning') ? 'text-warning' : 'text-success');
                                        $sched_icon = ($sched_status === 'danger') ? 'bi-exclamation-triangle-fill text-danger' : (($sched_status === 'warning') ? 'bi-exclamation-circle text-warning' : 'bi-calendar-check text-success');
                                        $sched_conf = round(($laya_guidance['schedule_assessment']['confidence'] ?? 0.95) * 100);

                                        $prog_conf = round(($laya_guidance['qualification_assessment']['confidence'] ?? 0.5) * 100);
                                        $prog_score = round((float)($laya_guidance['qualification_assessment']['score'] ?? 2.0), 1);

                                        $skills_text = $laya_guidance['skills_assessment']['summary'] ?? 'Assessed';
                                        $skills_score = round((float)($laya_guidance['skills_assessment']['score'] ?? 1.5), 1);
                                        $skills_conf = round(($laya_guidance['skills_assessment']['confidence'] ?? 0.5) * 100);

                                        $readiness = $laya_guidance['readiness_assessment'] ?? [];
                                        $readiness_prob = round(($readiness['probability'] ?? 0.5) * 100);

                                        $workload = $laya_guidance['workload_risk'] ?? [];
                                        $workload_prob = round(($workload['probability'] ?? 0.3) * 100);

                                        $intent_prob = round(($laya_guidance['intent_assessment']['probability'] ?? 0.5) * 100);
                                        $spec_conf = round(($laya_guidance['specificity_assessment']['confidence'] ?? 0.5) * 100);
                                    ?>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <div class="p-2 bg-white rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted-custom" style="font-size: 10px;">Schedule Fit</span>
                                                        <span class="badge bg-light text-muted-custom border" style="font-size: 9px; padding: 2px 4px;" title="Rule-based certainty against weekly 20-hour academic limit"><?= $sched_conf ?>% Match</span>
                                                    </div>
                                                    <strong class="<?= $sched_text_class ?> d-block" style="font-size: 11.5px; line-height: 1.2;">
                                                        <i class="bi <?= $sched_icon ?> me-1"></i>
                                                        <?= htmlspecialchars($laya_guidance['schedule_assessment']['summary'] ?? 'Assessed') ?>
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-white rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted-custom" style="font-size: 10px;">Program Fit</span>
                                                        <span class="badge bg-light text-muted-custom border" style="font-size: 9px; padding: 2px 4px;" title="ModernBERT confidence in academic qualification alignment"><?= $prog_conf ?>% Confidence</span>
                                                    </div>
                                                    <strong class="text-ink d-block" style="font-size: 11.5px; line-height: 1.2;">
                                                        <i class="bi bi-mortarboard text-primary me-1"></i>
                                                        <?= htmlspecialchars($laya_guidance['qualification_assessment']['summary'] ?? 'Evaluated') ?>
                                                    </strong>
                                                </div>
                                                <span class="text-muted-custom" style="font-size: 9.5px; margin-top: 2px;">Score: <?= $prog_score ?> / 3.0</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <div class="p-2 bg-white rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted-custom" style="font-size: 10px;">Skills Match</span>
                                                        <span class="badge bg-light text-muted-custom border" style="font-size: 9px; padding: 2px 4px;" title="ModernBERT confidence in practical skills alignment"><?= $skills_conf ?>% Confidence</span>
                                                    </div>
                                                    <strong class="text-ink d-block" style="font-size: 11.5px; line-height: 1.2;">
                                                        <i class="bi bi-cpu text-accent me-1"></i>
                                                        <?= htmlspecialchars($skills_text) ?>
                                                    </strong>
                                                </div>
                                                <span class="text-muted-custom" style="font-size: 9.5px; margin-top: 2px;">Score: <?= $skills_score ?> / 3.0</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-white rounded-3 border border-line h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted-custom" style="font-size: 10px;">Role Readiness</span>
                                                        <span class="badge <?= !empty($readiness['ready']) ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?> border" style="font-size: 9px; padding: 2px 4px;" title="Calibrated model confidence in candidate readiness"><?= $readiness_prob ?>% Confidence</span>
                                                    </div>
                                                    <strong class="<?= (!empty($readiness['ready'])) ? 'text-success' : 'text-muted-custom' ?> d-block" style="font-size: 11.5px; line-height: 1.2;">
                                                        <i class="bi <?= (!empty($readiness['ready'])) ? 'bi-check-circle-fill text-success' : 'bi-hourglass-split text-warning' ?> me-1"></i>
                                                        <?= htmlspecialchars($readiness['label'] ?? 'Evaluated') ?>
                                                    </strong>
                                                </div>
                                                <span class="text-muted-custom" style="font-size: 9.5px; margin-top: 2px;">Model readiness confidence</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-2 bg-white rounded-3 border border-line mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted-custom" style="font-size: 10px;">Academic Overload Risk</span>
                                            <span class="badge <?= !empty($workload['at_risk']) ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> border" style="font-size: 9px; padding: 2px 4px;" title="Estimated risk of academic burnout / schedule strain. Under 40% is Balanced"><?= $workload_prob ?>% Risk</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class="<?= (!empty($workload['at_risk'])) ? 'text-warning' : 'text-success' ?>" style="font-size: 11.5px;">
                                                <i class="bi <?= (!empty($workload['at_risk'])) ? 'bi-exclamation-triangle-fill text-warning' : 'bi-shield-check text-success' ?> me-1"></i>
                                                <?= htmlspecialchars($workload['label'] ?? 'Balanced') ?>
                                            </strong>
                                            <span class="text-muted-custom" style="font-size: 10px;">Coursework vs duty load</span>
                                        </div>
                                    </div>

                                    <?php if (!empty($laya_guidance['schedule_assessment']['warning'])): ?>
                                        <div class="alert <?= $sched_status === 'danger' ? 'alert-danger' : 'alert-warning' ?> py-1 px-2 mb-2 small rounded-3 border-0 d-flex align-items-center gap-2" style="font-size: 11px;">
                                            <i class="bi <?= $sched_status === 'danger' ? 'bi-exclamation-octagon-fill text-danger' : 'bi-info-circle-fill text-warning' ?> flex-shrink-0"></i>
                                            <span><?= htmlspecialchars($laya_guidance['schedule_assessment']['warning']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-2 bg-white rounded-3 border border-line mb-2" style="font-size: 11px;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-ink">
                                                <i class="bi bi-chat-left-dots text-accent me-1"></i> Suggested Interview Probe:
                                            </span>
                                            <button type="button" class="btn btn-link p-0 text-accent text-decoration-none" style="font-size: 10px;" onclick="copyLayaProbeToNotes(<?= htmlspecialchars(json_encode($laya_guidance['suggested_interview_probe']['guidance_tip'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="bi bi-clipboard-plus"></i> Use in Notes
                                            </button>
                                        </div>
                                        <div class="text-muted-custom">
                                            <strong><?= htmlspecialchars($laya_guidance['suggested_interview_probe']['topic'] ?? 'Topic') ?>:</strong>
                                            <?= htmlspecialchars($laya_guidance['suggested_interview_probe']['guidance_tip'] ?? '') ?>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1" style="font-size: 10px;">
                                        <span class="text-muted-custom">
                                            <i class="bi bi-info-circle me-1"></i> Transparent AI: ModernBERT calibrated outputs.
                                        </span>
                                        <span class="text-muted-custom">
                                            Sincerity: <strong><?= htmlspecialchars($laya_guidance['intent_assessment']['sincerity_rating'] ?? 'Reviewed') ?></strong> (<span class="badge bg-light text-muted-custom border" style="font-size: 9px; padding: 1px 3px;"><?= $intent_prob ?>% Confidence</span>) &bull; Specificity: <strong><?= htmlspecialchars($laya_guidance['specificity_assessment']['summary'] ?? '') ?></strong> (<span class="badge bg-light text-muted-custom border" style="font-size: 9px; padding: 1px 3px;"><?= $spec_conf ?>% Confidence</span>)
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <p class="small text-muted-custom mb-0" style="font-size: 11px;">
                                        <i class="bi bi-shield me-1"></i> Standard manual evaluation mode. The supervisor maintains full discretion over candidate selection.
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Status Transition Form -->
                            <form action="review-app.php?id=<?= $target_app['id'] ?>" method="POST" class="form-paper">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                                
                                <div class="mb-3">
                                    <label class="form-label" for="eval-status">Update Candidate Stage <span class="text-danger">*</span></label>
                                    <select name="status" id="eval-status" class="form-select" onchange="toggleInterviewFields(this.value)">
                                        <option value="pending" <?= in_array($target_app['status'], ['pending', 'Pending Review']) ? 'selected' : '' ?>>Pending Review</option>
                                        <option value="under_review" <?= in_array($target_app['status'], ['under_review', 'under review', 'Under Evaluation']) ? 'selected' : '' ?>>Under Evaluation</option>
                                        <option value="interview_scheduled" <?= in_array($target_app['status'], ['interview_scheduled', 'Interview Scheduled']) ? 'selected' : '' ?>>Shortlist & Schedule Interview</option>
                                        <option value="accepted" <?= in_array($target_app['status'], ['accepted', 'Accepted / Hired']) ? 'selected' : '' ?>>Accept & Officially Hire</option>
                                        <option value="declined" <?= in_array($target_app['status'], ['declined', 'Declined / Position Filled']) ? 'selected' : '' ?>>Decline / Position Filled</option>
                                    </select>
                                </div>

                                <!-- Dynamic Interview Details Box -->
                                <?php
                                $is_interview_selected = in_array($target_app['status'], ['interview_scheduled', 'Interview Scheduled']);
                                $stored_int_date = $target_app['interview_date'] ?? '';
                                $is_stored_date_valid = (!empty($stored_int_date) && strtotime($stored_int_date) >= strtotime(date('Y-m-d')));
                                $default_int_date = $is_stored_date_valid ? $stored_int_date : date('Y-m-d', strtotime('+2 days'));
                                ?>
                                <div id="interviewBox" class="p-3 bg-cream rounded-4 border border-line mb-3" style="<?= !$is_interview_selected ? 'display:none;' : '' ?>">
                                    <h4 class="card-paper-title fs-6 mb-2">
                                        <i class="bi bi-calendar-event text-accent me-1"></i> Interview Logistics
                                    </h4>
                                    
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-1" for="int-date">Interview Date</label>
                                            <input type="date" name="interview_date" id="int-date" min="<?= date('Y-m-d') ?>" class="form-control" value="<?= htmlspecialchars($default_int_date) ?>" <?= !$is_interview_selected ? 'disabled' : '' ?>>
                                            <?php if (!empty($stored_int_date) && !$is_stored_date_valid): ?>
                                                <div class="small text-muted-custom mt-1" style="font-size: 11px;">
                                                    <i class="bi bi-clock-history me-1"></i> Prior: <?= format_display_date($stored_int_date) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-1" for="int-time">Interview Time</label>
                                            <select name="interview_time" id="int-time" class="form-select" <?= !$is_interview_selected ? 'disabled' : '' ?>>
                                                <?php
                                                $standard_times = [
                                                    '08:00 AM', '08:30 AM', '09:00 AM', '09:30 AM', 
                                                    '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
                                                    '01:00 PM', '01:30 PM', '02:00 PM', '02:30 PM', 
                                                    '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM', '05:00 PM'
                                                ];
                                                $cur_time = $target_app['interview_time'] ?? '10:00 AM';
                                                foreach ($standard_times as $t):
                                                ?>
                                                    <option value="<?= $t ?>" <?= (strcasecmp($cur_time, $t) === 0) ? 'selected' : '' ?>><?= $t ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small mb-1" for="int-venue">Venue / Room</label>
                                        <select name="interview_venue" id="int-venue" class="form-select" <?= !$is_interview_selected ? 'disabled' : '' ?>>
                                            <?php
                                            $dept_office = $user['office_location'] ?? ($user['department'] ?? 'Campus Main Office');
                                            $standard_venues = [
                                                $dept_office,
                                                'KLD Admin Building, 1st Floor, Room 102 (HR Office)',
                                                'KLD Tech Building, 3rd Floor, MIS Center',
                                                'KLD Main Building, 2nd Floor (Dean\'s Conference Room)',
                                                'KLD Library, Multi-Media Presentation Room',
                                                'Student Affairs & Services Office (SASO) - Room 204',
                                                'University Student Pavilion - Meeting Hall B',
                                                'Online via Google Meet (Video Call Link to be emailed)'
                                            ];
                                            $standard_venues = array_unique(array_filter($standard_venues));
                                            $cur_venue = $target_app['interview_venue'] ?? $dept_office;
                                            if (!in_array($cur_venue, $standard_venues) && !empty($cur_venue)) {
                                                array_unshift($standard_venues, $cur_venue);
                                            }
                                            foreach ($standard_venues as $v):
                                            ?>
                                                <option value="<?= htmlspecialchars($v) ?>" <?= ($cur_venue === $v) ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="supervisor_notes">Supervisor Remarks / Notes</label>
                                    <textarea name="supervisor_notes" id="supervisor_notes" rows="3" class="form-control" placeholder="Add specific feedback, instructions, or internal notes..."><?= htmlspecialchars($target_app['supervisor_notes'] ?? '') ?></textarea>
                                </div>

                                <button type="submit" id="btn-save-decision" class="btn-pill w-100 mb-2">
                                    <i class="bi bi-check2-circle"></i> Save Evaluation Decision
                                </button>
                                <a href="applicants.php" class="btn-pill-outline btn-pill-sm w-100 text-center">
                                    Return to Roster
                                </a>
                            </form>

                        </div>
                    </div>

                </div>

            </div>
        </main>

        <!-- PDF Resume Preview Modal -->
        <div class="modal fade" id="resumeModal" tabindex="-1" aria-labelledby="resumeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content rounded-4 border-line shadow-lg overflow-hidden">
                    <div class="modal-header bg-cream border-bottom border-line py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 me-auto">
                            <div class="icon-circle icon-circle-sm icon-circle-success">
                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-ink mb-0 fs-6" id="resumeModalLabel">Candidate Resume: <?= htmlspecialchars($target_app['student_name']) ?></h5>
                                <span class="small text-muted-custom" style="font-size: 11.5px;"><?= htmlspecialchars($target_app['resume_file'] ?? 'Student_Resume.pdf') ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <a href="../view-resume.php?app_id=<?= $target_app['id'] ?>" target="_blank" class="btn-pill-outline btn-pill-sm py-1 px-3 text-nowrap d-inline-flex align-items-center gap-1" style="font-size: 12px;">
                                <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                            </a>
                            <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body p-0 bg-dark position-relative" style="min-height: 580px;">
                        <iframe src="../view-resume.php?app_id=<?= $target_app['id'] ?>" style="width: 100%; height: 600px; border: none;" title="PDF Resume Viewer"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laya Metric Guide Modal -->
        <div class="modal fade" id="layaMetricGuideModal" tabindex="-1" aria-labelledby="layaMetricGuideModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border border-line shadow-sm">
                    <div class="modal-header border-bottom border-line py-3 px-4">
                        <h5 class="modal-title fs-6 fw-bold text-ink d-flex align-items-center gap-2" id="layaMetricGuideModalLabel">
                            <i class="bi bi-compass text-accent"></i> Understanding Laya Advisory Metrics
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 small text-muted-custom">
                        <p class="mb-3 text-ink">
                            All scores are computed by a fine-tuned <strong>ModernBERT neural engine</strong>. ModernBERT evaluates semantic context without keyword matching. Percentages are calibrated to assist—never replace—your hiring discretion:
                        </p>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="p-3 bg-cream rounded-3 border border-line">
                                <strong class="text-ink d-block mb-1">
                                    <i class="bi bi-shield-check text-primary me-1"></i> % Confidence (Certainty Rate)
                                </strong>
                                <span>Reflects statistical certainty based on explicit textual evidence in the student's degree and cover letter. Scores between <strong>35%–60%</strong> represent realistic, calibrated evidence (not arbitrary 99% hype).</span>
                            </div>
                            <div class="p-3 bg-cream rounded-3 border border-line">
                                <strong class="text-ink d-block mb-1">
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> % Risk (Academic Overload)
                                </strong>
                                <span>Estimates the likelihood that this assistantship's hours combined with academic coursework will cause schedule strain. <strong>Under 40%</strong> is a safe, balanced workload.</span>
                            </div>
                            <div class="p-3 bg-cream rounded-3 border border-line">
                                <strong class="text-ink d-block mb-1">
                                    <i class="bi bi-bar-chart-fill text-accent me-1"></i> Rubric Scores (0.0 to 3.0)
                                </strong>
                                <span>Academic rubric scale: <strong>0</strong> = Minimal evidence, <strong>1</strong> = Baseline fit, <strong>2</strong> = Solid match, <strong>3</strong> = Exemplary alignment.</span>
                            </div>
                        </div>
                        <p class="mb-0 text-muted-custom" style="font-size: 11px;">
                            <i class="bi bi-info-circle me-1"></i> Tip: Use the <strong>Suggested Interview Probe</strong> during the interview to verify areas where the student provided brief or partial information.
                        </p>
                    </div>
                    <div class="modal-footer border-top border-line py-2 px-4">
                        <button type="button" class="btn btn-sm btn-dark rounded-pill px-3" data-bs-dismiss="modal">Close Guide</button>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

<script>
function toggleInterviewFields(status) {
    const box = document.getElementById('interviewBox');
    if (box) {
        const isScheduled = (status === 'interview_scheduled');
        box.style.display = isScheduled ? 'block' : 'none';
        
        const inputs = box.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.disabled = !isScheduled;
            if (isScheduled && input.id === 'int-date') {
                const today = new Date().toISOString().split('T')[0];
                input.min = today;
                if (!input.value || input.value < today) {
                    const future = new Date();
                    future.setDate(future.getDate() + 2);
                    input.value = future.toISOString().split('T')[0];
                }
            }
        });
    }
}

function copyLayaProbeToNotes(text) {
    if (!text) return;
    const notes = document.getElementById('supervisor_notes');
    if (!notes) return;
    const prefix = "Suggested Interview Discussion Probe: ";
    if (notes.value.trim() === '') {
        notes.value = prefix + text;
    } else {
        notes.value = notes.value.trim() + "\n\n" + prefix + text;
    }
    notes.focus();
}

document.addEventListener('DOMContentLoaded', function() {

    const select = document.getElementById('eval-status');
    if (select) {
        toggleInterviewFields(select.value);
    }

    const decisionForm = document.querySelector('form.form-paper');
    if (decisionForm) {
        decisionForm.addEventListener('submit', function() {
            const btn = document.getElementById('btn-save-decision');
            if (btn) {
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving Decision...';
                }, 10);
            }
        });
    }
});
</script>

