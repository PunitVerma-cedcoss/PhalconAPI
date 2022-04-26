<?php
(new \Phalcon\Debug())->listen();
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH);

// requiring vendor autoload 🍫
require '../../vendor/autoload.php';

$config = new Config([]);
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$container->set(
    'profiler',
    function () {
        $profiler = new \Fabfuel\Prophiler\Profiler();
        return $profiler;
    }
);


$application = new Application($container);


//setting up mongo db
$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client("mongodb://mongo5", array("username" => "root", "password" => "password123"));
        return $mongo->frontend2;
    },
    true
);

$response = $application->handle(
    $_SERVER["REQUEST_URI"]
);

$response->send();

// try {
//     // Handle the request
//     $response = $application->handle(
//         $_SERVER["REQUEST_URI"]
//     );

//     $response->send();
// } catch (\Exception $e) {
//     echo 'Exception: ', $e->getMessage();
// }
