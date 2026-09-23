<?php
/**
 * Campus Job Posting System - Employer / Campus Department Dispatches
 * Archetype B: Department Announcements Hub (COAL101 Blueprint)
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['employer', 'admin']);
$user = get_logged_user();
$page_title = 'Department Dispatches & Bulletins';

$org_name = $user['organization_name'] ?? ($user['department'] ?? 'Campus Department');
$error = null;
$action = $_POST['action'] ?? null;

// Handle Add / Edit / Delete POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } elseif ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $author_name = trim($_POST['author_name'] ?? $user['name']);
        $author_role = trim($_POST['author_role'] ?? 'Department Supervisor');
        $author_office = trim($_POST['author_office'] ?? $org_name);

        if (empty($title) || empty($content)) {
            $error = 'Headline title and article content are required.';
        } else {
            add_career_update([
                'title' => $title,
                'category' => 'Department Notice',
                'summary' => $summary,
                'content' => $content,
                'image' => $image,
                'author_name' => $author_name,
                'author_role' => $author_role,
                'author_office' => $author_office
            ]);
            set_flash('success', "Your department dispatch '{$title}' was published live immediately.");
            header('Location: updates.php');
            exit;
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $author_name = trim($_POST['author_name'] ?? $user['name']);
        $author_role = trim($_POST['author_role'] ?? 'Department Supervisor');
        $author_office = trim($_POST['author_office'] ?? $org_name);

        if ($id <= 0 || empty($title) || empty($content)) {
            $error = 'Valid update ID, title, and content are required.';
        } else {
            update_career_update($id, [
                'title' => $title,
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
            set_flash('success', "Dispatch #{$id} has been removed.");
            header('Location: updates.php');
            exit;
        }
    }
}

$all_updates = get_career_updates();
// If employer, highlight their department's updates first
$dept_updates = array_filter($all_updates, function($u) use ($org_name, $user) {
    return ($user['role'] === 'admin') || 
           stripos($u['author']['office'] ?? '', $org_name) !== false || 
           stripos($u['author']['name'] ?? '', $user['name']) !== false;
});
// Last line: view template
require __DIR__ . '/../includes/templates/employer-updates-view.php';

