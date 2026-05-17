<?php declare(strict_types = 1);

use App\Controller\AuthController;
use App\Controller\HomeController;
use App\Controller\GameController;

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [HomeController::class, 'index']);
    
    $r->addRoute('GET', '/login', [AuthController::class, 'loginForm']);
    $r->addRoute('POST', '/login', [AuthController::class, 'login']);
    $r->addRoute('GET', '/register', [AuthController::class, 'registerForm']);
    $r->addRoute('POST', '/register', [AuthController::class, 'register']);
    $r->addRoute('POST', '/logout', [AuthController::class, 'logout']);
    $r->addRoute('GET', '/verify', [AuthController::class, 'verifyEmail']);

    /*
    $r->addRoute('GET', '/verify', [AuthController::class, 'verifyEmail']);
    $r->addRoute('GET', '/check/username', [AuthController::class, 'checkUsername']);
    $r->addRoute('GET', '/check/email', [AuthController::class, 'checkEmail']);
    */

    $r->addRoute('GET', '/{slug}', [GameController::class, 'index']);
    //$r->addRoute('POST', '/{slug}/attempt', [GameController::class, 'attempt']);
};