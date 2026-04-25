<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class UserFranchiseStats {
    public function __construct(
        private int $id,
        private int $userId,
        private int $franchiseId,
        private int $gamesPlayed,
        private int $gamesWon,
        private int $currentStreak,
        private int $maxStreak,
        private int $avgAttempts,
        private \DateTimeImmutable $updatedAt
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getUserId() : int {
        return $this->userId;
    }

    public function getFranchiseId() : int {
        return $this->franchiseId;
    }

    public function getGamesPlayed() : int {
        return $this->gamesPlayed;
    }

    public function getGamesWon() : int {
        return $this->gamesWon;
    }

    public function getCurrentStreak() : int {
        return $this->currentStreak;
    }

    public function getMaxStreak() : int {
        return $this->maxStreak;
    }

    public function getAvgAttempts() : int {
        return $this->avgAttempts;
    }

    public function getUpdatedAt() : \DateTimeImmutable {
        return $this->updatedAt;
    }


    public function getCompletionRate() : ?float {
        if($this->gamesPlayed <= 0) {
            return null;
        }
        
        return round(($this->gamesWon / $this->gamesPlayed) * 100, 1);
    }
}