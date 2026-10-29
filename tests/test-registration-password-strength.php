<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/data-helper.php';
require_once __DIR__ . '/../includes/services/user-service.php';

$assertions = [
    // [password, expected_valid, expected_error_substring]
    ['short', false, 'at least 8 characters'],
    ['1234567', false, 'at least 8 characters'],
    ['abcdefgh', false, 'stronger password'],
    ['12345678', false, 'stronger password'],
    ['ABCDEFGH', false, 'stronger password'],
    ['!@#$%^&*', false, 'stronger password'],
    ['Abcdefgh', true, null],
    ['abcdefg1', true, null],
    ['abcdef1!', true, null],
    ['Abcdef1!', true, null],
];

foreach ($assertions as [$pass, $expectedValid, $expectedSub]) {
    $res = validate_password_strength($pass);
    if ($res['valid'] !== $expectedValid) {
        throw new RuntimeException("Assertion failed for '{$pass}': expected valid=" . var_export($expectedValid, true) . ", got " . var_export($res['valid'], true));
    }
    if ($expectedSub !== null && (empty($res['error']) || stripos($res['error'], $expectedSub) === false)) {
        throw new RuntimeException("Assertion failed for '{$pass}': expected error to contain '{$expectedSub}', got '{$res['error']}'");
    }
}

echo "PASS: All " . count($assertions) . " password strength validation checks passed.\n";
