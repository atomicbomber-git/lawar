<?php
namespace App\Controller;

use App\Model\Clerk;
use PHPassLib\Hash\BCrypt;

class AuthenticationController extends BaseController
{
    public function login ($request, $response)
    {
        return $this->container->view->render($response, "authentication/login.twig");
    }

    public function processLogin ($request, $response)
    {
        $data = $request->getParsedBody();

        /* TODO: Handle errors in case username or password fields are missing */

        /* Retrieve clerk data based on username */
        $clerk = Clerk::select("username", "password")
            ->where("username", $data["username"])
            ->first();

        if ( ! $clerk ) {
            /* TODO: Handle error if clerk with inserted username isn't found */
            $path = $this->container->get("router")->pathFor("login");
            return $response
                ->withStatus(302)
                ->withHeader('Location', $path);
        }

        if ( ! BCrypt::verify($data["password"], $clerk->password) ) {
            /* TODO: Handle error if password is incorrect */
            $path = $this->container->get("router")->pathFor("login");
            return $response
                ->withStatus(302)
                ->withHeader('Location', $path);
        }

        $path = $this->container->get("router")->pathFor("inventory");

        return $response
            ->withStatus(302)
            ->withHeader('Location', $path);
    }

    public function signup()
    {

    }

    public function processSignup()
    {

    }
}
