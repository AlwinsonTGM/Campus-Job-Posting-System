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

$active_filters_count = 0;
if (!empty($keyword)) $active_filters_count++;
if (!empty($category)) $active_filters_count++;
if (!empty($department)) $active_filters_count++;
if (!empty($job_type)) $active_filters_count++;
if (!empty($work_setup)) $active_filters_count++;
if (!empty($employer_type)) $active_filters_count++;

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

<style>
.category-visual-tile {
    border: 1px solid var(--line);
    border-radius: 14px;
    background: var(--white);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.2s ease;
}
.category-visual-tile:hover {
    transform: translateY(-4px);
    border-color: rgba(13, 59, 46, 0.4);
    box-shadow: 0 12px 28px rgba(13, 59, 46, 0.1) !important;
}
.category-visual-tile:hover .cat-tile-img {
    transform: scale(1.08);
}
.category-visual-tile--active {
    border: 2px solid var(--accent) !important;
    box-shadow: 0 10px 26px rgba(13, 59, 46, 0.14) !important;
    background: var(--surface) !important;
}
.cat-tile-img {
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <!-- Student Opportunity Discovery Banner -->
                <div class="card-paper p-0 overflow-hidden mb-4 reveal-fade-rise border-line position-relative shadow-sm" style="background: linear-gradient(135deg, #0d3b2e 0%, #175343 55%, #1e6955 100%); color: #ffffff;">
                    <div style="position: absolute; right: -25px; bottom: -25px; opacity: 0.08; pointer-events: none;">
                        <i class="bi bi-compass-fill" style="font-size: 220px; line-height: 1;"></i>
                    </div>
                    <div class="p-4 p-md-5 position-relative">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <h1 class="display-6 fw-bold mb-2 text-white" style="letter-spacing: -0.02em;">Find Your On-Campus Opportunity</h1>
                                <p class="mb-3 text-white-50" style="max-width: 620px; font-size: 14.5px; line-height: 1.6;">
                                    Explore verified student assistantships, academic laboratory assignments, library roles, and peer tutoring opportunities designed to work smoothly around your class schedule.
                                </p>
                                <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                                    <span class="badge rounded-2" style="background: rgba(255,255,255,0.15); font-weight: 500; font-size: 12px; padding: 6px 12px;">
                                        <i class="bi bi-briefcase-fill text-warning me-1"></i> <?= count($jobs) ?> Positions Live
                                    </span>
                                    <span class="badge rounded-2" style="background: rgba(255,255,255,0.15); font-weight: 500; font-size: 12px; padding: 6px 12px;">
                                        <i class="bi bi-clock-history text-accent me-1"></i> Max 20 hrs/week Safe Cap
                                    </span>
                                    <span class="badge rounded-2" style="background: rgba(255,255,255,0.15); font-weight: 500; font-size: 12px; padding: 6px 12px;">
                                        <i class="bi bi-cash-stack text-warning me-1"></i> ₱80 – ₱120 / hr Verified Pay
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end d-none d-lg-block" id="hero-active-filter-card">
                                <div class="p-3 rounded-3 text-start d-inline-block shadow-sm" style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2); min-width: 220px;">
                                    <div class="small text-white-50 text-uppercase fw-bold mb-1" style="font-size: 11px;">Active Discipline</div>
                                    <div class="fw-bold text-white fs-6 mb-0 d-flex align-items-center gap-1">
                                        <i class="bi bi-funnel-fill text-accent"></i>
                                        <span><?= !empty($category) ? htmlspecialchars($category) : 'All Job Families' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Category Discovery Section -->
                <div class="mb-4" id="category-discovery-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h6 fw-bold text-ink mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-grid-3x3-gap-fill text-accent"></i> Explore by Job Family
                            </h2>
                            <span class="small text-muted-custom">Select a discipline family to filter verified openings</span>
                        </div>
                    </div>

                    <div class="row g-3" id="category-tiles-grid">
                        <?php foreach ($categories as $cat): 
                            $is_active_cat = (strcasecmp($category, $cat['name']) === 0 || strcasecmp($category, (string)$cat['id']) === 0);
                            $cat_cover = !empty($cat['image']) ? $cat['image'] : 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=900&auto=format&fit=crop';
                            $cat_job_count = (int)($cat['job_count'] ?? 0);
                        ?>
                            <div class="col-6 col-md-4 col-lg-2">
                                <a href="jobs.php?category=<?= urlencode($cat['name']) ?>" 
                                   class="card-paper p-0 overflow-hidden d-flex flex-column text-decoration-none h-100 position-relative category-visual-tile <?= $is_active_cat ? 'category-visual-tile--active' : '' ?>"
                                   data-category="<?= htmlspecialchars($cat['name']) ?>"
                                   role="button"
                                   tabindex="0"
                                   title="Filter by <?= htmlspecialchars($cat['name']) ?>">
                                    
                                    <!-- Tile Cover Photo -->
                                    <div class="position-relative overflow-hidden" style="height: 85px; background: #e5e7eb;">
                                        <img src="<?= htmlspecialchars($cat_cover) ?>" 
                                             alt="<?= htmlspecialchars($cat['name']) ?>" 
                                             class="w-100 h-100 cat-tile-img" 
                                             loading="lazy"
                                             style="object-fit: cover; object-position: center;"
                                             onerror="this.src='https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=900&auto=format&fit=crop';">
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.65) 100%);"></div>
                                        
                                        <!-- Tile Icon Badge -->
                                        <div class="position-absolute bottom-0 start-0 m-2">
                                            <div class="icon-circle icon-circle-sm bg-white shadow-sm" style="width: 28px; height: 28px; font-size: 13px;">
                                                <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-briefcase') ?> text-accent"></i>
                                            </div>
                                        </div>

                                        <?php if ($is_active_cat): ?>
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge rounded-pill bg-success text-white shadow-sm" style="font-size: 10px;">
                                                    <i class="bi bi-check-lg"></i> Active
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tile Text -->
                                    <div class="p-2 text-center d-flex flex-column flex-grow-1 bg-white">
                                        <span class="fw-bold text-ink line-clamp-1 small mb-1" style="font-size: 12px; line-height: 1.25;" title="<?= htmlspecialchars($cat['name']) ?>">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </span>
                                        <span class="text-muted-custom small mt-auto" style="font-size: 11px;">
                                            <?= $cat_job_count ?> <?= $cat_job_count === 1 ? 'Opening' : 'Openings' ?>
                                        </span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Search & Filters Container -->
                <div class="card-paper mb-4 p-3 p-md-4" id="jobs-filter-card">
                    <!-- Mobile Dropdown Toggle Header (visible on mobile < lg) -->
                    <div class="d-flex align-items-center justify-content-between cursor-pointer d-lg-none py-1" 
                         id="mobileFilterToggleBtn"
                         data-bs-toggle="collapse" 
                         data-bs-target="#jobsFilterCollapse" 
                         aria-expanded="false" 
                         aria-controls="jobsFilterCollapse">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center bg-cream border border-line rounded-circle flex-shrink-0" style="width: 36px; height: 36px;">
                                <i class="bi bi-funnel-fill text-accent" style="font-size: 14px;"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-ink" style="font-size: 14.5px; line-height: 1.25;">Filter &amp; Search Opportunities</div>
                                <div class="small text-muted-custom" style="font-size: 11.5px;" id="mobileFilterSummary">
                                    <?= $active_filters_count > 0 ? ($active_filters_count . ' active filter' . ($active_filters_count > 1 ? 's' : '') . ' · Tap to adjust') : 'Tap to expand search &amp; filters' ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($active_filters_count > 0): ?>
                                <span class="badge bg-accent text-dark rounded-pill px-2" style="font-size: 11px;">
                                    <?= $active_filters_count ?>
                                </span>
                            <?php endif; ?>
                            <i class="bi bi-chevron-down mobile-filter-chevron text-muted-custom"></i>
                        </div>
                    </div>

                    <!-- Collapsible Filter Form (Desktop: always visible d-lg-block; Mobile: collapse) -->
                    <div class="collapse d-lg-block mt-3 mt-lg-0" id="jobsFilterCollapse">
                        <form action="jobs.php" method="GET" class="form-paper auto-filter-form">
                        <div class="row g-3 align-items-end">
                            <!-- Keyword Input -->
                            <div class="col-lg-3 col-md-6">
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

                            <!-- Category Dropdown -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="filter-cat">Job Category</label>
                                <select name="category" id="filter-cat" class="form-select">
                                    <option value="">All Job Categories</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= htmlspecialchars($c['name']) ?>" <?= (strcasecmp($category, $c['name']) === 0 || $category == $c['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Department Dropdown -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label" for="filter-dept">Department</label>
                                <select name="department" id="filter-dept" class="form-select">
                                    <option value="">All Offices</option>
                                    <optgroup label="Academic Institutes">
                                        <?php foreach (get_kld_institutes_and_courses() as $inst => $courses): ?>
                                            <option value="<?= htmlspecialchars($inst) ?>" <?= ($department === $inst) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Administrative Offices">
                                        <option value="Office of the University Registrar" <?= ($department === 'Office of the University Registrar') ? 'selected' : '' ?>>Registrar</option>
                                        <option value="Student Affairs & Services Office (SASO)" <?= ($department === 'Student Affairs & Services Office (SASO)') ? 'selected' : '' ?>>SASO</option>
                                        <option value="Management Information Systems (MIS)" <?= ($department === 'Management Information Systems (MIS)') ? 'selected' : '' ?>>MIS & Tech</option>
                                        <option value="KLD University Library" <?= ($department === 'KLD University Library') ? 'selected' : '' ?>>Library</option>
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
                            <div class="col-lg-1 col-md-2">
                                <label class="form-label" for="filter-setup">Setup</label>
                                <select name="work_setup" id="filter-setup" class="form-select">
                                    <option value="">Any</option>
                                    <?php foreach ($all_work_setups as $k => $label): ?>
                                        <option value="<?= htmlspecialchars($k) ?>" <?= ($work_setup === $k) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Hidden filter states for full AJAX persistence -->
                            <input type="hidden" name="employer_type" id="filter-employer-type" value="<?= htmlspecialchars($employer_type) ?>">
                            <input type="hidden" name="pay_type" id="filter-pay-type" value="<?= htmlspecialchars($pay_type) ?>">

                            <!-- Filter Reset Button -->
                            <div class="col-lg-1 col-md-2 d-flex justify-content-md-start justify-content-lg-end">
                                <div>
                                    <label class="form-label d-none d-md-block" style="visibility: hidden;">Reset</label>
                                    <a href="jobs.php" class="btn-filter-reset" title="Reset all filters" aria-label="Reset all filters">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Filter Chips -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top border-line" id="quick-filter-chips">
                            <span class="small fw-bold text-muted-custom text-uppercase" style="font-size: 11px;">Quick Filters:</span>
                            <a href="jobs.php" class="chip chip-selectable <?= (empty($job_type) && empty($work_setup) && empty($employer_type) && empty($pay_type) && empty($category)) ? 'active' : '' ?>" data-filter-type="reset">
                                All Roles
                            </a>
                            <a href="jobs.php?job_type=Student+Assistant" class="chip chip-selectable <?= ($job_type === 'Student Assistant') ? 'active' : '' ?>" data-filter-name="job_type" data-filter-val="Student Assistant">
                                Student Assistant
                            </a>
                            <a href="jobs.php?job_type=Lab+Assistant" class="chip chip-selectable <?= ($job_type === 'Lab Assistant') ? 'active' : '' ?>" data-filter-name="job_type" data-filter-val="Lab Assistant">
                                Lab Assistant
                            </a>
                            <a href="jobs.php?job_type=Library+Aide" class="chip chip-selectable <?= ($job_type === 'Library Aide') ? 'active' : '' ?>" data-filter-name="job_type" data-filter-val="Library Aide">
                                Library Aide
                            </a>
                            <a href="jobs.php?work_setup=On-Campus" class="chip chip-selectable <?= ($work_setup === 'On-Campus') ? 'active' : '' ?>" data-filter-name="work_setup" data-filter-val="On-Campus">
                                <i class="bi bi-geo-alt"></i> On-Campus
                            </a>
                            <a href="jobs.php?work_setup=Hybrid" class="chip chip-selectable <?= ($work_setup === 'Hybrid') ? 'active' : '' ?>" data-filter-name="work_setup" data-filter-val="Hybrid">
                                <i class="bi bi-laptop"></i> Hybrid
                            </a>
                            <a href="jobs.php?employer_type=approved_partner" class="chip chip-selectable <?= ($employer_type === 'approved_partner') ? 'active' : '' ?>" data-filter-name="employer_type" data-filter-val="approved_partner">
                                <i class="bi bi-patch-check-fill text-accent"></i> Approved Partner
                            </a>
                        </div>

                        <!-- Mobile Action to Minimize / Apply -->
                        <div class="d-lg-none mt-3 pt-3 border-top border-line d-flex gap-2">
                            <a href="jobs.php" class="btn btn-sm btn-outline-secondary rounded-pill py-2 flex-grow-1 fw-semibold text-center" style="font-size: 13px;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                            <button type="button" class="btn btn-sm btn-dark rounded-pill py-2 flex-grow-1 fw-semibold" data-bs-toggle="collapse" data-bs-target="#jobsFilterCollapse" style="font-size: 13px;">
                                <i class="bi bi-check2 me-1"></i> Done &amp; Minimize
                            </button>
                        </div>
                    </form>
                </div>
            </div>

                <!-- Dynamic Filter Results Container -->
                <div id="filter-results-container">
                    <!-- Results Header Count -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="small text-muted-custom">
                            Showing <strong><?= count($jobs) ?></strong> verified campus opportunities
                        </span>
                        <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border bg-success-subtle text-success-emphasis border-success-subtle" style="font-size: 11px;">
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
