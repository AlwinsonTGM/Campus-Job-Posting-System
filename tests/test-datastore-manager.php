<?php
declare(strict_types=1);

/**
 * Unit & Integration Test for DatastoreManager & Fixture Lifecycle
 */

require_once __DIR__ . '/../includes/data-helper.php';

echo "=====================================================\n";
echo "DATASTORE MANAGER DEEP MODULE TEST RUNNER\n";
echo "=====================================================\n";

$passCount = 0;
$failCount = 0;

function assert_test(bool $condition, string $name): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] $name\n";
        $passCount++;
    } else {
        echo "  [FAIL] $name\n";
        $failCount++;
    }
}

// 1. Data mode queries
$mode = DatastoreManager::getMode();
assert_test(is_string($mode) && ($mode === 'demo' || $mode === 'real'), "getMode returns valid system mode ('{$mode}')");

$shimMode = get_system_data_mode();
assert_test($shimMode === $mode, 'get_system_data_mode() shim returns matching mode');

// 2. Fixture constant integrity
assert_test(count(DatastoreManager::SEED_FILES) === 8, 'SEED_FILES contains exactly 8 essential fixtures');
assert_test(in_array('users.json', DatastoreManager::SEED_FILES, true), 'SEED_FILES contains users.json');
assert_test(in_array('jobs.json', DatastoreManager::SEED_FILES, true), 'SEED_FILES contains jobs.json');
assert_test(in_array('applications.json', DatastoreManager::SEED_FILES, true), 'SEED_FILES contains applications.json');
assert_test(in_array('categories.json', DatastoreManager::SEED_FILES, true), 'SEED_FILES contains categories.json');

// 3. Schema memoization and execution
try {
    DatastoreManager::ensureSchema();
    assert_test(true, 'ensureSchema executes gracefully');
} catch (Throwable $e) {
    assert_test(false, 'ensureSchema threw unexpected exception: ' . $e->getMessage());
}

// Ensure schema shims execute without error
try {
    ensure_password_resets_table();
    assert_test(true, 'ensure_password_resets_table shim executes without error');
} catch (Throwable $e) {
    assert_test(false, 'ensure_password_resets_table failed: ' . $e->getMessage());
}

try {
    ensure_email_verifications_table();
    assert_test(true, 'ensure_email_verifications_table shim executes without error');
} catch (Throwable $e) {
    assert_test(false, 'ensure_email_verifications_table failed: ' . $e->getMessage());
}

// 4. Session purge testing
$_SESSION['user'] = ['id' => 99, 'name' => 'Ephemeral Test User', 'role' => 'student'];
$_SESSION['temp_key'] = 'random_val';
assert_test(!empty($_SESSION['user']), 'Session user initialized for purge test');

DatastoreManager::purgeSession();
assert_test(empty($_SESSION['user']), 'purgeSession clears session user');
assert_test(empty($_SESSION['temp_key']), 'purgeSession clears all session data');

// 5. Check allowed tables synchronization in migrate.php
$migrateCode = file_get_contents(__DIR__ . '/../database/migrate.php');
assert_test(str_contains($migrateCode, "'email_verifications'"), 'migrate.php allowed_tables includes email_verifications');
assert_test(str_contains($migrateCode, "'password_resets'"), 'migrate.php allowed_tables includes password_resets');

echo "=====================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "=====================================================\n";

exit($failCount === 0 ? 0 : 1);
