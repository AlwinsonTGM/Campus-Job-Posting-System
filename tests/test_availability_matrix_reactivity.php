<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/data-helper.php';

echo "Running Availability Matrix & Reactivity Test..." . PHP_EOL;

// 1. Verify user hydration parses legacy string into availability array
$u1 = get_user_by_id(1);
if (empty($u1['availability'])) {
    throw new RuntimeException("Assertion failed: User 1 availability is empty.");
}
if (!in_array('Mon - Afternoon (1PM–5PM)', $u1['availability'], true)) {
    throw new RuntimeException("Assertion failed: User 1 missing Mon Afternoon.");
}
echo "[PASS] Test 1: User profile availability successfully hydrated from datastore.\n";

// 2. Verify normalize_availability_slots with multi-day / multi-slot strings
$norm = normalize_availability_slots('Tue/Thu 9:00 AM - 4:00 PM (12 hrs/week)');
if (!in_array('Tue - Morning (8AM–12NN)', $norm, true) || !in_array('Thu - Afternoon (1PM–5PM)', $norm, true)) {
    throw new RuntimeException("Assertion failed: normalize_availability_slots failed multi-day range.");
}
echo "[PASS] Test 2: normalize_availability_slots handles day ranges and timeslots.\n";

// 3. Verify HTML rendering contains interactive script, cursor pointer, and matrix-cell-selected
ob_start();
render_availability_matrix(['Mon - Morning (8AM–12NN)'], 'availability[]', false);
$html = ob_get_clean();

if (!str_contains($html, 'matrix-cell-selected')) {
    throw new RuntimeException("Assertion failed: matrix-cell-selected missing from rendered HTML.");
}
if (!str_contains($html, 'initMatrixInteractions')) {
    throw new RuntimeException("Assertion failed: initMatrixInteractions script missing from rendered HTML.");
}
if (!str_contains($html, 'style="cursor: pointer;"')) {
    throw new RuntimeException("Assertion failed: cursor pointer missing from interactive cells.");
}
echo "[PASS] Test 3: render_availability_matrix generates interactive markup and dynamic event scripts.\n";

// 4. Verify CSS contains :has(.matrix-check:checked)
$customCss = file_get_contents(__DIR__ . '/../assets/css/custom.css');
if (!str_contains($customCss, '.matrix-table td:has(.matrix-check:checked)')) {
    throw new RuntimeException("Assertion failed: custom.css missing :has(.matrix-check:checked).");
}
$darkCss = file_get_contents(__DIR__ . '/../assets/css/features/dark-overrides.css');
if (!str_contains($darkCss, '.matrix-table td:has(.matrix-check:checked)')) {
    throw new RuntimeException("Assertion failed: dark-overrides.css missing :has(.matrix-check:checked).");
}
echo "[PASS] Test 4: CSS rules include reactive :has(.matrix-check:checked) selectors.\n";

echo "ALL AVAILABILITY MATRIX TESTS PASSED!\n";
