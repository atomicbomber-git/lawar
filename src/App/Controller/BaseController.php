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
}