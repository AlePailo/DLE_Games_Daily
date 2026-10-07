<?php declare(strict_types = 1);

namespace App\Model\DTO;

readonly class UserStatsData implements \JsonSerializable {
    public function __construct(
        public int $gamesPlayed,
        public float $winRate,
        public int $currentStreak,
        public int $maxStreak
    ){}

    public function jsonSerialize(): array {
        return [
            'games_played' => $this->gamesPlayed,
            'win_rate' => $this->winRate,
            'current_streak' => $this->currentStreak,
            'max_streak' => $this->maxStreak
        ];
    }
}