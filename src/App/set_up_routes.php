<?php
/* Expects an instance of Slim\App */

$app->get("/login", "AuthenticationController:login")->setName("login");
$app->post("/login", "AuthenticationController:processLogin");

$app->get("/inventory", "InventoryController:all")->setName("inventory");
$app->get("/inventory/edit/{item_id}", "InventoryController:edit")->setName("inventory-edit");