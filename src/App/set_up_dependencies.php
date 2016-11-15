<?php
/* Expects $app, an instance of Slim\App */
use App\Controller\AuthenticationController;

$container = $app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('src/View');
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    return $view;
};

/* Authentication controller */
$container["AuthenticationController"] = function ($container)
{
    return new AuthenticationController($container);
};