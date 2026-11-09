<?php declare(strict_types = 1);

namespace App\Console;

use App\Model\Repository\IFranchiseRepository;
use App\Service\ChallengeCreation;
use App\Service\DailyChallengeService;

class GenerateDailyChallengesCommand {
    public function __construct(
        private IFranchiseRepository $franchiseRepository,
        private DailyChallengeService $dailyChallengeService
    ) {}

    public function execute(int $days = 1) : void {
        $franchises = $this->franchiseRepository->findAllActive();
        $start = new \DateTimeImmutable('today');

        for($i = 0; $i < $days; $i++) {
            $date = $start->modify("+{$i} days");

            foreach($franchises as $franchise) {
                $label = "[{$date->format('Y-m-d')}] {$franchise->getName()}";

                echo match($this->dailyChallengeService->createIfMissing($franchise->getId(), $date)) {
                    ChallengeCreation::Created          => "$label: created\n",
                    ChallengeCreation::AlreadyExists    => "$label: already exists\n",
                    ChallengeCreation::NoCharacters     => "$label: No characters available\n"
                };
            }
        }
    }
}