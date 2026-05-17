<?php declare(strict_types = 1);

namespace App\Core;

use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;

class Router {
    private string $basePath;

    public function __construct(
        private ContainerInterface $container,
        private Dispatcher $dispatcher
    ) {
        $this->basePath = $_ENV['APP_BASE_PATH'] ?? '/';
    }

    public function dispatch() : void {
        // URI
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->resolveUri();

        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo '404 - Pagina non trovata';
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo '405 - Metodo non consentito';
                break;

            case Dispatcher::FOUND:
                [$controllerClass, $action] = $routeInfo[1];
                $vars = $routeInfo[2];

                $controller = $this->container->get($controllerClass);
                $controller->$action($vars);
                break;
        }
    }

    private function resolveUri() : string {
        $uri = $_SERVER['REQUEST_URI'];

        // Rimuove base path e query string
        if (str_starts_with($uri, $this->basePath)) {
            $uri = '/' . ltrim(substr($uri, strlen($this->basePath)), '/');
        }
        return strtok($uri, '?');
    }
}