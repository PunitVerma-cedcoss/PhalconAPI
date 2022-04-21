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
                if (!is_file("storage/acl.cache")) {
                    return true;
                }
                $acl = unserialize(file_get_contents('storage/acl.cache'));
                $url = explode("/", $this->request->getQuery()['_url']);
                $controller = '/';
                $action = '/';
                if (count($url) >= 2) {
                    $controller = $url[1];
                    $action = $url[2];
                }
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
                'get'
            ]
        );

        $acl->allow('admin', '*', '*');
        $acl->allow('guest', 'order', 'create');
        $acl->allow('guest', 'auth', '*');

        file_put_contents($aclFile, serialize($acl));

        echo "acl generated";
    }
    public function prepareOrder($data)
    {
        // if data is empty
        if (count($data) === 0) {
            return false;
        }
        $msg = [];
        if (!isset($data["customer name"])) {
            array_push($msg, "customer name is required");
        }
        if (!isset($data["product id"])) {
            array_push($msg, "Product id is required");
        }
        if (count($msg) > 0) {
            return $msg;
        }

        $mongo = new \Api\Component\MongoComponent();

        // check if id is a valid product id or not
        try {
            $resp = $mongo->read("products", ["_id" => new \MongoDB\BSON\ObjectID($data["product id"])]);
            if (count($resp)) {
                // now push order into db

                $arr = [
                    "customer name" => $data["customer name"],
                    "product id" => $data["product id"],
                    "order date" => new \MongoDB\BSON\UTCDateTime(new \DateTime()),
                    "status" => "paid"
                ];
                //if variant is set
                if (isset($data["variant"])) {
                    $arr["variant"] = $data["variant"];
                }
                $x = $mongo->insert("orders", $arr);
                if ($x) {
                    return true;
                } else {
                    return ["some error occured"];
                }
            } else {
                return ["not product found with this id"];
            }
        } catch (\Exception $e) {
            return ["id is invalid"];
        }

        die;

        return true;
    }
    public function prepareOrderUpdate($data)
    {
        // send reuired errors
        $msg = [];
        if (!isset($data["product id"])) {
            array_push($msg, "product id is required");
        }
        if (!isset($data["data"])) {
            array_push($msg, "data is reuired");
        }
        // send error messages
        if (count($msg) > 0) {
            return $msg;
        }

        $mongo = new \Api\Component\MongoComponent();

        // check if order id is correct
        try {
            $resp = $mongo->read("orders", ["_id" => new \MongoDB\BSON\ObjectID($data["product id"])]);
            if (count($resp)) {
                $mongo->update(
                    "orders",
                    ["_id" => new \MongoDB\BSON\ObjectID($data["product id"])],
                    [
                        '$set' => $data["data"]
                    ]
                );

                return true;
            } else {
                return ["some error occured"];
            }
        } catch (\Exception $e) {
            return ["order id is not valid"];
        }
    }
}
