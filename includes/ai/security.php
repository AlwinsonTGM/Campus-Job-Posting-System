<?php
declare(strict_types=1);

/**
 * AI Config — query sanitization & security
 * Extracted from includes/ai-config.php
 */

function sanitize_ai_prompt(mixed $raw_input, int $max_length = 800): string {
    if (!is_string($raw_input)) {
        return '';
    }
    $clean = strip_tags(trim($raw_input));
    // Remove null bytes and non-printable control characters (except newline, tab)
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean) ?? '';
    if (function_exists('mb_strlen')) {
        if (mb_strlen($clean, 'UTF-8') > $max_length) {
            $clean = mb_substr($clean, 0, $max_length, 'UTF-8');
        }
    } else {
        if (strlen($clean) > $max_length) {
            $clean = substr($clean, 0, $max_length);
        }
    }
    return $clean;
}
