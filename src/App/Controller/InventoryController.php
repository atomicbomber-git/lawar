<?php
namespace App\Controller;

use App\Model\Item;

class InventoryController extends BaseController
{
    public function all ($request, $response)
    {

        $filter = $request->getQueryParam("filter");
        if ( !$filter && $filter !== "0" ) { $filter = ""; }

        $items = Item::where("name", "LIKE", "%$filter%")
            ->get();

        return $this->container->view->render($response, "inventory/inventory.twig", ["items" => $items, "filter" => $filter]);
    }
}