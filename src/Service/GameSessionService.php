<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Repository\IGameSessionRepository;
use App\Model\Repository\IUserFranchiseStatsRepository;
use App\Model\Entity\GameSession;

class GameSessionService {
    public function __construct(
        private \PDO $pdo,
        private IGameSessionRepository $gameSessionRepository,
        private IUserFranchiseStatsRepository $statsRepository
    ) {}

    public function getOrCreateSession(int $challengeId, ?int $userId, ?string $guestToken) : GameSession {
        $gameSession = $userId !== null 
        ? $this->gameSessionRepository->findByUserAndChallenge($userId, $challengeId)
        : $this->gameSessionRepository->findByGuestTokenAndChallenge($guestToken, $challengeId);
    
        if($gameSession !== null) {
            return $gameSession;
        }

        $id = $this->gameSessionRepository->create([
            'user_id' => $userId,
            'guest_token' => $guestToken,
            'challenge_id' => $challengeId
        ]);

        return $this->gameSessionRepository->findById($id);
    }

    public function migrateGuestSessions(string $guestToken, int $userId) : void {
        try {
            $this->pdo->beginTransaction();
            $this->gameSessionRepository->migrateGuestSessions($guestToken, $userId);
            $this->statsRepository->insertFromMigratedSessions($userId);
            $this->pdo->commit();
        } catch(\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatsOnComplete(int $userId, int $franchiseId, int $attempts, bool $solved) : void {
        $this->statsRepository->upsertOnGameCompletion($userId, $franchiseId, $attempts, $solved);
    }
}