<?php
/**
 * Campus Job Posting System - Career Center Article Detail Reader
 * Archetype F: Public & Information Reader (COAL101 Blueprint)
 */
require_once __DIR__ . '/includes/data-helper.php';
require_once __DIR__ . '/includes/auth-check.php';

$article_id = $_GET['id'] ?? null;
$article = null;

if ($article_id !== null) {
    $article = get_career_update_by_id($article_id);
}

if ($article) {
    $page_title = $article['title'] . ' | Career Center';
    $pub_time = strtotime($article['published_at'] ?? 'now');
    $formatted_date = date('F j, Y', $pub_time);
    $formatted_time = date('g:i A', $pub_time);
    $latest_articles = get_latest_career_updates(3, $article['id']);
} else {
    $page_title = 'Article Not Found | Career Center';
    $latest_articles = get_latest_career_updates(3);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="sheet-perspective-wrapper">
    <div class="sheet flat-sheet">
        <?php require_once __DIR__ . '/includes/navbar.php'; ?>

        <main class="py-5">
            <div class="container-paper">
                <?php if (!$article): ?>
                    <!-- 404 / Not Found State -->
                    <div class="card-paper text-center py-5 my-5 max-w-640 mx-auto" style="max-width: 600px;">
                        <div class="faq-help-icon-box mx-auto mb-3">
                            <i class="bi bi-file-earmark-x-fill text-danger"></i>
                        </div>
                        <h2 class="h4 fw-bold text-ink mb-2">Article Not Found</h2>
                        <p class="text-muted-custom small mb-4">
                            The update or bulletin you are looking for may have been archived, removed, or the link provided is invalid.
                        </p>
                        <a href="<?= $base_url ?>updates.php" class="btn-paper-primary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Back to Career Updates
                        </a>
                    </div>
                <?php else: ?>
                    <div class="article-reader-wrap">
                        <!-- Breadcrumbs -->
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?= $base_url ?>index.php" class="text-muted-custom text-decoration-none"><i class="bi bi-house-door me-1"></i>Home</a></li>
                                <li class="breadcrumb-item"><a href="<?= $base_url ?>updates.php" class="text-muted-custom text-decoration-none">Career Updates</a></li>
                                <li class="breadcrumb-item active text-ink fw-semibold" aria-current="page"><?= htmlspecialchars($article['category'] ?? 'Announcement') ?></li>
                            </ol>
                        </nav>

                        <!-- Category Pill -->
                        <div class="mb-3">
                            <a href="<?= $base_url ?>updates.php?category=<?= urlencode(strtolower($article['category'] ?? 'all')) ?>" class="badge rounded-pill bg-dark text-white text-decoration-none px-3 py-2 fw-semibold">
                                <i class="bi bi-tag-fill text-white me-1"></i> <?= htmlspecialchars($article['category'] ?? 'Career Bulletin') ?>
                            </a>
                        </div>

                        <!-- 1. Article Title -->
                        <h1 class="h2 fw-extrabold text-ink mb-4 tracking-tight" style="font-size: 2.25rem; line-height: 1.25;">
                            <?= htmlspecialchars($article['title']) ?>
                        </h1>

                        <!-- 2. Author, Date & Time Byline Bar -->
                        <div class="article-header-meta mb-4">
                            <!-- Author Profile -->
                            <div class="article-author-box">
                                <div class="article-author-avatar">
                                    <?= htmlspecialchars($article['author']['avatar'] ?? 'CC') ?>
                                </div>
                                <div>
                                    <span class="d-block fw-bold text-ink small lh-1 mb-1"><?= htmlspecialchars($article['author']['name']) ?></span>
                                    <span class="d-block text-muted-custom small lh-1"><?= htmlspecialchars($article['author']['role']) ?> &bull; <?= htmlspecialchars($article['author']['office']) ?></span>
                                </div>
                            </div>

                            <!-- Date & Time Meta -->
                            <div class="ms-md-auto d-flex align-items-center gap-3 text-muted-custom small">
                                <span>
                                    <i class="bi bi-calendar3 me-1 text-ink"></i> <?= $formatted_date ?> at <?= $formatted_time ?>
                                </span>
                                <span>
                                    <i class="bi bi-clock me-1 text-ink"></i> <?= htmlspecialchars($article['read_time'] ?? '4 min read') ?>
                                </span>
                            </div>
                        </div>

                        <!-- 3. Featured Hero Picture -->
                        <div class="article-hero-wrap">
                            <img src="<?= htmlspecialchars($article['image'] ?? 'assets/img/hero-office.jpg') ?>" class="article-hero-photo" alt="<?= htmlspecialchars($article['title']) ?>">
                        </div>

                        <!-- 4. Structured Article Content -->
                        <article class="article-content-body pb-4 border-bottom border-line mb-5">
                            <?= $article['content'] ?>
                        </article>

                        <!-- Share & Navigation Row -->
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 pb-5 mb-5 border-bottom border-line">
                            <a href="<?= $base_url ?>updates.php" class="btn-paper-secondary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-arrow-left"></i> All Updates
                            </a>
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted-custom me-2">Share this update:</span>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" rel="noopener noreferrer" class="social-circle-link" title="Share on Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['title']) ?>" target="_blank" rel="noopener noreferrer" class="social-circle-link" title="Share on X">
                                    <i class="bi bi-twitter-x"></i>
                                </a>
                                <button type="button" class="social-circle-link border-0" onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');" title="Copy Link">
                                    <i class="bi bi-link-45deg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 5. Latest Articles Section (Chronological) -->
                        <?php if (!empty($latest_articles)): ?>
                            <div class="mb-5">
                                <div class="d-flex justify-content-between align-items-end mb-4">
                                    <div>
                                        <span class="eyebrow-badge text-muted-custom d-block mb-1">STAY INFORMED</span>
                                        <h3 class="h4 fw-extrabold text-ink mb-0">Latest Career Updates</h3>
                                    </div>
                                    <a href="<?= $base_url ?>updates.php" class="text-ink fw-bold text-decoration-none small">
                                        View all updates <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>

                                <div class="row g-3">
                                    <?php foreach ($latest_articles as $lat_art): 
                                        $lat_time = strtotime($lat_art['published_at'] ?? 'now');
                                        $lat_date = date('M j, Y', $lat_time);
                                    ?>
                                        <div class="col-md-4">
                                            <a href="<?= $base_url ?>update-detail.php?id=<?= urlencode($lat_art['id']) ?>" class="update-compact-card">
                                                <img src="<?= htmlspecialchars($lat_art['image'] ?? 'assets/img/hero-office.jpg') ?>" class="update-compact-thumb" alt="<?= htmlspecialchars($lat_art['title']) ?>">
                                                <div class="p-3 d-flex flex-column flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="badge rounded-pill bg-light text-dark border small" style="font-size: 0.7rem;"><?= htmlspecialchars($lat_art['category'] ?? 'News') ?></span>
                                                        <span class="text-muted-custom" style="font-size: 0.75rem;"><?= $lat_date ?></span>
                                                    </div>
                                                    <h4 class="h6 fw-bold text-ink mb-0 line-clamp-2" style="font-size: 0.9rem;"><?= htmlspecialchars($lat_art['title']) ?></h4>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Opportunity Banner -->
                        <div class="card-paper bg-cream p-4 p-md-5 text-center reveal-fade-rise">
                            <h3 class="h4 fw-extrabold text-ink mb-2">Find a Student Assistantship That Fits Your Schedule</h3>
                            <p class="text-muted-custom small max-w-640 mx-auto mb-4" style="max-width: 540px;">
                                Apply to verified academic institute vacancies with schedule matching and real-time status tracking.
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="<?= $base_url ?>student/jobs.php" class="btn-paper-primary d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-search"></i> Explore Open Roles
                                </a>
                                <a href="<?= $base_url ?>faqs.php" class="btn-paper-secondary d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-question-circle"></i> Work Regulations
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php require_once __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
