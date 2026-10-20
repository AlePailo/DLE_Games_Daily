<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Repository\IUserRepository;
use App\Model\Entity\User;

class ProfileService {
    public function __construct(
        private IUserRepository $userRepository
    ){}

    public function getUserData(int $userId) : ?User {
        return $this->userRepository->findById($userId);
    }

    public function updateProfile(int $userId, string $username, string $userIconUrl, User $currentUser): void {
        if ($username === $currentUser->getUsername() && $userIconUrl === $currentUser->getUserIconUrl()) {
            return;
        }

        if ($username !== $currentUser->getUsername()) {
            if (!$this->checkUsernameAvailability($username)) {
                throw \App\Exception\AuthException::usernameAlreadyTaken();
            }
        }

        $this->userRepository->updateProfile($userId, $username, $userIconUrl);
    }

    public function checkUsernameAvailability(string $username) : bool {
        return $this->userRepository->findByUsername($username) === null;
    }
}