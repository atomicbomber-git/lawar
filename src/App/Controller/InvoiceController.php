<?php
namespace App\Controller;

use App\Model\TransactionItem;
use App\Model\Transaction;
use App\Model\Item;
use Illuminate\Database\Capsule\Manager as Capsule;
use Respect\Validation\Validator as V;

class InvoiceController extends BaseController
{
    public function cartDisplay($request, $response)
    {
        /* Retrieve messages that were stored in the session */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );

        }

        /* Retrieve persisted form field values */
        $persisted = null;
        if ( isset($_SESSION["persisted"] ) ) {
            $persisted = $_SESSION["persisted"];
            unset( $_SESSION["persisted"] );
        }

        $cart = TransactionItem::leftJoin("items", "transaction_items.item_id", "=", "items.id")
            ->select(
                    "transaction_items.id AS id",
                    "transaction_items.item_id AS item_id",
                    "transaction_items.transaction_id AS transaction_id",
                    "transaction_items.name AS name",
                    "transaction_items.type AS type",
                    "transaction_items.size AS size",
                    "transaction_items.price AS price",
                    "transaction_items.stock_warehouse AS stock_warehouse",
                    "transaction_items.stock_store AS stock_store",
                    "items.stock_warehouse AS orig_stock_warehouse",
                    "items.stock_store AS orig_stock_store"
                )
            ->having("transaction_id", "=", $_SESSION["cart_id"])
            ->get();

        /* Calculate total sum (sum of price * quantity) */
        $sum = TransactionItem::where("transaction_id", $_SESSION["cart_id"])
            ->sum(Capsule::raw("price * (stock_warehouse + stock_store)"));

        /* Check if there exists transaction items which quantities are more than the currently available stock */
        $count_error = TransactionItem::leftJoin("items", "transaction_items.item_id", "=", "items.id")
            ->select(Capsule::raw("
                items.stock_store - transaction_items.stock_store AS diff_stock_store,
                items.stock_warehouse - transaction_items.stock_warehouse AS diff_stock_warehouse
            "))
            ->where("transaction_items.transaction_id", "=", $_SESSION["cart_id"])
            ->havingRaw("(diff_stock_store < 0) OR (diff_stock_warehouse < 0)")
            ->first();

        /* There's an error in our cart if there exists transaction items which quantity is larger than what's available in
            stock
        */
        $is_error = false;
        if ( $count_error ) { $is_error = true; } 

        return $this->view->render(
            $response,
            "invoice/cart.twig",
            ["cart" => $cart, "sum" => $sum, "message" => $message, "persisted" => $persisted, "is_error" => $is_error]
        );
    }

    public function cartFinish($request, $response) {
        $data = $request->getParsedBody();

        /* ---Validate data--- */
        $has_error = false;

        /* Calculate total sum (sum of price * quantity) */
        $sum = TransactionItem::where("transaction_id", $_SESSION["cart_id"])
            ->sum(Capsule::raw("price * (stock_warehouse + stock_store)"));

        /* Validate the amount_paid field */
        if ( ! V::numeric()->min($sum)->validate($data["amount_paid"]) ) {
            $has_error = true;
            $formatted_sum = number_format($sum, 2, ",", ".");
            $_SESSION["message"]["form_error"]["amount_paid"] = "Jumlah uang yang dibayar tidak boleh kurang dari Rp. $formatted_sum.";
        }

        if ($has_error) {
            /* Persist previous form data */
            $_SESSION["persisted"]["amount_paid"] = $data["amount_paid"];

            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cart"));
        }


        /* ---Substract all stock by the amount of purchased items--- */

        /* Retrieve purchased items */
        $transaction_items = TransactionItem::select("item_id", "stock_store", "stock_warehouse")
            ->where("transaction_id", $_SESSION["cart_id"])
            ->get();

        foreach ($transaction_items as $transaction_item) {
            /* Issue a query to substract stock items */
            Item::find( $transaction_item->item_id )->decrement("stock_store", $transaction_item->stock_store );
            Item::find( $transaction_item->item_id )->decrement("stock_warehouse", $transaction_item->stock_warehouse );

            /* TODO: Store item transfer history */
        }

        /* Mark cart / transaction invoice as finished, so the next time the user log ins, she'll get a brand new empty cart */
        $transaction = Transaction::find($_SESSION["cart_id"]);
        $transaction->is_finished = true;
        $transaction->save();

        /* Create a fresh new cart */
        $cart = new Transaction([
            "customer_name" => "",
            "customer_phone" => "",
            "datetime" => date("Y-m-d H-i-s"),
            "clerk_id" => $_SESSION["user_id"],
            "is_finished" => 0
        ]);

        $cart->save();

        /* Save cart id into session so the site knows which cart we're using currently */
        $_SESSION["cart_id"] = $cart->id;
        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("inventory"));
    }

    public function addTransactionItem ($request, $response, $args)
    {
        $item = Item::find($args["item_id"]);
        return $this->view->render($response, "invoice/transaction_item_add.twig", ["item" => $item]);
    }

    public function processAddTransactionItem ($request, $response, $args)
    {

        /* Retrieve POST data */
        $data = $request->getParsedBody();

        /* Attempt to retrieve the item from the cart. Will be used to decide whether to update if the item already exist or to add if it isn't. */
        $old_transaction_item = TransactionItem::where("transaction_id", $_SESSION["cart_id"])
            ->where("item_id", $args["item_id"])
            ->first();

        if ( ! $old_transaction_item ) {
            /* If the item isn't already in the cart: Add the item into the cart */

            /* Retrieve the item that's going to be added */
            $item = Item::find($args["item_id"]);

            $new_transaction_item = new TransactionItem([
                "name"=> $item->name,
                "item_id" => $item->id,
                "transaction_id" => $_SESSION["cart_id"],
                "description"=> $item->description,
                "size"=> $item->size,
                "price"=> $item->price,
                "type"=> $item->type[name],
                "stock_store"=> $data["stock_store"],
                "stock_warehouse"=> $data["stock_warehouse"]
            ]);

            $new_transaction_item->save();
        }
        else {
            /* Item is already inside the cart: Update the quantities */
            $old_transaction_item->stock_store += $data["stock_store"];
            $old_transaction_item->stock_warehouse += $data["stock_warehouse"];
            $old_transaction_item->save();
        }

        /* Store messages */
        $_SESSION["message"]["success"]["add"] = "Berhasil menambahkan item!";

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cart") . "#message");
    }

    public function deleteTransactionItem ($request, $response, $args)
    {
        $transaction_item = TransactionItem::find($args["item_id"]);
        $transaction_item->delete();
        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cart"));
    }

    public function editTransactionItem ($request, $response, $args) {

        /* Retrieve messages that were stored in the session */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $transaction_item = TransactionItem::leftJoin("items", "transaction_items.item_id", "=", "items.id")
            ->select(
                "transaction_items.id AS id",
                "transaction_items.item_id AS item_id",
                "transaction_items.transaction_id AS transaction_id",
                "transaction_items.name AS name",
                "transaction_items.type AS type",
                "transaction_items.size AS size",
                "transaction_items.price AS price",
                "transaction_items.stock_warehouse AS stock_warehouse",
                "transaction_items.stock_store AS stock_store",
                "items.stock_warehouse AS orig_stock_warehouse",
                "items.stock_store AS orig_stock_store"
            )
            ->where("transaction_items.id", $args["item_id"])
            ->first();

        return $this->view->render($response, "invoice/transaction_item_edit.twig", ["item" => $transaction_item, "message" => $message]);
    }

    public function processEditTransactionItem ($request, $response, $args)
    {
    
        $data = $request->getParsedBody();

        /* Validate */
        $hasError = false;
        if ( ! V::intVal()->min(0)->validate($data["stock_store"]) ) {
            $_SESSION["message"]["error"]["stock_store"] = "Error: Nilai wajib positif";
            $hasError = true;
        }

        if ( ! V::intVal()->min(0)->validate($data["stock_warehouse"]) ) {
            $_SESSION["message"]["error"]["stock_warehouse"] = "Error: Nilai wajib positif";
            $hasError = true;
        }

        if ( $hasError ) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("invoice-item-edit", ["item_id" => $args["item_id"]]));
        }

        $transaction_item = TransactionItem::find($args["item_id"]);
        $transaction_item->update( $request->getParsedBody() );

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cart"));
    }
}
