<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Entity\DailyChallenge;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;

class DailyChallengeService {
    private const RECENT_EXCLUSION = 10;

    private bool $autoCreate;

    public function __construct(
        private IDailyChallengeRepository $dailyChallengeRepository,
        private ICharacterRepository $characterRepository,
        ?bool $autoCreate = null
    ) {
        $this->autoCreate = $autoCreate ?? filter_var($_ENV['CHALLENGE_AUTO_CREATE'] ?? true, FILTER_VALIDATE_BOOL);
    }

    public function getForDate(int $franchiseId, \DateTimeImmutable $date) : ?DailyChallenge {
        return $this->autoCreate
            ? $this->ensureForDate($franchiseId, $date) 
            : $this->dailyChallengeRepository->findByFranchiseAndDate($franchiseId, $date);
    }

    public function ensureForDate(int $franchiseId, \DateTimeImmutable $date) : ?DailyChallenge {
        $existing = $this->dailyChallengeRepository->findByFranchiseAndDate($franchiseId, $date);
        if($existing != null) {
            return $existing;
        }

        $this->insertRandom($franchiseId, $date);
        return $this->dailyChallengeRepository->findByFranchiseAndDate($franchiseId, $date);
    }

    public function createIfMissing(int $franchiseId, \DateTimeImmutable $date) : ChallengeCreation {
        if($this->dailyChallengeRepository->findByFranchiseAndDate($franchiseId, $date)) {
            return ChallengeCreation::AlreadyExists;
        }

        return $this->insertRandom($franchiseId, $date);
    }

    private function insertRandom(int $franchiseId, \DateTimeImmutable $date) : ChallengeCreation {
        $recentIds = $this->dailyChallengeRepository->findRecentCharactersIds($franchiseId, self::RECENT_EXCLUSION);
        $characterId = $this->characterRepository->findRandomIdByFranchise($franchiseId, $recentIds) 
            ?? $this->characterRepository->findRandomIdByFranchise($franchiseId, []);
        
        if($characterId === null) {
            return ChallengeCreation::NoCharacters;
        }

        return $this->dailyChallengeRepository->create($franchiseId, $characterId, $date)
            ? ChallengeCreation::Created
            : ChallengeCreation::AlreadyExists;
    }
}