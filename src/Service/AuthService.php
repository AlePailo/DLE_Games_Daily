<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Repository\IUserRepository;
use App\Core\Mailer;
use App\Exception\AuthException;

class AuthService {
    public function __construct(
        private IUserRepository $userRepository,
        private Mailer $mailer
    ) {}

    public function loginLocal(string $email, string $password, /*bool $remember*/) : User {
        $user = $this->userRepository->findByEmail($email);

        if($user === null) {
            throw AuthException::invalidCredentials();
        }

        if(!$user->isVerified()) {
            throw AuthException::emailNotVerified();
        }

        $userPassword = $this->userRepository->findUserPasswordByUserId($user->getId());

        if($userPassword === null || !$userPassword->verifyPassword($password)) {
            throw AuthException::invalidCredentials();
        }

        /*
        if($remember) {
            $this->setRememberToken($user->getId());
        }
        */

        return $user;
    }

    /*
    private function setRememberToken(int $userId) {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expires = new \DateTimeImmutable('+30 days');

        $this->userRepository->saveRememberToken($userId, $hash, $expires);

        setcookie('remember_token', $token, [
            'expires' => $expires->getTimestamp(),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    */

    public function loginOauth(string $provider, string $providerId, string $username, string $email) : User {
        $user = $this->userRepository->findByOAuthProvider($provider, $providerId);

        if($user === null) {
            $user = $this->userRepository->createOAuthUser($username, $email, $provider, $providerId);
        }

        return $user;
    }

    public function usernameExists(string $username) : bool {
        return $this->userRepository->findByUsername($username) !== null;
    }

    public function emailExists(string $email) : bool {
        return $this->userRepository->findByEmail($email) !== null;
    }

    public function registerLocalUser(string $username, string $email, string $plainPassword) : void {
        if($this->usernameExists($username)) {
            throw AuthException::usernameAlreadyTaken();
        }

        if($this->emailExists($email)) {
            throw AuthException::emailAlreadyTaken();
        }

        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $verificationToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $verificationToken);
        $expiresAt = new \DateTimeImmutable('+1 day');

        $createdUserId = $this->userRepository->createLocalUser($username, $email, $hashedPassword, $hashedToken, $expiresAt);

        $this->mailer->sendVerification($email, $verificationToken);
    }

    public function verifyEmail(string $rawToken) : void {
        $hashedToken = hash('sha256', $rawToken);
        $userPassword = $this->userRepository->findUserPasswordByToken($hashedToken);

        if($userPassword === null) {
            throw AuthException::invalidToken();
        }

        if($userPassword->isTokenExpired()) {
            throw AuthException::expiredToken();
        }

        $this->userRepository->markAsVerified($userPassword->getUserId());

        $this->userRepository->removeVerificationToken($userPassword->getUserId());
    }

    public function resendVerification(string $email) : void {
        $user = $this->userRepository->findByEmail($email);

        if($user === null) {
            return;
        }

        if($user->isVerified()) {
            return;
        }

        $newVerificationToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $newVerificationToken);
        $expiresAt = new \DateTimeImmutable('+1 day');

        $this->userRepository->updateVerificationToken($user->getId(), $hashedToken, $expiresAt);

        $this->mailer->sendVerification($email, $newVerificationToken);
    }
}