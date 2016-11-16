<?php
namespace App\Controller;

use App\Model\Clerk;
use PHPassLib\Hash\BCrypt;
use Illuminate\Database\Capsule\Manager as Capsule;

class AuthenticationController extends BaseController
{
    public function login ($request, $response)
    {
        /* Retrieve error messages from session if they exist */
        $message = null;
        if ( isset($_SESSION["message"]["error"]) ) {
            $message["error"] = $_SESSION["message"]["error"];
            unset($_SESSION["message"]["error"]);
        }

        return $this->container->view->render($response, "authentication/login.twig", ["message" => $message]);
    }

    public function processLogin ($request, $response)
    {
        $data = $request->getParsedBody();

        /* Check if username and password field is not empty */
        $hasError = false;
        if ( ! $data["username"] ) {
            $hasError = true;
            $_SESSION["message"]["error"]["username"] = "Username wajib diisi";
        }

        if ( ! $data["password"] ) {
            $hasError = true;
            $_SESSION["message"]["error"]["password"] = "Password wajib diisi";
        }

        if ( $hasError ) {
            $path = $this->container->get("router")->pathFor("login");
            return $response
                ->withStatus(302)
                ->withHeader('Location', $path);
        }

        /* Retrieve clerk data based on username */
        $clerk = Clerk::select("username", "password")
            ->whereRaw("BINARY username = '$data[username]'")
            ->first();

        if ( ! $clerk ) {
            $_SESSION["message"]["error"]["incorrect"] = "Username atau password keliru";
            $path = $this->container->get("router")->pathFor("login");
            return $response
                ->withStatus(302)
                ->withHeader('Location', $path);
        }

        if ( ! BCrypt::verify($data["password"], $clerk->password) ) {
            $_SESSION["message"]["error"]["incorrect"] = "Username atau password keliru";
            $path = $this->container->get("router")->pathFor("login");
            return $response
                ->withStatus(302)
                ->withHeader('Location', $path);
        }

        /* Mark the user as logged in */
        $_SESSION["is_logged_in"] = true;

        $path = $this->container->get("router")->pathFor("inventory");

        return $response
            ->withStatus(302)
            ->withHeader('Location', $path);
    }

    public function logout ($request, $response)
    {
        $_SESSION["is_logged_in"] = false;
        $path = $this->container->get("router")->pathFor("login");
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
