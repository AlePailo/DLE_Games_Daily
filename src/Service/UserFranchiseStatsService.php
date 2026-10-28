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

    private const LEADERBOARD_METRICS = [
        'avg_attempts' => ['column' => 'avg_attempts', 'order' => 'ASC'],
        'max_streak'   => ['column' => 'max_streak',   'order' => 'DESC'],
        'games_played' => ['column' => 'games_played', 'order' => 'DESC'],
    ];

    public function getLeaderboardByFranchise(int $franchiseId, string $metric, int $limit): array {
        $metricConfig = self::LEADERBOARD_METRICS[$metric] ?? self::LEADERBOARD_METRICS['max_streak'];
        return $this->statsRepository->findLeaderboardByFranchise($franchiseId, $metricConfig, $limit);
    }

    public function getGlobalLeaderboard(string $metric, int $limit = 10) : array {
        $metricConfig = self::LEADERBOARD_METRICS[$metric] ?? self::LEADERBOARD_METRICS['avg_attempts'];
        return $this->statsRepository->findGlobalLeaderboard($metricConfig, $limit);
    }

    public function getLeaderboard(string $franchise, string $metric, int $limit = 10) : array {
        if($franchise === 'global') {
            return $this->getGlobalLeaderboard($metric, $limit);
        }

        if (!is_numeric($franchise)) {
            return $this->getGlobalLeaderboard($metric, $limit);
        }

        return $this->getLeaderboardByFranchise((int)$franchise, $metric, $limit);
    }
}