<?php
/**
 * Campus Job Posting System - Data Helper & Persistence Engine
 * Thin aggregator: boots session/DB and loads domain service layers.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

define('DATA_DIR', dirname(__DIR__) . '/data');

// Domain services (order matters: common first for shared helpers)
require_once __DIR__ . '/services/common-service.php';
require_once __DIR__ . '/services/user-service.php';
require_once __DIR__ . '/services/job-service.php';
require_once __DIR__ . '/services/system-service.php';
