<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\Character;

interface ICharacterRepository {
    public function findByFranchiseId(int $franchiseId) : array;
}