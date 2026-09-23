<?php
/**
 * Campus Job Posting System - Admin Category Management
 * Archetype B/C: Category Taxonomy Grid (COAL101 Blueprint)
 * Enhanced with Related Category Photography & Visual Profiles
 */
require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/auth-check.php';

require_auth(['admin']);
$user = get_logged_user();
$page_title = 'Job Family Category Taxonomy';

$error = null;

// Helper to resolve picture URL for both external Unsplash URLs and local uploads
function get_category_cover(array $cat): string {
    $img = $cat['image'] ?? '';
    if (!empty($img)) {
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '/')) {
            return $img;
        }
        return '../' . ltrim($img, '/');
    }

    // Smart curated fallbacks based on category title/slug keywords
    $haystack = strtolower(($cat['name'] ?? '') . ' ' . ($cat['slug'] ?? ''));
    if (str_contains($haystack, 'tech') || str_contains($haystack, 'it') || str_contains($haystack, 'computer')) {
        return '../assets/img/categories/cat-tech.jpg';
    }
    if (str_contains($haystack, 'lib') || str_contains($haystack, 'book')) {
        return '../assets/img/categories/cat-library.jpg';
    }
    if (str_contains($haystack, 'admin') || str_contains($haystack, 'clerk') || str_contains($haystack, 'office')) {
        return '../assets/img/categories/cat-admin.jpg';
    }
    if (str_contains($haystack, 'sci') || str_contains($haystack, 'lab') || str_contains($haystack, 'chem')) {
        return '../assets/img/categories/cat-lab.jpg';
    }
    if (str_contains($haystack, 'tutor') || str_contains($haystack, 'peer') || str_contains($haystack, 'mentor')) {
        return '../assets/img/categories/cat-tutor.jpg';
    }
    if (str_contains($haystack, 'sport') || str_contains($haystack, 'athletic') || str_contains($haystack, 'gym')) {
        return '../assets/img/categories/cat-sports.jpg';
    }
    if (str_contains($haystack, 'media') || str_contains($haystack, 'art') || str_contains($haystack, 'design')) {
        return '../assets/img/categories/cat-media.jpg';
    }
    if (str_contains($haystack, 'food') || str_contains($haystack, 'cafe') || str_contains($haystack, 'barista')) {
        return '../assets/img/categories/cat-cafe.jpg';
    }
    return '../assets/img/categories/cat-general.jpg';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed: Invalid or expired security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? 'create';

        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                $error = 'Category name cannot be empty.';
            } else {
                $icon = $_POST['icon'] ?? 'bi-briefcase';
                if (!preg_match('/^bi-[a-z0-9-]+$/', $icon)) {
                    $icon = 'bi-briefcase';
                }
                $color = $_POST['color'] ?? 'primary';
                if (!in_array($color, ['primary', 'accent', 'danger', 'info', 'warning', 'success', 'secondary'], true)) {
                    $color = 'primary';
                }

                $cat_id = create_category([
                    'name'         => $name,
                    'description'  => trim($_POST['description'] ?? ''),
                    'icon'         => $icon,
                    'color'        => $color,
                    'badge_tag'    => trim($_POST['badge_tag'] ?? ''),
                    'badge_icon'   => trim($_POST['badge_icon'] ?? 'bi-star-fill'),
                    'hourly_range' => trim($_POST['hourly_range'] ?? ''),
                    'image'        => trim($_POST['image'] ?? '')
                ], $_FILES['category_photo'] ?? null);

                if ($cat_id > 0) {
                    set_flash('success', "New category '{$name}' with related visual profile was created successfully.");
                    header('Location: categories.php');
                    exit;
                }
                $error = "Failed to create category '{$name}'. A category with this name or slug may already exist.";
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if ($id <= 0 || empty($name)) {
                $error = 'Valid category ID and name are required for updating.';
            } else {
                $icon = $_POST['icon'] ?? 'bi-briefcase';
                if (!preg_match('/^bi-[a-z0-9-]+$/', $icon)) {
                    $icon = 'bi-briefcase';
                }
                $color = $_POST['color'] ?? 'primary';
                if (!in_array($color, ['primary', 'accent', 'danger', 'info', 'warning', 'success', 'secondary'], true)) {
                    $color = 'primary';
                }

                $ok = update_category($id, [
                    'name'         => $name,
                    'description'  => trim($_POST['description'] ?? ''),
                    'icon'         => $icon,
                    'color'        => $color,
                    'badge_tag'    => trim($_POST['badge_tag'] ?? ''),
                    'badge_icon'   => trim($_POST['badge_icon'] ?? 'bi-star-fill'),
                    'hourly_range' => trim($_POST['hourly_range'] ?? ''),
                    'image'        => trim($_POST['image'] ?? '')
                ], $_FILES['category_photo'] ?? null);

                if ($ok) {
                    set_flash('success', "Category '{$name}' and related picture updated successfully.");
                    header('Location: categories.php');
                    exit;
                }
                $error = "Failed to update category '{$name}'.";
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                delete_category($id);
                set_flash('success', "Category #{$id} was deleted permanently.");
                header('Location: categories.php');
                exit;
            }
            $error = 'Invalid category ID specified for deletion.';
        }
    }
}

$categories = get_categories();
$all_jobs = get_jobs();
// Last line: view template
require __DIR__ . '/../includes/templates/admin-categories-view.php';

