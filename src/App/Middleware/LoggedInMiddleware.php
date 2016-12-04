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

        $this->container->logger->addInfo("Passed through LoggedInMiddleware!");

        /* Check if the user has been logged in */
        if ( ! $_SESSION["user"] ) {
            $path = $this->container->get("router")->pathFor("login");

            /* Indicates that an error has occured */
            $_SESSION["message"]["error"]["must_log_in"] = true;

            return $response->withStatus(302)->withHeader("Location", $path);
        }

        $response = $next($request, $response);
        return $response;
    }
}