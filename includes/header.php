<?php
/**
 * Campus Job Posting System - Shared Header Partial
 */
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CAMPUS HIRE');
}

if (!isset($page_title)) {
    $page_title = SITE_NAME . ' — Campus Job Posting System';
}

// Compute base path relative to current script
$project_root = str_replace('\\', '/', realpath(dirname(__DIR__)));
$script_file = isset($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'])) : '';
$script_dir = $script_file ? dirname($script_file) : '';

if ($script_dir && strpos($script_dir, $project_root) === 0) {
    $rel = trim(substr($script_dir, strlen($project_root)), '/');
    $depth = $rel === '' ? 0 : substr_count($rel, '/') + 1;
    $base_url = $depth > 0 ? str_repeat('../', $depth) : '';
} else {
    // Fallback based on PHP_SELF / SCRIPT_NAME
    $script_path = trim($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '', '/');
    $depth = $script_path === '' ? 0 : substr_count($script_path, '/');
    $base_url = $depth > 0 ? str_repeat('../', $depth) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | <?= htmlspecialchars(SITE_NAME) ?></title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts (Inter 500-800) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Theme & Paper Sheet CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/custom.css">
</head>
<body>

