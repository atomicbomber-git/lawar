<?php

/* 
    This file is intended to be require_once'd by index.php where an instance of
    Slim\App has been instantiated.
*/

use App\Middleware\LoggedInMiddleware;
use App\Middleware\AdminOnlyMiddleware;

$container = $app->getContainer();

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

    /* Home and all inventory items */
    $this->get("/home", "InventoryController:home")->setName("home");
    $this->get("/all", "InventoryController:all")->setName("inventory");
    $this->get("/filtered", "InventoryController:filtered")->setName("inventory-filtered");
    $this->get("/search", "InventoryController:searchItem")->setName("inventory-item-search");

    /* Routes pertaining the item management functionality */
    $this->get("/item/add", "InventoryController:addItem")->setName("inventory-item-add");
    $this->post("/item/add", "InventoryController:processAddItem")->setName("inventory-item-process-add");
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


    $this->get("/cash_register", "InventoryController:cashRegister")
        ->setName("cash_register")
        ->add(new AdminOnlyMiddleware($container));
    
    $this->post("/cash_register/add_history", "InventoryController:addCashHistory")->setName("cash_register-add_history");

    /* --Routes pertaining the shopping cart functionality-- */
    /* These routes deal with the current active shopping cart / invoice */
    $this->get("/cart/display", "InvoiceController:cartDisplay")->setName("cart");
    $this->post("/cart/finish", "InvoiceController:cartFinish")->setName("cart-finish");
    $this->get("/transaction_item/add/{item_id}", "InvoiceController:addTransactionItem")->setName("invoice-item-add");
    $this->get("/transaction_item/delete/{item_id}", "InvoiceController:deleteTransactionItem")->setName("invoice-item-delete");

    /* These routes apply universally to any TransactionItems*/
    $this->get("/transaction_item/edit/{item_id}", "InvoiceController:editTransactionItem")->setName("invoice-item-edit");
    $this->post("/transaction_item/edit/{item_id}", "InvoiceController:processEditTransactionItem")->setName("invoice-item-process-edit");
    $this->post("/transaction_item/add/{item_id}", "InvoiceController:processAddTransactionItem")->setName("invoice-item-process-add");


})->add(new LoggedInMiddleware($container));