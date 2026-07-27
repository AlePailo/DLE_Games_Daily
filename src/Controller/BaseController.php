<?php declare(strict_types = 1);

namespace App\Controller;

use App\Core\SessionManager;

abstract class BaseController {
    public function __construct(
        protected SessionManager $sessionManager
    ) {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
        }
    }

    /*
    public function validateCsrfToken() : void {
        $submittedToken = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        $savedToken = $this->sessionManager->getCsrfToken();

        // Return if token is valid
        if(!empty($savedToken) && hash_equals($savedToken, $submittedToken)) {
            return;
        }
        
        $isAjax = !empty($_SERVER['HTTP_X_CSRF_TOKEN']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        if($isAjax) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF Token. Request denied']);
            exit;
        }

        $this->sessionManager->setFlash('error', 'Invalid or expired session. Try again.');

        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header("Location: " . $referer);
        exit;
    }
    */

    public function validateCsrfToken() : void {
        $submittedToken = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        $savedToken = $this->sessionManager->getCsrfToken();

        // Se il token è valido, tutto ok
        if(!empty($savedToken) && hash_equals($savedToken, $submittedToken)) {
            return;
        }
        
        // Controlla se è AJAX O se la rotta inizia per /api/
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $isApiRoute = str_contains($uri, '/api/');
        $isAjax = $isApiRoute 
            || !empty($_SERVER['HTTP_X_CSRF_TOKEN']) 
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        if($isAjax) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF Token. Request denied']);
            exit;
        }

        $this->sessionManager->setFlash('error', 'Invalid or expired session. Try again.');

        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header("Location: " . $referer);
        exit;
    }
}