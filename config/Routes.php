<?php declare(strict_types = 1);

use App\Controller\HomeController;
//use App\Controller\GameController;

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [HomeController::class, 'index']);

    /*
    $r->addRoute('GET', '/{slug}', [GameController::class, 'index']);
    $r->addRoute('POST', '/{slug}/attempt', [GameController::class, 'attempt']);
    */
};