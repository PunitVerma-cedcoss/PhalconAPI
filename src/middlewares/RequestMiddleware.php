<?php

namespace Api\Middleware;

use Phalcon\Events\Event;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class RequestMiddleware implements MiddlewareInterface
{
    public function beforeExecuteRoute(
        Event $event,
        Micro $application
    ) {
        $util = new \Api\Component\UtilsComponent();
        if ($application->request->getQuery()['_url'] != '/auth/token' && $application->request->getQuery()['_url'] != '/') {
            // 🏁
            $resp = $util->checkTokenAndAcl();
            // 🏁
            if (!$resp)
                die;
        }
    }
    public function call(Micro $application)
    {
        return true;
    }
}
