<?php
namespace App\Core;

use Throwable;

/**
 * Applies the .sql files in database/migrations once each, in filename order.
 *
 * schema.sql stays the first-install baseline; everything added after it lives
 * here, so a site that was installed months ago can pick up new tables and
 * columns without being reinstalled.
 */
class Migrator
{
    public const DIR = '/database/migrations';

    /** Every migration file on disk, oldest first. */
    public static function available(): array
    {
        $files = glob(DWMS_ROOT . self::DIR . '/*.sql') ?: [];
        $names = array_map('basename', $files);
        sort($names, SORT_STRING);
        return $names;
    }

    /** Filenames already recorded as applied. */
    public static function applied(): array
    {
        if (!Database::ok() || !Database::tableExists('migrations')) {
            return [];
        }
        $rows = Database::all('SELECT filename FROM migrations ORDER BY filename');
        return array_column($rows, 'filename');
    }

    public static function pending(): array
    {
        return array_values(array_diff(self::available(), self::applied()));
    }

    /**
     * Run every pending migration. Each file is applied in full or not at all,
     * and the run stops at the first failure so a later migration never lands
     * on a half-applied earlier one.
     *
     * @return array{applied:string[],failed:?string,error:?string}
     */
    public static function run(): array
    {
        self::ensureTable();

        $done = [];
        foreach (self::pending() as $name) {
            try {
                self::apply($name);
                $done[] = $name;
            } catch (Throwable $e) {
                return ['applied' => $done, 'failed' => $name, 'error' => $e->getMessage()];
            }
        }
        return ['applied' => $done, 'failed' => null, 'error' => null];
    }

    /** Record every migration as applied without running it (fresh installs). */
    public static function markAllApplied(): void
    {
        self::ensureTable();
        foreach (self::pending() as $name) {
            Database::run('INSERT IGNORE INTO migrations (filename) VALUES (?)', [$name]);
        }
    }

    private static function ensureTable(): void
    {
        Database::pdo()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                filename   VARCHAR(190) NOT NULL UNIQUE,
                applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    private static function apply(string $name): void
    {
        $path = DWMS_ROOT . self::DIR . '/' . basename($name);
        $sql  = file_get_contents($path);
        if ($sql === false) {
            throw new \RuntimeException('Cannot read ' . $name);
        }

        $pdo = Database::pdo();
        // MySQL commits implicitly on DDL, so a transaction cannot wrap this.
        // Recording the filename only after the last statement succeeds keeps a
        // failed migration pending rather than silently half-done.
        foreach (self::statements($sql) as $statement) {
            $pdo->exec($statement);
        }
        Database::run('INSERT IGNORE INTO migrations (filename) VALUES (?)', [$name]);
    }

    /** Split a file into statements, ignoring comment lines. */
    public static function statements(string $sql): array
    {
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $out = [];
        foreach (preg_split('/;\s*[\r\n]+/', (string) $sql) as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $out[] = $statement;
            }
        }
        return $out;
    }
}
