<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\UserFranchiseStats;

interface IUserFranchiseStatsRepository {
    public function insertFromMigratedSessions(int $userId) : void;
    public function upsertOnGameCompletion(int $userId, int $franchiseId, int $attempts, bool $solved) : ?UserFranchiseStats;
    public function findByUserAndFranchise(int $userId, int $franchiseId) : ?UserFranchiseStats;
    public function findAllByUser(int $userId) : array;
    public function findLeaderboardByFranchise(int $franchiseId, array $metricConfig, int $limit) : array;
    public function findGlobalLeaderboard(array $metricConfig, int $limit) : array;
}