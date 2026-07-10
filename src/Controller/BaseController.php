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
}