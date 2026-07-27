<?php declare(strict_types = 1);

namespace App\Model\Repository;

interface IGameAttemptRepository {
    public function create(int $gameSessionId, int $guessedCharacterId, int $attemptNumber) : int;
    public function findByGameSessionWithResults(int $gameSessionId) : array;
    public function findAllGuessedCharactersBySession(int $gameSessionId) : array;

    public function findAllBySessionId(int $sessionId): array;

    public function createWithResults(int $sessionId, int $guessedCharacterId, int $attemptNumber, array $resultsWithIds) : void;
}