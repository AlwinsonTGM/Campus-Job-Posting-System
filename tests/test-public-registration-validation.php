<?php
declare(strict_types=1);
require __DIR__ . '/../includes/services/common-service.php';
require __DIR__ . '/../includes/services/registration-validation.php';

$institutes = get_kld_institutes_and_courses();
$institute = array_key_first($institutes);
$today = new DateTimeImmutable('2026-10-26', new DateTimeZone('Asia/Manila'));
$student = [
    'role' => 'student', 'first_name' => 'Test', 'last_name' => 'Student',
    'email' => 'student@kld.edu.ph', 'phone' => '0917 123 4567',
    'student_id' => '2025-2-000065', 'department' => $institute,
    'course' => $institutes[$institute][0], 'year_level' => '1st Year',
    'sex' => 'Female', 'birthdate' => '2008-10-26', 'terms' => '1'
];
$partner = ['role' => 'employer', 'name' => 'Test Partner', 'email' => 'partner@example.com', 'phone' => '+63 917 123 4567', 'organization_name' => 'Example Company', 'terms' => '1'];
$cases = [
    ['Exactly 18 is eligible', $student, null],
    ['Older applicant is eligible', array_replace($student, ['birthdate' => '2000-10-26']), null],
    ['Under 18 is rejected', array_replace($student, ['birthdate' => '2008-10-27']), 2],
    ['Future birthday is rejected', array_replace($student, ['birthdate' => '2045-01-01']), 2],
    ['Impossible date is rejected', array_replace($student, ['birthdate' => '2000-02-30']), 2],
    ['Missing date is rejected', array_replace($student, ['birthdate' => '']), 2],
    ['Short student ID is rejected', array_replace($student, ['student_id' => '2025-2-00065']), 2],
    ['Missing ID hyphens is rejected', array_replace($student, ['student_id' => '20252000065']), 2],
    ['Two-digit term is rejected', array_replace($student, ['student_id' => '2025-22-000065']), 2],
    ['Wrong institute/program pairing is rejected', array_replace($student, ['course' => 'BS Nursing (BSN)']), 2],
    ['Unknown institute is rejected', array_replace($student, ['department' => 'Unknown']), 2],
    ['Other institute requires its name', array_replace($student, ['department' => 'Other Institute / Outsider', 'course' => 'Other Degree Program']), 2],
    ['Explicit other institute remains supported', array_replace($student, ['department' => 'Other Institute / Outsider', 'other_institute' => 'Other University', 'course' => 'Other Degree Program']), null],
    ['Invalid sex is rejected', array_replace($student, ['sex' => 'Other']), 2],
    ['Male remains supported', array_replace($student, ['sex' => 'Male']), null],
    ['Alumni status remains supported', array_replace($student, ['year_level' => 'Alumnus / Graduated']), null],
    ['Invalid year is rejected', array_replace($student, ['year_level' => 'Unknown']), 2],
    ['Malformed email is rejected', array_replace($student, ['email' => 'invalid-email']), 1],
    ['Letters in phone are rejected', array_replace($student, ['phone' => '0917ABC4567']), 1],
    ['Misplaced plus is rejected', array_replace($student, ['phone' => '0917+1234567']), 1],
    ['Short phone is rejected', array_replace($student, ['phone' => '123456']), 1],
    ['Too-long phone is rejected', array_replace($student, ['phone' => '1234567890123456']), 1],
    ['International phone is accepted', array_replace($student, ['phone' => '+63 917 123 4567']), null],
    ['Terms are required', array_replace($student, ['terms' => '']), 3],
    ['Valid partner is accepted', $partner, null],
    ['Partner needs company name', array_replace($partner, ['organization_name' => '']), 2],
    ['Partner needs valid email', array_replace($partner, ['email' => 'invalid']), 1],
    ['Partner needs valid phone', array_replace($partner, ['phone' => '123abc4567']), 1],
];
foreach ($cases as [$name, $payload, $expectedStep]) {
    $result = validate_public_registration_fields($payload, $institutes, get_year_levels(), $today);
    if (($result['step'] ?? null) !== $expectedStep) throw new RuntimeException($name . ': ' . json_encode($result));
    echo "PASS: $name\n";
}
echo count($cases) . " validation checks passed. No database or account mutations.\n";
