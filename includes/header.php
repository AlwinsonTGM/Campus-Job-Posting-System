<?php
/**
 * Campus Job Posting System - Shared Header Partial
 */
require_once __DIR__ . '/components.php';

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

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

    <!-- Anti-FOUC: Apply saved theme before CSS paint to prevent white flash -->
    <script>
    (function(){
      var t = localStorage.getItem('campus_hire_theme');
      if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', t);
      document.documentElement.setAttribute('data-bs-theme', t);
    })();
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $base_url ?>assets/img/favicon.svg">
    <link rel="apple-touch-icon" href="<?= $base_url ?>assets/img/favicon.svg">
    
    <!-- Bootstrap 5 CSS (Local Offline Vendor) -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/vendor/bootstrap/bootstrap.min.css">
    
    <!-- Bootstrap Icons (Local Offline Vendor) -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    
    <!-- Self-Hosted Fonts (Inter, Plus Jakarta Sans, Outfit - 100% Offline) -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/vendor/fonts/fonts.css">
    
    <!-- Custom Theme & Paper Sheet CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : '1.0' ?>">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/custom.css?v=<?= file_exists(__DIR__ . '/../assets/css/custom.css') ? filemtime(__DIR__ . '/../assets/css/custom.css') : '1.0' ?>">
</head>
<body>

