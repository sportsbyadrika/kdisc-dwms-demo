<?php
/**
 * Router for PHP's built-in web server, so the project can be developed
 * locally without Apache:
 *
 *     php -S localhost:8000 server.php
 *
 * Shared hosting uses .htaccess instead; this file is never reached there.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Never expose application internals through the dev server.
if (preg_match('#^/(app|database|node_modules|\.git)(/|$)#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server stream the asset
}
require __DIR__ . '/index.php';
