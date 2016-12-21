<?php
namespace App\Controller;

use App\Model\Clerk;
use App\Model\Transaction;
use PHPassLib\Hash\BCrypt;
use Illuminate\Database\Capsule\Manager as Capsule;
use Respect\Validation\Validator as V;

class UserController extends BaseController
{
    public function manage ($request, $response)
    {
        /* Get previously stored error messages */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $users = Clerk::select("name", "privilege", "id")->get();
        return $this->view->render($response, "user/user_manage.twig", ["users" => $users, "message" => $message]);
    }

    public function signup ($request, $response)
    {
        $message = null;
        if ( isset($_SESSION["message"]["error"] ) ) {
            $message["error"] = $_SESSION["message"]["error"];
            unset( $_SESSION["message"]["error"] );
        }

        return $this->view->render($response, "user/user_signup.twig", ["message" => $message]);
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

        if ( ! V::notEmpty()->validate( $data["name"] ) ) {
            $hasError = true;
            $_SESSION["message"]["error"]["name"] = "Nama asli wajib diisi";
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
                ->withHeader('Location', $this->router->pathFor("user-signup") );
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
                ->withHeader('Location', $this->router->pathFor("user-signup") );
        }

        /* Check if password and password_retry fields contain the same value */
        if ( $data["password"] !== $data["password_retry"] ) {
            /* Error: Password and password retry must have the same value */
            $_SESSION["message"]["error"]["password"] = "Kata sandi dan pengulangannya wajib sama.";
            $_SESSION["message"]["error"]["password_retry"] = "Kata sandi dan pengulangannya wajib sama.";
            return $response
                ->withStatus(302)
                ->withHeader('Location', $this->router->pathFor("user-signup") );
        }

        $clerk = new Clerk;
        $clerk->username = $data["username"];
        $clerk->name = $data["name"];
        $clerk->phone = $data["phone"];
        $clerk->privilege = $data["privilege"];
        $clerk->password = BCrypt::hash($data["password"]);
        $clerk->save();

        $_SESSION["message"]["success"]["register"] = true;

        return $response
            ->withStatus(302)
            ->withHeader('Location', $this->router->pathFor("user-manage"));
    }

    function edit ($request, $response, $args)
    {
        /* Get previously stored error messages */
        $message = null;
        if ( isset($_SESSION["message"] ) ) {
            $message = $_SESSION["message"];
            unset( $_SESSION["message"] );
        }

        $clerk = Clerk::find($args["id"]);
        return $this->view->render($response, "user/user_edit.twig", ["clerk" => $clerk, "message" => $message]);
    }

    function processEdit ($request, $response, $args)
    {
        /* Retrieve input data */
        $data = $request->getParsedBody();

        /* Validate data */
        $hasError = false;

        if ( ! V::notEmpty()->validate($data["username"]) ) {
            $_SESSION["message"]["error"]["username"] = "Kolom tidak boleh kosong";
            $hasError = true;
        }

        if ( ! V::notEmpty()->validate($data["name"]) ) {
            $_SESSION["message"]["error"]["name"] = "Kolom tidak boleh kosong";
            $hasError = true;
        }

        if ( ! V::notEmpty()->validate($data["phone"]) ) {
            $_SESSION["message"]["error"]["phone"] = "Kolom tidak boleh kosong";
            $hasError = true;
        }

        if ( ! V::notEmpty()->validate($data["privilege"]) ) {
            $_SESSION["message"]["error"]["privilege"] = "Kolom tidak boleh kosong";
            $hasError = true;
        }

        if ($hasError) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("user-edit", ["id" => $args["id"]]));
        }

        /* Finally update user data */
        $_SESSION["message"]["success"]["userdata_changed"] = "Data akun berhasil diganti";
        $user = Clerk::find($args["id"]);
        $user->username = $data["username"];
        $user->name = $data["name"];
        $user->phone = $data["phone"];
        $user->privilege = $data["privilege"];
        $user->save();

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("user-edit", ["id" => $args["id"]]));
    }

    function processEditPassword ($request, $response, $args)
    {
        /* Retrieve input data */
        $data = $request->getParsedBody();

        /* Validate data */
        $hasError = false;

        if ( ! V::notEmpty()->validate($data["password"]) ) {
            $_SESSION["message"]["error"]["password"] = "Kolom tidak boleh kosong";
            $hasError = true;
        }

        if ( ! V::notEmpty()->validate($data["password_retry"]) ) {
            $_SESSION["message"]["error"]["password_retry"] = "Kolom tidak boleh kosong";
            $hasError = true;
        }

        if ($hasError) {
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("user-edit", ["id" => $args["id"]]) . "#edit-password");
        }

        /* Check if password equals password_retry */
        if ( ! V::equals($data["password"])->validate($data["password_retry"]) ) {
            $_SESSION["message"]["error"]["not_match"] = "Nilai kata sandi dan ulangan kata sandi berbeda";
            return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("user-edit", ["id" => $args["id"]]) . "#edit-password");
        }

        /* Finally alter the relevant user's password */
        $_SESSION["message"]["success"]["password_changed"] = "Kata sandi berhasil diganti";
        $user = Clerk::find($args["id"]);
        $user->password = BCrypt::hash($data["password"]);
        $user->save();

        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("user-edit", ["id" => $args["id"]]) . "#edit-password");
    }

    function delete ($request, $response, $args)
    {
        $user = Clerk::find($args["id"]);
        return $this->view->render($response, "user/user_delete.twig", ["user" => $user]);
    }

    function processDelete ($request, $response, $args)
    {
        $user = Clerk::find($args["id"]);
        $user->delete();

        $_SESSION["message"]["success"]["user_deleted"] = "Penghapusan pengguna '$user->username' berhasil dilakukan";
        return $response->withStatus(302)->withHeader("Location", $this->router->pathFor("user-manage"));
    }
}
