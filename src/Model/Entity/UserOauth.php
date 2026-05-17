<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class UserOauth {
    public function __construct(
        private int $id,
        private int $userId,
        private Provider $provider,
        private string $providerId,
        private \DateTimeImmutable $createdAt
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getUserId() : int {
        return $this->userId;
    }

    public function getProvider() : Provider {
        return $this->provider;
    }

    public function getProviderId() : string {
        return $this->providerId;
    }

    public function getCreatedAt() : \DateTimeImmutable {
        return $this->createdAt;
    }
}