<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\GameSession;

class GameSessionRepository implements IGameSessionRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function findById(int $id) : ?GameSession {
        $stmt = $this->pdo->prepare("SELECT * FROM game_sessions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();

        return $res ? $this->mapGameSession($res) : null;
    }

    public function findByUserAndChallenge(int $userId, int $challengeId): ?GameSession
    {
        $stmt = $this->pdo->prepare("SELECT * FROM game_sessions WHERE user_id = :user_id AND challenge_id = :challenge_id AND DATE(created_at) = CURDATE()");
        $stmt->execute(['user_id' => $userId, 'challenge_id' => $challengeId]);
        $res = $stmt->fetch();

        return $res ? $this->mapGameSession($res) : null;
    }

    public function findByGuestTokenAndChallenge(string $guestToken, int $challengeId): ?GameSession
    {
        $stmt = $this->pdo->prepare("SELECT * FROM game_sessions WHERE guest_token = :guest_token AND challenge_id = :challenge_id AND DATE(created_at) = CURDATE()");
        $stmt->execute(['guestToken' => $guestToken, 'challenge_id' => $challengeId]);
        $res = $stmt->fetch();

        return $res ? $this->mapGameSession($res) : null;
    }

    public function findCompletedByGuestToken(string $guestToken): array
    {
        $stmt = $this->pdo->prepare("SELECT gs.*, dc.franchise_id FROM game_sessions gs INNER JOIN daily_challenges dc WHERE guest_token = :guest_token AND gs.solved = 1 AND DATE(created_at) = CURDATE()");
        $stmt->execute(['guest_token' => $guestToken]);
        return array_map(fn($row) => $this->mapGameSession($row), $stmt->fetchAll());
    }

    public function create(array $data) : int {
        $stmt = $this->pdo->prepare("INSERT INTO game_sessions(user_id, guest_token, challenge_id, attempts_count, solved) VALUES (:user_id, :guest_token, :challenge_id, 0, 0)");
        $stmt->execute(['user_id' => $data['user_id'] ?? null, 'guest_token' => $data['guest_token'] ?? null, 'challenge_id' => $data['challenge_id']]);

        return (int)$this->pdo->lastInsertId();
    }

    public function migrateGuestSessions(string $guestToken, int $userId): void
    {
        $stmt = $this->pdo->prepare("UPDATE game_sessions SET user_id = :user_id, guest_token = null WHERE guest_token = :guest_token AND DATE(created_at) = CURDATE()");
        $stmt->execute(['user_id' => $userId, 'guest_token' => $guestToken]);        
    }

    public function incrementAttempts(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE game_sessions SET attempts_count = attempts_count + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);        
    }

    public function markAsSolved(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE game_sessions SET solved = 1, completed_at = CURDATE() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function markAsCompleted(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE game_sessions SET completed_at = CURDATE() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function mapGameSession(array $row) : GameSession {
        return new GameSession(
            id: (int)$row['id'],
            userId: (int)$row['user_id'],
            guestToken: $row['guest_token'],
            challengeId: (int)$row['challenge_id'],
            attemptsCount: (int)$row['attempts_count'],
            solved: (bool)$row['solved'],
            completed_at: $row['completed_at'] ? new \DateTimeImmutable($row['completed_at']) : null,
            created_at: new \DateTimeImmutable($row['created_at'])
        );
    }
}