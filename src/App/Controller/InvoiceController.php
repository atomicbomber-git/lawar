<?php
namespace App\Controller;

use App\Model\TransactionItem;
use App\Model\Item;

class InvoiceController extends BaseController
{
    public function cart($request, $response)
    {

        $transaction_item = new TransactionItem([
            "name" => "Test",
            "description" => "Test",
            "size" => "Test",
            "type" => "Test",
            "quantity" => 4
        ]);

        $transaction_item->save();

        return $this->view->render($response, "invoice/cart.twig");
    }

    public function addTransactionItem ($request, $response, $args)
    {
        $item = Item::find($args["item_id"]);
        return $this->view->render($response, "invoice/add_transaction_item.twig", ["item" => $item]);
    }

    public function processAddTransactionItem ($reques, $response, $args)
    {

        $item = Item::find();

    }
}
