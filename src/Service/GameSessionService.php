<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Entity\GameSession;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\IGameSessionRepository;
use App\Model\Repository\IGameAttemptRepository;

class GameSessionService {
    public function __construct(
        private \PDO $pdo,
        private IFranchiseRepository $franchiseRepository,
        private IGameSessionRepository $gameSessionRepository,
        private IGameAttemptRepository $gameAttemptRepository,
    ) {}

    public function getOrCreateSession(int $challengeId, ?int $userId, ?string $guestToken) : GameSession {
        $gameSession = $this->findSessionContext($challengeId, $userId, $guestToken);
    
        if($gameSession !== null) {
            return $gameSession;
        }

        $id = $this->gameSessionRepository->create([
            'user_id'      => $userId,
            'guest_token'  => $guestToken,
            'challenge_id' => $challengeId
        ]);

        return $this->gameSessionRepository->findById($id);
    }

    public function migrateGuestSessions(string $guestToken, int $userId): void {
        $this->gameSessionRepository->migrateGuestSessions($guestToken, $userId);
    }

    public function registerAttempt(int $sessionId, int $franchiseId, int $guessedCharacterId, int $attemptNumber, array $comparedResults, bool $solved) : void {
        try {
            $this->pdo->beginTransaction();

            $attributeDefsMap = $this->franchiseRepository->getAttributeDefinitionMapByFranchiseId($franchiseId);

            $resultsWithIds = [];
            foreach($comparedResults as $key => $attrData) {
                if (isset($attributeDefsMap[$key])) {
                    $attrId = $attributeDefsMap[$key];
                    $rawStatus = $attrData['status'] ?? $attrData;
                    
                    $statusValue = $rawStatus instanceof \BackedEnum
                        ? $rawStatus->value
                        : $rawStatus;

                    $resultsWithIds[$attrId] = $statusValue;
                }
            }

            $this->gameAttemptRepository->createWithResults($sessionId, $guessedCharacterId, $attemptNumber, $resultsWithIds);
            $this->gameSessionRepository->updateSessionState($sessionId, $solved);
            
            $this->pdo->commit();
        } catch(\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    public function surrender(int $sessionId) : void {
        try {
            $this->pdo->beginTransaction();
            $this->gameSessionRepository->markAsCompleted($sessionId);
            $this->pdo->commit();
        } catch(\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findSessionContext(int $challengeId, ?int $userId, ?string $guestToken) : ?GameSession {
        if($userId !== null) {
            return $this->gameSessionRepository->findByUserAndChallenge($userId, $challengeId);
        }

        if($guestToken !== null) {
            return $this->gameSessionRepository->findByGuestTokenAndChallenge($guestToken, $challengeId);
        }

        return null;
    }

    public function hasSessionsToMigrate(string $guestToken) : bool {
        return count($this->gameSessionRepository->findCompletedByGuestToken($guestToken)) > 0;
    }
}