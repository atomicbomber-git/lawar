<?php
namespace App\Controller;

class InvoiceController extends BaseController
{
    public function cart($request, $response)
    {
        return $this->view->render($response, "invoice/cart.twig");
    }
}
