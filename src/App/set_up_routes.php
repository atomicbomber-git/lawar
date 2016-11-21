<?php
/* Expects an instance of Slim\App */
use App\Middleware\AuthMiddleware;

$container = $app->getContainer();
$auth_middleware = new AuthMiddleware($container);

/* Redirect "/" to "/login" */
$app->get("/", function ($request, $response) {
    $path = $this->get("router")->pathFor("login");
    return $response->withStatus(302)->withHeader("Location", $path);
});

/* Login and authentication related routes */
$app->get("/login", "AuthenticationController:login")->setName("login");
$app->post("/login", "AuthenticationController:processLogin");
$app->get("/logout", "AuthenticationController:logout")->setName("logout");
$app->get("/signup", "AuthenticationController:signup")->setName("signup");
$app->post("/signup", "AuthenticationController:processSignup");

$app->group("/inventory", function () {
    $this->get("", "InventoryController:all")->setName("inventory");
    $this->get("/filtered", "InventoryController:filtered")->setName("inventory-filtered");

    /* Routes pertaining the item management functionality */
    $this->get("/item/add", "InventoryController:addItem")->setName("inventory-item-add");
    $this->post("/item/add", "InventoryController:processAddItem")->setName("inventory-item-process-add");
    $this->get("/item/search", "InventoryController:searchItem")->setName("inventory-item-search");
    $this->get("/item/edit/{item_id}", "InventoryController:editItem")->setName("inventory-item-edit");
    $this->post("/item/edit/{item_id}", "InventoryController:processEditItem")->setName("inventory-item-process-edit");
    $this->get("/item/delete/{item_id}", "InventoryController:deleteItem")->setName("inventory-item-delete");
    $this->post("/item/delete/{item_id}", "InventoryController:processDeleteItem")->setName("inventory-item-process-delete");

    /* Routes pertaining the item type management functionality */
    $this->get("/type", "InventoryController:type")->setName("type");
    $this->post("/type/add", "InventoryController:addType")->setName("inventory-type-add");
    $this->get("/type/edit/{type_id}", "InventoryController:editType")->setName("inventory-type-edit");
    $this->post("/type/edit/{type_id}", "InventoryController:processEditType")->setName("inventory-type-process-edit");
    $this->get("/type/delete/{type_id}", "InventoryController:deleteType")->setName("inventory-type-delete");
    $this->post("/type/delete/{type_id}", "InventoryController:processDeleteType")->setName("inventory-type-process-delete");

    /* Routes pertaining the shopping cart functionality */
    $this->get("/cart", "InvoiceController:cart")->setName("cart");

    $this->get("/transaction_item/add/{item_id}", "InvoiceController:addTransactionItem")->setName("invoice-item-add");
    $this->post("/transaction_item/add/{item_id}", "InvoiceController:processAddTransactionItem")->setName("invoice-item-process-add");

})->add($auth_middleware);