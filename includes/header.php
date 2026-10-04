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

    <!-- Favicon: D4 Antenna Helper (light default, synced to dark pairing before paint) -->
    <link id="brandFavicon32" rel="icon" type="image/png" sizes="32x32" href="<?= $base_url ?>assets/img/favicon-32.png?v=d4">
    <link id="brandFavicon16" rel="icon" type="image/png" sizes="16x16" href="<?= $base_url ?>assets/img/favicon-16.png?v=d4">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $base_url ?>assets/img/apple-touch-180.png?v=d4">

    <!-- Anti-FOUC & Favicon Sync: Apply saved theme & favicon before CSS paint to prevent white flash / icon desync on refresh -->
    <script>
    (function(){
      try {
        var t = localStorage.getItem('campus_hire_theme');
        if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', t);
        document.documentElement.setAttribute('data-bs-theme', t);
        if (t === 'dark') {
          var f32 = document.getElementById('brandFavicon32');
          var f16 = document.getElementById('brandFavicon16');
          if (f32) f32.href = '<?= $base_url ?>assets/img/logo-d4-dark-32.png?v=d4';
          if (f16) f16.href = '<?= $base_url ?>assets/img/logo-d4-dark-32.png?v=d4';
        }
      } catch(e) {}
    })();
    </script>
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#F1EBDC">
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#101418">
    
    <!-- Bootstrap 5 CSS (Local Offline Vendor) -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/vendor/bootstrap/bootstrap.min.css">
    
    <!-- Bootstrap Icons (Local Offline Vendor) -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    
    <!-- Self-Hosted Fonts (Inter, Plus Jakarta Sans, Outfit - 100% Offline) -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/vendor/fonts/fonts.css">
    
    <!-- Custom Theme & Paper Sheet CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : '1.0' ?>">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/custom.css?v=<?= file_exists(__DIR__ . '/../assets/css/custom.css') ? filemtime(__DIR__ . '/../assets/css/custom.css') : '1.0' ?>">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/features/companion.css?v=<?= file_exists(__DIR__ . '/../assets/css/features/companion.css') ? filemtime(__DIR__ . '/../assets/css/features/companion.css') : '2.0' ?>">
</head>
<body>

