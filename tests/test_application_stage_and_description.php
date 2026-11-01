<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/services/job-service.php';
require_once __DIR__ . '/../includes/components.php';

$cases = [
    ['pending', 'under_review', true],
    ['under_review', 'pending', false],
    ['Interview Scheduled', 'under_review', false],
    ['interview_scheduled', 'declined', true],
    ['accepted', 'declined', false],
    ['accepted', 'accepted', true],
    ['withdrawn', 'pending', false],
    ['pending', 'invalid', false],
];
foreach ($cases as [$current, $next, $expected]) {
    if (can_advance_application_stage($current, $next) !== $expected) {
        throw new RuntimeException("Unexpected transition: {$current} to {$next}");
    }
}

$rendered = format_job_description("**Important**\n<script>alert(1)</script>");
if (!str_contains($rendered, '<strong>Important</strong>') || str_contains($rendered, '<script>') || !str_contains($rendered, '&lt;script&gt;')) {
    throw new RuntimeException('Description formatting must preserve bold while escaping HTML.');
}
echo "Application stage and description formatting checks passed.\n";
