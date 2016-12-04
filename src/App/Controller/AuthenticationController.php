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

        $path = $this->router->pathFor("inventory");

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

    public function signup ($request, $response)
    {
        $message = null;
        if ( isset($_SESSION["message"]["error"] ) ) {
            $message["error"] = $_SESSION["message"]["error"];
            unset( $_SESSION["message"]["error"] );
        }

        return $this->view->render($response, "authentication/signup.twig", ["message" => $message]);
    }

    public function processSignup ($request, $response)
    {   
        $data = $request->getParsedBody();

        /* Don't allow any of the signup form fields to be empty */
        $hasError = false;
        if ( ! $data["username"] ) {
            $hasError = true;
            $_SESSION["message"]["error"]["username"] = "Nama pengguna wajib diisi";
        }

        if ( ! $data["phone"] ) {
            $hasError = true;
            $_SESSION["message"]["error"]["phone"] = "Nomor telepon wajib diisi";
        }

        if ( ! $data["password"] ) {
            $hasError = true;
            $_SESSION["message"]["error"]["password"] = "Kata sandi wajib diisi";
        }

        if ( ! $data["password_retry"] ) {
            $hasError = true;
            $_SESSION["message"]["error"]["password_retry"] = "Ulangan kata wajib diisi";
        }

        if ( $hasError ) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', $this->router->pathFor("signup") );
        }

        /* Check there's a user with the same username */
        $clerk = Clerk::select("username", "password")
            ->whereRaw("BINARY username = '$data[username]'")
            ->first();

        if ($clerk) {
            /* Error: Username already exists */
            $_SESSION["message"]["error"]["user_exists"] = "Sudah ada nama pengguna yang sama: $data[username]";
            return $response
                ->withStatus(302)
                ->withHeader('Location', $this->router->pathFor("signup") );
        }

        /* Check if password and password_retry fields contain the same value */
        if ( $data["password"] !== $data["password_retry"] ) {
            /* Error: Password and password retry must have the same value */
            $_SESSION["message"]["error"]["password"] = "Kata sandi dan pengulangannya wajib sama.";
            $_SESSION["message"]["error"]["password_retry"] = "Kata sandi dan pengulangannya wajib sama.";
            return $response
                ->withStatus(302)
                ->withHeader('Location', $this->router->pathFor("signup") );
        }

        $clerk = new Clerk;
        $clerk->username = $data["username"];
        $clerk->phone = $data["phone"];
        $clerk->password = BCrypt::hash($data["password"]);
        $clerk->save();

        $_SESSION["message"]["success"]["register"] = true;

        return $response
            ->withStatus(302)
            ->withHeader('Location', $this->router->pathFor("login"));
    }
}
