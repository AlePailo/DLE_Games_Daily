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

    public function upsertOnGameCompletion(int $userId, int $franchiseId, int $attempts, bool $solved): UserFranchiseStats
    {
        
        $current = $this->findByUserAndFranchise($userId, $franchiseId);

        if($current === null) {
            $current = new UserFranchiseStats(id: 0, userId: $userId, franchiseId: $franchiseId, gamesPlayed: 0, gamesWon: 0, currentStreak: 0, maxStreak: 0, avgAttempts: 0.0,
                updatedAt: new \DateTimeImmutable());
        }

        $wonYesterday = $this->wonYesterday($userId, $franchiseId);

        $current->updateStats($solved, $attempts, $wonYesterday);

        if($current->getId() === 0) {
            $stmt = $this->pdo->prepare("INSERT INTO user_franchise_stats(user_id, franchise_id, games_played, games_won, current_streak, max_streak, avg_attempts) 
                VALUES (:user_id, :franchise_id, :games_played, :games_won, :current_streak, :max_streak, :avg_attempts)"
            );
        } else {
            $stmt = $this->pdo->prepare("UPDATE user_franchise_stats SET
                games_played = :games_played,
                games_won = :games_won,
                current_streak = :current_streak,
                max_streak = :max_streak,
                avg_attempts = :avg_attempts,
                updated_at = NOW()
                WHERE id = :id"
            );
        }

        $payload = [
            'games_played'   => $current->getGamesPlayed(),
            'games_won'      => $current->getGamesWon(),
            'current_streak' => $current->getCurrentStreak(),
            'max_streak'     => $current->getMaxStreak(),
            'avg_attempts'   => $current->getAvgAttempts(),
        ];

        if($current->getId() === 0) {
            $payload['user_id'] = $userId;
            $payload['franchise_id'] = $franchiseId;
        } else {
            $payload['id'] = $current->getId();
        }

        $stmt->execute($payload);

        return $current;
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

    public function findAllByUser(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT f.name as franchise_name, ufs.*
            FROM user_franchise_stats ufs
            INNER JOIN franchises f on ufs.franchise_id = f.id
            WHERE ufs.user_id = :user_id
            ORDER BY ufs.games_played DESC
        ");
        $stmt->execute(['user_id' => $userId]);

        $statsByFranchise = [];
    
        foreach ($stmt->fetchAll() as $row) {
            $statsByFranchise[$row['franchise_name']] = $this->mapUserFranchiseStats($row);

            /* MORE ROBUST SOLUTION (requires changes in profile view, current implementation shouldn't be a problem since user_id and franchise_id pair should always be unique in db)
            $statsByFranchise[$row['franchise_id']] = [
                'name'  => $row['franchise_name'],
                'stats' => $this->mapUserFranchiseStats($row)
            ];
            */
        }
        
        return $statsByFranchise;
    }

    public function findLeaderboardByFranchise(int $franchiseId, array $metricConfig, int $limit) : array {
        $col   = $metricConfig['column'];
        $order = $metricConfig['order'];

        $stmt = $this->pdo->prepare("SELECT ufs.*, u.username, u.user_icon_url
            FROM user_franchise_stats ufs INNER JOIN users u ON ufs.user_id = u.id
            WHERE ufs.franchise_id = :franchise_id
            AND ufs.games_played > 0
            ORDER BY {$col} {$order}
            LIMIT :limit
        ");
        
        $stmt->bindValue(':franchise_id', $franchiseId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findGlobalLeaderboard(array $metricConfig, int $limit) : array {
        $col   = $metricConfig['column'];
        $order = $metricConfig['order'];

        $stmt = $this->pdo->prepare("SELECT u.username,
                u.user_icon_url,
                SUM(ufs.games_played) AS games_played,
                SUM(ufs.games_won) AS games_won,
                MAX(ufs.max_streak) AS max_streak,
                SUM(ufs.avg_attempts * ufs.games_won) / NULLIF(SUM(ufs.games_won), 0) AS avg_attempts
            FROM user_franchise_stats ufs
            INNER JOIN users u ON ufs.user_id = u.id
            GROUP BY ufs.user_id, u.username, u.user_icon_url
            HAVING SUM(ufs.games_played) > 0
            ORDER BY {$col} {$order}
            LIMIT :limit
        ");

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
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