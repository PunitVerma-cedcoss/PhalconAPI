<?php

namespace Api\Component;

use Phalcon\Di\Injectable;
use Phalcon\Http\Response;

class UtilsComponent extends Injectable
{
    public function prepareProjection($data)
    {
        $final = [];
        $data = explode(",", $data);
        if (count($data) == 0)
            return false;
        foreach ($data as $projection) {
            $t = explode(":", $projection);
            if (count($t) <= 1)
                return false;
            if (!is_numeric($t[1]))
                return false;
            if ((trim($t[1]) != 1 && trim($t[1]) != 0))
                return false;
            if (trim($t[1]) == 1) {
                $final[trim($t[0])] = TRUE;
            } else {
                $final[trim($t[0])] = FALSE;
            }
        }
        // echo "<pre>";
        // print_r($final);
        // die;
        return $final;
    }
    public function checkToken()
    {
        if ($this->request->hasHeader('Bearer')) {
            $token = $this->request->getHeaders();
            // check and validate token 
            $jwt = new \Api\Component\JwtComponent();
            $resp = $jwt->validateJwtToken($token['Bearer']);
            if ($resp === true) {
                // if token is valid and ok
                return true;
            } else {
                $response = new Response();
                $response->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        [
                            'status' => 200,
                            'bearer' => $resp
                        ],
                        JSON_PRETTY_PRINT
                    );
                if (!$response->isSent())
                    $response->Send();
                return false;
            }
        } else {
            $response = new Response();
            $response->setStatusCode(200, 'OK')
                ->setJsonContent(
                    [
                        'status' => 200,
                        'bearer' => 'please provie bearear token at header'
                    ],
                    JSON_PRETTY_PRINT
                );
            if (!$response->isSent())
                $response->Send();
            return false;
            // return 'please provie bearear token at header';
        }
    }
}
