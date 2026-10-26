<?php
declare(strict_types=1);

$template = file_get_contents(__DIR__ . '/../includes/templates/student-job-details-view.php');
$start = strpos($template, '<!-- Schedule Compatibility Overview -->');
$end = strpos($template, '<!-- Action CTA -->', $start);
$panel = substr($template, $start, $end - $start);

$student_schedule_fit = ['is_guest' => true, 'choices' => []];
$job = ['status' => 'active', 'slots_total' => 3, 'slots_filled' => 0];

foreach (['admin' => false, 'student' => true, 'guest' => true] as $role => $expected) {
    $user = $role === 'guest' ? null : ['role' => $role];
    ob_start();
    eval('?>' . $panel);
    $html = ob_get_clean();
    $visible = str_contains($html, 'bi-calendar-check');
    if ($visible !== $expected) {
        throw new RuntimeException("Unexpected schedule panel visibility for {$role}");
    }
    echo "PASS: {$role} schedule panel visibility\n";
}
