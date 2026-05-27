<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\DailyChallenge;

interface IDailyChallengeRepository {
    public function findByFranchiseAndDate(int $franchiseId, \DateTimeImmutable $date) : ?DailyChallenge;
    public function create(int $franchiseId, int $characterId) : void;
    public function findRecentCharactersIds(int $franchiseId, int $limit) : array;
}