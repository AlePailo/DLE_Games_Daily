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
                throw new \App\Exception\NotFoundException('Route not found');
                /*
                http_response_code(404);
                echo '404 - Pagina non trovata';
                break;
                */

            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new \App\Exception\NotFoundException('Route not allowed');
                /*
                http_response_code(405);
                echo '405 - Metodo non consentito';
                break;
                */

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

        # 1. Rimuoviamo prima di tutto i parametri GET (?foo=bar)
        $uri = strtok($uri, '?');

        # 2. Decodifichiamo eventuali caratteri speciali (%20, ecc.)
        $uri = rawurldecode($uri);

        # 3. Rimuoviamo il basePath se presente all'inizio
        if (!empty($this->basePath) && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        # 4. Assicuriamoci che l'URI inizi SEMPRE con un solo '/'
        $uri = '/' . ltrim($uri, '/');

        return $uri;
    }
}