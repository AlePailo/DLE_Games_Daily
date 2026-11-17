<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\Controller\ApiController;
use App\Core\SessionManager;
use App\Exception\GameException;
use App\Service\GameService;

class GameApiController extends ApiController {
    public function __construct(
        protected SessionManager $sessionManager,
        private GameService $gameService
    ) {
        parent::__construct($sessionManager);
    }

    public function attempt(array $vars): void {
        $this->validateCsrfToken();
        $guessedCharId = filter_var(
            $this->getJsonBody()['character_id'] ?? null,
            FILTER_VALIDATE_INT
        );
 
        if (!$guessedCharId) {
            $this->renderJson(['success' => false, 'message' => 'Invalid input'], 400);
            return;
        }

        [$userId, $guestToken] = $this->identity();
 
        $context = $this->resolvePlayableContext($vars['slug'] ?? '', $userId, $guestToken);
        if ($context === null) return;
 
        try {
            $this->renderJson($this->gameService->processAttempt($context, $guessedCharId, $userId, $guestToken));
        } catch (GameException $e) {
            $this->renderJson(['success' => false, 'message' => $e->getMessage()], $this->statusFor($e));
        }

    }
 
    public function surrender(array $vars): void {
        $this->validateCsrfToken();

        [$userId, $guestToken] = $this->identity();

        $context = $this->resolvePlayableContext($vars['slug'] ?? '', $userId, $guestToken);
        if ($context === null) return;

        try {
            $this->renderJson($this->gameService->processSurrender($context, $userId, $guestToken));
        } catch (GameException $e) {
            $this->renderJson(['success' => false, 'message' => $e->getMessage()], $this->statusFor($e));
        }

    }

    private function identity() : array {
        $userId = $this->sessionManager->getUserId();
        $guestToken = $userId === null ? $this->sessionManager->getGuestToken() : null;

        return [$userId, $guestToken];
    }
 
    private function resolvePlayableContext(string $slug, ?int $userId, ?string $guestToken): ?\App\Model\DTO\GameContext {
        $context = $this->gameService->resolveGameContext($slug, $userId, $guestToken);
 
        if (!$context->isValid()) {
            $this->renderJson(['success' => false, 'message' => 'Game not found'], 404);
            return null;
        }
 
        if (!$context->isPlayable()) {
            $this->renderJson(['success' => false, 'message' => 'Game already completed'], 400);
            return null;
        }
 
        return $context;
    }

    private function statusFor(GameException $e): int {
        return match($e->getCode()) {
            GameException::CHARACTER_NOT_FOUND => 404,
            GameException::ALREADY_GUESSED => 409,
            default => 400,
        };
    }
}