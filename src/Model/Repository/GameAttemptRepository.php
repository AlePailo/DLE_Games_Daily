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

    public function findAllGuessedCharactersBySession(int $gameSessionId) : array {
        $stmt = $this->pdo->prepare("SELECT guessed_char_id FROM game_attempts WHERE session_id = :session_id");
        $stmt->execute(['session_id' => $gameSessionId]);
        $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $res;
    }



    public function findAllBySessionId(int $sessionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                ga.attempt_number,
                ga.guessed_char_id,
                c.name AS character_name,
                c.image_url AS character_image,
                ad.attribute_key,
                ca.value AS attribute_value,
                gar.result_status
            FROM game_attempts ga
            JOIN characters c ON ga.guessed_char_id = c.id
            LEFT JOIN game_attempt_results gar ON ga.id = gar.attempt_id
            LEFT JOIN attribute_definitions ad ON gar.attribute_def_id = ad.id
            LEFT JOIN character_attributes ca ON ca.attribute_definition_id = ad.id AND ca.character_id = c.id
            WHERE ga.session_id = :session_id
            ORDER BY ga.attempt_number ASC
        ");

        $stmt->execute(['session_id' => $sessionId]);
        $rows = $stmt->fetchAll();

        $attempts = [];

        foreach ($rows as $row) {
            $currentNumber = (int)$row['attempt_number'];

            // Se è la prima riga di questo tentativo, inizializziamo la struttura base
            if (!isset($attempts[$currentNumber])) {
                $attempts[$currentNumber] = [
                    'character' => [
                        'name'      => $row['character_name'],
                        'image_url' => $row['character_image'],
                        'status'    => 'Correct'
                    ],
                    'attributes' => []
                ];
            }

            // Aggiungiamo l'attributo corrente all'array interno
            if ($row['attribute_key'] !== null) {
                $status = $row['result_status'] ?? 'Wrong';

                $attempts[$currentNumber]['attributes'][$row['attribute_key']] = [
                    'value'  => $row['attribute_value'] ?? '',
                    'status' => $status
                ];

                if($status !== 'correct') {
                    $attempts[$currentNumber]['character']['status'] = 'Wrong';
                }
            }
        }

        // Ritorna l'array resettando le chiavi (0, 1, 2...) così in JSON diventa una lista [] e non un oggetto {}
        return array_values($attempts);
    }


    public function createWithResults(int $sessionId, int $guessedCharacterId, int $attemptNumber, array $resultsWithIds) : void {
        $sqlAttempt = $this->pdo->prepare("INSERT INTO game_attempts(session_id, guessed_char_id, attempt_number) VALUES(:session_id, :guessed_char_id, :attempt_number)");
        $sqlAttempt->execute(['session_id' => $sessionId, 'guessed_char_id' => $guessedCharacterId, 'attempt_number' => $attemptNumber]);

        $attemptId = (int)$this->pdo->lastInsertId();

        $sqlResult = $this->pdo->prepare("INSERT INTO game_attempt_results(attempt_id, attribute_def_id, result_status) VALUES(:attempt_id, :attribute_def_id, :result_status)");
        foreach($resultsWithIds as $attributeDefId => $statusValue) {
            $sqlResult->execute(['attempt_id' => $attemptId, 'attribute_def_id' => $attributeDefId, 'result_status' => $statusValue]);
        }
    }
}