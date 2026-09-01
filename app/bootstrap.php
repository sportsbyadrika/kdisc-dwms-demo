<?php
/**
 * DWMS 2.0 bootstrap - autoloading, session, error handling.
 */
define('DWMS_ROOT', dirname(__DIR__));
define('DWMS_START', microtime(true));

require DWMS_ROOT . '/app/helpers.php';

spl_autoload_register(static function (string $class): void {
    if (strpos($class, 'App\\') !== 0) {
        return;
    }
    $path = DWMS_ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

// Configuration comes from .env at the project root. It is read after the
// autoloader is registered because the reader is an App\Core class, and before
// config() is first called.
\App\Core\Env::load(DWMS_ROOT . '/.env');

date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));

if (config('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(config('security.session_name', 'dwms_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_url() ?: '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

// Idle-timeout
$ttl = (int) config('security.session_ttl', 7200);
if ($ttl > 0 && isset($_SESSION['_last_seen']) && (time() - $_SESSION['_last_seen']) > $ttl) {
    session_unset();
    session_destroy();
    session_start();
    flash('info', 'Your session timed out. Please sign in again.');
}
$_SESSION['_last_seen'] = time();

set_exception_handler(static function (Throwable $ex): void {
    error_log('[DWMS] ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    if (config('app.debug')) {
        abort(500, $ex->getMessage() . ' — ' . $ex->getFile() . ':' . $ex->getLine());
    }
    abort(500, 'An unexpected error occurred. Please try again.');
});
