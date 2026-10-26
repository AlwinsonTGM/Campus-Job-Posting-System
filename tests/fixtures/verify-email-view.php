<?php
/** CLI-only view fixture: renders no controller, sends no email, changes no account. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../../includes/services/common-service.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__, 2) . '/verify-email.php';
$_SERVER['SCRIPT_NAME'] = '/verify-email.php';
$_SESSION = ['pending_verification' => ['dev_code' => '123456']];
$existing_user = ['role' => ($argv[1] ?? 'student') === 'partner' ? 'employer' : 'student'];
$pending_email = ($argv[1] ?? 'student') === 'long' ? str_repeat('sample', 10) . '@example.com' : 'sample@example.com';
$smtp_ready = ($argv[2] ?? '') !== 'testing';
$remaining_seconds = (int)($argv[3] ?? 2);
$error = null;
$page_title = 'Verify Your Email Address';
require __DIR__ . '/../../includes/templates/verify-email-view.php';
