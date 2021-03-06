<?php

use FastRoute\Dispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require_once __DIR__ . '/../vendor/autoload.php';
/*
 * Request instance (use this instead of $_GET, $_POST, etc).
 */
$request = Request::createFromGlobals();

/**
 * In future rewrite this by using env file
 */
$env = 'dev';

/*
 * Error handler
 */
$whoops = new Run;
if ($env === 'dev') {
    $whoops->pushHandler(
        new PrettyPageHandler()
    );
} else {
    $whoops->pushHandler(
    // Using the pretty error handler in production is likely a bad idea.
    // Instead respond with a generic error message.
        function () use ($request) {
            Response::create('An internal server error has occurred.', Response::HTTP_INTERNAL_SERVER_ERROR)
                ->prepare($request)
                ->send();
        }
    );
}
$whoops->register();

/*
 * Routes
 */
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $routes = require __DIR__ . '../config/routes.php';
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});
/*
 * Dispatch
 */
$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        // No matching route was found.
        Response::create("404 Not Found", Response::HTTP_NOT_FOUND)
            ->prepare($request)
            ->send();
        break;
    case Dispatcher::METHOD_NOT_ALLOWED:
        // A matching route was found, but the wrong HTTP method was used.
        Response::create("405 Method Not Allowed", Response::HTTP_METHOD_NOT_ALLOWED)
            ->prepare($request)
            ->send();
        break;
    case Dispatcher::FOUND:
        // Controller method responsible for handling the request
        $routeMethod = $routeInfo[1][1];
        // Route parameters (ex. /products/{category}/{id})
        $routeParams = $routeInfo[2];
        // Generate a response by invoking the appropriate route method in the controller
        $response = call_user_func($routeInfo[1][0], $routeParams);
        if ($response instanceof Response) {
            // Send the generated response back to the user
            $response
                ->prepare($request)
                ->send();
        }
        break;
    default:
        // According to the dispatch(..) method's documentation this shouldn't happen.
        // But it's here anyways just to cover all of our bases.
        Response::create('Received unexpected response from dispatcher.', Response::HTTP_INTERNAL_SERVER_ERROR)
            ->prepare($request)
            ->send();
        return;
}