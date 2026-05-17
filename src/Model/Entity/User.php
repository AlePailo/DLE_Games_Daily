<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class User {
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private bool $isVerified,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getUsername() : string {
        return $this->username;
    }

    public function getEmail() : string {
        return $this->email;
    }

    public function isVerified() : bool {
        return $this->isVerified;
    }

    public function getCreatedAt() : \DateTimeImmutable {
        return $this->createdAt;
    }
    
    public function getUpdatedAt() : \DateTimeImmutable {
        return $this->updatedAt;
    }
}