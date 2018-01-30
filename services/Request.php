<?php

namespace sampleAPI\services;

use sampleAPI\services\Router;
use sampleAPI\services\Response;

class Request
{
    public $postData;

    public $getData;

    public $requestData;

    public $url;

    public function handleRequest ()
    {
        $this->postData = $_POST;
        $this->getData = $_GET;
        $this->requestData = $_REQUEST;

        $data = (new Router())->findRoute($this->url, $this);


        return (new Response($data));
    }
}