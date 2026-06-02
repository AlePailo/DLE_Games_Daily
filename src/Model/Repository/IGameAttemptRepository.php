<?php declare(strict_types = 1);

namespace App\Model\Repository;

interface IGameAttemptRepository {
    public function create(int $gameSessionId, int $guessedCharacterId, int $attemptNumber) : int;
    public function findByGameSessionWithResults(int $gameSessionId) : array;
}