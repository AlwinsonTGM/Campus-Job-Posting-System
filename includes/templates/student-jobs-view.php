<?php
/**
 * View template for student/jobs.php
 * Pure HTML/PHP echo. Controller sets variables before require.
 * Inline <script> blocks intentionally stay here (guardrail).
 */
require_once __DIR__ . '/../header.php';
?>

<style>
#category-tiles-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 16px;
}
#category-tiles-grid > div {
    flex: 0 1 240px;
    min-width: 0;
    max-width: 100%;
    display: flex;
}
.category-visual-tile {
    width: 100%;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: var(--white);
    transition: border-color 0.15s ease;
}
.category-visual-tile:hover,
.category-visual-tile--active {
    border-color: var(--ink);
}
.category-visual-tile:focus-visible {
    outline: 2px solid var(--ink);
    outline-offset: 3px;
}
.category-tile-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: var(--white);
    color: var(--ink);
    font-size: 18px;
}
.category-tile-label {
    min-height: 2.5em;
    overflow-wrap: anywhere;
}
@media (max-width: 575.98px) {
    #category-tiles-grid > div { flex-basis: 100%; }
}
@media (prefers-reduced-motion: reduce) {
    .category-visual-tile { transition: none; }
}
</style>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/../navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                
                <section class="campus-page-header campus-page-header--jobs mb-4" aria-labelledby="jobs-page-title">
                    <div class="campus-page-header__main">
                        <div>
                            <h1 id="jobs-page-title">Find Your On-Campus Opportunity</h1>
                            <p class="campus-page-header__description">Explore verified student assistantships, academic laboratory assignments, library roles, and peer tutoring opportunities designed to work smoothly around your class schedule.</p>
                            <div class="campus-page-header__facts">
                                <span><i class="bi bi-briefcase" aria-hidden="true"></i> <?= $total_jobs ?> Positions Live</span>
                                <span><i class="bi bi-clock" aria-hidden="true"></i> Max 20 hrs/week Safe Cap</span>
                                <span><i class="bi bi-cash-stack" aria-hidden="true"></i> ₱80 – ₱120 / hr Verified Pay</span>
                            </div>
                        </div>
                        <div class="campus-page-header__discipline" id="hero-active-filter-card">
                            <div class="campus-page-header__label">Active discipline</div>
                            <div class="campus-page-header__filter"><i class="bi bi-funnel" aria-hidden="true"></i><span><?= !empty($category) ? htmlspecialchars($category) : 'All Job Families' ?></span></div>
                        </div>
                    </div>
                </section>

                <!-- Visual Category Discovery Section -->
                <div class="mb-4" id="category-discovery-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h6 fw-bold text-ink mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-grid-3x3-gap-fill text-ink"></i> Explore by Job Family
                            </h2>
                            <span class="small text-muted-custom">Select a discipline family to filter verified openings</span>
                        </div>
                    </div>

                    <div id="category-tiles-grid">
                        <?php foreach ($categories as $cat): 
                            $is_active_cat = (strcasecmp($category, $cat['name']) === 0 || strcasecmp($category, (string)$cat['id']) === 0);
                            $cat_raw_img = !empty($cat['image']) ? $cat['image'] : 'assets/img/categories/cat-general.jpg';
                            $cat_cover = (str_starts_with($cat_raw_img, 'http://') || str_starts_with($cat_raw_img, 'https://') || str_starts_with($cat_raw_img, '/'))
                                ? $cat_raw_img
                                : $base_url . ltrim($cat_raw_img, '/');
                            $cat_job_count = (int)($cat['job_count'] ?? 0);
                        ?>
                            <div>
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
                                             onerror="this.src='<?= $base_url ?>assets/img/categories/cat-general.jpg';">
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.65) 100%);"></div>
                                        
                                        <!-- Tile Icon Badge -->
                                        <div class="position-absolute bottom-0 start-0 m-2">
                                            <div class="category-tile-icon" aria-hidden="true">
                                                <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-briefcase') ?>"></i>
                                            </div>
                                        </div>

                                        <?php if ($is_active_cat): ?>
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge rounded-2 text-ink" style="background: var(--white); font-size: 10px;">
                                                    <i class="bi bi-check-lg"></i> Active
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tile Text -->
                                    <div class="p-3 text-start d-flex flex-column flex-grow-1">
                                        <span class="fw-bold text-ink category-tile-label mb-2" style="font-size: 14px; line-height: 1.25;" title="<?= htmlspecialchars($cat['name']) ?>">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </span>
                                        <span class="text-muted-custom small mt-auto" style="font-size: 13px;">
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
                                        <?php foreach ($view_get_kld_institutes_and_courses as $inst => $courses): ?>
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
                            <a href="jobs.php" class="chip chip-selectable text-decoration-none <?= (empty($job_type) && empty($work_setup) && empty($employer_type) && empty($pay_type) && empty($category)) ? 'active' : '' ?>" data-filter-type="reset">
                                All Roles
                            </a>
                            <a href="jobs.php?job_type=Student+Assistant" class="chip chip-selectable text-decoration-none <?= ($job_type === 'Student Assistant') ? 'active' : '' ?>" data-filter-name="job_type" data-filter-val="Student Assistant">
                                <i class="bi bi-mortarboard"></i> Student Assistant
                            </a>
                            <a href="jobs.php?job_type=Part-Time" class="chip chip-selectable text-decoration-none <?= ($job_type === 'Part-Time') ? 'active' : '' ?>" data-filter-name="job_type" data-filter-val="Part-Time">
                                <i class="bi bi-clock"></i> Part-Time
                            </a>
                            <a href="jobs.php?job_type=<?= urlencode('Internship / OJT') ?>" class="chip chip-selectable text-decoration-none <?= ($job_type === 'Internship / OJT') ? 'active' : '' ?>" data-filter-name="job_type" data-filter-val="Internship / OJT">
                                <i class="bi bi-briefcase"></i> Internship / OJT
                            </a>
                            <a href="jobs.php?category=Peer+Tutor" class="chip chip-selectable text-decoration-none <?= $is_peer_tutor_active ? 'active' : '' ?>" data-filter-name="category" data-filter-val="Peer Tutor">
                                <i class="bi bi-person-video3"></i> Peer Tutor
                            </a>
                            <a href="jobs.php?category=<?= urlencode('Science & Computer Lab Assistant') ?>" class="chip chip-selectable text-decoration-none <?= $is_lab_assistant_active ? 'active' : '' ?>" data-filter-name="category" data-filter-val="Science & Computer Lab Assistant">
                                <i class="bi bi-radioactive"></i> Lab Assistant
                            </a>
                            <a href="jobs.php?category=Library+Services" class="chip chip-selectable text-decoration-none <?= $is_library_aide_active ? 'active' : '' ?>" data-filter-name="category" data-filter-val="Library Services">
                                <i class="bi bi-book"></i> Library Aide
                            </a>
                            <a href="jobs.php?work_setup=On-Campus" class="chip chip-selectable text-decoration-none <?= ($work_setup === 'On-Campus') ? 'active' : '' ?>" data-filter-name="work_setup" data-filter-val="On-Campus">
                                <i class="bi bi-geo-alt"></i> On-Campus
                            </a>
                            <a href="jobs.php?work_setup=Hybrid" class="chip chip-selectable text-decoration-none <?= ($work_setup === 'Hybrid') ? 'active' : '' ?>" data-filter-name="work_setup" data-filter-val="Hybrid">
                                <i class="bi bi-laptop"></i> Hybrid
                            </a>
                            <a href="jobs.php?employer_type=approved_partner" class="chip chip-selectable text-decoration-none <?= ($employer_type === 'approved_partner') ? 'active' : '' ?>" data-filter-name="employer_type" data-filter-val="approved_partner">
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
                            <?php if ($total_jobs === 0): ?>
                                Showing <strong>0</strong> verified campus opportunities
                            <?php elseif ($total_pages > 1): ?>
                                Showing <strong><?= $range_start ?>&ndash;<?= $range_end ?></strong> of <strong><?= $total_jobs ?></strong> verified campus opportunities
                            <?php else: ?>
                                Showing <strong><?= $total_jobs ?></strong> verified campus opportunities
                            <?php endif; ?>
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
                        <div class="row g-4 mb-4 align-content-start" data-pagination-content>
                            <?php foreach ($jobs as $job): ?>
                                <div class="col-md-6 col-lg-4">
                                    <?php render_job_card($job, '../'); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-5" id="jobs-pagination">
                                <span class="small text-muted-custom">
                                    Page <strong><?= $page ?></strong> of <strong><?= $total_pages ?></strong>
                                </span>
                                <nav aria-label="Campus opportunities pagination">
                                    <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="jobs.php?<?= htmlspecialchars(http_build_query(array_merge($pagination_query, ['page' => max(1, $page - 1)]))) ?>" aria-label="Previous page" <?= ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                            <?php if ($p === 1 || $p === $total_pages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                                                <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                                                    <a class="page-link" href="jobs.php?<?= htmlspecialchars(http_build_query(array_merge($pagination_query, ['page' => $p]))) ?>" <?= ($p === $page) ? 'aria-current="page"' : '' ?>>
                                                        <?= $p ?>
                                                    </a>
                                                </li>
                                            <?php elseif ($p === $page - 3 || $p === $page + 3): ?>
                                                <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="jobs.php?<?= htmlspecialchars(http_build_query(array_merge($pagination_query, ['page' => min($total_pages, $page + 1)]))) ?>" aria-label="Next page" <?= ($page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </main>

        <?php require_once __DIR__ . '/../footer.php'; ?>
    </div>
</div>

