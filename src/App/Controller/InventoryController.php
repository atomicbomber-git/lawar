<?php
namespace App\Controller;

use App\Model\Item;
use App\Model\Type;
use App\Model\CashHistory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Jenssegers\Date\Date;
use Respect\Validation\Validator as V;

class InventoryController extends BaseController
{
    public function debug ($request, $response)
    {
        // echo $WEB_ROOT;
    }

    public function home ($request, $response)
    {
        return $this->view->render($response, "inventory/home.twig");
    }

    public function all ($request, $response)
    {
        /* Get page from the query string */
        $page = $request->getQueryParam("page");

        if ( ! V::intVal()->min(1)->validate($page) ) {
            /* Invalid page parameter */
            $page = 1;
        }

        $items_per_page = 5;

        $items = Item::limit($items_per_page)
            ->offset( ($page - 1) * $items_per_page)
            ->orderBy("entry_date")
            ->get();

        $pagination = $this->getPagination(Item::count(), $items_per_page, $page);

        return $this->view->render($response, "inventory/inventory.twig", ["items" => $items, "pagination" => $pagination]);
    }

    public function filtered ($request, $response) {

        /* Amount of items to be displayed in a single page */
        $items_per_page = 5;

        /* Get page from the query string */
        $page = $request->getQueryParam("page");

        /* Validate the page query parameter */
        if ( ! V::intVal()->min(1)->validate($page) ) {
            /* Invalid page parameter */
            $page = 1;
        }

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
                ->offset( ($page - 1) * $items_per_page)
                ->limit($items_per_page)
                ->get();

            $count = Item::with("type")
                ->where($filter_type, "LIKE", "%$keyword%")
                ->count();
            }
        else {
            $items = Item::with("type")
                ->whereHas("type", function ($query) use ($keyword) {
                    $query->where("name", "LIKE", "%$keyword%");
                })
                ->offset( ($page - 1) * $items_per_page)
                ->limit($items_per_page)
                ->get();

            $count = Item::with("type")
                ->whereHas("type", function ($query) use ($keyword) {
                    $query->where("name", "LIKE", "%$keyword%");
                })
                ->count();
        }

        $pagination = $this->getPagination($count, $items_per_page, $page);

        /* Search params to be shown as alert in the result page */
        $search_params["keyword"] = $keyword;
        $search_params["filter_type"] = $filter_type;
        switch ($filter_type) {
            case "name":
                $search_params["filter_type_view"] = "nama";
                break;

            case "size":
                $search_params["filter_type_view"] = "ukuran";
                break;

            case "type":
                $search_params["filter_type_view"] = "tipe";
                break;

            case "type":
                $search_params["filter_type_view"] = "deskripsi";
                break;
        }

        return $this->view->render($response, "inventory/inventory_filtered.twig",
            ["items" => $items, "search_params" => $search_params, "pagination" => $pagination]
        );
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
        $return_page = $request->getQueryParam("return_page");

        $message = null;
        /* Retrieve messages that were stored in the session */
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $current_date = date("d/m/Y");

        $types = Type::get();
        return $this->view->render($response, "inventory/item_add.twig",
            ["types" => $types, "message" => $message, "current_date" => $current_date, "return_page" => $return_page]
        );
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

        if ( ! V::notEmpty()->validate($data["entry_date"]) ) {
            $has_error = true;
            $_SESSION["message"]["form_error"]["entry_date"] = "Tanggal masuk wajib ada";
        }

        if ($has_error) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory-item-add"));
        }

        /* Format date so it can be inserted to database */
        $data["entry_date"] = (new Date($data["entry_date"]))->format("Y-m-d");

        $item = new Item( $data );
        $item->save();

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory"));
    }

    public function editItem ($request, $response, $args)
    {
        $return_page = $request->getQueryParam("return_page");

        $message = null;
        /* Retrieve messages that were stored in the session */
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $item = Item::find($args["item_id"]);

        /* List of all available types to be displayed in <select> tag */
        $types = Type::get();

        return $this->view->render($response, "inventory/item_edit.twig",
            ["item" => $item, "types" => $types, "message" => $message, "return_page" => $return_page]
        );
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
        $return_page = $request->getQueryParam("return_page");

        $item = Item::find($args["item_id"]);
        return $this->view->render($response, "inventory/item_delete.twig", ["item" => $item, "return_page" => $return_page]);
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
        /* Retrieve messages that were stored in the session */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $cash_history = CashHistory::get();

        return $this->view->render($response, "inventory/cash_register.twig", ["cash_history" => $cash_history, "message" => $message]);
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
            "datetime" => date("Y-m-d H:i:s")
        ]);

        $cash_history->save();
        $_SESSION["message"]["success"]["add_cash_history"] = "Catatan keuangan berhasil ditambahkan";

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cash_register"));
    }

    public function ledgerInput ($request, $response)
    {
        /* Retrieve messages that were stored in the session */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        /* Load current cash data */
        $file_path = "$GLOBALS[WEB_ROOT]/storage/cash.json";
        $cash_file = file_get_contents($file_path);
        $cash_data = json_decode($cash_file);
        $cash_amount = $cash_data->cash;

        $current_date = date("m/d/Y");

        return $this->view->render($response, "inventory/ledger_input.twig",
            ["cash_history" => $cash_history, "cash" => $cash_amount, "message" => $message, "current_date" => $current_date]);
    }

    public function ledgerList ($request, $response)
    {
        /* Get page from the query string */
        $page = $request->getQueryParam("page");

        if ( ! V::intVal()->min(1)->validate($page) ) {
            /* Invalid page parameter */
            $page = 1;
        }

        $items_per_page = 5;

        /* Retrieve POST data */
        $data = $request->getQueryParams();
        $start_date = $data["start_date"];
        $end_date = $data["end_date"];

        $has_error = false;
        /* Validate dates */
        if ( ! V::date()->Validate($start_date) ) {
            $has_error = true;
            $_SESSION["message"]["error"]["start_date"] = "Nilai harus berupa tanggal";
        }

        if ( ! V::date()->Validate($end_date) ) {
            $has_error = true;
            $_SESSION["message"]["error"]["end_date"] = "Nilai harus berupa tanggal";
        }

        if ( $has_error ) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("ledger-input"));
        }

        $start_date = new Date($start_date);
        $end_date = new Date($end_date);

        /* End date shifted by a day so that when two of the input dates are the same, the results won't be empty */
        $shifted_end_date = new Date($end_date);
        $shifted_end_date->add("1 day");

        /* Retrieve the total amount of items to show */
        $count = CashHistory::leftJoin("clerks", "cash_history.clerk_id", "=", "clerks.id")
            ->whereBetween("cash_history.datetime", [$start_date->format("Y-m-d"), $shifted_end_date->format("Y-m-d")])
            ->count();

        /* Create pagination object */
        $pagination = $this->getPagination($count, $items_per_page, $page);

        /* Load current cash data */
        $file_path = "$GLOBALS[WEB_ROOT]/storage/cash.json";
        $cash_file = file_get_contents($file_path);
        $cash_data = json_decode($cash_file);
        $cash_amount = $cash_data->cash;

        $cash_history = CashHistory::leftJoin("clerks", "cash_history.clerk_id", "=", "clerks.id")
            ->select(
                "cash_history.id", "cash_history.amount",
                "cash_history.description", "cash_history.transaction_id",
                "cash_history.datetime", "clerks.name")
            ->orderBy("cash_history.datetime", "desc")
            ->whereBetween("cash_history.datetime", [$start_date->format("Y-m-d"), $shifted_end_date->format("Y-m-d")])
            ->offset( ($page - 1) * $items_per_page)
            ->limit($items_per_page)
            ->get();

        /* Format dates */
        Date::setLocale('id');
        $start_date = $start_date->format("l, j F Y");
        $end_date = $end_date->format("l, j F Y");

        /* Formats date to a human readable form */
        foreach ($cash_history as $record) {
            $date = new Date($record->datetime);
            $record->datetime = $date->format("l, j F Y - h:i");

            /* Human readable formats (like '5 days ago', 'Just now', etc.)*/
            $record->h_datetime = $date->diffForHumans();
        }

        return $this->view->render($response, "inventory/ledger_list.twig",
            [
                "cash_history" => $cash_history, "cash" => $cash_amount,
                "h_start_date" => $start_date, "h_end_date" => $end_date,
                "start_date" => $data["start_date"], "end_date" => $data["end_date"],
                "pagination" => $pagination
            ]
        );
    }
}
