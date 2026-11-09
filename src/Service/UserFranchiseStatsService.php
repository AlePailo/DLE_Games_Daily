<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\DTO\UserStatsData;
use App\Model\Entity\UserFranchiseStats;
use App\Model\Repository\IUserFranchiseStatsRepository;

class UserFranchiseStatsService {
    public function __construct(
        private IUserFranchiseStatsRepository $statsRepository
    ){}

    public function upsertOnGameCompletion(int $userId, int $franchiseId, int $attempts, bool $solved): UserFranchiseStats {
        return $this->statsRepository->upsertOnGameCompletion($userId, $franchiseId, $attempts, $solved);
    }

    public function getAggregatedStatsByUser(int $userId) : array {
        $allStats = $this->statsRepository->findAllByUser($userId);

        if(empty($allStats)) {
            return [
                'games_played' => 0,
                'max_streak'   => 0,
                'avg_attempts' => 0.0,
                'surrenders'   => 0,
            ];
        }

        $gamesPlayed  = array_sum(array_map(fn($s) => $s->getGamesPlayed(), $allStats));
        $gamesWon     = array_sum(array_map(fn($s) => $s->getGamesWon(), $allStats));
        $maxStreak    = max(array_map(fn($s) => $s->getMaxStreak(), $allStats));
 
        // Weighted average of avg_attempts based on number of games won on each franchise
        $totalWonWeighted = array_sum(array_map(fn($s) => $s->getAvgAttempts() * $s->getGamesWon(), $allStats));
        $avgAttempts = $gamesWon > 0 ? $totalWonWeighted / $gamesWon : 0.0;
 
        return [
            'games_played' => $gamesPlayed,
            'max_streak'   => $maxStreak,
            'avg_attempts' => round($avgAttempts, 1),
            'surrenders'   => $gamesPlayed - $gamesWon
        ];
    }

    public function getStatsByUser(int $userId) : array {
        return $this->statsRepository->findAllByUser($userId);
    }

    public function insertFromMigratedSessions(int $userId): void {
        $this->statsRepository->insertFromMigratedSessions($userId);
    }

    public function buildUserStatsData(int $userId, int $franchiseId): ?UserStatsData {
        $stats = $this->statsRepository->findByUserAndFranchise($userId, $franchiseId);
        if ($stats === null) return null;
 
        return new UserStatsData(
            gamesPlayed:   $stats->getGamesPlayed(),
            avgAttempts:   $stats->getAvgAttempts(),
            currentStreak: $stats->getCurrentStreak(),
            maxStreak:     $stats->getMaxStreak(),
            surrenders:    $stats->getSurrenders()
        );
    }

    private const MIN_WINS_FOR_AVERAGE = 3;

    private const LEADERBOARD_METRICS = [
        'avg_attempts' => ['column' => 'avg_attempts', 'order' => 'ASC', 'min_wins' => self::MIN_WINS_FOR_AVERAGE],
        'max_streak'   => ['column' => 'max_streak',   'order' => 'DESC', 'min_wins' => 1],
        'games_played' => ['column' => 'games_played', 'order' => 'DESC', 'min_wins' => 1],
    ];

    public function getLeaderboard(string $franchise, string $metric, int $limit = 10) : array {
        $metric = $this->resolveMetric($metric);

        $rows = ctype_digit($franchise)
            ? $this->getLeaderboardByFranchise((int)$franchise, $metric, $limit)
            : $this->getGlobalLeaderboard($metric, $limit);

        return array_map(fn($row) => [
            'username'      => $row['username'],
            'user_icon_url' => $row['user_icon_url'],
            'value'         => $this->formatLeaderboardValue($row, $metric),
        ], $rows);
    }

    private function formatLeaderboardValue(array $row, string $metric): string {
        return $metric === 'avg_attempts'
            ? number_format((float)$row['avg_attempts'], 2)
            : (string)(int)$row[$metric];
    }

    public function resolveMetric(string $metric): string {
        return array_key_exists($metric, self::LEADERBOARD_METRICS) ? $metric : 'avg_attempts';
    }

    private function getLeaderboardByFranchise(int $franchiseId, string $metric, int $limit): array {
        return $this->statsRepository->findLeaderboardByFranchise($franchiseId, self::LEADERBOARD_METRICS[$metric], $limit);
    }

    private function getGlobalLeaderboard(string $metric, int $limit): array {
        return $this->statsRepository->findGlobalLeaderboard(self::LEADERBOARD_METRICS[$metric], $limit);
    }
}