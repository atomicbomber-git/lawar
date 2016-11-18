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

        return $this->view->render($response, "inventory/inventory.twig", ["items" => $items, "filter" => $filter]);
    }

    public function addItem ($request, $response)
    {
        /* Retrieve messages that were stored in the session */
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $types = Type::get();
        return $this->view->render($response, "inventory/item_add.twig", ["types" => $types, "message" => $message]);
    }

    public function processAddItem ($request, $response)
    {
        $item = new Item( $request->getParsedBody() );
        $item->save();

        $_SESSION["message"]["success"]["add"] = "Item '$item->name' berhasil ditambahkan!";

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory-item-add"));
    }

    public function editItem ($request, $response, $args)
    {
        /* Retrieve messages that were stored in the session */
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $item = Item::find($args["item_id"]);

        /* List of all available types to be displayed in <select> tag */
        $types = Type::get();

        return $this->view->render($response, "inventory/item_edit.twig", ["item" => $item, "types" => $types, "message" => $message]);
    }

    public function processEditItem ($request, $response, $args)
    {

        $item = Item::find($args["item_id"]);
        $item->update( $request->getParsedBody() );

        /* Success message to be displayed on the edit page! */
        $_SESSION["message"]["success"]["edit"] = "Data berhasil diubah!";

        $path = $this->router->pathFor("inventory-item-edit", ["item_id" => $args["item_id"]]);
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
        /* Retrieve messages that were stored in the session */
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $types = Type::get();
        return $this->view->render($response, "inventory/type.twig", ["types" => $types, "message" => $message]);
    }

    public function addType ($request, $response)
    {

        if ( ! $request->getParsedBody()["name"] && $request->getParsedBody()["name"] !== "0" ) {
            $_SESSION["message"]["error"]["name"] = "Nama tipe baru tidak boleh kosong.";
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("type"));
        }

        $type = new Type;
        $type->name = $request->getParsedBody()["name"];
        $type->save();

        /* Success message to be displayed on the type page! */
        $_SESSION["message"]["success"]["add"] = "Tipe '$type->name' berhasil ditambahkan.";

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("type"));
    }

    public function editType ($request, $response, $args)
    {
        $type = Type::find($args["type_id"]);
        return $this->view->render($response, "inventory/type_edit.twig", ["type" => $type]);
    }

    public function processEditType ($request, $response, $args)
    {

        $type = Type::find($args["type_id"]);
        $type->name = $request->getParsedBody()["name"];
        $type->save();

        /* Success message to be displayed on the type page! */
        $_SESSION["message"]["success"]["add"] = "Pengubahan data berhasil dilakukan.";

        $path = $this->router->pathFor("type");
        return $response->withStatus(302)->withHeader("Location", $path);

    }

    public function deleteType ($request, $response, $args)
    {
        $type = Type::find($args["type_id"]);

        return $this->view->render($response, "inventory/type_delete.twig", ["type" => $type]);
    }

    public function processDeleteType ($request, $response, $args)
    {
        $type = Type::find($args["type_id"]);

        /* Success message to be displayed on the type page! */
        $_SESSION["message"]["success"]["delete"] = "Tipe '$type[name]' berhasil dihapus.";

        $type->delete();

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("type"));
    }
}