<?php
namespace App\Middleware;

class ManagerOrAdminOnlyMiddleware
{
    public function __construct($container)
    {
        $this->container = $container;
    }


    public function __invoke ($request, $response, $next)
    {

        $this->container->logger->addInfo("Manger or Admin!, Privilege " . $_SESSION["user"]->privilege);
        
        if ( $_SESSION["user"]->privilege !== "ADMINISTRATOR" && $_SESSION["user"]->privilege !== "MANAGER" )  {

            /* Redirect to auth error page */
            $path = $this->container->get("router")->pathFor("autherror");
            return $response->withStatus(302)->withHeader("Location", $path);
        }

        $response = $next($request, $response);
        return $response;
    }
}