<?php
namespace App\Controller;

class BaseController
{
    public function __construct ($container)
    {
        $this->container = $container;
    }
}