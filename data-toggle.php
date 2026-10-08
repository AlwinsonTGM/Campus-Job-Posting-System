<?php
/**
 * Campus Job Posting System - Dataset Mode Switcher Controller (RETIRED)
 *
 * Dataset toggling has been retired. The system operates exclusively
 * on the normalized MySQL / MariaDB relational datastore.
 */
require_once __DIR__ . '/includes/data-helper.php';

http_response_code(404);
if (!headers_sent()) {
    header('Location: settings.php');
}
exit('Dataset mode switching has been permanently retired in favor of authoritative MySQL storage.');
