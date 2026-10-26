<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/components.php';

$cases = [
    'Pending Review' => ['Pending Review', 'bi-clock'],
    'under_review' => ['Under Evaluation', 'bi-clipboard2-check'],
    'interview_scheduled' => ['Interview Scheduled', 'bi-calendar-event'],
    'accepted' => ['Accepted / Hired', 'bi-check-circle'],
    'Declined / Position Filled' => ['Declined / Filled', 'bi-x-circle'],
    'withdrawn' => ['Withdrawn', 'bi-dash-circle'],
    'archived' => ['Archived', 'bi-archive'],
    'Active' => ['Active', 'bi-check-circle'],
];
foreach ($cases as $value => [$label, $icon]) {
    $html = render_status_text($value);
    if (!str_contains($html, $label) || !str_contains($html, $icon) || str_contains($html, 'badge') || !str_contains($html, 'aria-hidden="true"')) {
        throw new RuntimeException('Incorrect plain status: ' . $value);
    }
}
if (str_contains(render_status_text('<script>alert(1)</script>'), '<script>')) {
    throw new RuntimeException('Unknown status labels must be escaped.');
}
if (!str_contains(render_status_badge('pending'), 'badge-status--pending')) {
    throw new RuntimeException('Other pages must retain their existing status badges.');
}
echo "PASS: Employer status aliases, outline icons, escaped labels, and existing badge behavior.\n";
