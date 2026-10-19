<?php declare(strict_types = 1);

namespace App\Model\DTO;

readonly class UserStatsData implements \JsonSerializable {
    public function __construct(
        public int $gamesPlayed,
        public float $avgAttempts,
        public int $currentStreak,
        public int $maxStreak,
        public int $surrenders
    ){}

    public function jsonSerialize(): array {
        return [
            'games_played' => $this->gamesPlayed,
            'avg_attempts' => $this->avgAttempts,
            'current_streak' => $this->currentStreak,
            'max_streak' => $this->maxStreak,
            'surrenders' => $this->surrenders
        ];
    }
}