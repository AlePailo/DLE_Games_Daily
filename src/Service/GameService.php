<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\DTO\CompletedGameData;
use App\Model\DTO\GameContext;
use App\Model\DTO\UserStatsData;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IDailyChallengeRepository;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\IGameAttemptRepository;
use App\Model\Repository\IUserFranchiseStatsRepository;

class GameService {
    public function __construct(
        private IFranchiseRepository $franchiseRepository,
        private IDailyChallengeRepository $dailyChallengeRepository,
        private GameSessionService $gameSessionService,
        private ICharacterRepository $characterRepository,
        private CharacterComparisonService $comparisonService,
        private IGameAttemptRepository $gameAttemptRepository,
        private IUserFranchiseStatsRepository $statsRepository,
        private UserFranchiseStatsService $statsService
    ) {}

    public function resolveGameContext(string $slug, ?int $userId, ?string $guestToken) {
        $franchise = $this->franchiseRepository->findBySlugWithAttributes($slug);
        if($franchise === null) return new GameContext(null, null, null);

        $dailyChallenge = $this->dailyChallengeRepository->findByFranchiseAndDate($franchise->getId(), new \DateTimeImmutable());
        if($dailyChallenge === null) return new GameContext($franchise, null, null);

        $gameSession = $this->gameSessionService->findSessionContext($dailyChallenge->getId(), $userId, $guestToken);

        return new GameContext($franchise, $dailyChallenge, $gameSession);
    }

    public function getGameViewData(GameContext $context, ?int $userId, ?string $guestToken) : array {
        $franchise = $context->franchise;
        $dailyChallenge = $context->dailyChallenge;
        $gameSession = $context->gameSession
            ?? $this->gameSessionService->getOrCreateSession($dailyChallenge->getId(), $userId, $guestToken);
        
        $characters = $this->characterRepository->findForSearchByFranchise($franchise->getId());

        $previousGuesses = $this->gameAttemptRepository->findAllBySessionId($gameSession->getId());
        $guessedCharIds = $this->gameAttemptRepository->findAllGuessedCharactersBySession($gameSession->getId());

        $isCompleted = $gameSession->isCompleted();
        $completeData = null;

        if($isCompleted) {
            $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());
            $stats = $userId !== null
                ? $this->statsService->buildUserStatsData($userId, $franchise->getId())
                : null;
            $attemptsCount = $gameSession->isSolved() ? $gameSession->getAttemptsCount() : null;
            $completeData = new CompletedGameData($correctChar, $stats, $attemptsCount);
        }

        return [
            'gameSession' => $gameSession,
            'characters' => $characters,
            'guessed_chars_ids' => $guessedCharIds,
            'is_completed' => $isCompleted,
            'completed_data' => $completeData,
            'previous_guesses' => $previousGuesses
        ];
    } 

    public function processAttempt(GameContext $context, int $guessedCharId, ?int $userId) : ?array {
        $franchise = $context->franchise;
        $dailyChallenge = $context->dailyChallenge;
        $gameSession    = $context->gameSession;
 
        $correctCharId = $dailyChallenge->getCharacterId();
        $solved = $guessedCharId === $correctCharId;

        $guessedChar = $this->characterRepository->findByIdWithAttributes($guessedCharId);
        if($guessedChar === null) {
            return ['success' => false, 'message' => 'Character not found'];
        }

        $correctChar = $solved ? $guessedChar : $this->characterRepository->findByIdWithAttributes($correctCharId);
        $comparedResults = $this->comparisonService->compare($guessedChar, $correctChar);
        $attemptNumber = $context->gameSession->getAttemptsCount() + 1;

        $this->gameSessionService->registerAttempt(
            $gameSession->getId(),
            $franchise->getId(),
            $guessedCharId,
            $attemptNumber,
            $comparedResults,
            $solved
        );

        $response = [
            'success' => true,
            'solved' => $solved,
            'attempt' => [
                'character' => [
                    'name' => $guessedChar->getName(),
                    'image_url' => $guessedChar->getImageUrl(),
                    'status' => $solved ? 'Correct' : 'Wrong'
                ],
                'attributes' => $comparedResults
            ]
        ];

        if($solved) {
            if($userId !== null) {
                $this->statsService->upsertOnGameCompletion(
                    $userId,
                    $franchise->getId(),
                    $attemptNumber,
                    true
                );
            }

            $stats = $userId !== null
                ? $this->statsService->buildUserStatsData($userId, $franchise->getId())
                : null;
            
            $response['completed_data'] = new CompletedGameData($correctChar, $stats, $attemptNumber);
        }

        return $response;
    }

    public function processSurrender(GameContext $context, ?int $userId) : ?array {
        $franchise   = $context->franchise;
        $gameSession = $context->gameSession;
        $dailyChallenge = $context->dailyChallenge;
 
        $this->gameSessionService->surrender($gameSession->getId());

        if($userId !== null) {
            $this->statsService->upsertOnGameCompletion(
                $userId,
                $franchise->getId(),
                $gameSession->getAttemptsCount(),
                false
            );
        }

        $correctChar = $this->characterRepository->findByIdWithAttributes($dailyChallenge->getCharacterId());
        $stats = $userId !== null
            ? $this->statsService->buildUserStatsData($userId, $franchise->getId())
            : null;

        return [
            'success' => true,
            'surrender' => true,
            'completed_data' => new CompletedGameData($correctChar, $stats)
        ];
    }

    /*
    private function buildUserStatsData(int $userId, int $franchiseId) : ?UserStatsData {
        $stats = $this->statsRepository->findByUserAndFranchise($userId, $franchiseId);
        if($stats === null) return null;

        return new UserStatsData(
            gamesPlayed: $stats->getGamesPlayed(),
            winRate: $stats->getCompletionRate(),
            currentStreak: $stats->getCurrentStreak(),
            maxStreak: $stats->getMaxStreak()
        );
    }
    */
}