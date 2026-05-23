<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\GameSession;
use App\Model\Entity\GameAttempt;
use App\Model\Entity\UserFranchiseStats;

interface IGameSessionRepository {
    public function findById(int $id) : ?GameSession;
    public function findByUserAndChallenge(int $userId, int $challengeId) : ?GameSession;
    public function findByGuestTokenAndChallenge(string $guestToken, int $challengeId) : ?GameSession;
    public function findCompletedByGuestToken(string $guestToken) : array;
    public function create(array $data) : int;

    public function migrateGuestSessions(string $guestToken, int $userId) : void;
    public function incrementAttempts(int $id) : void;
    public function markAsSolved(int $id): void;
}