<?php

namespace Api\Component;

use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtComponent
{
    public function getJwtToken()
    {
        $now = new DateTime();
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => $now->getTimestamp(),
            "nbf" => $now->modify('-1 minute')->getTimestamp(),
            "exp" => $now->modify('+1 hour')->getTimestamp()
        );
        $jwt = JWT::encode($payload, $key, 'HS512');
        return $jwt;
    }
    public function validateJwtToken($token)
    {
        $now = new DateTime();
        $key = "example_key";
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS512'));
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
