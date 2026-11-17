<?php declare(strict_types = 1);

namespace App\Service;

use App\Core\Transaction;
use App\Exception\GameException;
use App\Model\DTO\CompletedGameData;
use App\Model\DTO\GameContext;
use App\Model\Repository\ICharacterRepository;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\IGameAttemptRepository;

class GameService {
    public function __construct(
        private IFranchiseRepository $franchiseRepository,
        private DailyChallengeService $dailyChallengeService,
        private GameSessionService $gameSessionService,
        private ICharacterRepository $characterRepository,
        private CharacterComparisonService $comparisonService,
        private IGameAttemptRepository $gameAttemptRepository,
        private UserFranchiseStatsService $statsService,
        private Transaction $transaction
    ) {}

    public function resolveGameContext(string $slug, ?int $userId, ?string $guestToken) {
        $franchise = $this->franchiseRepository->findBySlugWithAttributes($slug);
        if($franchise === null) return new GameContext(null, null, null);

        $dailyChallenge = $this->dailyChallengeService->getForDate($franchise->getId(), new \DateTimeImmutable());
        if($dailyChallenge === null) return new GameContext($franchise, null, null);

        $gameSession = $this->gameSessionService->findSessionContext($dailyChallenge->getId(), $userId, $guestToken);

        return new GameContext($franchise, $dailyChallenge, $gameSession);
    }

    public function getGameViewData(GameContext $context, ?int $userId) : array {
        $franchise = $context->franchise;
        $dailyChallenge = $context->dailyChallenge;
        $gameSession = $context->gameSession;       // null until user takes the first guess
        
        $characters = $this->characterRepository->findForSearchByFranchise($franchise->getId());
        $previousGuesses = $gameSession !== null ? $this->gameAttemptRepository->findAllBySessionId($gameSession->getId()) : [];
        $guessedCharIds = $gameSession !== null ? $this->gameAttemptRepository->findAllGuessedCharactersBySession($gameSession->getId()) : [];
        $isCompleted = $gameSession?->isCompleted() ?? false;
        
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

    public function processAttempt(GameContext $context, int $guessedCharId, ?int $userId, ?string $guestToken) : ?array {
        if($userId === null && $guestToken === null) {
            throw GameException::noIdentity();
        }

        $franchise = $context->franchise;
        $dailyChallenge = $context->dailyChallenge;

        $guessedChar = $this->characterRepository->findByIdWithAttributes($guessedCharId);
        if($guessedChar === null) {
            throw GameException::characterNotFound();
        }
 
        $correctCharId = $dailyChallenge->getCharacterId();
        $solved = $guessedCharId === $correctCharId;
        $correctChar = $solved ? $guessedChar : $this->characterRepository->findByIdWithAttributes($correctCharId);
        $comparedResults = $this->comparisonService->compare($guessedChar, $correctChar);
        
        // Attempt, session state and stats update, all tied to the transaction. In case of an error everything is rolled back
        $attemptNumber = $this->transaction->run(function () use ($context, $userId, $guestToken, $franchise, $dailyChallenge, $guessedCharId, $comparedResults, $solved) {
            $session = $this->gameSessionService->lockOrCreateSession(
                $context->gameSession,
                $userId,
                $guestToken,
                $dailyChallenge->getId()
            );

            if($session->isCompleted()) {
                throw GameException::alreadyCompleted();
            }

            $attemptNumber = $session->getAttemptsCount() + 1;

            try {
                $this->gameSessionService->registerAttempt(
                    $session->getId(),
                    $franchise->getId(),
                    $guessedCharId,
                    $attemptNumber,
                    $comparedResults,
                    $solved
                );
            } catch(\PDOException $e) {
                // 1062 = UNIQUE constraint violation: character already guessed in this game session
                if((int)($e->errorInfo[1] ?? 0) === 1062) {
                    throw GameException::alreadyGuessed();
                }
                throw $e;
            }

            if($solved && $userId !== null) {
                $this->statsService->upsertOnGameCompletion(
                    $userId,
                    $franchise->getId(),
                    $attemptNumber,
                    true,
                    $dailyChallenge->getChallengeDate()
                );
            }

            return $attemptNumber;
        });

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
            $stats = $userId !== null
                ? $this->statsService->buildUserStatsData($userId, $franchise->getId())
                : null;

            $response['completed_data'] = new CompletedGameData($correctChar, $stats, $attemptNumber);
        }

        return $response;
    }

    public function processSurrender(GameContext $context, ?int $userId, ?string $guestToken) : ?array {
        if($userId === null && $guestToken === null) {
            throw GameException::noIdentity();
        }

        $franchise   = $context->franchise;
        $dailyChallenge = $context->dailyChallenge;

        $this->transaction->run(function () use ($context, $userId, $guestToken, $franchise, $dailyChallenge) {
            $session = $this->gameSessionService->lockOrCreateSession(
                $context->gameSession,
                $userId,
                $guestToken,
                $dailyChallenge->getId()
            );

            if($session->isCompleted()) {
                throw GameException::alreadyCompleted();
            }

            $this->gameSessionService->surrender($session->getId());

            if($userId !== null) {
                $this->statsService->upsertOnGameCompletion(
                    $userId,
                    $franchise->getId(),
                    $session->getAttemptsCount(),
                    false,
                    $dailyChallenge->getChallengeDate()
                );
            }
        });

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
}