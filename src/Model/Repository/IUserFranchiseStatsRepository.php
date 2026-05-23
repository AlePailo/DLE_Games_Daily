<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\UserFranchiseStats;

interface IUserFranchiseStatsRepository {
    public function insertFromMigratedSessions(int $userId) : void;
    public function upsertOnGameCompletion(int $userId, int $franchiseId, int $attempts, bool $solved) : void;
    public function findByUserAndFranchise(int $userId, int $franchiseId) : ?UserFranchiseStats;
}