<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\Controller\ApiController;
use App\Core\SessionManager;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\IGameAttemptRepository;
use App\Model\Repository\IGameAttemptResultRepository;
use App\Model\Repository\IGameSessionRepository;
use App\Service\CharacterComparisonService;
use App\Service\GameSessionService;
use App\Model\Entity\Franchise;
use App\Model\Entity\DailyChallenge;
use App\Model\Entity\GameSession;

class GameApiController extends ApiController {
    public function __construct(
        private ICharacterRepository $characterRepository,
        private CharacterComparisonService $comparisonService,
        private IGameAttemptRepository $gameAttemptRepository,
        private IGameAttemptResultRepository $gameAttemptResultRepository,
        private IGameSessionRepository $gameSessionRepository,
        private GameSessionService $gameSessionService,
        private IFranchiseRepository $franchiseRepository,
        private IDailyChallengeRepository $dailyChallengeRepository,
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function attempt(array $vars) : void {
        $slug = $vars['slug'] ?? '';
        $body = $this->getJsonBody();
        $guessedCharId = isset($body['character_id']) ? (int)$body['character_id'] : null;

        if($guessedCharId === null) {
            $this->renderJson(['success' => false, 'message' => 'Invalid input'], 400);
            return;
        }

        [$franchise, $dailyChallenge, $gameSession] = $this->resolveGameContext($slug);
        if(!$this->validateActiveGameSession($franchise, $dailyChallenge, $gameSession)) return;

        $guessedChar = $this->characterRepository->findByIdWithAttributes($guessedCharId);
        $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());

        if($guessedChar === null) {
            $this->renderJson(['success' => false, 'message' => 'Character not found'], 404);
            return;
        }

        $attributeDefs = $franchise->getAttributeDefinitions();
        $compareResults = $this->comparisonService->compare($guessedChar, $correctChar);

        $defsByKey = [];
        foreach($attributeDefs as $def) {
            $defsByKey[$def->getKey()] = $def;
        }

        $resultsWithIds = [];
        foreach($compareResults as $key => $status) {
            if(isset($defsByKey[$key])) {
                $resultsWithIds[$defsByKey[$key]->getId()] = $status;
            }
        }

        $attemptNumber = $gameSession->getAttemptsCount() + 1;
        $attemptId = $this->gameAttemptRepository->create($gameSession->getId(), $guessedCharId, $attemptNumber);
        $this->gameAttemptResultRepository->createMany($attemptId, $resultsWithIds);
        $this->gameSessionRepository->incrementAttempts($gameSession->getId());

        $solved = $guessedCharId === $dailyChallenge->getCharacterId();
        $response = [
            'success' => true,
            'solved' => $solved,
            'attempt' => [
                'character' => [
                    'name' => $guessedChar->getName(),
                    'image_url' => $guessedChar->getImageUrl(),
                ],
                'results' => array_map(fn($r) => $r->value, $compareResults)
            ]];

        if($solved) {
            $this->gameSessionRepository->markAsSolved($gameSession->getId());

            if($userId = $this->sessionManager->getUserId()) {
                $this->gameSessionService->updateStatsOnComplete($userId, $franchise->getId(), $attemptNumber, true);
            }

            $response = array_merge($response, [
                'correct_char' => [
                    'name' => $correctChar->getName(),
                    'image_url' => $correctChar->getImageUrl(),
                    'attributes' => $correctChar->getAttributes()
                ],
                'attempts_count' => $attemptNumber
            ]);
        }

        $this->renderJson($response);
    }

    public function surrender(array $vars) : void {
        $slug = $vars['slug'] ?? '';

        [$franchise, $dailyChallenge, $gameSession] = $this->resolveGameContext($slug);
        if(!$this->validateActiveGameSession($franchise, $dailyChallenge, $gameSession)) return;

        $this->gameSessionRepository->markAsCompleted($gameSession->getId());

        if($userId = $this->sessionManager->getUserId()) {
            $this->gameSessionService->updateStatsOnComplete($userId, $franchise->getId(), $gameSession->getAttemptsCount(), false);
        }

        $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());

        $this->renderJson(['success' => true, 'surrender' => true, 'character' => ['name' => $correctChar->getName(), 'imageUrl' => $correctChar->getImageUrl()]]);
        
    }

    private function resolveGameContext(string $slug) : array {
        $franchise = $this->franchiseRepository->findBySlugWithAttributes($slug);
        if($franchise === null) return [null, null, null];

        $dailyChallenge = $this->dailyChallengeRepository->findByFranchiseAndDate($franchise->getId(), new \DateTimeImmutable());
        if($dailyChallenge === null) return [null, null, null];

        $userId = $this->sessionManager->getUserId();
        $guestToken = $userId === null ? $this->sessionManager->getGuestToken() : null;

        $gameSession = $userId !== null ? $this->gameSessionRepository->findByUserAndChallenge($userId, $dailyChallenge->getId()) : $this->gameSessionRepository->findByGuestTokenAndChallenge($guestToken, $dailyChallenge->getId());
    
        return [$franchise, $dailyChallenge, $gameSession];
    }

    private function validateActiveGameSession(?Franchise $franchise, ?DailyChallenge $dailyChallenge, ?GameSession $gameSession) : bool {
        if($franchise === null || $dailyChallenge === null || $gameSession === null) {
            $this->renderJson(['success' => false, 'message' => 'Game not found'], 404);
            return false;
        }

        if($gameSession->isSolved() || $gameSession->isCompleted()) {
            $this->renderJson(['success' => false, 'message' => 'Game already completed'], 400);
            return false;
        }

        return true;
    }
}