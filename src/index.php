<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Http\Response;
use Phalcon\Di\FactoryDefault;

// requiring vendor autoload 🍫
require 'vendor/autoload.php';

//setting container
$container = new FactoryDefault();


$loader = new Loader();
$loader->registerNamespaces(
    [
        'Api\Component' => './components',
    ]
);

$loader->register();

$app = new Micro($container);

//setting up mongo db
$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client("mongodb://mongo5", array("username" => "root", "password" => "password123"));
        return $mongo->test;
    },
    true
);

// intilializing mongo component
$mongo = new \Api\Component\MongoComponent();
$util = new \Api\Component\UtilsComponent();
$jwt = new \Api\Component\JwtComponent();

// home route
$app->get(
    '/',
    function () {
        return '😎 Api is up and running 😎';
    }
);

$app->get(
    '/products/get',
    function () use ($mongo, $util) {
        // 🏁
        $resp = $util->checkToken();
        // 🏁
        if ($resp !== false) {
            $formData = $this->request->getQuery();
            $per_page = isset($formData['per_page']) ? (int) $formData['per_page'] : 0;
            $page = isset($formData['page']) ? (int) $formData['page'] : 0;
            if (isset($formData["project"])) {
                $data = $util->prepareProjection($formData["project"]);
                if ($data) {
                    $response = new Response();
                    $response->setStatusCode(200, 'OK')
                        ->setJsonContent(
                            [
                                'status' => 200,
                                'data' => $mongo->read("products", [], $per_page, is_null($data) ? [] : $data, $page)
                            ],
                            JSON_PRETTY_PRINT
                        );
                    if (!$response->isSent())
                        $response->Send();
                } else {
                    $response = new Response();
                    $response->setStatusCode(403, 'BAD REQUEST')
                        ->setJsonContent(
                            [
                                'status' => 403,
                                'msg' => 'projection data is not correct'
                            ],
                            JSON_PRETTY_PRINT
                        );
                    if (!$response->isSent())
                        $response->Send();
                }
            } else {
                $response = new Response();
                $response->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        [
                            'status' => 200,
                            'data' => $mongo->read("products", [], $per_page, [], $page)
                        ],
                        JSON_PRETTY_PRINT
                    );
                if (!$response->isSent())
                    $response->Send();
            }
        }
    }
);


$app->get(
    '/products/search/{name}',
    function ($name) use ($mongo, $util) {
        // 🏁
        $resp = $util->checkToken();
        // 🏁
        if ($resp !== false) {
            $req = explode(" ", urldecode($name));
            if (count($req) == 2) {
                // send search with variations
                $response = new Response();
                $response->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        [
                            'status' => 200,
                            'data' => $mongo->read(
                                "products",
                                [
                                    '$or' => [
                                        [
                                            'product name' => new \MongoDB\BSON\Regex($req[0]),
                                            'variations.variant.capacity' => new \MongoDB\BSON\Regex($req[1])
                                        ],
                                        [
                                            'product name' => new \MongoDB\BSON\Regex($req[0]),
                                        ],
                                        [
                                            'product name' => new \MongoDB\BSON\Regex($req[1]),
                                        ]
                                    ]
                                ]
                            )
                        ],
                        JSON_PRETTY_PRINT
                    );
                if (!$response->isSent())
                    $response->Send();
            } else {
                //send data with product name
                $response = new Response();
                $response->setStatusCode(200, 'OK')
                    ->setJsonContent(
                        [
                            'status' => 200,
                            'data' => $mongo->read(
                                "products",
                                [
                                    'product name' => new \MongoDB\BSON\Regex($name)
                                ]
                            )
                        ],
                        JSON_PRETTY_PRINT
                    );
                if (!$response->isSent())
                    $response->Send();
            }
        }
    }
);

$app->get(
    '/auth/token',
    function () use ($jwt) {
        $response = new Response();
        $response->setStatusCode(200, 'OK')
            ->setJsonContent(
                [
                    'status' => 200,
                    'bearer' => $jwt->getJwtToken()
                ],
                JSON_PRETTY_PRINT
            );
        if (!$response->isSent())
            $response->Send();
    }
);


$app->handle(
    $_SERVER["REQUEST_URI"]
);
