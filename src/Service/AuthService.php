<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Repository\IUserRepository;
use App\Core\SessionManager;
use App\Core\Mailer;
use App\Exception\AuthException;

class AuthService {
    public function __construct(
        private IUserRepository $userRepository,
        private SessionManager $sessionManager,
        private Mailer $mailer
    ) {}



    // ----- Login Methods -----

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

        return $user;
    }


    public function loginOauth(string $provider, string $providerId, string $username, string $email) : User {
        $user = $this->userRepository->findByOAuthProvider($provider, $providerId);

        if($user === null) {
            $user = $this->userRepository->createOAuthUser($username, $email, $provider, $providerId);
        }

        return $user;
    }



    // ----- Auto-login -----

    public function attemptAutoLogin() : ?User {
        $userId = $this->sessionManager->getUserId();
        if($userId !== null) {
            return $this->userRepository->findById($userId);
        }

        $token = $this->sessionManager->getRememberToken();
        if($token === null) {
            return null;
        }

        $hashedToken = hash('sha256', $token);
        $user = $this->userRepository->findByRememberToken($hashedToken);

        if($user === null) {
            $this->sessionManager->clearRememberCookie();
            return null;
        }

        return $user;
    }



    // ----- Remember me methods -----

    public function createSessionForUser(User $user, bool $remember) : void {
        $this->sessionManager->regenerateAndSet($user->getId(), $user->getUsername(), $user->getUserIconUrl());

        if($remember) {
            $this->createRememberToken($user->getId());
        }
    }


    public function createRememberToken(int $userId) : void {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expires = new \DateTimeImmutable('+30 days');

        $this->userRepository->saveRememberToken($userId, $hashedToken, $expires);
        $this->sessionManager->setRememberCookie($token, $expires);
    }



    // ----- Logout -----

    public function logout() : void {
        $token = $this->sessionManager->getRememberToken();
        if($token !== null) {
            $this->userRepository->deleteRememberToken(hash('sha256,', $token));
            $this->sessionManager->clearRememberCookie();
        }
        
        $this->sessionManager->clearUserSession();
    }



    // ----- Registration -----

    public function registerLocalUser(string $username, string $email, string $plainPassword) : User {
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

        return $this->userRepository->findById($createdUserId);     //Returning user so that controller can use the id for session guest migration
    }



    // ----- Email verification methods -----

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

        if($user === null || $user->isVerified()) {
            return;
        }

        $newVerificationToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $newVerificationToken);
        $expiresAt = new \DateTimeImmutable('+1 day');

        $this->userRepository->updateVerificationToken($user->getId(), $hashedToken, $expiresAt);

        $this->mailer->sendVerification($email, $newVerificationToken);
    }



    // ----- Helpers -----

    public function usernameExists(string $username) : bool {
        return $this->userRepository->findByUsername($username) !== null;
    }


    public function emailExists(string $email) : bool {
        return $this->userRepository->findByEmail($email) !== null;
    }
}