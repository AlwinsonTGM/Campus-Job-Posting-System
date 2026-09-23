<?php
/**
 * Campus Job Posting System - NVIDIA NIM AI Configuration & Security Gateway
 * Aggregator: domain modules extracted under includes/ai/ (verbatim moves).
 * Public function signatures preserved for all callers.
 */

if (!defined('NVIDIA_CONFIG_LOADED')) {
    define('NVIDIA_CONFIG_LOADED', true);
}

require_once __DIR__ . '/ai/env.php';
require_once __DIR__ . '/ai/security.php';
require_once __DIR__ . '/ai/rate-limit.php';
require_once __DIR__ . '/ai/models.php';
require_once __DIR__ . '/ai/core.php';
require_once __DIR__ . '/ai/local-fallback.php';
