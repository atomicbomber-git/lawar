<?php
namespace App\Controller;

use App\Model\Item;
use App\Model\Type;
use App\Model\CashHistory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Respect\Validation\Validator as V;

class InventoryController extends BaseController
{
    public function home ($request, $response)
    {
        return $this->view->render($response, "inventory/home.twig");
    }

    public function all ($request, $response)
    {
        $items = Item::get();
        return $this->view->render($response, "inventory/inventory.twig", ["items" => $items]);
    }

    public function filtered ($request, $response) {

        $keyword = $request->getQueryParam("keyword");
        $filter_type = $request->getQueryParam("filter_type");

        /* Bizarre PHP behavior where "0" is treated as a false value; Have to add an exception so it isn't
            treated as so.
        */
        if ( !$keyword && $keyword !== "0" ) {
            /* The keyword query must not be empty. */
            $_SESSION["message"]["error"]["keyword"] = "Error: Kata kunci tidak boleh kosong.";
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory-item-search"));
        }

        if ($filter_type !== "type") {

            $items = Item::with("type")
                ->having($filter_type, "LIKE", "%$keyword%")
                ->get();

        }
        else {
            $items = Item::with("type")
                ->whereHas("type", function ($query) use ($keyword) {
                    $query->where("name", "LIKE", "%$keyword%");
                })
                ->get();
        }

        /* Search params to be shown as alert in the result page */
        $search_params["keyword"] = $keyword;
        switch ($filter_type) {
            case "name":
                $search_params["filter_type"] = "nama";
                break;

            case "size":
                $search_params["filter_type"] = "ukuran";
                break;

            case "type":
                $search_params["filter_type"] = "tipe";
                break;

            case "type":
                $search_params["filter_type"] = "deskripsi";
                break;
        }

        return $this->view->render($response, "inventory/inventory_filtered.twig", ["items" => $items, "search_params" => $search_params]);
    }

    public function searchItem ($request, $response) {

        $message = null;
        /* Retrieve messages that were stored in the session */
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        return $this->view->render($response, "inventory/item_search.twig", ["message" => $message]);
    }

    public function addItem ($request, $response)
    {
        $message = null;
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
        $data = $request->getParsedBody();

        /* Validate data */
        $has_error = false;
        if ( ! V::numeric()->positive()->validate($data["price"]) ) {
            $has_error = true;
            $_SESSION["message"]["form_error"]["price"] = "Data wajib diisi dan wajib berupa angka positif";
        }

        if ( ! V::numeric()->min(0)->validate($data["stock_store"]) ) {
            $has_error = true;
            $_SESSION["message"]["form_error"]["stock_store"] = "Data minimal bernilai 0";
        } 

        if ( ! V::numeric()->min(0)->validate($data["stock_warehouse"]) ) {
            $has_error = true;
            $_SESSION["message"]["form_error"]["stock_warehouse"] = "Data minimal bernilai 0";
        } 

        if ($has_error) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory-item-add"));
        }

        $item = new Item( $request->getParsedBody() );
        $item->save();

        $_SESSION["message"]["success"]["add"] = "Item '$item->name' berhasil ditambahkan!";

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory-item-add") . "#message");
    }

    public function editItem ($request, $response, $args)
    {
        $message = null;
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

        $path = $this->router->pathFor("inventory");
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
        $message = null;
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

    public function cashRegister ($request, $response)
    {
        $cash_history = CashHistory::get();

        return $this->view->render($response, "inventory/cash_register.twig", ["cash_history" => $cash_history]);
    }

    public function addCashHistory ($request, $response)
    { 
        $data = $request->getParsedBody();

        /* TODO: Validation */

        $amount = $data["amount"];
        /* Amount out is negative if we're taking money from the register */
        if ($data["is_out"]) {
            $amount *= -1;
        }

        $cash_history = new CashHistory([
            "amount" => $amount,
            "description" => $data["description"],
            "clerk_id" => $_SESSION["user"]->id,
        ]);

        $cash_history->save();

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cash_register"));
    }
}