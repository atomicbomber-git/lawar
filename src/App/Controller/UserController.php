<?php
namespace App\Controller;

use App\Model\Clerk;
use App\Model\Transaction;
use PHPassLib\Hash\BCrypt;
use Illuminate\Database\Capsule\Manager as Capsule;

class UserController extends BaseController
{
    public function manage ($request, $response)
    {

    }

    public function signup ($request, $response)
    {
        $message = null;
        if ( isset($_SESSION["message"]["error"] ) ) {
            $message["error"] = $_SESSION["message"]["error"];
            unset( $_SESSION["message"]["error"] );
        }

        return $this->view->render($response, "user/signup.twig", ["message" => $message]);
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

        if ( ! $data["privilege"] ) {
            $hasError = true;
            $_SESSION["message"]["error"]["privilege"] = "Privilege user wajib diisi";
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
        $clerk->privilege = $data["privilege"];
        $clerk->password = BCrypt::hash($data["password"]);
        $clerk->save();

        $_SESSION["message"]["success"]["register"] = true;

        return $response
            ->withStatus(302)
            ->withHeader('Location', $this->router->pathFor("user-manage"));
    }
}
