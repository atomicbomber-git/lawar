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

            /* Error message to be passed to the login page processor */
            $_SESSION["message"]["error"]["must_log_in"] = "Anda harus log in terlebih dahulu";

            return $response->withStatus(302)->withHeader("Location", $path);
        }

        $response = $next($request, $response);
        return $response;
    }
}