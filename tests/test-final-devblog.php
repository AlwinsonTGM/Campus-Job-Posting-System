<?php
declare(strict_types=1);

define('DATA_DIR', dirname(__DIR__) . '/data');
$GLOBALS['blogRows'] = [];
function get_db_connection(): object {
    return new class {
        public function query(string $sql): object {
            return new class {
                public function fetchAll(): array { return $GLOBALS['blogRows']; }
            };
        }
    };
}
function hydrate_devblog(mixed $row): ?array { return $row; }
require dirname(__DIR__) . '/includes/services/system-service.php';

$GLOBALS['blogRows'] = [['id' => 'day-29']];
$blogs = get_devblogs();
if (($blogs[0]['id'] ?? '') !== 'day-30' || count($blogs) !== 2) {
    throw new RuntimeException('The final entry should appear for an older database.');
}
$GLOBALS['blogRows'] = [['id' => 'day-30'], ['id' => 'day-29']];
$blogs = get_devblogs();
if (count($blogs) !== 2 || count(array_filter($blogs, fn($entry) => $entry['id'] === 'day-30')) !== 1) {
    throw new RuntimeException('The final entry should not be duplicated.');
}
echo "Final devblog compatibility checks passed.\n";
