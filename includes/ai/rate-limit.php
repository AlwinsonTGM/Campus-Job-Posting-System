<?php
declare(strict_types=1);

/**
 * AI Config — session/IP rate limiting
 * Extracted from includes/ai-config.php
 * Hardened with dual-bucket enforcement (Session + File-locked IP)
 */

function get_ai_ip_file(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    return sys_get_temp_dir() . '/cjps_ai_rl_' . md5($ip) . '.json';
}

function get_ai_ip_timestamps(string $ipFile, int $now, int $window): array {
    if (!file_exists($ipFile)) {
        return [];
    }
    $fp = @fopen($ipFile, 'r');
    if (!$fp) {
        return [];
    }
    $timestamps = [];
    if (flock($fp, LOCK_SH)) {
        $content = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        $data = json_decode($content ?: '[]', true);
        if (is_array($data)) {
            $timestamps = array_values(array_filter($data, fn($ts) => is_numeric($ts) && ($now - (int)$ts) < $window));
        }
    }
    fclose($fp);
    return $timestamps;
}

function record_ai_ip_timestamp(string $ipFile, int $now, int $window): void {
    $fp = @fopen($ipFile, 'c+');
    if (!$fp) {
        return;
    }
    if (flock($fp, LOCK_EX)) {
        $content = stream_get_contents($fp);
        $data = json_decode($content ?: '[]', true);
        $timestamps = is_array($data) ? array_filter($data, fn($ts) => is_numeric($ts) && ($now - (int)$ts) < $window) : [];
        $timestamps[] = $now;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode(array_values($timestamps)));
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

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

    // Clean session timestamps older than window
    $_SESSION['ai_rate_limit_timestamps'] = array_values(array_filter(
        $_SESSION['ai_rate_limit_timestamps'],
        fn($ts) => is_numeric($ts) && ($now - (int)$ts) < $window
    ));

    $sess_count = count($_SESSION['ai_rate_limit_timestamps']);
    $sess_oldest = !empty($_SESSION['ai_rate_limit_timestamps']) ? (int)reset($_SESSION['ai_rate_limit_timestamps']) : $now;

    // IP-level sliding window
    $ipFile = get_ai_ip_file();
    $ip_timestamps = get_ai_ip_timestamps($ipFile, $now, $window);
    $ip_count = count($ip_timestamps);
    $ip_oldest = !empty($ip_timestamps) ? (int)reset($ip_timestamps) : $now;

    $effective_count = max($sess_count, $ip_count);
    $oldest = min($sess_oldest, $ip_oldest);
    $retry_after = ($effective_count >= $limit_per_minute) ? max(1, $window - ($now - $oldest)) : 0;
    $remaining = max(0, $limit_per_minute - $effective_count);

    return [
        'allowed' => $effective_count < $limit_per_minute,
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
    $window = 60;
    $_SESSION['ai_rate_limit_timestamps'][] = $now;

    $ipFile = get_ai_ip_file();
    record_ai_ip_timestamp($ipFile, $now, $window);

    return [
        'allowed' => true,
        'retry_after' => 0,
        'remaining' => max(0, $status['remaining'] - 1),
        'limit_max' => $status['limit_max']
    ];
}
