<?php
namespace App\Controller;

use App\Model\Item;

class InventoryController extends BaseController
{
    public function all ($request, $response)
    {
        $items = Item::get();
        return $this->container->view->render($response, "inventory/inventory.twig", ["items" => $items]);
    }
}