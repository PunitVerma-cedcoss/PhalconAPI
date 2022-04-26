<?php

namespace Api\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;


class ProductsMiddleware
{
    public function afterUpdate(
        Event $event,
        Micro $application
    ) {
        $mongo = new \Api\Component\MongoComponent();
        $data = $mongo->read("hooks", [
            "event" => "Product.create"
        ], 0, [
            "url" => 1,
            "_id" => 0
        ]);
        $client = new Client();
        foreach ($data as $url) {
            try {
                $response = $client->request(
                    "POST",
                    $url->url,
                    [
                        "form_params" => [
                            'data' => json_encode($mongo->read("products", []))
                        ]
                    ]
                );
                $response = json_decode($response->getBody()->getContents(), true);
            } catch (ClientException $e) {
                // $s = explode("message", $e->getMessage())[1];
                // print_r(substr($s, 3, strlen($s) - 8));
                echo $e->getMessage();
            }
        }
    }
    public function afterCreate(
        Event $event,
        Micro $application
    ) {
        $mongo = new \Api\Component\MongoComponent();
        $data = $mongo->read("hooks", [
            "event" => "Product.create"
        ], 0, [
            "url" => 1,
            "_id" => 0
        ]);
        $client = new Client();
        foreach ($data as $url) {
            try {
                $response = $client->request(
                    "POST",
                    $url->url,
                    [
                        "form_params" => [
                            'data' => json_encode($mongo->read("products", []))
                        ]
                    ]
                );
                $response = json_decode($response->getBody()->getContents(), true);
            } catch (ClientException $e) {
                // $s = explode("message", $e->getMessage())[1];
                // print_r(substr($s, 3, strlen($s) - 8));
                echo $e->getMessage();
            }
        }
    }
}
