<?php declare(strict_types = 1);

namespace App\Core;

use App\Controller\Web\ErrorController;
use App\Exception\NotFoundException;
use App\Service\AuthService;
use Dotenv\Dotenv;

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

        $routes = require BASE_PATH . 'config/Routes.php';
        $dispatcher = \FastRoute\simpleDispatcher($routes);

        $router = new Router($container, $dispatcher);
        
        try {
            $router->dispatch();
        } catch(NotFoundException) {
            if(self::isApiRequest()) {
                self::respondJsonError(404, 'Not Found');
            } else {
                $container->get(ErrorController::class)->notFound();
            }
        }
    }

    private static function guardRoutes(?object $currentUser) : void {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $basePath = $_ENV['APP_BASE_PATH'] ?? '/';
        if (str_starts_with($uri, $basePath)) {
            $uri = '/' . ltrim(substr($uri, strlen($basePath)), '/');
        }

        $protectedRoutes = ['/profile', '/profile/update'];

        if(in_array($uri, $protectedRoutes) && $currentUser === null) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /* ----- Handles errors and exceptions that are not handled already somewhere else ----- */
    private static function registerErrorHandlers() : void {
        $isDev = ($_ENV['APP_ENV'] ?? 'production') === 'development';

        set_exception_handler(function (\Throwable $e) use ($isDev) {
            error_log((string) $e);
            http_response_code(500);

            if ($isDev) {
                echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
                return;
            }

            if (self::isApiRequest()) {
                self::respondJsonError(500, 'Internal server error');
                return;
            }

            require BASE_PATH . 'templates/errors/500.php';
        });

        //Converts PHP errors in Exceptions
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
            if (!(error_reporting() & $errno)) {
                return false;
            }
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    private static function isApiRequest() : bool {
        return str_contains((string) strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/api/');
    }

    private static function respondJsonError(int $status, string $message) : void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    }
}