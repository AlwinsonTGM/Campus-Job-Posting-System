<?php
declare(strict_types=1);

/**
 * AI Config — session/IP rate limiting
 * Extracted from includes/ai-config.php
 */

function get_ai_rate_limit_status(?int $limit_per_minute = null): array {
    if ($limit_per_minute === null) {
        $limit_per_minute = (int)get_ai_env('AI_RATE_LIMIT_PER_MINUTE', 10);
    }
    $limit_per_minute = max(1, min(60, $limit_per_minute));

    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }

    $now = time();
    $window = 60; // 60 seconds

    if (!isset($_SESSION['ai_rate_limit_timestamps']) || !is_array($_SESSION['ai_rate_limit_timestamps'])) {
        $_SESSION['ai_rate_limit_timestamps'] = [];
    }

    // Clean timestamps older than window
    $_SESSION['ai_rate_limit_timestamps'] = array_filter(
        $_SESSION['ai_rate_limit_timestamps'],
        fn($ts) => ($now - $ts) < $window
    );

    $count = count($_SESSION['ai_rate_limit_timestamps']);
    $oldest = reset($_SESSION['ai_rate_limit_timestamps']) ?: $now;
    $retry_after = ($count >= $limit_per_minute) ? max(1, $window - ($now - $oldest)) : 0;
    $remaining = max(0, $limit_per_minute - $count);

    return [
        'allowed' => $count < $limit_per_minute,
        'retry_after' => $retry_after,
        'remaining' => $remaining,
        'limit_max' => $limit_per_minute
    ];
}

/**
 * Check and enforce rate limiting per session and IP (records request hit if allowed)
 * 
 * @param int|null $limit_per_minute
 * @return array ['allowed' => bool, 'retry_after' => int, 'remaining' => int, 'limit_max' => int]
 */
function check_ai_rate_limit(?int $limit_per_minute = null): array {
    $status = get_ai_rate_limit_status($limit_per_minute);

    if (!$status['allowed']) {
        return $status;
    }

    $now = time();
    $_SESSION['ai_rate_limit_timestamps'][] = $now;

    return [
        'allowed' => true,
        'retry_after' => 0,
        'remaining' => max(0, $status['remaining'] - 1),
        'limit_max' => $status['limit_max']
    ];
}
