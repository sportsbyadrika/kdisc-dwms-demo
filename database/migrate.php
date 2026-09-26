<?php
/**
 * Apply pending database migrations from the command line:
 *
 *     php database/migrate.php
 *
 * Useful over SSH, or when the /setup routes have been removed after install
 * as the README recommends. Refuses to run through a web server.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script runs from the command line only.\n");
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Migrator;

if (!Database::ok()) {
    fwrite(STDERR, "Cannot connect to the database: " . Database::error() . "\n");
    fwrite(STDERR, "Check DB_* in your .env file.\n");
    exit(1);
}
if (!Database::tableExists('settings')) {
    fwrite(STDERR, "The database is not installed yet. Visit /setup first.\n");
    exit(1);
}

$pending = Migrator::pending();
if (!$pending) {
    echo "Database is up to date — nothing to apply.\n";
    exit(0);
}

echo count($pending) . " migration(s) pending:\n";
foreach ($pending as $file) {
    echo "  - $file\n";
}

$result = Migrator::run();
foreach ($result['applied'] as $file) {
    echo "applied  $file\n";
}
if ($result['failed'] !== null) {
    fwrite(STDERR, "FAILED   {$result['failed']}\n         {$result['error']}\n");
    exit(1);
}
echo "Done.\n";
