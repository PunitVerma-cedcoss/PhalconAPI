<?php

use Phalcon\Mvc\Micro;
use Phalcon\Loader;

$loader = new Loader();
// $loader->registerNamespaces(
//     [
//         'Api'
//     ]
// );

$app = new Micro();

$app->get(
    '/test',
    function () {
        return json_encode(['data' => '123']);
    }
);

$app->handle(
    $_SERVER["REQUEST_URI"]
);
