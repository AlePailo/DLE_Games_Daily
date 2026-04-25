<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class GameAttempt {
    public function __construct(
        private int $id,
        private int $sessionId,
        private int $guessedCharacterId,
        //private int $attemptNumber,
        private \DateTimeImmutable $createdAt
    ){}

    public function getId() : int {
        return $this->id;
    }

    public function getSessionId() : int {
        return $this->sessionId;
    }

    public function getGuessedCharacterId() : int {
        return $this->guessedCharacterId;
    }

    /*public function getAttemptNumber() : int {
        return $this->attemptNumber
    }*/
    
    public function getCreatedAt() : \DateTimeImmutable {
        return $this->createdAt;
    }
}