<?php
/* Expects an instance of Slim\App */
use App\Middleware\AuthMiddleware;

$container = $app->getContainer();
$auth_middleware = new AuthMiddleware($container);

$app->get("/login", "AuthenticationController:login")->setName("login");
$app->post("/login", "AuthenticationController:processLogin");

$app->get("/logout", "AuthenticationController:logout")->setName("logout");

$app->group("/inventory", function () {
    $this->get("", "InventoryController:all")->setName("inventory");
    $this->get("/edit/{item_id}", "InventoryController:edit")->setName("inventory-edit");
    $this->post("/edit/{item_id}", "InventoryController:processEdit")->setName("inventory-process-edit");
})->add($auth_middleware);