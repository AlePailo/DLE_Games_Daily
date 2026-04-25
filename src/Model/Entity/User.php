<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class User {
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private string $passwordHash,
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

    public function getCreatedAt() : \DateTimeImmutable {
        return $this->createdAt;
    }
    
    public function getUpdatedAt() : \DateTimeImmutable {
        return $this->updatedAt;
    }

    public function verifyPassword(string $plainPassword) : bool {
        return password_verify($plainPassword, $this->passwordHash);
    }
}