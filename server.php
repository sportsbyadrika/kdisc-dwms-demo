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

// Never expose application internals through the dev server. Apache does this
// with .htaccess; mirroring it here keeps development honest.
if (preg_match('#^/(app|database|node_modules|\.git)(/|$)#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

// Dotfiles (.env above all) and configuration artefacts are never served.
if (preg_match('#(^|/)\.(?!well-known/)#', $path)
    || preg_match('#\.(env|ya?ml|json|lock|md|sql|log|ini|sh|bak|dist|example|sample)$#i', $path)
    || preg_match('#^/(server|tailwind\.config)\.#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

// Mirror the uploads/.htaccess rule: nothing under /uploads is ever executed.
if (preg_match('#^/uploads/#', $path) && preg_match('#\.(php[0-9]*|phtml|phar|pl|py|cgi|sh)$#i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server stream the asset
}
require __DIR__ . '/index.php';
