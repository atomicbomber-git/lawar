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
            case "view":
                return $this->container->view;
            case "router":
                return $this->container->get("router");
            default:
                return null;
        }        
    }
}