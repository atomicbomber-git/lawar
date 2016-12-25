<?php

/*
    This file is intended to be require_once'd by index.php where an instance of
    Slim\App has been instantiated.
*/

use App\Middleware\LoggedInMiddleware;
use App\Middleware\ManagerOrAdminOnlyMiddleware;
use App\Middleware\AdminOnlyMiddleware;

$container = $app->getContainer();

/* Not found error handler */
$container["notFoundHandler"] = function($container) {
    return function($request, $response) use($container) {
        return $container->view->render($response, "general/404_error.twig");
    };
};

/* Redirect "/" to "/login" */
$app->get("/", function ($request, $response) {
    $path = $this->get("router")->pathFor("login");
    return $response->withStatus(302)->withHeader("Location", $path);
});

/* Login and authentication related routes */
$app->get("/login", "AuthenticationController:login")->setName("login");
$app->post("/login", "AuthenticationController:processLogin")->setName("process-login");

$app->get("/auth_error", "AuthenticationController:authError")
    ->setName("autherror")
    ->add(new LoggedInMiddleware($container));

$app->group("/inventory", function() use ($container) {

    /* Can only log out if you're logged in, duh */
    $this->get("/logout", "AuthenticationController:logout")->setName("logout");

    /* Home and all inventory items */
    $this->get("/home", "InventoryController:home")->setName("home");
    $this->get("/all", "InventoryController:all")->setName("inventory");
    $this->get("/filtered", "InventoryController:filtered")->setName("inventory-filtered");
    $this->get("/search", "InventoryController:searchItem")->setName("inventory-item-search");

    /* Routes for displaying ledgers */
    $this->get("/ledger/input", "InventoryController:ledgerInput")->setName("ledger-input");
    $this->get("/ledger/list", "InventoryController:ledgerList")->setName("ledger-list");

    /* URLs accessible only by user with ADMIN or MANAGER privilege */
    $this->group("", function() use ($container) {

        /* Cash register related */
        $this->get("/cash_register", "InventoryController:cashRegister")->setName("cash_register");
        $this->post("/cash_register/add_history", "InventoryController:addCashHistory")->setName("cash_register-add_history");

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

    })->add(new ManagerOrAdminOnlyMiddleware($container));

})->add(new LoggedInMiddleware($container));

$app->group("/user", function() use($container)  {
    $this->get("/signup", "UserController:signup")->setName("user-signup");
    $this->post("/signup", "UserController:processSignup")->setName("user-process-signup");
    $this->get("/manage", "UserController:manage")->setName("user-manage");
    $this->get("/edit/{id}", "UserController:edit")->setName("user-edit");
    $this->post("/edit/{id}", "UserController:processEdit")->setName("user-process-edit");
    $this->get("/editPassword/{id}", "UserController:editPassword/")->setName("user-edit-password");
    $this->post("/editPassword/{id}", "UserController:processEditPassword")->setName("user-process-edit-password");
    $this->get("/delete/{id}", "UserController:delete")->setName("user-delete");
    $this->post("/delete/{id}", "UserController:processDelete")->setName("user-process-delete");
})->add(new LoggedInMiddleware($container))->add(new AdminOnlyMiddleware($container));

$app->group("/invoice", function() use($container) {
    /* --Routes pertaining the shopping cart functionality-- */
    /* These routes deal with the current active shopping cart / invoice */
    $this->get("/cart/display", "InvoiceController:cartDisplay")->setName("cart");
    $this->post("/cart/finish", "InvoiceController:cartFinish")->setName("cart-finish");
    $this->get("/cart/finished", "InvoiceController:cartFinished")->setName("cart-finished");
    $this->get("/cart/return/{id}", "InvoiceController:cartReturn")->setName("cart-return");
    $this->post("/cart/return/{id}", "InvoiceController:processCartReturn")->setName("cart-process-return");

    $this->get("/transaction_item/add/{item_id}", "InvoiceController:addTransactionItem")->setName("invoice-item-add");
    $this->post("/transaction_item/add/{item_id}", "InvoiceController:processAddTransactionItem")->setName("invoice-item-process-add");
    $this->get("/transaction_item/delete/{item_id}", "InvoiceController:deleteTransactionItem")->setName("invoice-item-delete");
    $this->get("/transaction_item/edit/{item_id}", "InvoiceController:editTransactionItem")->setName("invoice-item-edit");
    $this->post("/transaction_item/edit/{item_id}", "InvoiceController:processEditTransactionItem")->setName("invoice-item-process-edit");

    $this->get("/transaction_input", "InvoiceController:transactionInput")->setName("invoice-transaction-input");
    $this->get("/transaction_list", "InvoiceController:transactionList")->setName("invoice-transaction-list");
    $this->get("/transaction_detail/{id}", "InvoiceController:transactionDetail")->setName("invoice-transaction-detail");
})->add(new LoggedInMiddleware($container));
