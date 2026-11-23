<?php declare(strict_types = 1);

namespace App\Core;

class SessionManager {
    public function __construct() {
        if(session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');   // doesn't accept session ID not emitted by the server
            ini_set('session.use_only_cookies', '1');  // never session ID in the url
            session_set_cookie_params($this->cookieOptions());
            session_start();
        }
    }



    // ----- User session -----

    public function getUserId() : ?int {
        return isset($_SESSION['user_id']) ? (int)($_SESSION['user_id']) : null;
    }

    public function getSessionData() : array {
        return [
            'is_logged_in' => $this->isLoggedIn(),
            'username' => $_SESSION['username'] ?? null,
            'user_icon_url' => $_SESSION['user_icon_url'] ?? null
        ];
    }

    public function updateSessionUsername(string $newUsername) : void {
        $_SESSION['username'] = $newUsername;
    }

    public function updateSessionIconUrl(string $newUrl) : void {
        $_SESSION['user_icon_url'] = $newUrl;
    }

    public function updateUserSession(string $newUsername, string $newUrl) : void {
        $_SESSION['username'] = $newUsername;
        $_SESSION['user_icon_url'] = $newUrl;
    }

    public function regenerateAndSet(int $userId, string $username, ?string $userIconUrl) : void {
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['user_icon_url'] = $userIconUrl;
    }

    public function clearUserSession() : void {
        $_SESSION = [];

        if(session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Remove session cookie from browser
        setcookie(session_name(), '', $this->cookieOptions(['expires' => time() - 3600]));
    }

    public function isLoggedIn() : bool {
        return isset($_SESSION['user_id']);
    }



    // ----- Remember token cookie -----

    public function getRememberToken() : ?string {
        return $_COOKIE['remember_token'] ?? null;
    }

    public function setRememberCookie(string $token, \DateTimeImmutable $expires) : void {
        setcookie('remember_token', $token, $this->cookieOptions(['expires' => $expires->getTimestamp()]));
    }

    public function clearRememberCookie() : void {
        setcookie('remember_token', '', $this->cookieOptions(['expires' => time() - 3600]));
    }



    // ----- Guest token cookie -----

    public function getGuestToken() : ?string {
        return $_COOKIE['guest_token'] ?? null;
    }

    public function createGuestToken() : string {
        $token = bin2hex(random_bytes(32));
        setcookie('guest_token', $token, $this->cookieOptions(['expires' => strtotime('+30 days')]));

        return $token;
    }

    public function getOrCreateGuestToken() : string {
        return $this->getGuestToken() ?? $this->createGuestToken();
    }

    public function clearGuestToken() : void {
        setcookie('guest_token', '', $this->cookieOptions(['expires' => time() - 3600]));
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



    // ----- Session helpers -----

    private function cookieOptions(array $extra = []) : array {
        return $extra + [
            'path' => '/',
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
    }

    private function isHttps() : bool {
        $https = strtolower((string)($SERVER['HTTPS'] ?? ''));

        return ($https !== '' && $https !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}