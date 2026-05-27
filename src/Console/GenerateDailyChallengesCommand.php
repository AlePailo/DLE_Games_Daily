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

        foreach($franchises as $franchise) {
            $recentIds = $this->dailyChallengeRepository->findRecentCharactersIds($franchise->getId(), 10);
            $characterId = $this->characterRepository->findRandomIdByFranchise($franchise->getId(), $recentIds);

            if($characterId === null) {
                // TODO: log — franchise with less than 10 characters, should not be happening
                continue;
            }

            $this->dailyChallengeRepository->create($franchise->getId(), $characterId);
        }
    }
}