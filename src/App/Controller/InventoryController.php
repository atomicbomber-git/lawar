<?php
namespace App\Controller;

use App\Model\Item;
use App\Model\Type;

class InventoryController extends BaseController
{
    public function all ($request, $response)
    {
        /* Attempt to retrieve the filter query from the URL s*/
        $filter = $request->getQueryParam("filter");

        /* Bizarre PHP behavior where "0" is treated as a false value; Have to add an exception so it isn't
            treated as so.
        */
        if ( !$filter && $filter !== "0" ) { $filter = ""; }

        $items = Item::where("name", "LIKE", "%$filter%")
            ->get();

        return $this->container->view->render($response, "inventory/inventory.twig", ["items" => $items, "filter" => $filter]);
    }

    public function editItem ($request, $response, $args)
    {
        $item = Item::find($args["item_id"]);
        return $this->container->view->render($response, "inventory/item_edit.twig", ["item" => $item]);
    }

    public function processEditItem ($request, $response, $args)
    {

        $item = Item::find($args["item_id"]);
        $item->update( $request->getParsedBody() );

        $path = $this->container->get("router")->pathFor("inventory");
        return $response->withStatus(302)->withHeader("Location", $path);
    }

    public function deleteItem ($request, $response, $args)
    {
        $item = Item::find($args["item_id"]);
        return $this->view->render($response, "inventory/item_delete.twig", ["item" => $item]);
    } 

    public function processDeleteItem ($request, $response, $args)
    {
        $item = Item::find($args["item_id"]);
        $item->delete();

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory") );
    }

    public function type ($request, $response)
    {
        $types = Type::get();
        return $this->view->render($response, "inventory/type.twig", ["types" => $types]);
    }

    public function editType ($request, $response, $args)
    {
        $type = Type::find($args["type_id"]);
        return $this->container->view->render($response, "inventory/type_edit.twig", ["type" => $type]);
    }

    public function processEditType ($request, $response, $args)
    {

        $type = Type::find($args["type_id"]);
        $type->name = $request->getParsedBody()["name"];
        $type->save();

        $path = $this->router->pathFor("type");
        return $response->withStatus(302)->withHeader("Location", $path);
    }
}