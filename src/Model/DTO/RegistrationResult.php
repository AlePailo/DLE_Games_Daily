<?php declare(strict_types = 1);

namespace App\Model\DTO;

use App\Model\Entity\User;

readonly class RegistrationResult {
    public function __construct(
        public User $user,
        public bool $verificationEmailSent
    ) {}
}