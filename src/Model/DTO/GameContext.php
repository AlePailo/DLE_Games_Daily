<?php declare(strict_types = 1); 

namespace App\Model\DTO;

use App\Model\Entity\DailyChallenge;
use App\Model\Entity\Franchise;
use App\Model\Entity\GameSession;

readonly class GameContext {
    public function __construct(
        public ?Franchise $franchise,
        public ?DailyChallenge $dailyChallenge,
        public ?GameSession $gameSession
    ) {}

    public function isValid(): bool {
        return $this->franchise !== null
            && $this->dailyChallenge !== null
            && $this->gameSession !== null;
    }
 
    public function isActive(): bool {
        return $this->isValid()
            && !$this->gameSession->isSolved()
            && !$this->gameSession->isCompleted();
    }
}