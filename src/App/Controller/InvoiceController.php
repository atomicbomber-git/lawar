<?php
namespace App\Controller;

use App\Model\TransactionItem;
use App\Model\Transaction;
use App\Model\Item;
use App\Model\CashHistory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Respect\Validation\Validator as V;
use Jenssegers\Date\Date;

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
                    "transaction_items.stock_event AS stock_event",
                    "items.stock_warehouse AS orig_stock_warehouse",
                    "items.stock_store AS orig_stock_store",
                    "items.stock_event AS orig_stock_event"
                )
            ->having("transaction_id", "=", $_SESSION["cart_id"])
            ->get();

        /* Calculate total sum (sum of price * quantity) */
        $sum = TransactionItem::where("transaction_id", $_SESSION["cart_id"])
            ->sum(Capsule::raw("price * (stock_warehouse + stock_store + stock_event)"));

        /* Check if there exists transaction items which quantities are more than the currently available stock */
        $count_error = TransactionItem::leftJoin("items", "transaction_items.item_id", "=", "items.id")
            ->select(Capsule::raw("
                items.stock_store - transaction_items.stock_store AS diff_stock_store,
                items.stock_warehouse - transaction_items.stock_warehouse AS diff_stock_warehouse,
                items.stock_event - transaction_items.stock_event AS diff_stock_event
            "))
            ->where("transaction_items.transaction_id", "=", $_SESSION["cart_id"])
            ->havingRaw("(diff_stock_store < 0) OR (diff_stock_warehouse < 0) OR (diff_stock_event < 0)")
            ->first();

        /* Check if there's any transaction item which quantity is completely zero */
        $quantity_error = TransactionItem::where("transaction_items.stock_store", 0)
            ->where("transaction_items.stock_warehouse", 0)
            ->where("transaction_items.stock_event", 0)
            ->where("transaction_items.transaction_id", "=", $_SESSION["cart_id"])
            ->first();

        $is_error = false;
        if ( $count_error ) { $is_error = true; }
        if ( $quantity_error ) { $is_error = true; }

        return $this->view->render(
            $response,
            "invoice/cart.twig",
            ["cart" => $cart, "sum" => $sum, "message" => $message, "persisted" => $persisted, "is_error" => $is_error]
        );
    }

    public function cartFinish($request, $response) {
        $data = $request->getParsedBody();

        /* ---Check if cart is empty---*/
        $transaction_items = TransactionItem::where("transaction_id", $_SESSION["cart_id"])
            ->first();

        if ( ! $transaction_items ) {
            $_SESSION["message"]["error"]["cart_empty"] = "Keranjang belanjaan masih kosong";
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cart"));
        }

        /* ---Validate data--- */
        $has_error = false;

        /* Calculate total sum (sum of price * quantity) */
        $sum = TransactionItem::where("transaction_id", $_SESSION["cart_id"])
            ->sum(Capsule::raw("price * (stock_warehouse + stock_store + stock_event)"));

        /* Validate the amount_paid field */
        if ( ! V::numeric()->min($sum)->validate($data["amount_paid"]) ) {
            $has_error = true;
            $formatted_sum = number_format($sum, 2, ",", ".");
            $_SESSION["message"]["form_error"]["amount_paid"] = "Jumlah uang yang dibayar tidak boleh kurang dari Rp. $formatted_sum.";
        }

        /* Check if there exists transaction items which quantities are more than the currently available stock */
        $count_error = TransactionItem::leftJoin("items", "transaction_items.item_id", "=", "items.id")
            ->select(Capsule::raw("
                items.stock_store - transaction_items.stock_store AS diff_stock_store,
                items.stock_warehouse - transaction_items.stock_warehouse AS diff_stock_warehouse,
                items.stock_event - transaction_items.stock_event AS diff_stock_event
            "))
            ->where("transaction_items.transaction_id", "=", $_SESSION["cart_id"])
            ->havingRaw("(diff_stock_store < 0) OR (diff_stock_warehouse < 0) OR (diff_stock_event < 0)")
            ->first();

        if ( $count_error ) { $has_error = true; }

        /* Check if there's any transaction item which quantity is completely zero */
        $quantity_error = TransactionItem::where("transaction_items.stock_store", 0)
            ->where("transaction_items.stock_warehouse", 0)
            ->where("transaction_items.stock_event", 0)
            ->where("transaction_items.transaction_id", "=", $_SESSION["cart_id"])
            ->first();

        if ( $quantity_error ) { $has_error = true; }

        if ($has_error) {
            /* Persist previous form data */
            $_SESSION["persisted"]["amount_paid"] = $data["amount_paid"];

            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cart"));
        }

        /* ---Substract all stock by the amount of purchased items--- */
        /* Retrieve purchased items */
        $transaction_items = TransactionItem::select("item_id", "stock_store", "stock_warehouse", "stock_event")
            ->where("transaction_id", $_SESSION["cart_id"])
            ->get();

        foreach ($transaction_items as $transaction_item) {
            /* Issue a query to substract stock items */
            $item = Item::find( $transaction_item->item_id );
            $item->decrement("stock_store", $transaction_item->stock_store );
            $item->decrement("stock_warehouse", $transaction_item->stock_warehouse );
            $item->decrement("stock_event", $transaction_item->stock_event );
        }

        /* Mark cart / transaction invoice as finished, so the next time the user log ins, she'll get a brand new empty cart */
        $transaction = Transaction::find($_SESSION["cart_id"]);
        $transaction->datetime = date("Y-m-d H:i:s");
        $transaction->is_finished = true;
        $transaction->save();

        /* Save cash history */
        $cash_history = new CashHistory([
            "amount" => $sum,
            "description" => "Transaksi jual beli.",
            "clerk_id" => $_SESSION["user"]->id,
            "transaction_id" => $transaction->id,
            "datetime" => date("Y-m-d H:i:s")
        ]);
        $cash_history->save();

        /* Create a fresh new cart */
        $cart = new Transaction([
            "customer_name" => "",
            "customer_phone" => "",
            "datetime" => date("Y-m-d H-i-s"),
            "clerk_id" => $_SESSION["user"]->id,
            "is_finished" => 0
        ]);

        $cart->save();

        /* Save cart id into session so the site knows which cart we're using currently */
        $_SESSION["cart_id"] = $cart->id;

        /* Redirect to cart_finished page, to prevent repeated finishing action when the user refreshed the page */
        $_SESSION["change"] = $data["amount_paid"] - $sum;
        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("cart-finished"));
    }

    public function cartFinished($request, $response, $args) {

        $change = null;
        if ( isset($_SESSION["change"]) ) {
            $change = $_SESSION["change"];
            unset($_SESSION["change"]);
        }

        return $this->view->render($response, "invoice/transaction_finished.twig", ["change" => $change]);
    }

    public function addTransactionItem ($request, $response, $args)
    {
        $return_page = $request->getQueryParam("return_page");

        /* Retrieve messages that were stored in the session */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $item = Item::find($args["item_id"]);
        return $this->view->render(
            $response,
            "invoice/transaction_item_add.twig",
            ["item" => $item, "message" => $message, "return_page" => $return_page]
        );
    }

    public function processAddTransactionItem ($request, $response, $args)
    {

        /* Retrieve POST data */
        $data = $request->getParsedBody();

        /*---Validate user input---*/
        $hasError = false;

        if ( ! V::intVal()->validate($data["stock_store"]) ) {
            $hasError = true;
            $_SESSION["message"]["error"]["stock_store"] = "Nilai wajib berupa bilangan bulat";
        }

        if ( ! V::intVal()->validate($data["stock_warehouse"]) ) {
            $hasError = true;
            $_SESSION["message"]["error"]["stock_warehouse"] = "Nilai wajib berupa bilangan bulat";
        }

        if ( ! V::intVal()->validate($data["stock_event"]) ) {
            $hasError = true;
            $_SESSION["message"]["error"]["stock_event"] = "Nilai wajib berupa bilangan bulat";
        }

        if ( ! V::min(1)->validate( $data["stock_store"] + $data["stock_warehouse"] + $data["stock_event"] ) ) {
            $hasError = true;
            $_SESSION["message"]["error"]["min_stock"] = "Jumlah minimal pembelian adalah satu (1) item";
        }

        if ($hasError) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("invoice-item-add", ["item_id" => $args["item_id"]]));
        }

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
                "stock_warehouse"=> $data["stock_warehouse"],
                "stock_event"=> $data["stock_event"]
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
                "transaction_items.stock_event AS stock_event",
                "items.stock_warehouse AS orig_stock_warehouse",
                "items.stock_store AS orig_stock_store",
                "items.stock_event AS orig_stock_event"
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

    public function transactionList ($request, $response, $args)
    {
        /* Retrieve messages that were stored in the session */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $page = $this->getCurrentPage($request);
        $items_per_page = 2;

        $transactions = Capsule::table(Capsule::raw("transactions AS t LEFT JOIN clerks ON t.clerk_id = clerks.id"))
            ->select(
                "clerks.name",
                "t.id",
                "t.datetime",
                Capsule::raw("(SELECT SUM(ti.price * (ti.stock_store + ti.stock_warehouse + ti.stock_event)) FROM transaction_items AS ti WHERE ti.transaction_id = t.id) AS price_sum")
            )
            ->where("is_finished", 1)
            ->orderBy("t.datetime", "desc")
            ->offset(($page - 1) * $items_per_page)
            ->limit($items_per_page)
            ->get();

        $count = Capsule::table(Capsule::raw("transactions AS t LEFT JOIN clerks ON t.clerk_id = clerks.id"))
            ->where("is_finished", 1)
            ->count();

        $pagination = $this->getPagination($count, $items_per_page, $page);

        return $this->view->render($response, "invoice/transaction_list.twig",
            [
                "transactions" => $transactions, "message" => $message,
                "pagination" => $pagination
            ]);
    }

    public function transactionDetail ($request, $response, $args)
    {
        $transaction = Transaction::leftJoin("clerks", "transactions.clerk_id", "=", "clerks.id")
            ->select("transactions.id", "datetime", "clerks.name")
            ->where("transactions.id", $args["id"])
            ->first();

        /* Format transaction date */
        Date::setLocale('id');
        $date = new Date($transaction->datetime);
        $transaction->datetime = $date->format("j F Y - h:i");
        $transaction->h_datetime = $date->diffForHumans();

        $transaction_items = TransactionItem::where("transaction_id", $args["id"])->get();

        /* Calculate total sum (sum of price * quantity) */
        $sum = TransactionItem::where("transaction_id", $args["id"])
            ->sum(Capsule::raw("price * (stock_warehouse + stock_store + stock_event)"));

        /* Page of transaction list to return to */
        $return_page = $request->getQueryParam("return_page");

        return $this->view->render($response, "invoice/transaction_detail.twig",
            ["transaction" => $transaction, "transaction_items" => $transaction_items, "sum" => $sum,
            "return_page" => $return_page]
        );
    }

    public function cartReturn($request, $response, $args) {
        $transaction = Transaction::leftJoin("clerks", "transactions.clerk_id", "=", "clerks.id")
            ->select("transactions.id", "datetime", "clerks.name")
            ->where("transactions.id", $args["id"])
            ->first();

        /* Format transaction date */
        Date::setLocale('id');
        $date = new Date($transaction->datetime);
        $transaction->datetime = $date->format("j F Y - h:i");
        $transaction->h_datetime = $date->diffForHumans();

        $transaction_items = TransactionItem::where("transaction_id", $args["id"])->get();

        /* Calculate total sum (sum of price * quantity) */
        $sum = TransactionItem::where("transaction_id", $args["id"])
            ->sum(Capsule::raw("price * (stock_warehouse + stock_store + stock_event)"));

        /* Transaction list page to return to */
        $return_page = $request->getQueryParam("return_page");

        return $this->view->render($response, "invoice/transaction_detail.twig",
            ["transaction" => $transaction, "transaction_items" => $transaction_items, "sum" => $sum, "is_return" => true,
            "return_page" => $return_page]
        );
    }

    public function processCartReturn($request, $response, $args) {

        /* Retrieve transaction items and delete them */
        $transaction_items = TransactionItem::where("transaction_id", $args["id"])->get();

        foreach ($transaction_items as $transaction_item) {

            /* Get the Item record stored in the items table */
            $item = Item::find( $transaction_item->item_id );

            /*
                If found, increment the amount of available stock accordingly
                There's always a risk of an item not being found since the administrator may have deleted the item before
                this procedure is ran.
            */
            if ( $item ) {
                $item->increment("stock_store", $transaction_item->stock_store );
                $item->increment("stock_warehouse", $transaction_item->stock_warehouse );
                $item->increment("stock_event", $transaction_item->stock_store );
            }

            $transaction_item->delete();
        }

        $previous_cash_history = CashHistory::where("transaction_id", $args["id"])->first();

        /* Store a cash history record! */
        $cash_history = new CashHistory([
            "amount" => $previous_cash_history->amount * -1,
            "description" => "Retur transaksi",
            "clerk_id" => $_SESSION["user"]->id,
            "datetime" => date("Y-m-d H:i:s")
        ]);

        $cash_history->save();

        $transaction = Transaction::find($args["id"]);
        $transaction->delete();

        $_SESSION["message"]["success"]["return"] = "Retur transaksi sukses dilakukan";

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("invoice-transaction-list"));
    }
}
