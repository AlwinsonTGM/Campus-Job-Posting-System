<?php
/**
 * Campus Job Posting System - Jobs Redirect Handler
 * Seamless redirect to student jobs directory for direct and AI assistant navigation.
 */
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: student/jobs.php' . $qs, true, 302);
exit;
