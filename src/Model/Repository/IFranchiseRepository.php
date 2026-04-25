<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\Franchise;

interface IFranchiseRepository {
    public function findAll() : array;
    public function findBySlug(string $slug) : ?Franchise;
}