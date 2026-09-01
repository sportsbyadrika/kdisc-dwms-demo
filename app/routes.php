<?php
/**
 * Application routes. Every URL is extension-less and resolved by index.php.
 *
 * @var \App\Core\Router $router
 */

/* ------------------------------------------------------------- installer */
$router->get('/setup',          'SetupController@index');
$router->post('/setup/install', 'SetupController@install');

/* ---------------------------------------------------------------- public */
$router->get('/',               'HomeController@index');
$router->get('/about',          'PageController@about');
$router->get('/employers',      'PageController@employers');
$router->get('/faq',            'PageController@faq');
$router->get('/privacy',        'PageController@privacy');
$router->get('/terms',          'PageController@terms');
$router->get('/accessibility',  'PageController@accessibility');
$router->get('/sitemap',        'PageController@sitemap');
$router->get('/contact',        'PageController@contact');
$router->post('/contact',       'PageController@contactSubmit');
