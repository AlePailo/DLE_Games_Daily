<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Service\GameService;

class GameController extends WebController {
    public function __construct(
        protected SessionManager $sessionManager,
        private GameService $gameService
    ) {
        parent::__construct($sessionManager);
    }

    public function start(array $vars) : void {
        $slug = $vars['slug'] ?? '';
        $userId = $this->sessionManager->getUserId();
        $guestToken = $userId === null ? $this->sessionManager->getOrCreateGuestToken() : null;

        $context = $this->gameService->resolveGameContext($slug, $userId, $guestToken);

        if(!$context->franchise) {
            $this->notFound();
            return;
        }

        if(!$context->dailyChallenge) {
            $this->render('game_unavailable', [
                'title'     => "{$context->franchise->getName()} | DLE Games Daily",
                'css'       => ['game.css'],
                'js'        => ['game.js'],
                'franchise' => $context->franchise
            ]);
            return;
        }

        $gameData = $this->gameService->getGameViewData($context, $userId, $guestToken);

        $this->render('game', array_merge($gameData, [
            'title'     => "{$context->franchise->getName()} | DLE Games Daily",
            'css'       => ['game.css'],
            'js'        => ['game.js'],
            'franchise' => $context->franchise
        ]));
    }
}