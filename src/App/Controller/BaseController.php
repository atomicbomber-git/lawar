<?php
namespace App\Controller;
use Respect\Validation\Validator as V;

class BaseController
{
    public function __construct ($container)
    {
        $this->container = $container;
    }

    public function __get($value) {
        switch ($value) {
            case "router":
                return $this->container->get("router");
            default:
                return $this->container->$value;
        }
    }

    public function getCurrentPage($request)
    {
        /* Get page from the query string */
        $page = $request->getQueryParam("page");

        if ( ! V::intVal()->min(1)->validate($page) ) {
            /* Invalid page parameter */
            $page = 1;
        }

        return $page;
    }

    public function getPagination ($total_items, $items_per_page, $current_page)
    {

        $offset = ($current_page - 1) * $items_per_page;
        /* Next page is only available if there are enough items to display */
        if ($total_items - $offset > $items_per_page) {
            $next_page = $current_page + 1;
        }
        else {
            $next_page = null;
        }

        /* No previous page for the first page */
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
        }
        else {
            $prev_page = null;
        }

        /* A page has items if offset is equal or greater than the limit, otherwise it's empty */
        if ($offset < $total_items) {
            $has_items = true;
        }
        else {
            $has_items = false;
        }

        $item_end = $offset + $items_per_page;

        if ($item_end > $total_items) {
            /* Prevent item_end from exceeding the amount of items */
            $item_end = $total_items;
        }

        return [
            "has_items" => $has_items,
            "total_items" => $total_items,
            "current_page" => $current_page,
            "next_page" => $next_page,
            "prev_page" => $prev_page,
            "item_begin" => $offset + 1,
            "item_end" => $item_end
        ];
    }
}
