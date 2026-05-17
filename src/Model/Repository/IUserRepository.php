<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\User;
use App\Model\Entity\UserPassword;

interface IUserRepository {
    public function findById(int $id) : ?User;
    public function findByEmail(string $email) : ?User;
    public function findByUsername(string $username) : ?User;
    public function findByRememberToken(string $hashedToken) : ?User;

    //Find user associated hashed pass for local login
    public function findUserPasswordByUserId(int $userId) : ?UserPassword;
    public function findUserPasswordByToken(string $token): ?UserPassword;

    //OAuthLogin
    public function findByOAuthProvider(string $provider, string $provider_id) : ?User;

    public function createLocalUser(string $username, string $email, string $passwordHash, string $token, \DateTimeImmutable $expiresAt) : int;
    public function createOAuthUser(string $username, string $email, string $provider, string $providerId) : int;

    public function markAsVerified(int $id) : void;
    public function updateVerificationToken(int $userId, string $hashedToken, \DateTimeImmutable $expiresAt) : void;
    public function removeVerificationToken(int $userId) : void;

    public function saveRememberToken(int $userId, string $hash, \DateTimeImmutable $expires) : void;
    public function deleteRememberToken(string $hashedToken) : void;
}