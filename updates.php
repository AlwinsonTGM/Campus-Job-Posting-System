<?php
/**
 * Campus Job Posting System - Career Center Updates & Editorial Dispatch
 * Impeccable Design Standard & Information Architecture (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$page_title = 'Career Center Updates & Announcements';
$updates = get_career_updates();

// Separate featured top story from the rest of the feed
$featured_story = !empty($updates) ? $updates[0] : null;
$secondary_updates = !empty($updates) ? array_slice($updates, 1) : [];

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <!-- Page Masthead -->
                <div class="mb-5 pb-3 border-bottom border-line">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                        <div>
                            <h1 class="h1 fw-extrabold text-ink mb-2 tracking-tight">Updates From The Career Center</h1>
                            <p class="text-muted-custom mb-0 max-w-640" style="max-width: 680px;">
                                Official bulletins, job fair schedules, student assistantship hiring policies, and career readiness guides published for the university community.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <?php 
                            $current_auth = get_current_auth_user();
                            if ($current_auth && in_array($current_auth['role'] ?? '', ['admin', 'employer'])): 
                                $post_link = ($current_auth['role'] === 'admin') ? $base_url . 'admin/updates.php' : $base_url . 'employer/updates.php';
                            ?>
                                <a href="<?= $post_link ?>" class="btn-paper-secondary d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-plus-circle-fill text-ink"></i> Post New Dispatch
                                </a>
                            <?php endif; ?>
                            <a href="<?= $base_url ?>student/jobs.php" class="btn-paper-primary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-search"></i> Browse Campus Jobs
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Search Input Bar -->
                <div class="card-paper bg-surface p-3 p-md-4 mb-5">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="updates-search-box w-100" style="max-width: 100%;">
                            <i class="bi bi-search updates-search-icon text-ink"></i>
                            <input type="text" id="updatesSearchInput" class="updates-search-input" placeholder="Search dispatches by headline, summary, or author..." aria-label="Search Updates">
                        </div>
                        <button type="button" id="resetSearchBtn" class="btn-search-clear flex-shrink-0" style="display: none;" onclick="resetSearch()">
                            <i class="bi bi-x-lg"></i> Clear Search
                        </button>
                    </div>

                    <!-- Search Status Counter -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-line small text-muted-custom">
                        <div id="filterResultsCount">
                            Showing <strong><?= count($updates) ?></strong> published career dispatches
                        </div>
                        <div id="activePageIndicator" class="fw-semibold text-ink">
                            Page <span id="currentPageNum">1</span> of <span id="totalPagesNum">1</span>
                        </div>
                    </div>
                </div>

                <?php if (empty($updates)): ?>
                    <div class="card-paper text-center py-5">
                        <i class="bi bi-newspaper display-4 text-muted-custom mb-3"></i>
                        <h4 class="h5 fw-bold text-ink mb-1">No Updates Published Yet</h4>
                        <p class="text-muted-custom small mb-0">Please check back soon for upcoming campus career announcements.</p>
                    </div>
                <?php else: ?>

                    <!-- 1. FEATURED MARQUEE DISPATCH SPOTLIGHT -->
                    <?php if ($featured_story): 
                        $feat_time = strtotime($featured_story['published_at'] ?? 'now');
                        $feat_date = date('F j, Y', $feat_time);
                        $feat_clock = date('g:i A', $feat_time);
                    ?>
                        <div class="mb-5" id="featuredStoryContainer" data-title="<?= htmlspecialchars(strtolower($featured_story['title'] ?? '')) ?>" data-summary="<?= htmlspecialchars(strtolower($featured_story['summary'] ?? '')) ?>" data-author="<?= htmlspecialchars(strtolower($featured_story['author']['name'] ?? '')) ?>">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="eyebrow-badge text-ink"><i class="bi bi-star-fill text-ink me-1"></i> Top Bulletin</span>
                                <span class="text-muted-custom small"><i class="bi bi-clock me-1 text-ink"></i><?= htmlspecialchars($featured_story['read_time'] ?? '4 min read') ?></span>
                            </div>
                            
                            <a href="<?= $base_url ?>update-detail.php?id=<?= urlencode($featured_story['id']) ?>" class="updates-spotlight-card reveal-fade-rise">
                                <div class="updates-spotlight-photo-wrap">
                                    <img src="<?= htmlspecialchars($featured_story['image']) ?>" class="updates-spotlight-photo" alt="<?= htmlspecialchars($featured_story['title']) ?>">
                                </div>
                                <div class="updates-spotlight-content d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-3 text-muted-custom small mb-3">
                                            <span><i class="bi bi-calendar3 me-1 text-ink"></i> <?= $feat_date ?> at <?= $feat_clock ?></span>
                                        </div>
                                        <h2 class="h3 fw-extrabold text-ink mb-3 tracking-tight" style="line-height: 1.3;">
                                            <?= htmlspecialchars($featured_story['title']) ?>
                                        </h2>
                                        <p class="text-muted-custom mb-4" style="line-height: 1.6;">
                                            <?= htmlspecialchars($featured_story['summary']) ?>
                                        </p>
                                    </div>

                                    <div class="pt-3 border-top border-line d-flex flex-wrap align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="article-author-avatar" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                                <?= htmlspecialchars($featured_story['author']['avatar'] ?? 'CC') ?>
                                            </div>
                                            <div>
                                                <span class="d-block small fw-bold text-ink lh-1 mb-1"><?= htmlspecialchars($featured_story['author']['name']) ?></span>
                                                <span class="d-block text-muted-custom small lh-1" style="font-size: 0.75rem;"><?= htmlspecialchars($featured_story['author']['role']) ?></span>
                                            </div>
                                        </div>
                                        <span class="btn-paper-primary btn-sm d-inline-flex align-items-center gap-2">
                                            Read Full Dispatch <i class="bi bi-arrow-right"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- 2. EDITORIAL FEED + INSTITUTIONAL SIDEBAR -->
                    <div class="row g-4 mb-5">
                        <!-- Main Updates Feed (8 Cols) -->
                        <div class="col-lg-8 updates-feed-col">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-line">
                                <h3 class="h5 fw-bold text-ink mb-0">Recent Career Dispatches</h3>
                                <span class="text-muted-custom small" id="feedCountLabel">Showing dispatches</span>
                            </div>

                            <!-- Zero State (Hidden by default) -->
                            <div id="noResultsState" class="card-paper text-center py-5 my-3" style="display: none;">
                                <div class="faq-help-icon-box mx-auto mb-3">
                                    <i class="bi bi-search text-muted-custom"></i>
                                </div>
                                <h4 class="h5 fw-bold text-ink mb-2">No Matching Updates Found</h4>
                                <p class="text-muted-custom small max-w-640 mx-auto mb-4" style="max-width: 440px;">
                                    We couldn't find any announcements matching your current search terms.
                                </p>
                                <button type="button" class="btn-paper-secondary d-inline-flex align-items-center gap-2" onclick="resetSearch()">
                                    <i class="bi bi-arrow-clockwise"></i> Clear Search
                                </button>
                            </div>

                            <!-- Secondary Articles Grid (Paginated 4 cards per page) -->
                            <div class="row g-4 updates-grid-layout" id="updatesGrid">
                                <?php foreach ($secondary_updates as $article): 
                                    $pub_time = strtotime($article['published_at'] ?? 'now');
                                    $formatted_date = date('M j, Y', $pub_time);
                                ?>
                                    <div class="col-md-6 update-card-wrapper" data-title="<?= htmlspecialchars(strtolower($article['title'] ?? '')) ?>" data-summary="<?= htmlspecialchars(strtolower($article['summary'] ?? '')) ?>" data-author="<?= htmlspecialchars(strtolower($article['author']['name'] ?? '')) ?>">
                                        <a href="<?= $base_url ?>update-detail.php?id=<?= urlencode($article['id']) ?>" class="update-feed-card reveal-fade-rise">
                                            <img src="<?= htmlspecialchars($article['image'] ?? 'assets/img/hero-office.jpg') ?>" class="update-feed-thumb" alt="<?= htmlspecialchars($article['title']) ?>">
                                            <div class="p-4 d-flex flex-column flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="small text-muted-custom"><i class="bi bi-calendar3 me-1"></i><?= $formatted_date ?></span>
                                                    <span class="small text-muted-custom"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($article['read_time'] ?? '3 min read') ?></span>
                                                </div>
                                                <h4 class="update-card-title"><?= htmlspecialchars($article['title']) ?></h4>
                                                <p class="update-card-summary"><?= htmlspecialchars($article['summary']) ?></p>
                                                
                                                <div class="pt-3 border-top border-line d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="article-author-avatar" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                                            <?= htmlspecialchars($article['author']['avatar'] ?? 'CC') ?>
                                                        </div>
                                                        <div>
                                                            <span class="d-block small fw-bold text-ink lh-1 mb-1"><?= htmlspecialchars($article['author']['name']) ?></span>
                                                            <span class="d-block text-muted-custom small lh-1" style="font-size: 0.7rem;"><?= htmlspecialchars($article['author']['office']) ?></span>
                                                        </div>
                                                    </div>
                                                    <span class="btn-circle-arrow-accent flex-shrink-0"><i class="bi bi-arrow-up-right"></i></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- 4-PER-PAGE TOGGLE ARROW PAGINATION BAR -->
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 updates-pagination-bar" id="paginationControls">
                                <div class="small text-muted-custom" id="paginationSummary">
                                    Showing 1&ndash;4 of <?= count($secondary_updates) ?> dispatches
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" id="prevPageBtn" class="pagination-nav-btn" title="Previous Page">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </button>
                                    <div id="pageNumberButtons" class="d-flex gap-1">
                                        <!-- Dynamically generated page pills -->
                                    </div>
                                    <button type="button" id="nextPageBtn" class="pagination-nav-btn" title="Next Page">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar (4 Cols) -->
                        <div class="col-lg-4">
                            <div class="d-flex flex-column gap-4">
                                <!-- Sidebar Card 1: Helpdesk & Inquiries -->
                                <div class="sidebar-info-card reveal-fade-rise">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="faq-help-icon-box m-0" style="width: 36px; height: 36px; font-size: 1rem;">
                                            <i class="bi bi-building-fill text-ink"></i>
                                        </div>
                                        <h4 class="card-paper-title h6 mb-0">Career Center Helpdesk</h4>
                                    </div>
                                    <p class="text-muted-custom small mb-3">
                                        Have questions about student assistant eligibility or require assistance with scheduling conflicts?
                                    </p>
                                    
                                    <div class="sidebar-info-item">
                                        <div class="sidebar-icon-badge"><i class="bi bi-geo-alt-fill text-ink"></i></div>
                                        <div class="small">
                                            <span class="d-block fw-bold text-ink">Physical Office</span>
                                            <span class="text-muted-custom">ICDI Building, 2nd Floor, Room 204</span>
                                        </div>
                                    </div>

                                    <div class="sidebar-info-item">
                                        <div class="sidebar-icon-badge"><i class="bi bi-clock-fill text-ink"></i></div>
                                        <div class="small">
                                            <span class="d-block fw-bold text-ink">Consultation Hours</span>
                                            <span class="text-muted-custom">Mon &ndash; Fri: 8:00 AM &ndash; 5:00 PM</span>
                                        </div>
                                    </div>

                                    <div class="sidebar-info-item">
                                        <div class="sidebar-icon-badge"><i class="bi bi-envelope-fill text-ink"></i></div>
                                        <div class="small text-truncate">
                                            <span class="d-block fw-bold text-ink">Institutional Email</span>
                                            <a href="mailto:careers@kld.edu.ph" class="text-muted-custom text-decoration-none">careers@kld.edu.ph</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sidebar Card 2: Student Guidelines -->
                                <div class="sidebar-info-card bg-cream reveal-fade-rise">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="faq-help-icon-box m-0" style="width: 36px; height: 36px; font-size: 1rem;">
                                            <i class="bi bi-journal-bookmark-fill text-ink"></i>
                                        </div>
                                        <h4 class="card-paper-title h6 mb-0">Essential Work Policies</h4>
                                    </div>
                                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                                        <li>
                                            <a href="<?= $base_url ?>faqs.php?open=2#faq-2" class="text-ink fw-bold text-decoration-none d-flex align-items-center justify-content-between">
                                                <span><i class="bi bi-check2-circle text-accent me-1"></i> 20-Hour Work Limit</span>
                                                <i class="bi bi-arrow-right text-muted-custom"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= $base_url ?>faqs.php?open=5#faq-5" class="text-ink fw-bold text-decoration-none d-flex align-items-center justify-content-between">
                                                <span><i class="bi bi-check2-circle text-accent me-1"></i> Required Documents (COR)</span>
                                                <i class="bi bi-arrow-right text-muted-custom"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= $base_url ?>faqs.php?open=6#faq-6" class="text-ink fw-bold text-decoration-none d-flex align-items-center justify-content-between">
                                                <span><i class="bi bi-check2-circle text-accent me-1"></i> Stipend &amp; Payroll Schedule</span>
                                                <i class="bi bi-arrow-right text-muted-custom"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= $base_url ?>privacy.php" class="text-ink fw-bold text-decoration-none d-flex align-items-center justify-content-between">
                                                <span><i class="bi bi-check2-circle text-accent me-1"></i> RA 10173 Data Privacy</span>
                                                <i class="bi bi-arrow-right text-muted-custom"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Sidebar Card 3: Semester Deadlines -->
                                <div class="sidebar-info-card reveal-fade-rise">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="faq-help-icon-box m-0" style="width: 36px; height: 36px; font-size: 1rem;">
                                            <i class="bi bi-calendar-check-fill text-ink"></i>
                                        </div>
                                        <h4 class="card-paper-title h6 mb-0">Academic Hiring Milestones</h4>
                                    </div>
                                    <div class="small text-muted-custom d-flex flex-column gap-2">
                                        <div class="d-flex justify-content-between pb-2 border-bottom border-line">
                                            <span class="text-ink fw-semibold">Term 1 Requisition Window</span>
                                            <span class="badge bg-light text-dark border">June 15 &ndash; 30</span>
                                        </div>
                                        <div class="d-flex justify-content-between pb-2 border-bottom border-line">
                                            <span class="text-ink fw-semibold">Midterm SA Review</span>
                                            <span class="badge bg-light text-dark border">Aug 10 &ndash; 15</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-ink fw-semibold">Final Term Evaluation</span>
                                            <span class="badge bg-light text-dark border">Oct 20 &ndash; 25</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

                <!-- Bottom Two-Column Action Suite -->
                <div class="row g-4 pt-4 border-top border-line">
                    <div class="col-lg-6">
                        <div class="card-paper h-100 reveal-fade-rise d-flex flex-column justify-content-between bg-surface">
                            <div>
                                <div class="faq-help-icon-box">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <h3 class="card-paper-title mb-2">Student Assistantship Program</h3>
                                <p class="card-paper-subtitle mb-4">
                                    Gain professional laboratory, library, and office experience while earning an hourly stipend that supports your educational journey.
                                </p>
                            </div>
                            <div>
                                <a href="<?= $base_url ?>student/jobs.php" class="btn-paper-primary d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-search"></i> Search Available Roles
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card-paper h-100 reveal-fade-rise d-flex flex-column justify-content-between bg-surface">
                            <div>
                                <div class="faq-help-icon-box">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                                <h3 class="card-paper-title mb-2">Department Supervisors &amp; Chairs</h3>
                                <p class="card-paper-subtitle mb-4">
                                    Register your campus office or laboratory to publish verified assistantship vacancies, match class schedules, and review applicants.
                                </p>
                            </div>
                            <div>
                                <a href="<?= $base_url ?>employer/dashboard.php" class="btn-paper-secondary d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-door-open"></i> Employer Portal Access
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

<!-- Interactive Client-Side Search & 4-Per-Page Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('updatesSearchInput');
    const resetSearchBtn = document.getElementById('resetSearchBtn');
    const featuredStory = document.getElementById('featuredStoryContainer');
    const updateCards = Array.from(document.querySelectorAll('.update-card-wrapper'));
    const noResultsState = document.getElementById('noResultsState');
    const filterResultsCount = document.getElementById('filterResultsCount');
    const paginationControls = document.getElementById('paginationControls');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageNumberButtons = document.getElementById('pageNumberButtons');
    const paginationSummary = document.getElementById('paginationSummary');
    const feedCountLabel = document.getElementById('feedCountLabel');
    const currentPageNum = document.getElementById('currentPageNum');
    const totalPagesNum = document.getElementById('totalPagesNum');

    const ITEMS_PER_PAGE = 4;
    let currentPage = 1;
    let searchQuery = '';

    function renderFeed() {
        // 1. Filter matching cards based on search query
        let matchingCards = [];

        // Check featured story match
        let featVisible = false;
        if (featuredStory) {
            const featTitle = (featuredStory.getAttribute('data-title') || '').toLowerCase();
            const featSummary = (featuredStory.getAttribute('data-summary') || '').toLowerCase();
            const featAuthor = (featuredStory.getAttribute('data-author') || '').toLowerCase();

            if (searchQuery === '' || featTitle.includes(searchQuery) || featSummary.includes(searchQuery) || featAuthor.includes(searchQuery)) {
                featuredStory.style.display = 'block';
                featVisible = true;
            } else {
                featuredStory.style.display = 'none';
                featVisible = false;
            }
        }

        updateCards.forEach(card => {
            const cardTitle = (card.getAttribute('data-title') || '').toLowerCase();
            const cardSummary = (card.getAttribute('data-summary') || '').toLowerCase();
            const cardAuthor = (card.getAttribute('data-author') || '').toLowerCase();

            if (searchQuery === '' || cardTitle.includes(searchQuery) || cardSummary.includes(searchQuery) || cardAuthor.includes(searchQuery)) {
                matchingCards.push(card);
            }
            card.style.display = 'none'; // hide by default, will show by page slice
        });

        const totalMatching = matchingCards.length;
        const totalPages = Math.max(1, Math.ceil(totalMatching / ITEMS_PER_PAGE));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        // Clean up any existing placeholder slots
        document.querySelectorAll('.update-placeholder-slot').forEach(el => el.remove());

        // 2. Slice and show matching cards for the current page (e.g. 1-4, 5-8)
        const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIndex = startIndex + ITEMS_PER_PAGE;
        const pageCards = matchingCards.slice(startIndex, endIndex);

        pageCards.forEach(card => {
            card.style.display = 'block';
        });

        // Fill remaining slots with invisible placeholders to lock height perfectly
        if (totalMatching > 0 && pageCards.length < ITEMS_PER_PAGE) {
            const neededPlaceholders = ITEMS_PER_PAGE - pageCards.length;
            const updatesGrid = document.getElementById('updatesGrid');
            if (updatesGrid) {
                for (let p = 0; p < neededPlaceholders; p++) {
                    const placeholder = document.createElement('div');
                    placeholder.className = 'col-md-6 update-card-wrapper update-placeholder-slot';
                    placeholder.setAttribute('aria-hidden', 'true');
                    placeholder.style.visibility = 'hidden';
                    placeholder.style.pointerEvents = 'none';
                    placeholder.innerHTML = `
                        <div class="update-feed-card" style="visibility: hidden;">
                            <div class="update-feed-thumb" style="visibility: hidden;"></div>
                            <div class="p-4 d-flex flex-column flex-grow-1" style="visibility: hidden;">
                                <div class="mb-2" style="height: 20px;"></div>
                                <h4 class="update-card-title" style="visibility: hidden;">Placeholder</h4>
                                <p class="update-card-summary" style="visibility: hidden;">Placeholder</p>
                                <div class="pt-3 border-top border-line" style="height: 46px; visibility: hidden;"></div>
                            </div>
                        </div>
                    `;
                    updatesGrid.appendChild(placeholder);
                }
            }
        }

        // 3. Update Result counters
        const totalDispatchesCount = totalMatching + (featVisible ? 1 : 0);
        if (totalDispatchesCount === 0) {
            noResultsState.style.display = 'block';
            if (paginationControls) paginationControls.style.display = 'none';
        } else {
            noResultsState.style.display = 'none';
            if (paginationControls) paginationControls.style.display = (totalMatching > 0) ? 'flex' : 'none';
        }

        if (filterResultsCount) {
            filterResultsCount.innerHTML = `Showing <strong>${totalDispatchesCount}</strong> published career dispatches`;
        }

        if (currentPageNum) currentPageNum.textContent = currentPage;
        if (totalPagesNum) totalPagesNum.textContent = totalPages;

        if (paginationSummary && totalMatching > 0) {
            const displayStart = startIndex + 1;
            const displayEnd = Math.min(endIndex, totalMatching);
            paginationSummary.innerHTML = `Showing <strong>${displayStart}&ndash;${displayEnd}</strong> of <strong>${totalMatching}</strong> recent dispatches`;
        }

        if (feedCountLabel) {
            feedCountLabel.textContent = `${totalMatching} dispatches in feed`;
        }

        // 4. Update Pagination Controls & Page Number Pills
        if (prevPageBtn) {
            prevPageBtn.disabled = (currentPage <= 1);
        }
        if (nextPageBtn) {
            nextPageBtn.disabled = (currentPage >= totalPages);
        }

        if (pageNumberButtons) {
            pageNumberButtons.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.type = 'button';
                pageBtn.className = `pagination-page-btn ${i === currentPage ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.addEventListener('click', function() {
                    currentPage = i;
                    renderFeed();
                });
                pageNumberButtons.appendChild(pageBtn);
            }
        }

        // Show/hide clear search button
        if (resetSearchBtn) {
            resetSearchBtn.style.display = (searchQuery !== '') ? 'inline-block' : 'none';
        }
    }

    // Prev / Next button listeners
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderFeed();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', function() {
            const totalMatching = updateCards.filter(c => {
                const cardTitle = (c.getAttribute('data-title') || '').toLowerCase();
                const cardSummary = (c.getAttribute('data-summary') || '').toLowerCase();
                const cardAuthor = (c.getAttribute('data-author') || '').toLowerCase();
                return searchQuery === '' || cardTitle.includes(searchQuery) || cardSummary.includes(searchQuery) || cardAuthor.includes(searchQuery);
            }).length;
            const totalPages = Math.ceil(totalMatching / ITEMS_PER_PAGE);
            if (currentPage < totalPages) {
                currentPage++;
                renderFeed();
            }
        });
    }

    // Live search input
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchQuery = this.value.trim().toLowerCase();
            currentPage = 1;
            renderFeed();
        });
    }

    window.resetSearch = function() {
        searchQuery = '';
        if (searchInput) searchInput.value = '';
        currentPage = 1;
        renderFeed();
    };

    // Initial render
    renderFeed();
});
</script>
