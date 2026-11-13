<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\GameSession;
use App\Model\Entity\GameAttempt;
use App\Model\Entity\UserFranchiseStats;

interface IGameSessionRepository {
    public function findById(int $id) : ?GameSession;
    public function findByUserAndChallenge(int $userId, int $challengeId) : ?GameSession;
    public function findByGuestTokenAndChallenge(string $guestToken, int $challengeId) : ?GameSession;
    public function create(array $data) : int;

    public function updateSessionState(int $id, bool $solved) : void;
    public function markAsCompleted(int $id) : void;

    public function findMigratable(string $guestToken, int $userId, \DateTimeImmutable $today) : array;
    public function assignToUser(array $sessionIds, int $userId) : void;
}