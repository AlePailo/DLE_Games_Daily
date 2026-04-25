<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class DailyChallenge {

    public function __construct(
        private int $id,
        private int $franchiseId,
        private int $characterId,
        private \DateTimeImmutable $challengeDate,
        private \DateTimeImmutable $createdAt
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getFranchiseId() : int {
        return $this->franchiseId;
    }

    public function getCharacterId() : int {
        return $this->characterId;
    }

    public function getChallengeDate() : \DateTimeImmutable {
        return $this->challengeDate;
    }

    public function getCreatedAt() : \DateTimeImmutable {
        return $this->createdAt;
    }
}