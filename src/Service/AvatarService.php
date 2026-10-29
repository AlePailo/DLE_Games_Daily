<?php declare(strict_types = 1);

namespace App\Service;

class AvatarService {
    private const AVATARS_DIR = 'public/assets/img/avatars/';

    public function getAvailableAvatars() : array {
        $files = glob(BASE_PATH . self::AVATARS_DIR . '*.svg');
        if(empty($files)) {
            throw new \RuntimeException('No avatars found in the avatars directory.');
        }

        sort($files);
        return array_map('basename', $files);
    }

    public function getRandomAvatar() : string {
        $avatars = $this->getAvailableAvatars();
        return $avatars[array_rand($avatars)];  
    }
    
    public function isValid(string $avatar) : bool {
        return in_array($avatar, $this->getAvailableAvatars(), true);
    }
}