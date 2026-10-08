<?php
require_once __DIR__ . '/../includes/services/common-service.php';
require_once __DIR__ . '/../includes/services/system-checks.php';

echo "=== TEST 1: Candidate with 2 slots ===\n";
$app2 = [
    'id' => 3,
    'student_name' => 'Maria Santos',
    'availability' => [
        'Mon - Morning (8AM–12NN)',
        'Wed - Morning (8AM–12NN)',
    ],
];
$sched2 = get_schedule_summary($app2);

echo "Slots count: " . count($app2['availability']) . "\n";
echo "Schedule Compatibility: " . $sched2['summary'] . "\n";
echo "Schedule Tier: " . $sched2['tier'] . "\n";
echo "Hours: " . $sched2['hours'] . "\n";

assert(count($app2['availability']) === 2, "Candidate must have 2 slots");
assert($sched2['tier'] === "limited", "Must be limited tier");
assert($sched2['hours'] === 8, "Must have 8 hours");

echo "\n=== TEST 2: Candidate with 0 slots ===\n";
$app0 = [
    'id' => 4,
    'student_name' => 'Zero Slots',
    'availability' => [],
];
$sched0 = get_schedule_summary($app0);

echo "Schedule Compatibility: " . $sched0['summary'] . "\n";
echo "Schedule Tier: " . $sched0['tier'] . "\n";

assert($sched0['tier'] === "conflict", "0 slots must have 'conflict' tier");
assert($sched0['hours'] === 0, "0 slots must have 0 hours");

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
