<?php
/**
 * DWMS 2.0 front controller. Every request lands here, so URLs never expose
 * a file extension.
 */
require __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Router;

$router = new Router();
require __DIR__ . '/app/routes.php';

// Guide the operator to the installer while the database is not ready yet.
// Everything under /setup is exempt, including the installer's own POST.
$path = current_path();
if (strpos($path, '/setup') !== 0 && (!Database::ok() || !Database::tableExists('settings'))) {
    redirect('/setup');
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
