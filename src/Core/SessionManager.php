<?php declare(strict_types = 1);

namespace App\Core;

use App\Model\Entity\User;
use App\Model\Repository\IUserRepository;

class SessionManager {
    public function __construct(
        private IUserRepository $userRepository
    ) {
        if(session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
            'path' => '/DLE_Games_Daily/public/', // Adatta al tuo BASE_URL
            'httponly' => true,
            'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public function login(User $user, bool $remember = false) : void {
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['user_id'] = $user->getId();

        if($remember) {
            $this->createRememberToken($user->getId());
        }
    }

    public function logout() : void {
        $token = $_COOKIE['remember_token'] ?? null;
        if($token !== null) {
            $hashedToken = hash('sha256', $token);
            $this->userRepository->deleteRememberToken($hashedToken);
            $this->clearRememberCookie();
        }

        $_SESSION = [];
        session_destroy();
    }

    public function attemptAutoLogin() : ?User {
        if(isset($_SESSION['user_id'])) {
            return $this->userRepository->findById((int)$_SESSION['user_id']);
        }

        $token = $_COOKIE['remember_token'] ?? null;
        if($token === null) {
            return null;
        }

        $hashedToken = hash('sha256', $token);
        $user = $this->userRepository->findByRememberToken($hashedToken);

        if($user === null) {
            $this->clearRememberCookie();
            return null;
        }

        $this->userRepository->deleteRememberToken($hashedToken);
        $this->createRememberToken($user->getId());

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();

        return $user;
    }

    public function getCurrentUser() : ?User {
        if(!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->userRepository->findById((int)$_SESSION['user_id']);
    }

    public function isLoggedIn() : bool {
        return isset($_SESSION['user_id']);    
    }

    private function createRememberToken(int $userId) : void {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expires = new \DateTimeImmutable('+30 days');

        $this->userRepository->saveRememberToken($userId, $hashedToken, $expires);

        setcookie('remember_token', $token, [
            'expires' => $expires->getTimestamp(),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    private function clearRememberCookie() : void {
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    
    public function setFlash(string $type, mixed $value) : void {
        $_SESSION['_flash'][$type] = $value;
    }

    public function getFlash(string $type) : mixed {
        $value = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $value;
    }

    public function hasFlash(string $type) : bool {
        return isset($_SESSION['_flash'][$type]);
    }

    public function getCsrfToken() : string {
        if(empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}