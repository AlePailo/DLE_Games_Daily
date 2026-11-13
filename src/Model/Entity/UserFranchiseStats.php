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
        private float $avgAttempts,
        private ?\DateTimeImmutable $lastWinDate,
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

    public function getAvgAttempts() : float {
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

    public function getSurrenders(): int {
        return $this->gamesPlayed - $this->gamesWon;
    }

    public function updateStats(bool $solved, int $attempts, \DateTimeImmutable $challengeDate) : void {
        $this->gamesPlayed++;

        if(!$solved) {
            $this->currentStreak = 0;
            return;
        }

        /*
        if($wonYesterday) {
            $this->currentStreak++;
        } else {
            $this->currentStreak = 1;
        }

        if($this->currentStreak > $this->maxStreak) {
            $this->maxStreak = $this->currentStreak;
        }

        $this->avgAttempts = (($this->avgAttempts * $this->gamesWon) + $attempts) / ($this->gamesWon + 1);

        $this->gamesWon++;
        */

        $continues = $this->lastWinDate !== null && $this->lastWinDate->format('Y-m-d') === $challengeDate->modify('-1 day')->format('Y-m-d');

        $this->currentStreak = $continues ? $this->currentStreak + 1 : 1;

        if($this->currentStreak > $this->maxStreak) {
            $this->maxStreak = $this->currentStreak;
        }

        $this->avgAttempts = (($this->avgAttempts * $this->gamesWon) + $attempts) / ($this->gamesWon + 1);

        $this->gamesWon++;
        $this->lastWinDate = $challengeDate;
    }

    public function getLastWinDate() : ?\DateTimeImmutable {
        return $this->lastWinDate;
    }

    public function getEffectiveStreak(\DateTimeImmutable $today) : int {
        if($this->lastWinDate === null) {
            return 0;
        }

        $limit = $today->modify('-1 day')->format('Y-m-d');
        return $this->lastWinDate->format('Y-m-d') >= $limit ? $this->currentStreak : 0;
    }
}