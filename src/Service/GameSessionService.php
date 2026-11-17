<?php declare(strict_types = 1);

namespace App\Service;

use App\Exception\GameException;
use App\Model\Entity\GameSession;
use App\Model\Repository\IFranchiseRepository;
use App\Model\Repository\IGameSessionRepository;
use App\Model\Repository\IGameAttemptRepository;

class GameSessionService {
    public function __construct(
        private IFranchiseRepository $franchiseRepository,
        private IGameSessionRepository $gameSessionRepository,
        private IGameAttemptRepository $gameAttemptRepository,
    ) {}

    public function lockOrCreateSession(?GameSession $known, ?int $userId, ?string $guestToken, int $challengeId) : GameSession {
        $id = $known?->getId()
            ?? $this->gameSessionRepository->createIfMissing($userId, $guestToken, $challengeId);
        
        $session = $this->gameSessionRepository->findByIdForUpdate($id);
    
        if($session->getUserId() !== $userId || $session->getGuestToken() !== $guestToken) {
            throw GameException::noIdentity();
        }

        return $session;
    }

    public function registerAttempt(int $sessionId, int $franchiseId, int $guessedCharacterId, int $attemptNumber, array $comparedResults, bool $solved) : void {
        $attributeDefsMap = $this->franchiseRepository->getAttributeDefinitionMapByFranchiseId($franchiseId);

        $resultsWithIds = [];
        foreach($comparedResults as $key => $attrData) {
            if (isset($attributeDefsMap[$key])) {
                $attrId = $attributeDefsMap[$key];
                $rawStatus = $attrData['status'] ?? $attrData;
                
                $statusValue = $rawStatus instanceof \BackedEnum
                    ? $rawStatus->value
                    : $rawStatus;

                $resultsWithIds[$attrId] = $statusValue;
            }
        }

        $this->gameAttemptRepository->createWithResults($sessionId, $guessedCharacterId, $attemptNumber, $resultsWithIds);
        $this->gameSessionRepository->updateSessionState($sessionId, $solved);
    }


    public function surrender(int $sessionId) : void {
        $this->gameSessionRepository->markAsCompleted($sessionId);
    }

    public function findSessionContext(int $challengeId, ?int $userId, ?string $guestToken) : ?GameSession {
        if($userId !== null) {
            return $this->gameSessionRepository->findByUserAndChallenge($userId, $challengeId);
        }

        if($guestToken !== null) {
            return $this->gameSessionRepository->findByGuestTokenAndChallenge($guestToken, $challengeId);
        }

        return null;
    }
}