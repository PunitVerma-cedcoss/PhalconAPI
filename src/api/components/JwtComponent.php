<?php

namespace Api\Component;

use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtComponent
{
    public function getJwtToken($role = "guest")
    {
        $now = new DateTime();
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "role" => $role,
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
            return ['isValid' => true, 'role' => $decoded->role];
        } catch (\Exception $e) {
            return ['isValid' => false, 'msg' => $e->getMessage()];
        }
    }
}
