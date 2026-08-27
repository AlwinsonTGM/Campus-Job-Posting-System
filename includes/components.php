<?php
/**
 * Campus Job Posting System — Shared UI Render Helpers
 * Blueprint & Design Law Single Source of Truth (COAL101)
 */

if (!function_exists('render_page_head')) {
    /**
     * Renders standardized inner-page header
     */
    function render_page_head($eyebrow, $title, $lead = '', $actionsHtml = '') {
        ?>
        <div class="page-head reveal-fade-rise">
            <div class="page-head-content">
                <?php if (!empty($eyebrow)): ?>
                    <div class="mb-2">
                        <span class="pill-badge">
                            <?= $eyebrow ?>
                        </span>
                    </div>
                <?php endif; ?>
                <h1 class="page-head-title"><?= htmlspecialchars($title) ?></h1>
                <?php if (!empty($lead)): ?>
                    <p class="page-head-lead"><?= htmlspecialchars($lead) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($actionsHtml)): ?>
                <div class="page-head-actions">
                    <?= $actionsHtml ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('render_flash')) {
    /**
     * Renders dismissible alert-paper flash message
     */
    function render_flash() {
        if (!isset($_SESSION['flash'])) {
            return;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        $type = $flash['type'] ?? 'info';
        $message = $flash['message'] ?? '';
        $type_class = match ($type) {
            'success' => 'alert-paper--success',
            'danger', 'error' => 'alert-paper--danger',
            'warning' => 'alert-paper--warning',
            default => 'alert-paper--info'
        };

        $icon = match ($type) {
            'success' => 'bi-check-circle-fill text-accent',
            'danger', 'error' => 'bi-exclamation-octagon-fill text-danger',
            'warning' => 'bi-exclamation-triangle-fill text-warning',
            default => 'bi-info-circle-fill text-primary'
        };
        ?>
        <div class="alert-paper <?= $type_class ?> alert alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi <?= $icon ?> fs-5"></i>
                <div class="fw-semibold small text-ink"><?= htmlspecialchars($message) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
    }
}

if (!function_exists('render_status_badge')) {
    /**
     * Single Source of Truth for Status Badges
     * Maps exact blueprint strings & internal keys to semantic status tokens
     */
    function render_status_badge($status) {
        $raw = trim((string)$status);
        $normalized = strtolower($raw);

        // Blueprint string mappings
        if ($raw === 'Pending Review' || $normalized === 'pending' || $normalized === 'pending review') {
            return '<span class="badge-status--pending"><i class="bi bi-hourglass-split"></i> Pending Review</span>';
        }
        if ($raw === 'Under Evaluation' || $raw === 'Under Review' || $normalized === 'under_review' || $normalized === 'under evaluation' || $normalized === 'under review') {
            return '<span class="badge-status--review"><i class="bi bi-search"></i> Under Evaluation</span>';
        }
        if ($raw === 'Interview Scheduled' || $normalized === 'interview_scheduled' || $normalized === 'interview scheduled' || $normalized === 'interview') {
            return '<span class="badge-status--interview"><i class="bi bi-calendar-event"></i> Interview Scheduled</span>';
        }
        if ($raw === 'Accepted / Hired' || $raw === 'Accepted' || $normalized === 'accepted' || $normalized === 'hired' || $normalized === 'accepted / hired') {
            return '<span class="badge-status--accepted"><i class="bi bi-check-circle-fill"></i> Accepted / Hired</span>';
        }
        if ($raw === 'Declined / Position Filled' || $raw === 'Declined' || $raw === 'Rejected' || $normalized === 'declined' || $normalized === 'rejected' || $normalized === 'declined / position filled') {
            return '<span class="badge-status--declined"><i class="bi bi-x-circle-fill"></i> Declined / Position Filled</span>';
        }
        if ($raw === 'Active' || $normalized === 'active') {
            return '<span class="badge-status--accepted"><i class="bi bi-check-circle"></i> Active</span>';
        }
        if ($raw === 'Suspended' || $normalized === 'suspended') {
            return '<span class="badge-status--declined"><i class="bi bi-slash-circle"></i> Suspended</span>';
        }

        return '<span class="chip">' . htmlspecialchars($raw) . '</span>';
    }
}

if (!function_exists('render_metric')) {
    /**
     * Renders standard metric KPI card
     */
    function render_metric($value, $label, $icon = 'bi-bar-chart-fill') {
        ?>
        <div class="metric">
            <div>
                <div class="metric-lbl"><?= htmlspecialchars($label) ?></div>
                <div class="metric-val"><?= htmlspecialchars((string)$value) ?></div>
            </div>
            <div class="icon-circle icon-circle-accent">
                <i class="bi <?= htmlspecialchars($icon) ?>"></i>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('render_job_card')) {
    /**
     * Renders standard Job Card for listings & dashboards
     */
    function render_job_card($job, $base_url = '') {
        $id = $job['id'] ?? 0;
        $title = $job['title'] ?? 'Campus Assistantship';
        $org_name = $job['organization_name'] ?? ($job['department'] ?? 'University Department');
        $location = $job['location'] ?? 'Campus Main Office';
        $pay_rate = $job['pay_rate'] ?? '₱80.00 / hour';
        $deadline = $job['deadline'] ?? 'Open';
        $slots_total = (int)($job['slots_total'] ?? $job['vacancies'] ?? 1);
        $slots_filled = (int)($job['slots_filled'] ?? 0);
        $pct = ($slots_total > 0) ? round(($slots_filled / $slots_total) * 100) : 0;
        $is_partner = ($job['employer_type'] ?? '') === 'approved_partner';
        $badges = $job['badges'] ?? [$job['job_type'] ?? 'Student Assistant', $job['work_setup'] ?? 'On-Campus'];
        ?>
        <div class="card-paper card-hover d-flex flex-column h-100 position-relative">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <div class="d-flex flex-wrap gap-1">
                    <?php if ($is_partner): ?>
                        <span class="chip chip-selectable" style="background-color: var(--ink); color: #fff;">
                            <i class="bi bi-patch-check-fill text-accent"></i> Approved Partner
                        </span>
                    <?php endif; ?>
                    <?php foreach ($badges as $b): ?>
                        <span class="chip"><?= htmlspecialchars($b) ?></span>
                    <?php endforeach; ?>
                </div>
                <span class="badge-status--accepted"><?= htmlspecialchars($pay_rate) ?></span>
            </div>

            <h3 class="card-paper-title mb-1 mt-1">
                <a href="<?= $base_url ?>student/job-details.php?id=<?= $id ?>" class="text-decoration-none text-ink">
                    <?= htmlspecialchars($title) ?>
                </a>
            </h3>

            <div class="d-flex align-items-center gap-1 text-muted-custom small mb-3">
                <i class="bi <?= $is_partner ? 'bi-patch-check-fill text-accent' : 'bi-building' ?>"></i>
                <span><?= htmlspecialchars($org_name) ?></span>
                <span>&bull;</span>
                <i class="bi bi-geo-alt"></i>
                <span><?= htmlspecialchars($location) ?></span>
            </div>

            <div class="mt-auto pt-3 border-top border-line">
                <div class="d-flex justify-content-between small text-muted-custom mb-1">
                    <span><?= $slots_filled ?> of <?= $slots_total ?> slots filled</span>
                    <span class="fw-bold text-ink"><?= $pct ?>%</span>
                </div>
                <div class="progress-paper mb-3">
                    <div class="progress-paper-bar" style="width: <?= $pct ?>%;"></div>
                </div>

                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="small text-muted-custom">
                        <i class="bi bi-calendar-event me-1"></i> <?= htmlspecialchars($deadline) ?>
                    </span>
                    <div class="d-flex gap-2">
                        <a href="<?= $base_url ?>student/job-details.php?id=<?= $id ?>" class="btn-pill-outline btn-pill-sm">
                            Details
                        </a>
                        <a href="<?= $base_url ?>student/apply.php?id=<?= $id ?>&job_id=<?= $id ?>" class="btn-pill btn-pill-sm">
                            Apply
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('render_empty_state')) {
    /**
     * Renders standard Empty State component
     */
    function render_empty_state($icon, $title, $body, $ctaHref = '', $ctaLabel = '') {
        ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi <?= htmlspecialchars($icon) ?>"></i>
            </div>
            <h3 class="empty-state-title"><?= htmlspecialchars($title) ?></h3>
            <p class="empty-state-body"><?= htmlspecialchars($body) ?></p>
            <?php if (!empty($ctaHref) && !empty($ctaLabel)): ?>
                <a href="<?= htmlspecialchars($ctaHref) ?>" class="btn-pill">
                    <?= htmlspecialchars($ctaLabel) ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('render_stepper')) {
    /**
     * Renders 4-Step Application Progress Stepper (with Declined terminal state)
     */
    function render_stepper($status) {
        $raw = trim((string)$status);
        $normalized = strtolower($raw);

        $is_declined = in_array($normalized, ['declined', 'rejected', 'declined / position filled']);
        
        $current_step = 1;
        if ($normalized === 'pending' || $raw === 'Pending Review' || $normalized === 'pending review') {
            $current_step = 1;
        } elseif ($normalized === 'under_review' || $raw === 'Under Evaluation' || $raw === 'Under Review' || $normalized === 'under evaluation') {
            $current_step = 2;
        } elseif ($normalized === 'interview_scheduled' || $raw === 'Interview Scheduled' || $normalized === 'interview scheduled') {
            $current_step = 3;
        } elseif ($normalized === 'accepted' || $raw === 'Accepted / Hired' || $normalized === 'accepted / hired') {
            $current_step = 4;
        }
        ?>
        <div class="stepper">
            <div class="stepper-step <?= ($current_step > 1) ? 'is-completed' : ($current_step === 1 && !$is_declined ? 'is-active' : '') ?>">
                <div class="stepper-dot">1</div>
                <div class="stepper-label">Pending Review</div>
            </div>
            <div class="stepper-step <?= ($current_step > 2) ? 'is-completed' : ($current_step === 2 && !$is_declined ? 'is-active' : '') ?>">
                <div class="stepper-dot">2</div>
                <div class="stepper-label">Under Evaluation</div>
            </div>
            <div class="stepper-step <?= ($current_step > 3) ? 'is-completed' : ($current_step === 3 && !$is_declined ? 'is-active' : '') ?>">
                <div class="stepper-dot">3</div>
                <div class="stepper-label">Interview Scheduled</div>
            </div>
            <div class="stepper-step <?= ($current_step === 4 && !$is_declined) ? 'is-completed is-active' : ($is_declined ? 'is-declined is-active' : '') ?>">
                <div class="stepper-dot">
                    <?php if ($is_declined): ?>
                        <i class="bi bi-x"></i>
                    <?php else: ?>
                        4
                    <?php endif; ?>
                </div>
                <div class="stepper-label">
                    <?= $is_declined ? 'Declined / Filled' : 'Accepted / Hired' ?>
                </div>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('render_availability_matrix')) {
    /**
     * Renders Mon–Sat x Timeslots Availability Checkbox Matrix
     */
    function render_availability_matrix($selected = [], $name = 'availability[]', $readonly = false) {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $slots = ['Morning (8AM–12NN)', 'Afternoon (1PM–5PM)', 'Evening (5PM–8PM)'];
        ?>
        <div class="table-responsive">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th style="width: 25%; text-align: left;">Shift Time</th>
                        <?php foreach ($days as $day): ?>
                            <th><?= $day ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slots as $slot): ?>
                        <tr>
                            <td class="small fw-bold text-ink" style="text-align: left; background-color: var(--cream);">
                                <?= htmlspecialchars($slot) ?>
                            </td>
                            <?php foreach ($days as $day): 
                                $key = "{$day} - {$slot}";
                                $is_checked = in_array($key, $selected) || in_array($day, $selected);
                            ?>
                                <td>
                                    <input 
                                        type="checkbox" 
                                        name="<?= htmlspecialchars($name) ?>" 
                                        value="<?= htmlspecialchars($key) ?>" 
                                        class="matrix-check form-check-input"
                                        <?= $is_checked ? 'checked' : '' ?>
                                        <?= $readonly ? 'disabled' : '' ?>
                                    >
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
