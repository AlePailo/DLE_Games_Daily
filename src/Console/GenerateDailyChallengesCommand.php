<?php declare(strict_types = 1);

namespace App\Console;

use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Model\Repository\IFranchiseRepository;

class GenerateDailyChallengesCommand {
    public function __construct(
        private IFranchiseRepository $franchiseRepository,
        private ICharacterRepository $characterRepository,
        private IDailyChallengeRepository $dailyChallengeRepository
    ) {}

    public function execute() : void {
        $franchises = $this->franchiseRepository->findAllActive();
        $today = new \DateTimeImmutable();

        foreach($franchises as $franchise) {
            // Checks if there's already a challenge for today
            $existing = $this->dailyChallengeRepository->findByFranchiseAndDate($franchise->getId(), $today);

            if($existing !== null) {
                echo "Daily challenge already exists for {$franchise->getName()} franchise\n";
                continue;
            }

            $recentIds = $this->dailyChallengeRepository->findRecentCharactersIds($franchise->getId(), 10);
            $characterId = $this->characterRepository->findRandomIdByFranchise($franchise->getId(), $recentIds);

            if($characterId === null) {
                // Fallback: if no characters are available when excluding last 10, try without restrictions (maybe franchise shouldn't exist :/)
                $characterId = $this->characterRepository->findRandomIdByFranchise($franchise->getId(), []);

                if($characterId) {
                    echo "No characters found for {$franchise->getName()}\n";
                    continue;
                }
            }

            $this->dailyChallengeRepository->create($franchise->getId(), $characterId);
            echo "Challenge created for {$franchise->getName()}\n";
        }
    }
}