<?php declare(strict_types = 1);

namespace App\Core;

use Dotenv\Dotenv;

class Application {
    public static function run() : void {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_CHARSET', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM', 'MAIL_FROM_NAME']);
        $container = self::buildContainer();

        $sessionManager = $container->get(SessionManager::class);
        $currentUser = $sessionManager->attemptAutoLogin();

        self::guardRoutes($currentUser);

        $routes = require BASE_PATH . 'config/routes.php';
        $dispatcher = \FastRoute\simpleDispatcher($routes);

        $router = new Router($container, $dispatcher);
        $router->dispatch();
    }

    private static function buildContainer() : \DI\Container {
        $definitions = require BASE_PATH . 'config/container.php';
        return (new \DI\ContainerBuilder())
            ->addDefinitions($definitions)
            ->build();
    }

    private static function guardRoutes(?object $currentUser) : void {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $basePath = $_ENV['APP_BASE_PATH'] ?? '/';
        if (str_starts_with($uri, $basePath)) {
            $uri = '/' . ltrim(substr($uri, strlen($basePath)), '/');
        }

        $publicRoutes = ['/', '/login', '/register', '/verify', '/auth/google', '/auth/google/callback'];

        if (!in_array($uri, $publicRoutes) && $currentUser === null) {
            header('Location: ' . $basePath . 'login');
            exit;
        }
    }
}