<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Model\Entity\DailyChallenge;
use App\Model\Entity\GameSession;
use App\Model\Entity\Franchise;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Service\GameSessionService;
use App\Model\Repository\IGameAttemptRepository;
use App\Model\Repository\IGameAttemptResultRepository;
use App\Service\CharacterComparisonService;
use App\Model\Repository\IGameSessionRepository;

class GameController extends WebController {
    public function __construct(
        SessionManager $sessionManager,
        private IFranchiseRepository $franchiseRepository,
        private ICharacterRepository $characterRepository,
        private IDailyChallengeRepository $dailyChallengeRepository,
        private GameSessionService $gameSessionService,
        private IGameAttemptRepository $gameAttemptRepository,
        private CharacterComparisonService $comparisonService,
        private IGameSessionRepository $gameSessionRepository,
        private IGameAttemptResultRepository $gameAttemptResultRepository
    ) {
        parent::__construct($sessionManager);
    }

    public function start(array $vars) : void {
        $slug = $vars['slug'] ?? '';
        
        $franchise = $this->franchiseRepository->findBySlugWithAttributes($slug);
        if($franchise === null) {
            $this->notFound();
            return;
        }

        $dailyChallenge = $this->dailyChallengeRepository->findByFranchiseAndDate($franchise->getId(), new \DateTimeImmutable());
        if($dailyChallenge === null) {
            //...
        }

        $userId = $this->sessionManager->getUserId();
        $guestToken = $userId === null ? $this->sessionManager->getOrCreateGuestToken() : null;
        $gameSession = $this->gameSessionService->getOrCreateSession($dailyChallenge->getId(), $userId, $guestToken);

        $attempts = $this->gameAttemptRepository->findByGameSessionWithResults($gameSession->getId());

        $characters = $this->characterRepository->findByFranchiseId($franchise->getId());

        $correctChar = null;
        if($gameSession->isSolved() || $gameSession->isCompleted()) {
            $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());
        }

        $this->render("game", [
            'title' => "{$slug} | DLE Games Daily",
            /*'css' => ['game.css'],
            'js' => ['game.js']*/
            'franchise' => $franchise,
            'gameSession' => $gameSession,
            'characters' => $characters,
            'show_recap' => $gameSession->isSolved() || $gameSession->isCompleted(),
            'correct_char' => $correctChar,
            'attempts' => $attempts
        ]);
    }
}