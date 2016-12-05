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
        
        if ( $_SESSION["user"]->privilege !== "ADMINISTRATOR" ) {
            /* Redirect to auth error page */
            $path = $this->container->get("router")->pathFor("autherror");
            return $response->withStatus(302)->withHeader("Location", $path);
        }

        $response = $next($request, $response);
        return $response;
    }
}