<?php
/**
 * Campus Job Posting System - Database Connection Layer (PDO)
 * Centralized database access for MySQL / MariaDB in XAMPP
 */

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'campus_job_portal');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Get singleton PDO connection to the MySQL database.
 * 
 * @return PDO
 * @throws PDOException
 */
function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database doesn't exist yet, attempt to connect to server without dbname
            if ($e->getCode() == 1049) {
                try {
                    $server_dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
                    $server_pdo = new PDO($server_dsn, DB_USER, DB_PASS, $options);
                    $server_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");
                    // Re-try connection
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                } catch (PDOException $inner_e) {
                    throw new PDOException("Database connection error: " . $inner_e->getMessage(), (int)$inner_e->getCode());
                }
            } else {
                throw new PDOException("Database connection error: " . $e->getMessage(), (int)$e->getCode());
            }
        }
    }

    return $pdo;
}

/**
 * Check if the database connection is currently alive and operational.
 * 
 * @return bool
 */
function is_db_connected() {
    try {
        $db = get_db_connection();
        return $db !== null;
    } catch (Exception $e) {
        return false;
    }
}
