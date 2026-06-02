<?php declare(strict_types = 1);

namespace App\Controller;

use App\Core\SessionManager;
use App\Model\Entity\DailyChallenge;
use App\Model\Entity\GameSession;
use App\Model\Entity\Franchise;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Service\GameSessionService;
use App\Model\Repository\IGameAttemptRepository;
use App\Model\Repository\IGameAttemptResultRepository;
use App\Service\CharacterComparisonService;
use App\Model\Repository\IGameSessionRepository;

class GameController extends BaseController{
    public function __construct(
        SessionManager $sessionManager,
        private IFranchiseRepository $franchiseRepository,
        private ICharacterRepository $characterRepository,
        private IDailyChallengeRepository $dailyChallengeRepository,
        private GameSessionService $gameSessionService,
        private IGameAttemptRepository $gameAttemptRepository,
        private CharacterComparisonService $comparisonService,
        private IGameSessionRepository $gameSessionRepository,
        private IGameAttemptResultRepository $gameAttemptResultRepository
    ) {
        parent::__construct($sessionManager);
    }

    public function start(array $vars) : void {
        $slug = $vars['slug'] ?? '';
        
        $franchise = $this->franchiseRepository->findBySlugWithAttributes($slug);
        if($franchise === null) {
            $this->notFound();
            return;
        }

        $dailyChallenge = $this->dailyChallengeRepository->findByFranchiseAndDate($franchise->getId(), new \DateTimeImmutable());
        if($dailyChallenge === null) {
            //...
        }

        $userId = $this->sessionManager->getUserId();
        $guestToken = $userId === null ? $this->sessionManager->getOrCreateGuestToken() : null;
        $gameSession = $this->gameSessionService->getOrCreateSession($dailyChallenge->getId(), $userId, $guestToken);

        $attempts = $this->gameAttemptRepository->findByGameSessionWithResults($gameSession->getId());

        $characters = $this->characterRepository->findByFranchiseId($franchise->getId());

        $this->render("game", [
            'title' => "{$slug} | DLE Games Daily",
            /*'css' => ['game.css'],
            'js' => ['game.js']*/
            'franchise' => $franchise,
            'gameSession' => $gameSession,
            'characters' => $characters,
            'show_recap' => $gameSession->isSolved() || $gameSession->isCompleted(),
            'attempts' => $attempts
        ]);
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
        if($solved) {
            $this->gameSessionRepository->markAsSolved($gameSession->getId());

            if($userId = $this->sessionManager->getUserId()) {
                $this->gameSessionService->updateStatsOnComplete($userId, $franchise->getId(), $attemptNumber, true);
            }
        }

        $this->renderJson(['success' => true, 'solved' => $solved, 'attempt' => ['character' => ['name' => $guessedChar->getName(), 'imageUrl' => $guessedChar->getImageUrl()], 'results' => array_map(fn($r) => $r->value, $compareResults)]]);
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