<?php declare(strict_types = 1);

namespace App\Model\Repository;

interface IGameAttemptResultRepository {
    public function createMany(int $attemptId, array $results) : void;
}