<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Repository\IUserRepository;
use App\Model\Entity\User;

class ProfileService {
    public function __construct(
        private IUserRepository $userRepository,
        private AvatarService $avatarService
    ){}

    public function getUserData(int $userId) : ?User {
        return $this->userRepository->findById($userId);
    }

    public function updateProfile(int $userId, string $username, string $userIconUrl, User $currentUser): bool {
        $usernameChanged = $username !== $currentUser->getUsername();
        $avatarChanged   = $userIconUrl !== $currentUser->getUserIconUrl();

        if (!$usernameChanged && !$avatarChanged) {
            return false;
        }

        if ($usernameChanged && !$this->checkUsernameAvailability($username)) {
            throw \App\Exception\AuthException::usernameAlreadyTaken();
        }

        if ($avatarChanged && !$this->avatarService->isValid($userIconUrl)) {
            throw \App\Exception\AuthException::invalidAvatar();
        }

        $this->userRepository->updateProfile($userId, $username, $userIconUrl);
        return true;
    }

    public function checkUsernameAvailability(string $username) : bool {
        return $this->userRepository->findByUsername($username) === null;
    }
}