<?php
declare(strict_types=1);

/**
 * AI Config — .env loader & config accessors
 * Extracted from includes/ai-config.php
 */

function load_env(?string $env_path = null): array {
    static $env_cache = null;
    if ($env_cache !== null) {
        return $env_cache;
    }

    if ($env_path === null) {
        $env_path = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($env_path)) {
            $env_path = dirname(__DIR__) . '/.env';
        }
    }

    $env_cache = [];
    if (!file_exists($env_path)) {
        return $env_cache;
    }

    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $env_cache;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
            continue;
        }

        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));

        // Unquote single/double quotes
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }

        $env_cache[$key] = $val;
        if (!getenv($key)) {
            putenv("$key=$val");
        }
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $val;
        }
    }

    return $env_cache;
}

/**
 * Get configuration value from .env or getenv with default
 */
function get_ai_env(string $key, mixed $default = null): mixed {
    $env = load_env();
    if (isset($env[$key]) && $env[$key] !== '') {
        return $env[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    return $default;
}
