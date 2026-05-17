<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class UserPassword {
    public function __construct(
        private int $userId,
        private string $passwordHash,
        private ?string $verificationToken,
        private ?\DateTimeImmutable $tokenExpiresAt
    ) {
        if(($this->verificationToken === null) !== ($this->tokenExpiresAt === null)) {
            throw new \InvalidArgumentException('verificationToken and tokenExpiresAt must both be present or both null');
        }
    }

    public function getUserId() : int {
        return $this->userId;
    }

    public function verifyPassword(string $plainInputPass) : bool {
        return password_verify($plainInputPass, $this->passwordHash);
    }

    public function getVerificationToken() : string {
        return $this->verificationToken;
    }

    public function getTokenExpiresAt() : \DateTimeImmutable {
        return $this->tokenExpiresAt;
    }

    public function isTokenExpired() : bool {
        if($this->tokenExpiresAt === null) {
            return true;
        }

        return $this->tokenExpiresAt < new \DateTimeImmutable();
    }
}