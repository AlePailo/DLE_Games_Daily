<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\UserFranchiseStats;

class UserFranchiseStatsRepository implements IUserFranchiseStatsRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function insertFromMigratedSessions(int $userId): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO user_franchise_stats(user_id, franchise_id, games_played, games_won, current_streak, max_streak, avg_attempts)
            SELECT gs.user_id, dc.franchise_id, COUNT(*) AS games_played, SUM(gs.solved) AS games_won, 1 AS current_streak, 1 AS max_streak, AVG(gs.attempts_count) AS avg_attempts 
            FROM game_sessions gs INNER JOIN daily_challenges dc ON gs.challenge_id = dc.id
            WHERE gs.user_id = :user_id
            AND gs.solved = 1
            AND DATE(gs.created_at) = CURDATE()
            GROUP BY dc.franchise_id");
        $stmt->execute(['user_id' => $userId]);
    }

    public function upsertOnGameCompletion(int $userId, int $franchiseId, int $attempts, bool $solved): void
    {
        $wonYesterday = $this->wonYesterday($userId, $franchiseId);
        
        $current = $this->findByUserAndFranchise($userId, $franchiseId);

        if($current === null) {
            $stmt = $this->pdo->prepare("INSERT INTO user_franchise_stats(user_id, franchise_id, games_played, games_won, current_streak, max_streak, avg_attempts) 
                VALUES (:user_id, :franchise_id, 1, :games_won, :current_streak, :max_streak, :avg_attempts)");
            $stmt->execute([
                'user_id'        => $userId,
                'franchise_id'   => $franchiseId,
                'games_won'      => $solved ? 1 : 0,
                'current_streak' => $solved ? 1 : 0,
                'max_streak'     => $solved ? 1 : 0,
                'avg_attempts'   => $solved ? $attempts : 0
            ]);
            return;
        }

        $newStreak = match(true) {
            !$solved          => 0,
            $wonYesterday     => $current->getCurrentStreak() + 1,
            default           => 1
        };

        $newMaxStreak = max($current->getMaxStreak(), $newStreak);

        
        if($solved) {
            $newAvg = (($current->getAvgAttempts() * $current->getGamesWon()) + $attempts)
                    / ($current->getGamesWon() + 1);
        } else {
            $newAvg = $current->getAvgAttempts();
        }

        // Update
        $stmt = $this->pdo->prepare("
            UPDATE user_franchise_stats SET
                games_played    = games_played + 1,
                games_won       = games_won + :games_won_increment,
                current_streak  = :current_streak,
                max_streak      = :max_streak,
                avg_attempts    = :avg_attempts,
                updated_at      = NOW()
            WHERE user_id = :user_id
            AND franchise_id = :franchise_id
        ");
        $stmt->execute([
            'games_won_increment' => $solved ? 1 : 0,
            'current_streak'      => $newStreak,
            'max_streak'          => $newMaxStreak,
            'avg_attempts'        => $newAvg,
            'user_id'             => $userId,
            'franchise_id'        => $franchiseId,
        ]);
    }

    private function wonYesterday(int $userId, int $franchiseId) : bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) 
            FROM game_sessions gs INNER JOIN daily_challenges dc ON gs.challenge_id = dc.id 
            WHERE gs.user_id = :user_id
            AND dc.franchise_id = :franchise_id
            AND gs.solved = 1
            AND DATE(gs.created_at) = CURDATE() - INTERVAL 1 DAY");
        $stmt->execute(['user_id' => $userId, 'franchise_id' => $franchiseId]);
        return (bool)$stmt->fetchColumn();
    }

    public function findByUserAndFranchise(int $userId, int $franchiseId): ?UserFranchiseStats
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_franchise_stats WHERE user_id = :user_id AND franchise_id = :franchise_id");
        $stmt->execute(['user_id' => $userId, 'franchise_id' => $franchiseId]);
        $res = $stmt->fetch();

        return $res ? $this->mapUserFranchiseStats($res) : null;
    }

    private function mapUserFranchiseStats(array $row) : UserFranchiseStats {
        return new UserFranchiseStats(
            id: (int)$row['id'],
            userId: (int)$row['user_id'],
            franchiseId: (int)$row['franchise_id'],
            gamesPlayed: (int)$row['games_played'],
            gamesWon: (int)$row['games_won'],
            currentStreak: (int)$row['current_streak'],
            maxStreak: (int)$row['max_streak'],
            avgAttempts: (float)$row['avg_attempts'],
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
    }
}