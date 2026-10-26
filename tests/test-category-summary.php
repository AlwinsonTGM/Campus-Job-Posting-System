<?php
declare(strict_types=1);

// Read-only integration check against the configured datastore.
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/admin/categories.php';
$_SERVER['PHP_SELF'] = '/admin/categories.php';
$_SERVER['REQUEST_URI'] = '/admin/categories.php';
require_once __DIR__ . '/../includes/data-helper.php';
$_SESSION['user'] = get_db_connection()->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
if (!$_SESSION['user']) {
    throw new RuntimeException('This check requires an existing admin.');
}
if (($argv[1] ?? '') === 'archived') {
    $_GET['tab'] = 'archived';
}

ob_start();
require __DIR__ . '/../admin/categories.php';
$html = ob_get_clean();

function summary_value(string $html, string $label): string {
    if (preg_match('~<dt[^>]*>' . preg_quote($label, '~') . '</dt>\s*<dd[^>]*>(.*?)</dd>~si', $html, $match)) {
        return trim(strip_tags($match[1]));
    }
    if (preg_match('~' . preg_quote($label, '~') . '</span>\s*</div>\s*<div[^>]*>(.*?)</div>~si', $html, $match)) {
        return trim(strip_tags($match[1] ?? ''));
    }
    return '';
}

$expectedPhotos = count(array_filter($active_categories, fn($cat) => get_category_cover($cat) !== ''));
$checks = [
    'Active families stay independent of the selected tab' => [summary_value($html, 'Active Families'), (string)count($active_categories)],
    'Photo count includes the covers displayed on cards' => [summary_value($html, 'Photo Profiles'), $expectedPhotos . ' / ' . count($active_categories)],
    'Active postings match the homepage metric' => [summary_value($html, 'Active Postings'), (string)get_metrics_total_active_jobs()],
    'Average pay matches the homepage metric' => [summary_value($html, 'Avg Hourly Pay'), get_metrics_avg_hourly_pay()],
];
$failed = 0;
foreach ($checks as $name => [$actual, $expected]) {
    $ok = $actual === $expected;
    echo ($ok ? 'PASS' : 'FAIL') . ': ' . $name . ' (actual=' . $actual . ', expected=' . $expected . ")\n";
    $failed += (int)!$ok;
}
exit($failed ? 1 : 0);
