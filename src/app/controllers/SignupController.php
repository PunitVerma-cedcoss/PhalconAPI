<?php

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SignupController extends Controller
{

    public function IndexAction()
    {
        // if got post
        if ($this->request->isPost()) {
            $fromData = $this->request->getPost();
            print_r($fromData);
            // saving to db
            $this->mongo->users->insertOne(
                [
                    "name" => $fromData["name"],
                    "email" => $fromData["email"],
                    "password" => $fromData["password"],
                    "role" => $fromData["role"],
                ]
            );

            $now = new DateTime();
            $key = "example_key";
            $payload = array(
                "iss" => "http://example.org",
                "aud" => "http://example.com",
                "role" => $fromData["role"],
                "iat" => $now->getTimestamp(),
                "nbf" => $now->modify('-1 minute')->getTimestamp(),
                "exp" => $now->modify('+1 hour')->getTimestamp()
            );
            $jwt = JWT::encode($payload, $key, 'HS512');
            $this->view->data = $jwt;
        }
    }
    public function registerAction()
    {
        $user = new Users();

        $user->assign(
            $this->request->getPost(),
            [
                'name',
                'email'
            ]
        );

        $success = $user->save();

        $this->view->success = $success;

        if ($success) {
            $this->view->message = "Register succesfully";
        } else {
            $this->view->message = "Not Register succesfully due to following reason: <br>" . implode("<br>", $user->getMessages());
        }
    }
}
