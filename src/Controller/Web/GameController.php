<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Service\GameSessionService;
use App\Model\Repository\IGameAttemptRepository;

class GameController extends WebController {
    public function __construct(
        protected SessionManager $sessionManager,
        private IFranchiseRepository $franchiseRepository,
        private ICharacterRepository $characterRepository,
        private IDailyChallengeRepository $dailyChallengeRepository,
        private GameSessionService $gameSessionService,
        private IGameAttemptRepository $gameAttemptRepository,
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
            $this->render("game_unavailable", [
                'title' => "{$franchise->getName()} | DLE Games Daily",
                'css' => ['game.css'],
                'js' => ['game.js'],
                'franchise' => $franchise
            ]);
            return;
        }

        $userId = $this->sessionManager->getUserId() ?? null;
        $guestToken = $userId === null ? $this->sessionManager->getOrCreateGuestToken() : null;
        $gameSession = $this->gameSessionService->getOrCreateSession($dailyChallenge->getId(), $userId, $guestToken);

        $previousGuesses = $this->gameAttemptRepository->findAllBySessionId($gameSession->getId());
        $guessedCharactersIds = $this->gameAttemptRepository->findAllGuessedCharactersBySession($gameSession->getId());

        $characters = $this->characterRepository->findForSearchByFranchise($franchise->getId());

        $correctChar = null;
        if($gameSession->isSolved() || $gameSession->isCompleted()) {
            $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());
        }

        $this->render("game", [
            'title' => "{$franchise->getName()} | DLE Games Daily",
            'css' => ['game.css'],
            'js' => ['game.js'],
            'franchise' => $franchise,
            'gameSession' => $gameSession,
            'characters' => $characters,
            'guessed_chars_ids' => $guessedCharactersIds,
            'is_completed' => $gameSession->isCompleted(),
            'correct_char' => $correctChar,
            'previous_guesses' => $previousGuesses
        ]);
    }
}