<?php declare(strict_types = 1);

namespace App\Model\Repository;

interface ICharacterRepository {
    public function findByFranchiseId(int $franchiseId) : array;
}