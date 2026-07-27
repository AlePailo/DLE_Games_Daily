<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\Controller\ApiController;
use App\Core\SessionManager;
use App\Model\Entity\DailyChallenge;
use App\Model\Entity\Franchise;
use App\Model\Entity\GameSession;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Model\Repository\IFranchiseRepository;
use App\Service\CharacterComparisonService;
use App\Service\GameSessionService;

class GameApiController extends ApiController {
    public function __construct(
        private ICharacterRepository $characterRepository,
        private CharacterComparisonService $comparisonService,
        private GameSessionService $gameSessionService,
        private IFranchiseRepository $franchiseRepository,
        private IDailyChallengeRepository $dailyChallengeRepository,
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function attempt(array $vars) : void {
        $body = $this->getJsonBody();
        $guessedCharId = filter_var($body['character_id'] ?? null, FILTER_VALIDATE_INT);

        if(!$guessedCharId) {
            $this->renderJson(['success' => false, 'message' => 'Invalid input'], 400);
            return;
        }

        $slug = $vars['slug'] ?? '';
        [$franchise, $dailyChallenge, $gameSession] = $this->resolveGameContext($slug);
        if(!$this->validateActiveGameSession($franchise, $dailyChallenge, $gameSession)) return;

        $correctCharId = $dailyChallenge->getCharacterId();
        $solved = $guessedCharId === $correctCharId;

        $guessedChar = $this->characterRepository->findByIdWithAttributes($guessedCharId);
        if($guessedChar === null) {
            $this->renderJson(['success' => false, 'message' => 'Character not found'], 404);
            return;
        }

        $correctChar = $solved 
            ? $guessedChar
            : $this->characterRepository->findByIdWithAttributes($correctCharId);

        $comparedResults = $this->comparisonService->compare($guessedChar, $correctChar);
        $attemptNumber = $gameSession->getAttemptsCount() + 1;

        $this->gameSessionService->registerAttempt($gameSession->getId(), $franchise->getId(), $guessedCharId, $attemptNumber, $comparedResults, $solved);

        $response = [
            'success' => true,
            'solved' => $solved,
            'attempt' => [
                'character' => [
                    'name'      => $guessedChar->getName(),
                    'image_url' => $guessedChar->getImageUrl(),
                    'status'    => $solved ? 'Correct' : 'Wrong'
                ],
                'attributes' => $comparedResults
            ]
        ];

        if($solved) {
            $userId = $this->sessionManager->getUserId();
            if($userId !== null) {
                $this->gameSessionService->updateStatsOnComplete($userId, $franchise->getId(), $attemptNumber, true);
            }

            $response['correct_char'] = [
                'name' => $correctChar->getName(),
                'image_url' => $correctChar->getImageUrl(),
                'attributes' => $correctChar->getAttributes()
            ];
            $response['attempts_count'] = $attemptNumber;
        }

        $this->renderJson($response);
    }

    public function surrender(array $vars) : void {
        $slug = $vars['slug'] ?? '';

        [$franchise, $dailyChallenge, $gameSession] = $this->resolveGameContext($slug);
        if(!$this->validateActiveGameSession($franchise, $dailyChallenge, $gameSession)) return;

        $this->gameSessionService->surrender($gameSession->getId(), $this->sessionManager->getUserId(), $franchise->getId(), $gameSession->getAttemptsCount());

        $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());

        $this->renderJson(['success' => true, 'surrender' => true, 'character' => ['name' => $correctChar->getName(), 'imageUrl' => $correctChar->getImageUrl()]]);
        
    }

    private function resolveGameContext(string $slug) : array {
        $franchise = $this->franchiseRepository->findBySlugWithAttributes($slug);
        if($franchise === null) return [null, null, null];

        $dailyChallenge = $this->dailyChallengeRepository->findByFranchiseAndDate($franchise->getId(), new \DateTimeImmutable());
        if($dailyChallenge === null) return [$franchise, null, null];

        $userId = $this->sessionManager->getUserId();
        $guestToken = ($userId === null) ? $this->sessionManager->getGuestToken() : null;

        $gameSession = $this->gameSessionService->findSessionContext($dailyChallenge->getId(), $userId, $guestToken);
    
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