<?php
namespace App\Controllers;

use App\Core\Database as DB;
use PDO;
use Throwable;

/**
 * First-run installer. Reachable at /setup until the schema exists.
 */
class SetupController
{
    public function index(): void
    {
        $envExists    = is_file(DWMS_ROOT . '/.env');
        $configExists = is_file(DWMS_ROOT . '/app/config.php');
        $configured   = $envExists || $configExists;
        $uploadsOk    = is_writable(DWMS_ROOT . '/uploads');

        $checks = [
            ['PHP 7.4 or newer',   version_compare(PHP_VERSION, '7.4.0', '>='), PHP_VERSION],
            ['PDO MySQL driver',   extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'loaded' : 'missing'],
            ['mbstring extension', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'loaded' : 'missing'],
            ['fileinfo extension', extension_loaded('fileinfo'), extension_loaded('fileinfo') ? 'loaded' : 'optional but recommended'],
            ['Configuration file', $configured,
             $envExists ? '.env found' : ($configExists ? 'app/config.php found' : 'copy .env.example to .env and fill it in')],
            ['uploads/ writable',  $uploadsOk, $uploadsOk ? 'writable' : 'chmod 755 (or 775) the uploads folder'],
        ];

        $dbOk      = DB::ok();
        $installed = $dbOk && DB::tableExists('settings');

        echo render('pages.setup', [
            'pageTitle' => 'Setup',
            'checks'    => $checks,
            'dbOk'      => $dbOk,
            'dbError'   => DB::error(),
            'installed' => $installed,
            'dbName'    => config('db.name'),
            'dbHost'    => config('db.host'),
            'envExists' => $envExists,
        ], 'blank');
    }

    public function install(): void
    {
        verify_csrf();
        if (!DB::ok()) {
            flash('error', 'Cannot connect to MySQL. Check the credentials in app/config.php.');
            redirect('/setup');
        }
        if (DB::tableExists('settings') && input('force') !== '1') {
            flash('info', 'The database is already installed.');
            redirect('/setup');
        }

        try {
            $this->runSqlFile(DWMS_ROOT . '/database/schema.sql');
            $this->runSqlFile(DWMS_ROOT . '/database/seed.sql');
        } catch (Throwable $e) {
            flash('error', 'Installation failed: ' . $e->getMessage());
            redirect('/setup');
        }

        flash('success', 'Database installed. Sign in as admin@dwms.local with the password Admin@12345 and change it immediately.');
        redirect('/setup');
    }

    /** Split on semicolons at end-of-line — sufficient for our own schema files. */
    private function runSqlFile(string $file): void
    {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new \RuntimeException('Cannot read ' . basename($file));
        }
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $pdo = DB::pdo();
        foreach (preg_split('/;\s*[\r\n]+/', $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') {
                continue;
            }
            $pdo->exec($stmt);
        }
    }
}
