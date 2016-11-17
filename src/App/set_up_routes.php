<?php
/* Expects an instance of Slim\App */
use App\Middleware\AuthMiddleware;

$container = $app->getContainer();
$auth_middleware = new AuthMiddleware($container);

/* Redirect to /login */
$app->get("/", function ($request, $response) {
    $path = $this->get("router")->pathFor("login");
    return $response->withStatus(302)->withHeader("Location", $path);
});

$app->get("/login", "AuthenticationController:login")->setName("login");
$app->post("/login", "AuthenticationController:processLogin");
$app->get("/logout", "AuthenticationController:logout")->setName("logout");
$app->get("/signup", "AuthenticationController:signup")->setName("signup");
$app->post("/signup", "AuthenticationController:processSignup");

$app->group("/inventory", function () {
    $this->get("", "InventoryController:all")->setName("inventory");
    $this->get("/edit/{item_id}", "InventoryController:editItem")->setName("inventory-item-edit");
    $this->post("/edit/{item_id}", "InventoryController:processEditItem")->setName("inventory-item-process-edit");
    $this->get("/delete/{item_id}", "InventoryController:deleteItem")->setName("inventory-item-delete");
    $this->post("/delete/{item_id}", "InventoryController:processDeleteItem")->setName("inventory-item-process-delete");
})->add($auth_middleware);