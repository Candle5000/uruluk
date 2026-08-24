<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Set up settings
$settings = require __DIR__ . '/../src/settings.php';

// Set up dependencies
$container = new \DI\Container();
$container->set('settings', $settings['settings']);

$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($container);

// Instantiate the app
$app = $container->get('app');

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// Run app
$app->run();
