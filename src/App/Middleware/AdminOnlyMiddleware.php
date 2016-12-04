<?php
namespace App\Middleware;

class AdminOnlyMiddleware
{
    public function __construct($container)
    {
        $this->container = $container;
    }


    public function __invoke ($request, $response, $next)
    {
        
        if ( ! $_SESSION["user"]->privilege === "ADMINISTRATOR" ) {
            die("You must be an admin in order to access this page");
        }

        $response = $next($request, $response);
        return $response;
    }
}