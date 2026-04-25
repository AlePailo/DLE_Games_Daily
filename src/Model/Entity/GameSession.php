<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class GameSession {
    public function __construct(
        private int $id,
        private int $userId,
        private int $challengeId,
        private int $attemptsCount,
        private bool $solved,
        private ?\DateTimeImmutable $completed_at,
        private \DateTimeImmutable $created_at
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getUserId() : int {
        return $this->userId;
    }

    public function getChallengeId() : int {
        return $this->challengeId;
    }

    public function getAttemptsCount() : int {
        return $this->attemptsCount;
    }

    public function isSolved() : bool {
        return $this->solved;
    }

    public function getCompletedAt() : ?\DateTimeImmutable {
        return $this->completed_at;
    }

    public function getCreatedAt() : \DateTimeImmutable {
        return $this->created_at;
    }
}