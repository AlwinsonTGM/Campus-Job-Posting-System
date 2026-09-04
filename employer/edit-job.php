<?php
/**
 * Campus Job Posting System - Edit Job Requisition Form
 * Archetype C: Detail & Sidebar Action Form (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$job_id = $_GET['id'] ?? null;
$job = get_job_by_id($job_id);

if (!$job) {
    set_flash('danger', 'The specified job requisition could not be found.');
    header('Location: dashboard.php');
    exit;
}

if (!can_manage_job($job, $user)) {
    set_flash('danger', 'Unauthorized: You can only edit requisitions posted by your office.');
    header('Location: dashboard.php');
    exit;
}

$categories = get_categories();
$job_types = get_job_types();
$work_setups = get_work_setups();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = $_POST['category'] ?? $job['category'];
        $job_type = $_POST['job_type'] ?? ($job['job_type'] ?? 'Student Assistant');
        $work_setup = $_POST['work_setup'] ?? ($job['work_setup'] ?? 'On-Campus');
        $location = trim($_POST['location'] ?? $job['location']);
        
        // Separated stipend rate
        $pay_amount = trim($_POST['pay_amount'] ?? '');
        $pay_period = trim($_POST['pay_period'] ?? '/ hour');

        if (is_numeric($pay_amount) && (float)$pay_amount > 0) {
            $formatted_amt = number_format((float)$pay_amount, 2);
            if ($pay_period === 'fixed stipend') {
                $pay_rate = '₱' . $formatted_amt . ' fixed stipend';
                $pay_type = 'Fixed Stipend';
            } else {
                $pay_rate = '₱' . $formatted_amt . ' ' . $pay_period;
                if (stripos($pay_period, 'hour') !== false) {
                    $pay_type = 'Hourly';
                } elseif (stripos($pay_period, 'month') !== false) {
                    $pay_type = 'Monthly';
                } elseif (stripos($pay_period, 'day') !== false) {
                    $pay_type = 'Daily';
                } elseif (stripos($pay_period, 'sem') !== false) {
                    $pay_type = 'Per Semester';
                } else {
                    $pay_type = 'Stipend';
                }
            }
        } elseif (!empty($_POST['pay_rate'])) {
            $pay_rate = trim($_POST['pay_rate']);
            $pay_type = $job['pay_type'] ?? 'Hourly';
        } else {
            $pay_rate = $job['pay_rate'] ?? '₱80.00 / hour';
            $pay_type = $job['pay_type'] ?? 'Hourly';
        }

        $hours_per_week = trim($_POST['hours_per_week'] ?? $job['hours_per_week']);
        $vacancies = (int)($_POST['vacancies'] ?? $job['vacancies']);
        $deadline = $_POST['deadline'] ?? $job['deadline'];
        $status = $_POST['status'] ?? $job['status'];
        $description = trim($_POST['description'] ?? $job['description']);
        
        // Dynamic lines handling
        $raw_resp = $_POST['responsibilities'] ?? [];
        $responsibilities = is_array($raw_resp)
            ? array_values(array_filter(array_map('trim', $raw_resp), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_resp)), fn($v) => $v !== ''));

        $raw_qual = $_POST['qualifications'] ?? [];
        $qualifications = is_array($raw_qual)
            ? array_values(array_filter(array_map('trim', $raw_qual), fn($v) => $v !== ''))
            : array_values(array_filter(array_map('trim', explode("\n", (string)$raw_qual)), fn($v) => $v !== ''));

        if (empty($title) || empty($description)) {
            $error = 'Please provide the vacancy title and detailed description.';
        } elseif ($vacancies < 1) {
            $error = 'Vacancy quota must be at least 1 position.';
        } else {
            // Resolve category_id
            $category_id = (int)($job['category_id'] ?? 3);
            foreach ($categories as $cat) {
                if (strcasecmp($cat['name'], $category) === 0) {
                    $category_id = (int)$cat['id'];
                    break;
                }
            }

            $photo_file = $_FILES['job_photo'] ?? null;
            $remove_photo = !empty($_POST['remove_photo']);

            update_job($job['id'], [
                'title' => $title,
                'category' => $category,
                'category_id' => $category_id,
                'job_type' => $job_type,
                'work_setup' => $work_setup,
                'location' => $location,
                'pay_rate' => $pay_rate,
                'pay_type' => $pay_type,
                'hours_per_week' => $hours_per_week,
                'vacancies' => $vacancies,
                'deadline' => $deadline,
                'status' => $status,
                'description' => $description,
                'remove_photo' => $remove_photo,
                'responsibilities' => $responsibilities,
                'qualifications' => $qualifications
            ], $photo_file);

            set_flash('success', "Vacancy '{$title}' updated successfully!");
            header('Location: dashboard.php');
            exit;
        }
    }
}

// Parse existing stipend amount and period
$parsed_amount = '';
$parsed_period = '/ hour';
if (!empty($job['pay_rate'])) {
    if (preg_match('/(?:₱|PHP)?\s*([\d,]+(?:\.\d+)?)\s*(?:\/|\bper\b)?\s*(.+)?/iu', $job['pay_rate'], $pm)) {
        $parsed_amount = str_replace(',', '', trim($pm[1]));
        $raw_period = trim($pm[2] ?? '');
        if (stripos($raw_period, 'hour') !== false || stripos($raw_period, 'hr') !== false) {
            $parsed_period = '/ hour';
        } elseif (stripos($raw_period, 'day') !== false) {
            $parsed_period = '/ day';
        } elseif (stripos($raw_period, 'week') !== false) {
            $parsed_period = '/ week';
        } elseif (stripos($raw_period, 'month') !== false || stripos($raw_period, 'mo') !== false) {
            $parsed_period = '/ month';
        } elseif (stripos($raw_period, 'sem') !== false) {
            $parsed_period = '/ semester';
        } elseif (stripos($raw_period, 'fixed') !== false) {
            $parsed_period = 'fixed stipend';
        } elseif (!empty($raw_period)) {
            $parsed_period = '/ ' . $raw_period;
        }
    }
}

$resp_items = is_array($job['responsibilities'] ?? null) ? $job['responsibilities'] : (!empty($job['responsibilities']) ? explode("\n", $job['responsibilities']) : []);
$qual_items = is_array($job['qualifications'] ?? null) ? $job['qualifications'] : (!empty($job['qualifications']) ? explode("\n", $job['qualifications']) : []);

$page_title = 'Edit ' . $job['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Back Link & Page Head -->
                <div class="mb-4">
                    <a href="dashboard.php" class="text-ink fw-bold small text-decoration-none d-inline-flex align-items-center gap-1 mb-3">
                        <i class="bi bi-arrow-left"></i> Back to Employer Dashboard
                    </a>
                    
                    <?php
                    render_page_head(
                        '<i class="bi bi-pencil-square text-accent me-1"></i> Edit Requisition',
                        'Edit Vacancy: ' . $job['title'],
                        'Update duties, requirements, quota, or application deadlines for this opening.'
                    );
                    ?>
                </div>

                <?php if ($error): ?>
                    <div class="alert-paper alert-paper--danger mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                            <div class="small fw-semibold text-ink"><?= htmlspecialchars($error) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="edit-job.php?id=<?= $job['id'] ?>" method="POST" enctype="multipart/form-data" class="form-paper" id="edit-job-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <div class="row g-4 mb-5">
                        
                        <!-- Left 8-col: Role Overview & Responsibilities -->
                        <div class="col-lg-8">
                            <div class="card-paper p-4 p-md-4 mb-4">
                                <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                    <i class="bi bi-card-text text-accent me-2"></i> 1. Vacancy Information
                                </h3>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label" for="edit-title">Vacancy Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="edit-title" class="form-control" value="<?= htmlspecialchars($job['title']) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="edit-cat">Job Family Category</label>
                                        <select name="category" id="edit-cat" class="form-select">
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($job['category'] === $cat['name']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="edit-type">Opportunity Type</label>
                                        <select name="job_type" id="edit-type" class="form-select">
                                            <?php foreach ($job_types as $k => $label): ?>
                                                <option value="<?= htmlspecialchars($k) ?>" <?= (($job['job_type'] ?? '') === $k) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="edit-setup">Workplace Setup</label>
                                        <select name="work_setup" id="edit-setup" class="form-select">
                                            <?php foreach ($work_setups as $k => $label): ?>
                                                <option value="<?= htmlspecialchars($k) ?>" <?= (($job['work_setup'] ?? '') === $k) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-desc">Detailed Job Description <span class="text-danger">*</span></label>
                                    <textarea name="description" id="edit-desc" rows="4" class="form-control" required><?= htmlspecialchars($job['description']) ?></textarea>
                                </div>

                                <!-- Key Duties & Responsibilities (Addable Input Bar) -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label mb-0 fw-bold">Key Duties & Responsibilities</label>
                                        <span class="small text-muted-custom">Add lines one by one</span>
                                    </div>
                                    <div class="small text-muted-custom mb-2">Type a task and click <strong class="text-ink">+</strong> (or press Enter) to add.</div>

                                    <!-- Addable Input Bar -->
                                    <div class="input-group mb-2">
                                        <input type="text" id="resp-input-bar" class="form-control" placeholder="e.g. Assist student visitors with computer lab login...">
                                        <button type="button" class="btn btn-accent px-3 fw-bold" id="btn-add-resp" title="Add Duty">
                                            <i class="bi bi-plus-lg me-1"></i> Add Line
                                        </button>
                                    </div>

                                    <!-- Dynamic Lines Container -->
                                    <div class="dynamic-lines-wrapper" id="resp-items-container">
                                        <div class="dynamic-empty-hint <?= !empty($resp_items) ? 'd-none' : '' ?>" id="resp-empty-hint">
                                            <i class="bi bi-info-circle me-1"></i> No duties added yet. Use the bar above and click <strong>+ Add Line</strong>.
                                        </div>
                                        <?php foreach ($resp_items as $idx => $r_text): ?>
                                            <?php $r_trimmed = trim((string)$r_text); if (empty($r_trimmed)) continue; ?>
                                            <div class="dynamic-line-item d-flex align-items-center gap-2 mb-2">
                                                <span class="dynamic-line-badge"><?= $idx + 1 ?></span>
                                                <input type="text" name="responsibilities[]" class="form-control form-control-sm border-0 bg-transparent px-2" value="<?= htmlspecialchars($r_trimmed) ?>" placeholder="Key duty or responsibility...">
                                                <button type="button" class="btn btn-outline-danger btn-sm dynamic-line-btn-del" title="Remove line">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Qualifications & Requirements (Addable Input Bar) -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label mb-0 fw-bold">Qualifications & Requirements</label>
                                        <span class="small text-muted-custom">Add lines one by one</span>
                                    </div>
                                    <div class="small text-muted-custom mb-2">Type a requirement and click <strong class="text-ink">+</strong> (or press Enter) to add.</div>

                                    <!-- Addable Input Bar -->
                                    <div class="input-group mb-2">
                                        <input type="text" id="qual-input-bar" class="form-control" placeholder="e.g. GWA of 2.25 or better...">
                                        <button type="button" class="btn btn-accent px-3 fw-bold" id="btn-add-qual" title="Add Qualification">
                                            <i class="bi bi-plus-lg me-1"></i> Add Line
                                        </button>
                                    </div>

                                    <!-- Dynamic Lines Container -->
                                    <div class="dynamic-lines-wrapper" id="qual-items-container">
                                        <div class="dynamic-empty-hint <?= !empty($qual_items) ? 'd-none' : '' ?>" id="qual-empty-hint">
                                            <i class="bi bi-info-circle me-1"></i> No qualifications added yet. Use the bar above and click <strong>+ Add Line</strong>.
                                        </div>
                                        <?php foreach ($qual_items as $idx => $q_text): ?>
                                            <?php $q_trimmed = trim((string)$q_text); if (empty($q_trimmed)) continue; ?>
                                            <div class="dynamic-line-item d-flex align-items-center gap-2 mb-2">
                                                <span class="dynamic-line-badge"><?= $idx + 1 ?></span>
                                                <input type="text" name="qualifications[]" class="form-control form-control-sm border-0 bg-transparent px-2" value="<?= htmlspecialchars($q_trimmed) ?>" placeholder="Qualification or requirement...">
                                                <button type="button" class="btn btn-outline-danger btn-sm dynamic-line-btn-del" title="Remove line">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Photo / Hiring Banner (Optional -> Qualifies for Featured) -->
                            <div class="card-paper p-4 p-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-line">
                                    <h3 class="card-paper-title fs-5 mb-0">
                                        <i class="bi bi-image text-accent me-2"></i> 2. Hiring Flyer / Office Banner <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-2">Optional</span>
                                    </h3>
                                </div>
                                
                                <div class="p-3 mb-3 rounded border" style="background-color: var(--surface); border-style: dashed !important; border-color: var(--line) !important;">
                                    <div class="d-flex gap-3 align-items-start">
                                        <div class="p-2 rounded bg-accent-subtle text-accent fs-4 flex-shrink-0">
                                            <i class="bi bi-stars"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-ink small mb-1">Featured Homepage Spotlight Status</div>
                                            <p class="text-muted-custom small mb-0" style="font-size: 13px;">
                                                Postings with an attached flyer or photo are <strong>automatically showcased in the "Featured Campus &amp; Partner Opportunities" carousel</strong> on the landing page!
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($job['image'])): ?>
                                    <div class="mb-3 p-3 border rounded bg-surface">
                                        <label class="form-label d-block fw-bold small text-ink mb-2">Current Flyer / Photo</label>
                                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                                            <div style="width: 140px; height: 80px; border-radius: 6px; overflow: hidden; border: 1px solid var(--line); flex-shrink: 0;">
                                                <img src="<?= (str_starts_with($job['image'], 'http') || str_starts_with($job['image'], '/')) ? htmlspecialchars($job['image']) : '../' . htmlspecialchars($job['image']) ?>" alt="Current Banner" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div>
                                                <div class="badge bg-success-subtle text-success border border-success-subtle mb-2">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Featured on Homepage
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remove_photo" id="remove-photo" value="1">
                                                    <label class="form-check-label small text-danger fw-semibold" for="remove-photo">
                                                        Remove photo (demotes from Featured status)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <label class="form-label" for="job-photo"><?= !empty($job['image']) ? 'Replace Photo / Flyer (JPG, PNG, WebP · Max 10MB)' : 'Upload Poster / Photo (JPG, PNG, WebP · Max 10MB)' ?></label>
                                    <input type="file" name="job_photo" id="job-photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text text-muted-custom small mt-1">
                                        <i class="bi bi-info-circle me-1"></i> Recommended 16:9 or 4:3 landscape ratio (e.g. 1200×675px).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right 4-col: Scheduling & Status Controls Sidebar -->
                        <div class="col-lg-4">
                            <div class="card-paper p-4 position-sticky" style="top: 90px;">
                                <h3 class="card-paper-title fs-5 mb-4 pb-2 border-bottom border-line">
                                    <i class="bi bi-sliders text-accent me-2"></i> 3. Terms &amp; Controls
                                </h3>

                                <div class="mb-3">
                                    <label class="form-label" for="edit-status">Vacancy Status</label>
                                    <select name="status" id="edit-status" class="form-select">
                                        <option value="active" <?= ($job['status'] === 'active' || $job['status'] === 'Active') ? 'selected' : '' ?>>Active / Open for Applications</option>
                                        <option value="closed" <?= ($job['status'] === 'closed' || $job['status'] === 'Closed') ? 'selected' : '' ?>>Closed / Position Filled</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit-loc">Physical Location</label>
                                    <input type="text" name="location" id="edit-loc" class="form-control" value="<?= htmlspecialchars($job['location']) ?>" required>
                                </div>

                                <!-- Separated Stipend Rate & Compensation -->
                                <div class="mb-3">
                                    <label class="form-label" for="edit-pay-amount">Stipend Rate &amp; Frequency <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-muted">₱</span>
                                        <input type="number" name="pay_amount" id="edit-pay-amount" class="form-control" placeholder="85.00" step="0.50" min="0" value="<?= htmlspecialchars($parsed_amount) ?>" required>
                                        <select name="pay_period" id="edit-pay-period" class="form-select" style="max-width: 155px;" required>
                                            <option value="/ hour" <?= ($parsed_period === '/ hour') ? 'selected' : '' ?>>/ hour</option>
                                            <option value="/ day" <?= ($parsed_period === '/ day') ? 'selected' : '' ?>>/ day</option>
                                            <option value="/ week" <?= ($parsed_period === '/ week') ? 'selected' : '' ?>>/ week</option>
                                            <option value="/ month" <?= ($parsed_period === '/ month') ? 'selected' : '' ?>>/ month</option>
                                            <option value="/ semester" <?= ($parsed_period === '/ semester') ? 'selected' : '' ?>>/ semester</option>
                                            <option value="fixed stipend" <?= ($parsed_period === 'fixed stipend') ? 'selected' : '' ?>>fixed stipend</option>
                                        </select>
                                    </div>
                                    <div class="form-text text-muted-custom small mt-1">
                                        <i class="bi bi-info-circle me-1"></i> Amount in PHP. Select frequency from the dropdown.
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="edit-vac">Open Slots</label>
                                        <input type="number" name="vacancies" id="edit-vac" class="form-control" value="<?= $job['vacancies'] ?>" min="1" max="20" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="edit-hours">Weekly Limit</label>
                                        <input type="text" name="hours_per_week" id="edit-hours" class="form-control" value="<?= htmlspecialchars($job['hours_per_week']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="edit-deadline">Application Deadline</label>
                                    <input type="date" name="deadline" id="edit-deadline" class="form-control" value="<?= htmlspecialchars($job['deadline']) ?>" required>
                                </div>

                                <button type="submit" class="btn-pill w-100 mb-2">
                                    <i class="bi bi-check2-circle"></i> SAVE CHANGES
                                </button>
                                <a href="dashboard.php" class="btn-pill-outline btn-pill-sm w-100 text-center">
                                    Cancel
                                </a>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>

<script>
function setupDynamicList(inputBarId, btnAddId, containerId, emptyHintId, fieldName, placeholderText) {
    const inputBar = document.getElementById(inputBarId);
    const btnAdd = document.getElementById(btnAddId);
    const container = document.getElementById(containerId);
    const emptyHint = document.getElementById(emptyHintId);

    function renumber() {
        const rows = container.querySelectorAll('.dynamic-line-item');
        if (rows.length === 0) {
            if (emptyHint) emptyHint.classList.remove('d-none');
        } else {
            if (emptyHint) emptyHint.classList.add('d-none');
            rows.forEach((row, idx) => {
                const badge = row.querySelector('.dynamic-line-badge');
                if (badge) badge.textContent = idx + 1;
            });
        }
    }

    // Attach delete listeners to existing rows
    container.querySelectorAll('.dynamic-line-item').forEach(row => {
        const delBtn = row.querySelector('.dynamic-line-btn-del');
        if (delBtn) {
            delBtn.addEventListener('click', function() {
                row.remove();
                renumber();
            });
        }
        const inp = row.querySelector('input');
        if (inp) {
            inp.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (inputBar) inputBar.focus();
                }
            });
        }
    });

    function addRow(valueText = '') {
        const row = document.createElement('div');
        row.className = 'dynamic-line-item d-flex align-items-center gap-2 mb-2';
        row.innerHTML = `
            <span class="dynamic-line-badge">1</span>
            <input type="text" name="${fieldName}[]" class="form-control form-control-sm border-0 bg-transparent px-2" placeholder="${placeholderText}" value="${valueText.replace(/"/g, '&quot;')}">
            <button type="button" class="btn btn-outline-danger btn-sm dynamic-line-btn-del" title="Remove line">
                <i class="bi bi-x-lg"></i>
            </button>
        `;

        row.querySelector('.dynamic-line-btn-del').addEventListener('click', function() {
            row.remove();
            renumber();
        });

        row.querySelector('input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                inputBar.focus();
            }
        });

        container.appendChild(row);
        renumber();
        return row;
    }

    function handleAdd() {
        const text = inputBar.value.trim();
        if (text) {
            addRow(text);
            inputBar.value = '';
            inputBar.focus();
        }
    }

    if (btnAdd && inputBar) {
        btnAdd.addEventListener('click', handleAdd);
        inputBar.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleAdd();
            }
        });
    }

    return { addRow, renumber };
}

document.addEventListener('DOMContentLoaded', function() {
    setupDynamicList(
        'resp-input-bar',
        'btn-add-resp',
        'resp-items-container',
        'resp-empty-hint',
        'responsibilities',
        'Key duty or responsibility...'
    );

    setupDynamicList(
        'qual-input-bar',
        'btn-add-qual',
        'qual-items-container',
        'qual-empty-hint',
        'qualifications',
        'Qualification or requirement...'
    );

    const editForm = document.getElementById('edit-job-form');
    if (editForm) {
        editForm.addEventListener('submit', function() {
            const submitBtn = editForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                setTimeout(() => {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving Changes...';
                }, 10);
            }
        });
    }
});
</script>
