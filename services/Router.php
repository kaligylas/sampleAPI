<?php

namespace sampleAPI\services;

class Router
{
    public function findRoute($url, $request)
    {
        $urlParts = explode('/', $url);
        $class = ucfirst($urlParts[1]);
        $method = 'indexAction';
        if(ucfirst($urlParts[2])) {
            $method = $urlParts[2] . 'Action';
        }



        if(class_exists($class)) {
            $controller = (new $class());
            if(method_exists((new $class()), $method)) {
                return $controller->$method($request);
            }

            throw new \Exception('Route does not exists');
        }

        throw new \Exception('Route does not exists');
    }
}