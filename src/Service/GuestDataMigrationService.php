<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Repository\IGameSessionRepository;
use App\Model\Repository\IUserFranchiseStatsRepository;

class GuestDataMigrationService {
    public function __construct(
        private \PDO $pdo,
        private IGameSessionRepository $gameSessionRepository,
        private IUserFranchiseStatsRepository $statsRepository
    ) {}

    public function migrate(string $guestToken, int $userId) : int {
        $today = new \DateTimeImmutable('today');
        $rows = $this->gameSessionRepository->findMigratable($guestToken, $userId, $today);

        $byChallenge = [];
        foreach($rows as $row) {
            $byChallenge[$row['challenge_id']] ??= $row;
        }
        $sessions = array_values($byChallenge);

        if($sessions === []) {
            return 0;
        }

        $this->pdo->beginTransaction();
        try {
            $this->gameSessionRepository->assignToUser(array_column($sessions, 'id'), $userId);

            foreach($sessions as $session) {
                if((bool) $session['is_completed']) {
                    $this->statsRepository->upsertOnGameCompletion(
                        $userId,
                        (int) $session['franchise_id'],
                        (int) $session['attempts_count'],
                        (bool) $session['solved'],
                        new \DateTimeImmutable($session['challenge_date'])
                    );
                }
            }

            $this->pdo->commit();
        } catch(\Throwable $e) {
            if($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return count($sessions);
    }
}