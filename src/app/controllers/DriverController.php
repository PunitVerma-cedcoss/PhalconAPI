<?php

use Phalcon\Mvc\Controller;


class DriverController extends Controller
{
    public function indexAction()
    {
        $this->assets->addJs("/js/main.js");
    }
}
