<?php declare(strict_types = 1);

use Dotenv\Dotenv;

define ('BASE_PATH', dirname(__DIR__) . '/');

require BASE_PATH . 'vendor/autoload.php';

//.env loading
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

//Critical variables check
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_CHARSET']);


// Container
$definitions = require BASE_PATH . 'config/container.php';
$container   = (new \DI\ContainerBuilder())
    ->addDefinitions($definitions)
    ->build();

// Router
$routes     = require BASE_PATH . 'config/routes.php';
$dispatcher = FastRoute\simpleDispatcher($routes);

// URI
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// Rimuove base path e query string
$basePath = '/DLE_Games_Daily/';
if (str_starts_with($uri, $basePath)) {
    $uri = '/' . ltrim(substr($uri, strlen($basePath)), '/');
}
$uri = strtok($uri, '?');

// Dispatching
$routeInfo = $dispatcher->dispatch($method, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 - Pagina non trovata';
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 - Metodo non consentito';
        break;

    case FastRoute\Dispatcher::FOUND:
        [$controllerClass, $action] = $routeInfo[1];
        $vars = $routeInfo[2];

        $controller = $container->get($controllerClass);
        $controller->$action($vars);
        break;
}