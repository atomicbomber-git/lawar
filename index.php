<?php
require __DIR__ . '/vendor/autoload.php';

/* Set timezone for date functions */
date_default_timezone_set('Asia/Pontianak');

use Slim\App as Slim;

$app = new Slim([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

include './src/App/set_up_db_connection.php';
include './src/App/set_up_dependencies.php';
include './src/App/set_up_routes.php';

$app->run();