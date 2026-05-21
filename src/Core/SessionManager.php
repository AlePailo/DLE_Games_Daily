<?php declare(strict_types = 1);

namespace App\Core;

class SessionManager {
    public function __construct() {
        if(session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
            'path' => '/DLE_Games_Daily/public/', // Adatta al tuo BASE_URL
            'httponly' => true,
            'samesite' => 'Lax'
            ]);
            session_start();
        }
    }



    // ----- User session -----

    public function getUserId() : ?int {
        return isset($_SESSION['user_id']) ? (int)($_SESSION['user_id']) : null;
    }

    public function regenerateAndSet(int $userId) : void {
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['user_id'] = $userId;
    }

    public function clearUserSession() : void {
        $_SESSION = [];
        session_destroy();
    }

    public function isLoggedIn() : bool {
        return isset($_SESSION['user_id']);
    }



    // ----- Remember token cookie -----

    public function getRememberToken() : ?string {
        return $_COOKIE['remember_token'] ?? null;
    }

    public function setRememberCookie(string $token, \DateTimeImmutable $expires) : void {
        setcookie('remember_token', $token, [
            'expires' => $expires->getTimestamp(),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public function clearRememberCookie() : void {
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }



    // ----- Guest token cookie -----

    public function getGuestToken() : ?string {
        return $_COOKIE['guest_token'] ?? null;
    }

    public function createGuestToken() : string {
        $token = bin2hex(random_bytes(32));
        setcookie('guest_token', $token, [
            'expires' => strtotime('+30 days'),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        return $token;
    }

    public function getOrCreateGuestToken() : string {
        return $this->getGuestToken() ?? $this->createGuestToken();
    }

    public function clearGuestToken() : void {
        setcookie('guest_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }



    // ----- Flash messages -----

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