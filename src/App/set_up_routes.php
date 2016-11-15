<?php
/* Expects an instance of Slim\App */

$app->get("/login", "AuthenticationController:login");