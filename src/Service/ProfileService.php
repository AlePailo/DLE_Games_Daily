<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Repository\IUserRepository;
use App\Service\UserFranchiseStatsService;

class ProfileService {
    public function __construct(
        private UserFranchiseStatsService $statsService,
        private IUserRepository $userRepository
    ){}

    /*
    public function formatUserData(array $sessionData) : array {
        $username = $sessionData['username'];
        $userIconUrl = $sessionData['user_icon_url'];
        
        $user = $this->userRepository->findByUsername($username);
        if($user === null) {
            return [];
        }

        $registrationDate = $user->getCreatedAt();

        return [
            'username' => $username,
            'user_icon_url' => $userIconUrl,
            'registration_date' => $registrationDate
        ];
    }
    */

    public function formatUserData(int $userId) : array {
        $user = $this->userRepository->findById($userId);
        if($user === null) {
            return [];
        }

        return [
            'username' => $user->getId(),
            'user_icon_url' => $user->getUserIconUrl(),
            'registration_date' => $user->getCreatedAt()
        ];
    }



    public function getProfileData(int $userId) {

    }

    public function updateProfile(int $userId, string $username, string $userIconUrl) {

    }

    public function checkUsernameAvailability(string $username, int $currentUserId) {

    }
}