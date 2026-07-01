<?php declare(strict_types = 1);

use App\Controller\AuthController;
use App\Controller\FranchiseController;
use App\Controller\HomeController;
use App\Controller\GameController;
use App\Controller\LeaderboardsController;
use App\Controller\ProfileController;

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [HomeController::class, 'index']);
    $r->addRoute('GET', '/games', [FranchiseController::class, 'index']);
    $r->addRoute('GET', '/leaderboards', [LeaderboardsController::class, 'index']);
    $r->addRoute('GET', '/profile', [ProfileController::class, 'index']);
    
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

    $r->addRoute('GET', '/{slug}', [GameController::class, 'start']);
    $r->addRoute('POST', '/{slug}/attempt', [GameController::class, 'attempt']);
    $r->addRoute('POST', '/{slug}/surrender', [GameController::class, 'surrender']);
};