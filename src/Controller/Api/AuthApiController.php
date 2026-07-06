<?php declare(strict_types = 1);

namespace App\Controller\Api;

use App\Controller\ApiController;
use App\Service\AuthService;
use App\Core\SessionManager;

class AuthApiController extends ApiController {
    public function __construct(
        private AuthService $authService,
        protected SessionManager $sessionManager
    ) {
        parent::__construct($sessionManager);
    }

    public function checkUsername(array $vars) : void {
        $username = trim($_GET['username'] ?? '');

        $this->renderJson(['available' => !empty($username) && !$this->authService->usernameExists($username)]);
    }

    public function checkEmail(array $vars) : void {
        $email = trim($_GET['email'] ?? '');

        $this->renderJson(['available' => !empty($email) && !$this->authService->emailExists($email)]);
    }
}