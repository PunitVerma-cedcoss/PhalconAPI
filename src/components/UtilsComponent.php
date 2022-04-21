<?php

namespace Api\Component;

use Phalcon\Di\Injectable;
use Phalcon\Http\Response;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;

class UtilsComponent extends Injectable
{
    /**
     * prepares the projection in monogodb
     *
     * @param [array] $data
     * @return array
     */
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
    /**
     * cheks roles as well as token from header
     *
     * @return mixed
     */
    public function checkTokenAndAcl()
    {
        if ($this->request->hasHeader('Bearer')) {
            $token = $this->request->getHeaders();
            // check and validate token 
            $jwt = new \Api\Component\JwtComponent();
            $resp = $jwt->validateJwtToken($token['Bearer']);
            if ($resp["isValid"] === true) {
                // if token is valid and ok
                // now check for ACL
                $acl = unserialize(file_get_contents('storage/acl.cache'));
                $url = explode("/", $this->request->getQuery()['_url']);
                $controller = isset($url[1]) ?? "/";
                $action = isset($url[2]) ?? "/";
                // send true if allowed
                if ($acl->isAllowed($resp["role"], $controller, $action)) {
                    return true;
                } else {
                    $response = new Response();
                    $response->setStatusCode(401, 'Unauthorized')
                        ->setJsonContent(
                            [
                                'status' => 401,
                                'error' => 'not Authorized'
                            ],
                            JSON_PRETTY_PRINT
                        );
                    if (!$response->isSent())
                        $response->Send();
                    return false;
                }
            } else {
                $response = new Response();
                $response->setStatusCode(403, 'Bad REQUEST')
                    ->setJsonContent(
                        [
                            'status' => 403,
                            'bearer' => $resp["msg"]
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
    /**
     * it will generate the acl.cache file in storage dir
     *
     * @return string
     */
    public function buildAcl()
    {
        $acl = new Memory();
        $aclFile =  'storage/acl.cache';
        $acl->addRole('admin');
        $acl->addRole('guest');

        $acl->addComponent(
            'products',
            [
                'search',
                'get',
            ]
        );

        $acl->addComponent(
            'auth',
            [
                'token',
            ]
        );
        $acl->addComponent(
            'acl',
            [
                'build',
            ]
        );
        $acl->addComponent(
            'order',
            [
                'create',
                'update',
            ]
        );

        $acl->allow('admin', '*', '*');
        $acl->allow('guest', 'auth', '*');

        file_put_contents($aclFile, serialize($acl));

        echo "acl generated";
    }
}
