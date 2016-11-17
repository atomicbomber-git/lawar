<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function __construct($container)
    {
        $this->container = $container;
    }


    public function __invoke ($request, $response, $next)
    {
        /* Check if the user has been logged in */
        if ( ! $_SESSION["is_logged_in"] ) {
            $path = $this->container->get("router")->pathFor("login");

            /* Indicates that an error has occured */
            $_SESSION["message"]["error"]["must_log_in"] = true;

            return $response->withStatus(302)->withHeader("Location", $path);
        }

        $response = $next($request, $response);
        return $response;
    }
}