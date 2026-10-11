<?php declare(strict_types = 1);

namespace App\Core;

use App\Service\AuthService;
use Dotenv\Dotenv;
use App\View\View;

class Application {
    public static function run() : void {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_CHARSET', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM', 'MAIL_FROM_NAME']);
        
        self::registerErrorHandlers();

        $container = ContainerFactory::build();

        $authService = $container->get(AuthService::class);
        $currentUser = $authService->attemptAutoLogin();

        self::guardRoutes($currentUser);

        $routes = require BASE_PATH . 'config/routes.php';
        $dispatcher = \FastRoute\simpleDispatcher($routes);

        $router = new Router($container, $dispatcher);
        $router->dispatch();
    }

    private static function guardRoutes(?object $currentUser) : void {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $basePath = $_ENV['APP_BASE_PATH'] ?? '/';
        if (str_starts_with($uri, $basePath)) {
            $uri = '/' . ltrim(substr($uri, strlen($basePath)), '/');
        }

        $protectedRoutes = ['/profile'];

        if(in_array($uri, $protectedRoutes) && $currentUser === null) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /* ----- Handles errors and exceptions that are not handled already somewhere else ----- */
    private static function registerErrorHandlers() : void {
        set_exception_handler(function (\Throwable $e) {
            if ($e instanceof \App\Exception\NotFoundException) {
                http_response_code(404);
                if ($_ENV['APP_ENV'] === 'development') {
                    echo '<pre>404: ' . $e->getMessage() . '</pre>';
                    return;
                }
                \App\View\View::render('errors/404', [
                    'title'      => 'Page not found | DLE Games Daily',
                    'isLoggedIn' => !empty($_SESSION['user_id']),
                    'css'        => [],
                    'js'         => []
                ]);
                return;
            }

            if ($e instanceof \App\Exception\DatabaseException) {
                error_log($e->getMessage());
                http_response_code(500);
                if ($_ENV['APP_ENV'] === 'development') {
                    echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
                } else {
                    View::render('errors/500', [], false);
                }
                return;
            }

            // Default case
            http_response_code(500);
            if($_ENV['APP_ENV'] === 'development') {
                echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
            } else {
                View::render('errors/500', []);
            }
        });

        //Converts PHP errors in Exceptions
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
            throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
        });
    }
}