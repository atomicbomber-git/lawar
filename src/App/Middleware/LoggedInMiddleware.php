<?php
namespace App\Middleware;

class LoggedInMiddleware
{
    public function __construct($container)
    {
        $this->container = $container;
    }


    public function __invoke ($request, $response, $next)
    {

        /* Check if the user has been logged in */
        if ( ! $_SESSION["user"] ) {
            $path = $this->container->get("router")->pathFor("login");

            /* Indicates that an error has occured */
            $_SESSION["message"]["error"]["must_log_in"] = true;

            return $response->withStatus(302)->withHeader("Location", $path);
        }

        /* Add Twig global */
        $this->container->view
            ->getEnvironment()->addGlobal("user", $_SESSION["user"]);

        $response = $next($request, $response);
        return $response;
    }
}