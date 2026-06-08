<?php declare(strict_types = 1);

namespace App\Controller;

use App\Service\AuthService;
use App\Core\SessionManager;
use App\Exception\AuthException;
use App\Service\GameSessionService;

class AuthController extends BaseController {
    public function __construct(
        private AuthService $authService,
        protected SessionManager $sessionManager,
        private GameSessionService $gameSessionService
    ) {
        parent::__construct($sessionManager);
    }

    public function loginForm(array $vars) : void {
        $this->redirectIfLoggedIn();

        //var_dump($_SESSION['test_manuale'] ?? 'Sessione manuale vuota');
        //var_dump($_SESSION['_flash'] ?? 'Flash in sessione vuoto');

        $this->renderAuthForm('login', 'Login | DLE Games Daily');
    }

    public function login(array $vars) : void {
        $this->redirectIfLoggedIn();

        $submittedToken = $_POST['csrf_token'] ?? '';
        if($submittedToken !== $this->sessionManager->getCsrfToken()) {
            $this->sessionManager->setFlash('error', 'Invalid session. Please try again.');
            $this->redirect('login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);


        if(empty($email) || empty($password)) {
            $this->sessionManager->setFlash('error', 'Fill in all fields');
            $this->sessionManager->setFlash('oldInput', ['email' => $email]);
            $this->redirect('login');
            return;
        }

        try {
            $user = $this->authService->loginLocal($email, $password);
            $this->authService->createSessionForUser($user, $remember);
            $this->redirect('');
        } catch(AuthException $e) {
            $this->sessionManager->setFlash('error', $e->getMessage());
            $this->sessionManager->setFlash('oldInput', ['email' => $email]);
            $this->redirect('login');
            return;
        }
    }

    public function registerForm(array $vars) : void {
        $this->redirectIfLoggedIn();

        $this->renderAuthForm('register', 'Register | DLE Games Daily');
    }

    public function register(array $vars) : void {
        $this->redirectIfLoggedIn();

        $submittedToken = $_POST['csrf_token'] ?? '';
        if($submittedToken !== $this->sessionManager->getCsrfToken()) {
            $this->sessionManager->setFlash('error', 'Invalid session. Please try again.');
            $this->redirect('register');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if(empty($username) || empty($email) || empty($password)) {
            $errors[] = 'All fields are required.';
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }

        if(strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $errors[] = "Username must be 3-20 characters (alphanumeric and underscore).";
        }

        if(!empty($errors)) {
            $this->sessionManager->setFlash('error', implode('<br>', $errors));
            $this->sessionManager->setFlash('oldInput', ['username' => $username, 'email' => $email]);
            $this->redirect('register');
            return;
        }

        try {
            $guestToken = $this->sessionManager->getGuestToken();
            $user = $this->authService->registerLocalUser($username, $email, $password);

            if($guestToken !== null) {
                try {
                    $this->gameSessionService->migrateGuestSessions($guestToken, $user->getId());     //TO DO: implement logic to migrate guest session to newly created user
                    $this->sessionManager->clearGuestToken();
                } catch(\Exception $e) {
                    //handle catch - user registered but no sessions to migrate
                }
            }

            $this->sessionManager->setFlash('success', 'Registration completed! Check Your email box.');
            $this->redirect('login');
        } catch(AuthException $e) {
            $this->sessionManager->setFlash('error', $e->getMessage());
            $this->sessionManager->setFlash('oldInput', ['username' => $username, 'email' => $email]);
            $this->redirect('register');
        }
    }

    public function verifyEmail(array $vars) : void {
        $token = $_GET['token'] ?? '';
        if(empty($token)) {
            $this->sessionManager->setFlash('error', 'Missing token');
            $this->redirect('login');
            return;
        }

        try {
            $this->authService->verifyEmail($token);
            $this->sessionManager->setFlash('success', 'Email verified! You can now login.');
            $this->redirect('login');
        } catch(AuthException $e) {
            $message = match($e->getCode()) {
                AuthException::EXPIRED_TOKEN => 'Verification link expired. Request a new one.',
                AuthException::INVALID_TOKEN => 'Verification link is invalid. Request a new one.',
                default => 'An error occurred. Try again.'
            };
            $this->sessionManager->setFlash('error', $message);
            $this->redirect('login');
        }
    }

    public function resendVerification(array $vars) : void {
        $email = trim($_GET['email'] ?? '');

        if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sessionManager->setFlash('error', 'Invalid or missing email address.');
            $this->redirect('login');
            return;
        }

        try {
            $this->authService->resendVerification($email);
            $this->sessionManager->setFlash('success', 'If an account exists with this email, a new verification link has been sent.');
            $this->redirect('login');
        } catch(\Exception $e) {
            $this->sessionManager->setFlash('error', 'Could not send the email. Please try again later.');
            $this->redirect('login');
        }
    }

    public function logout(array $vars) : void {
        $this->authService->logout();
        $this->redirect('login');
    }

    public function checkUsername(array $vars) : void {
        $username = trim($_GET['username'] ?? '');

        $this->renderJson(['available' => !empty($username) && !$this->authService->usernameExists($username)]);
    }

    public function checkEmail(array $vars) : void {
        $email = trim($_GET['email'] ?? '');

        $this->renderJson(['available' => !empty($email) && !$this->authService->emailExists($email)]);
    }

    private function renderAuthForm(string $view, string $title, array $css = ['auth.css'], array $js = ['formHandler.js']) : void {
        $this->render("auth/{$view}", [
            'title' => $title,
            'css' => $css,
            'js' => $js,
            'error' => $this->sessionManager->getFlash('error'),
            'success' => $this->sessionManager->getFlash('success'),
            'info' => $this->sessionManager->getFlash('info'),
            'csrf_token' => $this->sessionManager->getCsrfToken(),
            'old' => $this->sessionManager->getFlash('oldInput')
        ], false);
    }
    
    private function redirectIfLoggedIn() : void {
        if($this->sessionManager->isLoggedIn()) {
            $this->redirect('');
            return;
        }
    }
}