<?php
namespace App\Controller;

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

        return [
            "total_items" => $total_items,
            "current_page" => $current_page,
            "next_page" => $next_page,
            "prev_page" => $prev_page,
            "item_begin" => $offset + 1,
            "item_end" => $offset + $items_per_page
        ];
    }
}
