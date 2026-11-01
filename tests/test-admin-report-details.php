<?php
declare(strict_types=1);

$metrics = ['vacancies', 'applications', 'interviews', 'hired'];
if (isset($argv[1])) {
    require_once __DIR__ . '/../includes/data-helper.php';
    require_once __DIR__ . '/../includes/auth-check.php';
    if (!quick_login('admin')) throw new RuntimeException('A local admin is required.');
    $type = $argv[1];
    if ($type === 'report') {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        require __DIR__ . '/../admin/reports.php';
        $html = ob_get_clean();
        foreach (['vacancies', 'applications', 'interviews', 'hired'] as $metric) {
            if (!str_contains($html, 'report-details.php?metric=' . $metric)) throw new RuntimeException("Missing {$metric} report link");
        }
        if (!str_contains($html, 'href="users.php" class="metric report-metric-link')) throw new RuntimeException('Missing user directory link');
        if (str_contains($html, 'impeccable-variants-start a92af182')) throw new RuntimeException('Preview wrapper remains.');
        echo "PASS: report links all five metrics\n";
        exit;
    }
    $_GET = ['metric' => $type];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $rows = $type === 'vacancies' ? get_jobs() : get_applications();
    if ($type === 'interviews' || $type === 'hired') {
        $statuses = $type === 'interviews' ? ['interview_scheduled', 'interview scheduled', 'interview'] : ['accepted', 'accepted / hired', 'hired'];
        $rows = array_values(array_filter($rows, fn($row) => in_array(strtolower($row['status'] ?? ''), $statuses, true)));
    }
    ob_start();
    require __DIR__ . '/../admin/report-details.php';
    $html = ob_get_clean();
    $count = number_format(count($rows)) . (count($rows) === 1 ? ' record' : ' records');
    if (!str_contains($html, $count) || !str_contains($html, 'Back to reports')) throw new RuntimeException("Incorrect output for {$type}");
    if (count($rows) > 25 && !str_contains($html, 'page=2')) throw new RuntimeException("Missing pagination for {$type}");
    echo "PASS: {$type} detail count and navigation\n";
    exit;
}
foreach (array_merge($metrics, ['report']) as $metric) {
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . escapeshellarg($metric);
    passthru($command, $status);
    if ($status !== 0) exit($status);
}
