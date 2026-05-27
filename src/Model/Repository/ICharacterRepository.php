<?php declare(strict_types = 1);

namespace App\Model\Repository;

interface ICharacterRepository {
    public function findByFranchiseId(int $franchiseId) : array;
    public function findRandomIdByFranchise(int $franchiseId, array $excludedIds): ?int;
}