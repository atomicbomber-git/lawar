<?php
/* Expects $app, an instance of Slim\App */

use App\Controller\AuthenticationController;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$container = $app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('src/View');
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    return $view;
};

/* Monolog logger */
$container["logger"] = function ($container) use ($WEB_ROOT)
{
    $logger = new Logger("lawar_shop_logger");
    $logger->pushHandler(new StreamHandler("$WEB_ROOT/log/lawar_shop_web.log", Logger::DEBUG));
    return $logger;
};

/* Authentication controller */
$container["AuthenticationController"] = function ($container)
{
    return new AuthenticationController($container);
};

/* Invoice controller */
$container["InvoiceController"] = function ($container)
{
    return new \App\Controller\InvoiceController($container);
};

/* Inventory controller */
$container["InventoryController"] = function ($container)
{
    return new \App\Controller\InventoryController($container);
};

/* User controller */
$container["UserController"] = function ($container)
{
    return new \App\Controller\UserController($container);
};