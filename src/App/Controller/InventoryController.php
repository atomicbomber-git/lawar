<?php
namespace App\Controller;

class InventoryController
{
    public function all ($request, $response)
    {
        return $this->container->view->render($response, "inventory/inventory.twig");
    }
}