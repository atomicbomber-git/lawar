<?php
namespace App\Controller;

use App\Model\TransactionItem;
use App\Model\Transaction;
use App\Model\Item;
use Illuminate\Database\Capsule\Manager as Capsule;

class InvoiceController extends BaseController
{
    public function cart($request, $response)
    {
        $message = null;
        /* Retrieve messages that were stored in the session */
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $cart = TransactionItem::where("transaction_id", $_SESSION["cart_id"])->get();

        $sum = TransactionItem::where("transaction_id", $_SESSION["cart_id"])
            ->sum(Capsule::raw("price * (stock_warehouse + stock_store)"));

        return $this->view->render($response, "invoice/cart.twig", ["cart" => $cart, "sum" => $sum, "message" => $message]);
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
}
