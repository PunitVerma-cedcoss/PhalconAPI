<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class DriverController extends Controller
{
    public function indexAction()
    {
        $toolbar = new \Fabfuel\Prophiler\Toolbar($this->profiler);
        $toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
        $this->view->toolbar = $toolbar->render();
        $this->assets->addJs("/js/main.js");
    }
    public function productsAction()
    {
        $toolbar = new \Fabfuel\Prophiler\Toolbar($this->profiler);
        $toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
        $this->view->toolbar = $toolbar->render();
        $data = $this->mongo->products->find();
        $this->view->data = $data->toArray();
    }
    public function testAction()
    {
        //if got post
        if ($this->request->isPost()) {
            $this->mongo->products->deleteMany([]);
            foreach (json_decode($this->request->getPost("data"), true) as $o) {
                $this->mongo->products->insertOne(
                    [
                        "product name" => $o["product name"],
                        "category name" => $o["category name"],
                        "product price" => $o["product price"],
                        "product stock" => $o["product stock"],
                        "variations" => $o["variations"],
                        "metas" => $o["metas"],
                    ],
                );
            }
        }
    }
}
