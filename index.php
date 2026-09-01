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
$path = current_path();
if ($path !== '/setup' && (!Database::ok() || !Database::tableExists('settings'))) {
    redirect('/setup');
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
