<?php
/**
 * Campus Job Posting System — Shared UI Render Helpers
 * Blueprint & Design Law Single Source of Truth (COAL101)
 */

if (!function_exists('render_page_head')) {
    /**
     * Renders standardized inner-page header
     */
    function render_page_head($eyebrow, $title, $lead = '', $actionsHtml = '', $eyebrowClass = '') {
        $badgeClass = !empty($eyebrowClass) ? $eyebrowClass : 'badge rounded-pill d-inline-flex align-items-center gap-1 bg-success text-white shadow-sm text-wrap text-start lh-sm py-2 px-3 border-0';
        ?>
        <div class="page-head reveal-fade-rise">
            <div class="page-head-content">
                <?php if (!empty($eyebrow)): ?>
                    <div class="mb-2">
                        <span class="<?= htmlspecialchars($badgeClass) ?>" style="max-width: 100%; white-space: normal; font-size: 12px; font-weight: 600; letter-spacing: 0.02em;">
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
        <div class="alert-paper <?= $type_class ?> alert-paper--floating alert alert-dismissible alert-auto-dismiss fade show mb-0" role="alert">
            <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                <i class="bi <?= $icon ?> fs-5 flex-shrink-0"></i>
                <div class="fw-semibold small text-ink flex-grow-1 min-w-0 text-break pe-2"><?= htmlspecialchars($message) ?></div>
            </div>
            <button type="button" class="btn-close flex-shrink-0 align-self-start" data-bs-dismiss="alert" aria-label="Close"></button>
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
            return '<span class="badge-status--declined"><i class="bi bi-x-circle-fill"></i> <span class="d-none d-sm-inline">Declined / Position Filled</span><span class="d-sm-none">Declined / Filled</span></span>';
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
        <div class="metric h-100">
            <div class="d-flex flex-column justify-content-center min-w-0 flex-grow-1">
                <div class="metric-lbl"><?= htmlspecialchars($label) ?></div>
                <div class="metric-val"><?= htmlspecialchars((string)$value) ?></div>
            </div>
            <div class="icon-circle icon-circle-success flex-shrink-0">
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
        
        // Tags/badges collection without duplicating employer type
        $raw_badges = $job['badges'] ?? $job['tags'] ?? [$job['job_type'] ?? 'Student Assistant', $job['work_setup'] ?? 'On-Campus'];
        if (!is_array($raw_badges)) {
            $raw_badges = explode(',', (string)$raw_badges);
        }
        $display_badges = [];
        foreach ($raw_badges as $b) {
            $trimmed = trim((string)$b);
            if (!empty($trimmed) && strcasecmp($trimmed, 'Approved Partner') !== 0 && strcasecmp($trimmed, 'University Office') !== 0) {
                $display_badges[] = $trimmed;
            }
        }

        // Parse pay rate creatively
        $pay_raw = trim((string)$pay_rate);
        $pay_amount = $pay_raw;
        $pay_unit = '';
        if (preg_match('/^([^\/\b]+?)(?:\s*(?:\/|per)\s*(.+))?$/iu', $pay_raw, $m)) {
            $pay_amount = trim($m[1]);
            $pay_unit = isset($m[2]) ? trim($m[2]) : '';
        }
        // Resolve cover photo:
        $cover_image = '';
        if (!empty($job['image'])) {
            $img = $job['image'];
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/')) {
                $cover_image = $img;
            } else {
                $cover_image = $base_url . ltrim($img, '/');
            }
        } else {
            // Curated local offline domain fallbacks
            $cat_name = strtolower($job['category'] ?? '');
            if (str_contains($cat_name, 'tech') || str_contains($cat_name, 'it') || str_contains($cat_name, 'computer')) {
                $cover_image = $base_url . 'assets/img/categories/cat-tech.jpg';
            } elseif (str_contains($cat_name, 'lib') || str_contains($cat_name, 'book')) {
                $cover_image = $base_url . 'assets/img/categories/cat-library.jpg';
            } elseif (str_contains($cat_name, 'admin') || str_contains($cat_name, 'clerk') || str_contains($cat_name, 'office')) {
                $cover_image = $base_url . 'assets/img/categories/cat-admin.jpg';
            } elseif (str_contains($cat_name, 'sci') || str_contains($cat_name, 'lab')) {
                $cover_image = $base_url . 'assets/img/categories/cat-lab.jpg';
            } elseif (str_contains($cat_name, 'tutor') || str_contains($cat_name, 'peer')) {
                $cover_image = $base_url . 'assets/img/categories/cat-tutor.jpg';
            } elseif (str_contains($cat_name, 'sport') || str_contains($cat_name, 'athletic')) {
                $cover_image = $base_url . 'assets/img/categories/cat-sports.jpg';
            } else {
                $cover_image = $base_url . 'assets/img/categories/cat-general.jpg';
            }
        }
        ?>
        <div class="card-paper p-0 overflow-hidden card-hover d-flex flex-column h-100 position-relative shadow-sm" style="border-radius: var(--radius-card, 16px);">
            <!-- 1. Top Cover Picture Section -->
            <div class="position-relative overflow-hidden" style="height: 165px; background-color: #f1f3f4;">
                <a href="<?= $base_url ?>student/job-details.php?id=<?= $id ?>" class="d-block w-100 h-100 text-decoration-none">
                    <img src="<?= htmlspecialchars($cover_image) ?>" 
                         alt="<?= htmlspecialchars($title) ?>" 
                         class="w-100 h-100" 
                         loading="lazy"
                         style="object-fit: cover; object-position: center; transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);"
                         onmouseover="this.style.transform='scale(1.06)'"
                         onmouseout="this.style.transform='scale(1.0)'"
                         onerror="this.src='<?= $base_url ?>assets/img/categories/cat-general.jpg';">
                </a>
                
                <!-- Subtle Gradient Overlay -->
                <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="background: linear-gradient(180deg, rgba(17, 24, 39, 0.55) 0%, rgba(17, 24, 39, 0.05) 45%, rgba(17, 24, 39, 0.72) 100%);"></div>

                <!-- Top Badges Overlay -->
                <div class="position-absolute top-0 start-0 end-0 p-3 d-flex justify-content-between align-items-start pointer-events-none">
                    <?php if ($is_partner): ?>
                        <span class="badge rounded-pill shadow-sm border-0 d-inline-flex align-items-center gap-1 px-2 py-1 fw-bold" style="font-size: 11px; backdrop-filter: blur(8px); background: rgba(255, 255, 255, 0.95); color: #0d3b2e;">
                            <i class="bi bi-patch-check-fill text-accent"></i> Approved Partner
                        </span>
                    <?php else: ?>
                        <span class="badge rounded-pill shadow-sm border-0 d-inline-flex align-items-center gap-1 px-2 py-1 fw-bold" style="font-size: 11px; backdrop-filter: blur(8px); background: rgba(255, 255, 255, 0.95); color: #0d3b2e;">
                            <i class="bi bi-bank text-accent"></i> University Office
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($job['image']) || !empty($job['is_featured'])): ?>
                        <span class="badge rounded-pill shadow-sm border border-white-50 d-inline-flex align-items-center gap-1 px-2 py-1 fw-semibold text-white" style="font-size: 11px; backdrop-filter: blur(8px); background: rgba(17, 24, 39, 0.75);">
                            <i class="bi bi-stars text-warning"></i> Featured
                        </span>
                    <?php else: ?>
                        <span class="badge rounded-pill shadow-sm border border-white-50 d-inline-flex align-items-center gap-1 px-2 py-1 fw-semibold text-white" style="font-size: 11px; backdrop-filter: blur(8px); background: rgba(17, 24, 39, 0.75);">
                            <i class="bi bi-geo-alt-fill text-white-50"></i> <?= htmlspecialchars($job['work_setup'] ?? 'On-Campus') ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Bottom Badges Overlay: Category & Pay Rate -->
                <div class="position-absolute bottom-0 start-0 end-0 p-3 d-flex justify-content-between align-items-end pointer-events-none">
                    <span class="badge rounded-pill text-white border border-white-50 px-2 py-1 text-truncate" style="font-size: 11px; max-width: 170px; background: rgba(0, 0, 0, 0.72) !important;">
                        <i class="bi bi-tag-fill text-accent me-1"></i><?= htmlspecialchars($job['category'] ?? 'Campus Role') ?>
                    </span>
                    <span class="badge rounded-pill text-white border border-white-50 px-2 py-1 fw-bold" style="font-size: 11px; background: rgba(13, 59, 46, 0.9) !important;">
                        <?= htmlspecialchars($pay_raw) ?>
                    </span>
                </div>
            </div>

            <!-- 2. Card Body Content -->
            <div class="p-4 d-flex flex-column flex-grow-1">
                <!-- Job Title and Employer Info -->
                <div class="job-card-heading mb-3">
                    <h3 class="card-paper-title fs-5 mb-2">
                        <a href="<?= $base_url ?>student/job-details.php?id=<?= $id ?>" class="text-decoration-none text-ink">
                            <?= htmlspecialchars($title) ?>
                        </a>
                    </h3>

                    <div class="d-flex align-items-center flex-wrap gap-2 text-muted-custom small">
                        <span class="d-inline-flex align-items-center gap-1">
                            <i class="bi <?= $is_partner ? 'bi-patch-check-fill text-accent' : 'bi-building' ?>"></i>
                            <span class="fw-semibold text-ink"><?= htmlspecialchars($org_name) ?></span>
                        </span>
                        <span>&bull;</span>
                        <span class="d-inline-flex align-items-center gap-1 text-truncate">
                            <i class="bi bi-geo-alt"></i>
                            <span><?= htmlspecialchars($location) ?></span>
                        </span>
                    </div>
                </div>

                <!-- Tags Section -->
                <?php if (!empty($display_badges)): ?>
                    <div class="job-card-tags d-flex flex-wrap gap-1 mb-3">
                        <?php foreach ($display_badges as $b): ?>
                            <span class="chip chip-sm"><?= htmlspecialchars($b) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Slots, Progress, Deadline, Actions -->
                <div class="mt-auto pt-3 border-top border-line">
                    <div class="d-flex justify-content-between small text-muted-custom mb-1">
                        <span><?= $slots_filled ?> of <?= $slots_total ?> slots filled</span>
                        <span class="fw-bold text-ink"><?= $pct ?>%</span>
                    </div>
                    <div class="progress-paper mb-3">
                        <div class="progress-paper-bar" style="width: <?= $pct ?>%;"></div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="small text-muted-custom text-truncate">
                            <i class="bi bi-calendar-event me-1"></i> <?= htmlspecialchars($deadline) ?>
                        </span>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <a href="<?= $base_url ?>student/job-details.php?id=<?= $id ?>" class="btn-pill-outline btn-pill-sm text-decoration-none">
                                Details
                            </a>
                            <a href="<?= $base_url ?>student/apply.php?id=<?= $id ?>&job_id=<?= $id ?>" class="btn-pill btn-pill-sm text-decoration-none">
                                Apply
                            </a>
                        </div>
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
                <div class="stepper-dot">
                    <?= ($current_step > 1) ? '<i class="bi bi-check-lg"></i>' : '1' ?>
                </div>
                <div class="stepper-label">Pending Review</div>
            </div>
            <div class="stepper-step <?= ($current_step > 2) ? 'is-completed' : ($current_step === 2 && !$is_declined ? 'is-active' : '') ?>">
                <div class="stepper-dot">
                    <?= ($current_step > 2) ? '<i class="bi bi-check-lg"></i>' : '2' ?>
                </div>
                <div class="stepper-label">Under Evaluation</div>
            </div>
            <div class="stepper-step <?= ($current_step > 3) ? 'is-completed' : ($current_step === 3 && !$is_declined ? 'is-active' : '') ?>">
                <div class="stepper-dot">
                    <?= ($current_step > 3) ? '<i class="bi bi-check-lg"></i>' : '3' ?>
                </div>
                <div class="stepper-label">Interview Scheduled</div>
            </div>
            <div class="stepper-step <?= ($current_step === 4 && !$is_declined) ? 'is-completed is-active' : ($is_declined ? 'is-declined is-active' : '') ?>">
                <div class="stepper-dot">
                    <?php if ($is_declined): ?>
                        <i class="bi bi-x-lg"></i>
                    <?php elseif ($current_step === 4): ?>
                        <i class="bi bi-check-lg"></i>
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
        if (is_string($selected)) {
            $decoded = json_decode($selected, true);
            $selected = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($selected)) {
            $selected = [];
        }

        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $slots = ['Morning (8AM–12NN)', 'Afternoon (1PM–5PM)', 'Evening (5PM–8PM)'];
        ?>
        <div class="small text-muted-custom d-md-none mb-2" style="font-size: 11px;">
            <i class="bi bi-arrow-left-right text-accent me-1"></i> Swipe horizontally to view full schedule (Mon–Sat)
        </div>
        <div class="table-responsive matrix-responsive-container">
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
                                <td class="<?= $is_checked ? 'matrix-cell-selected' : '' ?>">
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
