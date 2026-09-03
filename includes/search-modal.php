<?php
/**
 * Campus Job Posting System - Shared Floating Spotlight Search Modal Partial
 * Paper Sheet Aesthetic (COAL101 Blueprint)
 */
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}
?>
<!-- Global Floating Spotlight Search Modal -->
<div class="modal fade spotlight-modal" id="globalSearchModal" tabindex="-1" aria-labelledby="globalSearchModalLabel" aria-hidden="true" data-base-url="<?= htmlspecialchars($base_url ?? '') ?>">
    <div class="modal-dialog modal-lg modal-spotlight-dialog">
        <div class="modal-content spotlight-modal-content border-line shadow-lg">
            
            <!-- Search Header Bar -->
            <div class="spotlight-header p-3 p-md-4 border-bottom border-line bg-surface">
                <form id="spotlightSearchForm" action="<?= $base_url ?>student/jobs.php" method="GET" class="m-0">
                    <div class="spotlight-input-wrap d-flex align-items-center gap-2">
                        <span class="spotlight-search-icon text-muted-custom fs-4">
                            <i class="bi bi-search"></i>
                        </span>
                        
                        <input 
                            type="text" 
                            id="spotlightSearchInput" 
                            name="keyword" 
                            class="spotlight-input form-control border-0 shadow-none bg-transparent fs-5 fw-semibold text-ink" 
                            placeholder="Search opportunities, titles, skills..." 
                            autocomplete="off"
                            spellcheck="false"
                            aria-label="Search campus jobs"
                        >

                        <!-- Clear Input Button (Visible when typing) -->
                        <button type="button" id="spotlightClearBtn" class="btn btn-sm btn-clear-search text-muted-custom border-0 p-1 d-none" aria-label="Clear Search Input" title="Clear">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>

                        <!-- Keyboard Shortcut Badge -->
                        <span class="spotlight-kbd-badge d-none d-md-inline-block">ESC</span>

                        <!-- Modal Close Button -->
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </form>

                <!-- Quick Filter Chips -->
                <div class="spotlight-chips-scroll d-flex align-items-center gap-2 pt-3">
                    <button type="button" class="spotlight-chip active" data-filter-type="" data-filter-val="">
                        All Roles
                    </button>
                    <button type="button" class="spotlight-chip" data-filter-type="job_type" data-filter-val="Student Assistant">
                        <i class="bi bi-mortarboard me-1"></i> Student Assistant
                    </button>
                    <button type="button" class="spotlight-chip" data-filter-type="job_type" data-filter-val="Part-Time Job">
                        <i class="bi bi-clock me-1"></i> Part-Time
                    </button>
                    <button type="button" class="spotlight-chip" data-filter-type="job_type" data-filter-val="Internship / OJT">
                        <i class="bi bi-briefcase me-1"></i> Internship / OJT
                    </button>
                    <button type="button" class="spotlight-chip" data-filter-type="job_type" data-filter-val="Peer Tutor">
                        <i class="bi bi-person-video3 me-1"></i> Peer Tutor
                    </button>
                    <button type="button" class="spotlight-chip" data-filter-type="work_setup" data-filter-val="On-Campus">
                        <i class="bi bi-geo-alt me-1"></i> On-Campus
                    </button>
                    <button type="button" class="spotlight-chip" data-filter-type="work_setup" data-filter-val="Remote">
                        <i class="bi bi-laptop me-1"></i> Remote
                    </button>
                </div>
            </div>

            <!-- Search Results Body -->
            <div class="spotlight-body p-3 p-md-4 bg-cream">
                <!-- Loading State -->
                <div id="spotlightLoading" class="text-center py-4 d-none">
                    <div class="spinner-border spinner-border-sm text-accent" role="status">
                        <span class="visually-hidden">Searching...</span>
                    </div>
                    <span class="ms-2 small text-muted-custom">Searching verified vacancies...</span>
                </div>

                <!-- Results List Container -->
                <div id="spotlightResultsList" class="d-flex flex-column gap-2">
                    <!-- Dynamic Job items will be injected here by main.js -->
                </div>

                <!-- Empty State (Hidden by default) -->
                <div id="spotlightEmptyState" class="text-center py-4 d-none">
                    <div class="d-inline-flex align-items-center justify-content-center bg-surface rounded-circle p-3 mb-2 shadow-sm" style="width: 52px; height: 52px;">
                        <i class="bi bi-search text-muted-custom fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-ink mb-1">No vacancies found</h6>
                    <p class="text-muted-custom small mb-3">Try different keywords or clear the active filter chip.</p>
                    <a href="<?= $base_url ?>student/jobs.php" class="btn-soft-pill py-1 px-3 small">
                        Browse All Campus Jobs
                    </a>
                </div>
            </div>

            <!-- Spotlight Footer Bar -->
            <div class="spotlight-footer px-3 py-2 px-md-4 py-md-3 border-top border-line bg-surface d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 small text-muted-custom">
                    <span id="spotlightResultCount" class="fw-semibold text-ink">Showing vacancies</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="spotlightFullSearchLink" href="<?= $base_url ?>student/jobs.php" class="btn-accent-pill py-1 px-3 small" style="font-size: 0.8125rem;">
                        View Full Results Page <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
