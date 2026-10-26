<?php
/** Pure validation for the public registration form, before DNS checks or uploads. */
function validate_public_registration_fields(array $data, array $institutes, array $year_levels, ?DateTimeImmutable $today = null): ?array {
    $fail = static fn(string $message, int $step): array => ['message' => $message, 'step' => $step];
    $student = ($data['role'] ?? 'student') === 'student';
    if ($student && (trim((string)($data['first_name'] ?? '')) === '' || trim((string)($data['last_name'] ?? '')) === '')) {
        return $fail('Please enter your first and last name.', 1);
    }
    if (!$student && trim((string)($data['name'] ?? '')) === '') {
        return $fail('Please enter the contact person’s full name.', 1);
    }
    if (!filter_var(trim((string)($data['email'] ?? '')), FILTER_VALIDATE_EMAIL)) {
        return $fail('Please enter a valid email address, for example name@domain.com.', 1);
    }
    $phone = trim((string)($data['phone'] ?? ''));
    $digits = preg_replace('/\D/', '', $phone);
    if (!preg_match('/^\+?[0-9][0-9 ()-]{5,18}[0-9]$/D', $phone) || strlen($digits) < 7 || strlen($digits) > 15) {
        return $fail('Please enter a valid phone number with 7–15 digits, e.g. 0917 123 4567 or +63 917 123 4567.', 1);
    }
    if (!$student && trim((string)($data['organization_name'] ?? '')) === '') {
        return $fail('Please enter your company / organization name.', 2);
    }
    if ($student) {
        if (!preg_match('/^[0-9]{4}-[0-9]-[0-9]{6}$/D', trim((string)($data['student_id'] ?? '')))) {
            return $fail('Student ID must use the format YYYY-T-XXXXXX, e.g. 2025-2-000065.', 2);
        }
        $institute = (string)($data['department'] ?? '');
        $course = (string)($data['course'] ?? '');
        if ($institute === 'Other Institute / Outsider') {
            if (trim((string)($data['other_institute'] ?? '')) === '' || $course !== 'Other Degree Program') {
                return $fail('Please specify your institute and select Other degree program.', 2);
            }
        } elseif (!array_key_exists($institute, $institutes) || !in_array($course, $institutes[$institute], true)) {
            return $fail('Select your academic institute, then choose a degree program offered by that institute.', 2);
        }
        if (!array_key_exists((string)($data['year_level'] ?? ''), $year_levels)) {
            return $fail('Please select a valid year level / academic status.', 2);
        }
        if (!in_array($data['sex'] ?? '', ['Male', 'Female'], true)) {
            return $fail('Please select Male or Female for biological sex.', 2);
        }
        $raw_birthdate = (string)($data['birthdate'] ?? '');
        $today = ($today ?? new DateTimeImmutable('today', new DateTimeZone('Asia/Manila')))->setTime(0, 0);
        $birthdate = DateTimeImmutable::createFromFormat('!Y-m-d', $raw_birthdate, $today->getTimezone());
        $date_errors = DateTimeImmutable::getLastErrors();
        if (!$birthdate || ($date_errors && ($date_errors['warning_count'] || $date_errors['error_count'])) || $birthdate->format('Y-m-d') !== $raw_birthdate) {
            return $fail('Please enter a valid date of birth.', 2);
        }
        if ($birthdate > $today->modify('-18 years')) {
            return $fail('You must be at least 18 years old to register.', 2);
        }
    }
    if (($data['terms'] ?? '') !== '1') {
        return $fail('Please agree to the Terms of Service and acknowledge the Privacy Policy.', 3);
    }
    return null;
}
