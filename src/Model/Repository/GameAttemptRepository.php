<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\GameAttempt;
use App\Model\Entity\ResultStatus;

class GameAttemptRepository implements IGameAttemptRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function create(int $gameSessionId, int $guessedCharacterId, int $attemptNumber) : int {
        $stmt = $this->pdo->prepare("INSERT INTO game_attempts(session_id, guessed_char_id, attempt_number) VALUES(:session_id, :guessed_char_id, :attempt_number)");
        $stmt->execute(['session_id' => $gameSessionId, 'guessed_char_id' => $guessedCharacterId, 'attempt_number' => $attemptNumber]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findByGameSessionWithResults(int $gameSessionId) : array {
        $stmt = $this->pdo->prepare("SELECT ga.id, ga.session_id, ga.guessed_char_id, ga.attempt_number, ga.created_at, gar.attribute_def_id, gar.result_status
                                    FROM game_attempts ga LEFT JOIN game_attempt_results gar ON ga.id = gar.attempt_id
                                    WHERE ga.session_id = :session_id
                                    ORDER BY ga.attempt_number ASC");
        $stmt->execute(['session_id' => $gameSessionId]);
        $rows = $stmt->fetchAll();

        if(empty($rows)) return [];

        $grouped = [];
        foreach($rows as $row) {
            $id = (int)$row['id'];
            if(!isset($grouped[$id])) {
                $grouped[$id] = [
                'id' => $id,
                'session_id' => (int)$row['session_id'],
                'guessed_char_id' => (int)$row['guessed_char_id'],
                'attempt_number' => (int)$row['attempt_number'],
                'created_at' => new \DateTimeImmutable($row['created_at']),
                'results' => []
                ];
            }
            if($row['attribute_def_id'] != null) {
                $grouped[$id]['results'][$row['attribute_def_id']] = ResultStatus::from($row['result_status']);
            }
        }

        return array_map([$this, 'mapGameAttemptWithResult'], $grouped);
    }

    private function mapGameAttemptWithResult(array $data) : GameAttempt {
        return new GameAttempt(
            id: $data['id'],
            sessionId: $data['session_id'],
            guessedCharacterId: $data['guessed_character_id'],
            attemptNumber: $data['attempt_number'],
            createdAt: $data['created_at'],
            results: $data['results']
        );
    }

    /*
    public function findByGameSession(int $gameSessionId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM game_attempts WHERE session_id = :session_id");
        $stmt->execute(['session_id' => $gameSessionId]);
        return array_map([$this, 'mapGameAttempt'], $stmt->fetchAll());
    }

    private function mapGameAttempt(array $row) : GameAttempt {
        return new GameAttempt(
            id: (int)$row['id'],
            sessionId: (int)$row['session_id'],
            guessedCharacterId: (int)$row['guessed_char_id'],
            attemptNumber: (int)$row['attempt_number'],
            createdAt: new \DateTimeImmutable($row['created_at'])
        );
    }
    */
}