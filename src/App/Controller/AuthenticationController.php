<?php
namespace App\Controller;

use App\Model\Clerk;
use App\Model\Transaction;
use PHPassLib\Hash\BCrypt;
use Illuminate\Database\Capsule\Manager as Capsule;

class AuthenticationController extends BaseController
{

    public function login ($request, $response)
    {
        /* Redirect to home if the user has been logged in before */
        if ( $_SESSION["user"] ) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("home"));
        }

        /* Retrieve error messages from session if they exist */
        $message = null;
        if ( isset($_SESSION["message"]) ) {
            $message = $_SESSION["message"];
            unset($_SESSION["message"]);
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
        $clerk = Clerk::whereRaw("BINARY username = '$data[username]'")
            ->first();

        if ( ! $clerk ) {
            $_SESSION["message"]["error"]["incorrect"] = "Username atau password keliru";
            $path = $this->router->pathFor("login");
            return $response
                ->withStatus(302)
                ->withHeader('Location', $path);
        }

        if ( ! BCrypt::verify($data["password"], $clerk->password) ) {
            $_SESSION["message"]["error"]["incorrect"] = "Username atau password keliru";
            $path = $this->router->pathFor("login");
            return $response
                ->withStatus(302)
                ->withHeader('Location', $path);
        }

        /* Attempt to find a cart registered to this clerk that hasn't been finished */
        $cart = Transaction::where("clerk_id", $clerk->id)
            ->where("is_finished", 0)
            ->first();

        if ( ! $cart ) {

            /* Creates a new Transaction object which represents this clerk's current empty shopping cart */
            $cart = new Transaction([
                "customer_name" => "",
                "customer_phone" => "",
                "datetime" => date("Y-m-d H-i-s"),
                "clerk_id" => $clerk->id,
                "is_finished" => 0
            ]);

            $cart->save();
        }

        /* Store the user data in the session. Will be used later to determine if
            the user has been logged in in his current session
         */
        $_SESSION["user"] = $clerk;

        /* Cart id is stored so it can be used later to determine which cart the user currently has */
        $_SESSION["cart_id"] = $cart->id;


        $path = $this->router->pathFor("home");

        return $response
            ->withStatus(302)
            ->withHeader('Location', $path);
    }

    public function logout ($request, $response)
    {
        session_destroy();

        return $response
            ->withStatus(302)
            ->withHeader('Location', $this->router->pathFor("login"));
    }

    public function authError ($request, $response)
    {
        return $this->view->render($response, "general/auth_error.twig");
    }
}
