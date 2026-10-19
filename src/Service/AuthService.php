<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Repository\IUserRepository;
use App\Core\SessionManager;
use App\Core\Mailer;
use App\Exception\AuthException;
use App\Model\Repository\IGameSessionRepository;

class AuthService {
    public function __construct(
        private \PDO $pdo,
        private GameSessionService $gameSessionService,
        private UserFranchiseStatsService $statsService,
        private IUserRepository $userRepository,
        private SessionManager $sessionManager,
        private IGameSessionRepository $gameSessionRepository,
        private Mailer $mailer
    ) {}



    // ----- Login Methods -----

    public function loginLocal(string $email, string $password) : User {
        $user = $this->userRepository->findByEmail($email);

        if($user === null) {
            throw AuthException::invalidCredentials();
        }

        $userPassword = $this->userRepository->findUserPasswordByUserId($user->getId());

        if($userPassword === null || !$userPassword->verifyPassword($password)) {
            throw AuthException::invalidCredentials();
        }

        if(!$user->isVerified()) {
            throw AuthException::emailNotVerified();
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

        $this->sessionManager->regenerateAndSet(
            $user->getId(), 
            $user->getUsername(), 
            $user->getUserIconUrl()
        );

        // ROTATE TOKEN: deletes old token and creates a new one for the current device
        $this->rotateRememberToken($user->getId(), $hashedToken);

        return $user;
    }

        public function rotateRememberToken(int $userId, string $oldHashedToken): void 
    {
        $this->userRepository->deleteRememberToken($oldHashedToken);

        $this->createRememberToken($userId);
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
            $this->userRepository->deleteRememberToken(hash('sha256', $token));
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

        try {
            $this->pdo->beginTransaction();
            $this->userRepository->markAsVerified($userPassword->getUserId());
            $this->userRepository->removeVerificationToken($userPassword->getUserId());
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    public function resendVerification(string $email) : void {
        $user = $this->userRepository->findByEmail($email);

        if($user === null || $user->isVerified()) {
            return;
        }

        $userPassword = $this->userRepository->findUserPasswordByUserId($user->getId());
    
        if ($userPassword === null) {
            return;
        }

        // 2 minutes cooldown before another verification email request
        if ($userPassword->getVerificationSentAt() !== null) {
            $cooldown = new \DateTimeImmutable('-2 minutes');
            if ($userPassword->getVerificationSentAt() > $cooldown) {
                throw AuthException::resendCooldown();
            }
        }

        $newVerificationToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $newVerificationToken);
        $expiresAt = new \DateTimeImmutable('+1 day');

        $this->userRepository->updateVerificationToken($user->getId(), $hashedToken, $expiresAt);

        $this->mailer->sendVerification($email, $newVerificationToken);
    }



    // ----- Guest data migration -----

    public function migrateGuestData(string $guestToken, int $userId): void {
        try {
            $this->pdo->beginTransaction();
            $this->gameSessionService->migrateGuestSessions($guestToken, $userId);
            $this->statsService->insertFromMigratedSessions($userId);
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }



    // ----- Helpers -----

    public function usernameExists(string $username) : bool {
        return $this->userRepository->findByUsername($username) !== null;
    }


    public function emailExists(string $email) : bool {
        return $this->userRepository->findByEmail($email) !== null;
    }



    public function hasSessionsToMigrate(string $guestToken) : bool {
        return count($this->gameSessionRepository->findCompletedByGuestToken($guestToken)) > 0;
    }
}