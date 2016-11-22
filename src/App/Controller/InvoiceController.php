<?php
namespace App\Controller;

use App\Model\TransactionItem;
use App\Model\Transaction;
use App\Model\Item;
use Illuminate\Database\Capsule\Manager as Capsule;

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

        $cart = TransactionItem::leftJoin("items", "transaction_items.item_id", "=", "items.id")
            ->select(
                    "transaction_items.id AS id",
                    "transaction_items.item_id AS item_id",
                    "transaction_items.transaction_id AS transaction_id",
                    "transaction_items.name AS name",
                    "transaction_items.type AS type",
                    "transaction_items.size AS size",
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
            ->havingRaw("diff_stock_store < 0 OR diff_stock_warehouse")
            ->first();

        /* There's an error in our cart if there exists transaction items which quantity is larger than what's available in
            stock
        */
        $is_error = false;
        if ( $count_error ) { $is_error = true; } 

        return $this->view->render(
            $response,
            "invoice/cart.twig",
            ["cart" => $cart, "sum" => $sum, "message" => $message, "is_error" => $is_error]
        );
    }

    public function cartFinish() {
        $transaction = Transaction::find($_SESSION["cart_id"]);
        $transaction->is_finished = true;
        $transaction->save();
    }

    public function addTransactionItem ($request, $response, $args)
    {
        $item = Item::find($args["item_id"]);
        return $this->view->render($response, "invoice/add_transaction_item.twig", ["item" => $item]);
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
}
