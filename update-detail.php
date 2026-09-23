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

// Preload view data — no service/DB calls in the template
$share_url = 'http://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');

// Last line: view template
require __DIR__ . '/includes/templates/update-detail-view.php';

