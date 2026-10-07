<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\Controller\ApiController;
use App\Core\SessionManager;
use App\Service\GameService;

class GameApiController extends ApiController {
    public function __construct(
        protected SessionManager $sessionManager,
        private GameService $gameService
    ) {
        parent::__construct($sessionManager);
    }

    public function attempt(array $vars): void {
        $guessedCharId = filter_var(
            $this->getJsonBody()['character_id'] ?? null,
            FILTER_VALIDATE_INT
        );
 
        if (!$guessedCharId) {
            $this->renderJson(['success' => false, 'message' => 'Invalid input'], 400);
            return;
        }
 
        $context = $this->resolveActiveContext($vars['slug'] ?? '');
        if ($context === null) return;
 
        $result = $this->gameService->processAttempt(
            $context,
            $guessedCharId,
            $this->sessionManager->getUserId()
        );
 
        // Character not found is a domain error returned from the service
        $status = isset($result['message']) && $result['success'] === false ? 404 : 200;
        $this->renderJson($result, $status);
    }
 
    public function surrender(array $vars): void {
        $context = $this->resolveActiveContext($vars['slug'] ?? '');
        if ($context === null) return;
 
        $result = $this->gameService->processSurrender(
            $context,
            $this->sessionManager->getUserId()
        );
 
        $this->renderJson($result);
    }
 
    private function resolveActiveContext(string $slug): ?\App\Model\DTO\GameContext {
        $userId     = $this->sessionManager->getUserId();
        $guestToken = $userId === null ? $this->sessionManager->getGuestToken() : null;
 
        $context = $this->gameService->resolveGameContext($slug, $userId, $guestToken);
 
        if (!$context->isValid()) {
            $this->renderJson(['success' => false, 'message' => 'Game not found'], 404);
            return null;
        }
 
        if (!$context->isActive()) {
            $this->renderJson(['success' => false, 'message' => 'Game already completed'], 400);
            return null;
        }
 
        return $context;
    }
}