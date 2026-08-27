<?php
/**
 * Campus Job Posting System - Student Job Listings & Search
 * Archetype B/C: Search & Card Grid (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

$page_title = 'Browse Campus Vacancies & Assistantships';

// GET Parameter Contract
$keyword = trim($_GET['keyword'] ?? $_GET['kw'] ?? $_GET['q'] ?? '');
$category = trim($_GET['category'] ?? $_GET['cat'] ?? '');
$department = trim($_GET['department'] ?? $_GET['dept'] ?? '');
$job_type = trim($_GET['job_type'] ?? '');
$work_setup = trim($_GET['work_setup'] ?? '');
$pay_type = trim($_GET['pay_type'] ?? '');
$employer_type = trim($_GET['employer_type'] ?? '');

$jobs = get_jobs(
    $category ?: null,
    $keyword ?: null,
    $department ?: null,
    $pay_type ?: null,
    $job_type ?: null,
    $employer_type ?: null,
    $work_setup ?: null
);

$categories = get_categories();
$all_job_types = get_job_types();
$all_work_setups = get_work_setups();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Page Head -->
                <?php
                render_page_head(
                    '',
                    'Find On-Campus Jobs & Assistantships',
                    'Browse verified student assistant openings, academic lab assignments, library assistantships, and peer tutoring opportunities.'
                );
                ?>

                <!-- Search & Filters Container -->
                <div class="card-paper mb-4 p-4">
                    <form action="jobs.php" method="GET" class="form-paper auto-filter-form">
                        <div class="row g-3 align-items-end">
                            <!-- Keyword Input -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label" for="filter-kw">Search Keywords</label>
                                <div class="search-input-wrap">
                                    <i class="bi bi-search text-muted-custom"></i>
                                    <input 
                                        type="text" 
                                        name="keyword" 
                                        id="filter-kw" 
                                        class="form-control" 
                                        placeholder="Job title, department, skills..." 
                                        value="<?= htmlspecialchars($keyword) ?>"
                                    >
                                </div>
                            </div>

                            <!-- Department Dropdown -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="filter-dept">Department / Office</label>
                                <select name="department" id="filter-dept" class="form-select">
                                    <option value="">All Departments & Offices</option>
                                    <optgroup label="Academic Institutes">
                                        <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                            <option value="<?= htmlspecialchars($inst) ?>" <?= ($department === $inst) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Administrative Offices">
                                        <option value="Office of the University Registrar" <?= ($department === 'Office of the University Registrar') ? 'selected' : '' ?>>Office of the University Registrar</option>
                                        <option value="Student Affairs & Services Office (SASO)" <?= ($department === 'Student Affairs & Services Office (SASO)') ? 'selected' : '' ?>>Student Affairs & Services Office (SASO)</option>
                                        <option value="Management Information Systems (MIS)" <?= ($department === 'Management Information Systems (MIS)') ? 'selected' : '' ?>>Management Information Systems (MIS)</option>
                                        <option value="KLD University Library" <?= ($department === 'KLD University Library') ? 'selected' : '' ?>>KLD University Library</option>
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Job Type Dropdown -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label" for="filter-type">Job Type</label>
                                <select name="job_type" id="filter-type" class="form-select">
                                    <option value="">All Types</option>
                                    <?php foreach ($all_job_types as $k => $label): ?>
                                        <option value="<?= htmlspecialchars($k) ?>" <?= ($job_type === $k) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Work Setup Dropdown -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label" for="filter-setup">Work Setup</label>
                                <select name="work_setup" id="filter-setup" class="form-select">
                                    <option value="">Any Setup</option>
                                    <?php foreach ($all_work_setups as $k => $label): ?>
                                        <option value="<?= htmlspecialchars($k) ?>" <?= ($work_setup === $k) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filter Reset Button -->
                            <div class="col-lg-1 col-md-4">
                                <label class="form-label d-none d-md-block" style="visibility: hidden;">Reset</label>
                                <a href="jobs.php" class="btn-filter-reset" title="Reset all filters" aria-label="Reset all filters">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Quick Filter Chips -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top border-line">
                            <span class="small fw-bold text-muted-custom text-uppercase" style="font-size: 11px;">Quick Filters:</span>
                            <a href="jobs.php" class="chip chip-selectable <?= (empty($job_type) && empty($work_setup) && empty($employer_type) && empty($pay_type)) ? 'active' : '' ?>">
                                All Roles
                            </a>
                            <a href="jobs.php?job_type=Student+Assistant" class="chip chip-selectable <?= ($job_type === 'Student Assistant') ? 'active' : '' ?>">
                                Student Assistant
                            </a>
                            <a href="jobs.php?job_type=Lab+Assistant" class="chip chip-selectable <?= ($job_type === 'Lab Assistant') ? 'active' : '' ?>">
                                Lab Assistant
                            </a>
                            <a href="jobs.php?job_type=Library+Aide" class="chip chip-selectable <?= ($job_type === 'Library Aide') ? 'active' : '' ?>">
                                Library Aide
                            </a>
                            <a href="jobs.php?work_setup=On-Campus" class="chip chip-selectable <?= ($work_setup === 'On-Campus') ? 'active' : '' ?>">
                                <i class="bi bi-geo-alt"></i> On-Campus
                            </a>
                            <a href="jobs.php?work_setup=Hybrid" class="chip chip-selectable <?= ($work_setup === 'Hybrid') ? 'active' : '' ?>">
                                <i class="bi bi-laptop"></i> Hybrid
                            </a>
                            <a href="jobs.php?employer_type=approved_partner" class="chip chip-selectable <?= ($employer_type === 'approved_partner') ? 'active' : '' ?>">
                                <i class="bi bi-patch-check-fill text-accent"></i> Approved Partner
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Dynamic Filter Results Container -->
                <div id="filter-results-container">
                    <!-- Results Header Count -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="small text-muted-custom">
                            Showing <strong><?= count($jobs) ?></strong> verified campus opportunities
                        </span>
                        <span class="pill-badge" style="font-size: 11px;">
                            <i class="bi bi-clock-history text-accent"></i> Max 20 hrs/week
                        </span>
                    </div>

                    <!-- Job Listings Grid -->
                    <?php if (empty($jobs)): ?>
                        <?php
                        render_empty_state(
                            'bi-search',
                            'No matching opportunities found',
                            'Try adjusting your search keywords, clearing selected department filters, or resetting filter chips.',
                            'jobs.php',
                            'Reset All Filters'
                        );
                        ?>
                    <?php else: ?>
                        <div class="row g-4 mb-5">
                            <?php foreach ($jobs as $job): ?>
                                <div class="col-md-6 col-lg-4">
                                    <?php render_job_card($job, '../'); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
