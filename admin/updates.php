<?php
/**
 * Campus Job Posting System - Admin Career Updates & Dispatches Management
 * Archetype B: Administration & Bulletin Management (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Career Center Dispatches Management';

$error = null;
$action = $_POST['action'] ?? null;

// Handle Add / Edit / Delete POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } elseif ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Campus News');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $author_name = trim($_POST['author_name'] ?? $user['name']);
        $author_role = trim($_POST['author_role'] ?? 'System Administrator');
        $author_office = trim($_POST['author_office'] ?? 'KLD Career Development & Placement Office');

        if (empty($title) || empty($content)) {
            $error = 'Headline title and article content are required.';
        } else {
            add_career_update([
                'title' => $title,
                'category' => $category,
                'summary' => $summary,
                'content' => $content,
                'image' => $image,
                'author_name' => $author_name,
                'author_role' => $author_role,
                'author_office' => $author_office
            ]);
            set_flash('success', "Career dispatch '{$title}' was published successfully.");
            header('Location: updates.php');
            exit;
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Campus News');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $author_name = trim($_POST['author_name'] ?? '');
        $author_role = trim($_POST['author_role'] ?? '');
        $author_office = trim($_POST['author_office'] ?? '');

        if ($id <= 0 || empty($title) || empty($content)) {
            $error = 'Valid update ID, title, and content are required for editing.';
        } else {
            update_career_update($id, [
                'title' => $title,
                'category' => $category,
                'summary' => $summary,
                'content' => $content,
                'image' => $image,
                'author_name' => $author_name,
                'author_role' => $author_role,
                'author_office' => $author_office
            ]);
            set_flash('success', "Dispatch #{$id} was updated successfully.");
            header('Location: updates.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            delete_career_update($id);
            set_flash('success', "Dispatch #{$id} was deleted permanently.");
            header('Location: updates.php');
            exit;
        }
    }
}

$all_updates = get_career_updates();
// Last line: view template
require __DIR__ . '/../includes/templates/admin-updates-view.php';

