<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Exception\AuthException;
use App\Service\AuthService;
use App\Service\ProfileService;
use App\Service\UserFranchiseStatsService;

class ProfileController extends WebController {
    public function __construct(
        protected SessionManager $sessionManager,
        private ProfileService $profileService,
        private UserFranchiseStatsService $statsService,
        private AuthService $authService
    ) {
        parent::__construct($sessionManager);
    }

    public function index(array $vars) : void {
        $userId = $this->sessionManager->getUserId();
        if($userId === null) {
            $this->redirect('login');
            return;
        }

        $userData = $this->profileService->getUserData($userId);
        if($userData === null) {
            $this->authService->logout();
            $this->redirect('login');
            return;
        }

        $aggregatedStats = $this->statsService->getAggregatedStatsByUser($userId);
        $franchiseStats = $this->statsService->getStatsByUser($userId);

        $this->render('profile', [
            'title' => 'Profile | DLE Games Daily',
            'user_data' => $userData,
            'aggregated_stats' => $aggregatedStats,
            'franchise_stats'  => $franchiseStats,
            'css' => [],
            'js' => []
        ]);
    }

    public function update(array $vars) : void {
        $userId = $this->sessionManager->getUserId();
        if($userId === null) {
            $this->redirect('login');
            return;
        }

        $this->validateCsrfToken();

        $username = trim($_POST['username'] ?? '');
        $userIconUrl = $_POST['user_icon_url'] ?? '';

        $user = $this->profileService->getUserData($userId);
        if ($user === null) {
            $this->authService->logout();
            $this->redirect('login');
            return;
        }

        try {
            $this->profileService->updateProfile($userId, $username, $userIconUrl, $user);

            $this->sessionManager->updateUserSession($username, $userIconUrl);

            $this->sessionManager->setFlash('success', 'Profile updated successfully.');
        } catch(AuthException $e) {
            $this->sessionManager->setFlash('error', $e->getMessage());
        }

        $this->redirect('profile');
    }
}