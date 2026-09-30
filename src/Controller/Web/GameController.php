<?php declare(strict_types = 1);

namespace App\Controller\Web;

use App\Controller\WebController;
use App\Core\SessionManager;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Service\GameSessionService;
use App\Model\Repository\IGameAttemptRepository;
use App\Model\Repository\IUserFranchiseStatsRepository;

class GameController extends WebController {
    public function __construct(
        protected SessionManager $sessionManager,
        private IFranchiseRepository $franchiseRepository,
        private ICharacterRepository $characterRepository,
        private IDailyChallengeRepository $dailyChallengeRepository,
        private GameSessionService $gameSessionService,
        private IGameAttemptRepository $gameAttemptRepository,
        private IUserFranchiseStatsRepository $statsRepository
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

        $stats = null;
        $correctChar = null;
        $isCompleted = $gameSession->isCompleted();
        $isSolved = $gameSession->isSolved();
        if($isCompleted) {
            $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());
            $completedData['correct_char'] = [
                'name' => $correctChar->getName(),
                'image_url' => $correctChar->getImageUrl(),
                'attributes' => $correctChar->getAttributes()
            ];
            if($userId !== null) {
                $stats = $this->statsRepository->findByUserAndFranchise($userId, $franchise->getId());
                $completedData['stats'] = [
                    'games_played'   => $stats->getGamesPlayed(),
                    'win_rate'       => $stats->getCompletionRate(),
                    'current_streak' => $stats->getCurrentStreak(),
                    'max_streak'     => $stats->getMaxStreak()
                ];
            }
            if($isSolved) {
                $completedData['attempts_count'] = $gameSession->getAttemptsCount();
            }
        }

        $this->render("game", [
            'title' => "{$franchise->getName()} | DLE Games Daily",
            'css' => ['game.css'],
            'js' => ['game.js'],
            'franchise'         => $franchise,
            'gameSession'       => $gameSession,
            'characters'        => $characters,
            'guessed_chars_ids' => $guessedCharactersIds,
            'is_completed'      => $isCompleted,
            'completed_data'    => $completedData ?? null,
            'previous_guesses'  => $previousGuesses
        ]);
    }
}