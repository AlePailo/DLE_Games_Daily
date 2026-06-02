<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\GameAttemptResult;
use App\Model\Entity\ResultStatus;

class GameAttemptResultRepository implements IGameAttemptResultRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function createMany(int $attemptId, array $results): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO game_attempt_results(attempt_id, attribute_def_id, result_status) VALUES(:attempt_id, :attribute_def_id, :result_status)");
        
        foreach($results as $attributeDefId => $resultStatus) {
            $stmt->execute(['attempt_id' => $attemptId, 'attribute_def_id' => $attributeDefId, 'result_status' => $resultStatus->value]);
        }
    }

    /*
    public function findByAttemptId(int $attemptId) : array {
        $stmt = $this->pdo->prepare("SELECT * FROM game_attempt_results WHERE attempt_id = :attempt_id");
        $stmt->execute(['attempt_id' => $attemptId]);

        return array_map([$this, 'mapGameAttemptResults'], $stmt->fetchAll());
    }

    private function mapGameAttemptResults(array $row) : GameAttemptResult {
        return new GameAttemptResult(
            id: (int)$row['id'],
            attemptId: (int)$row['attempt_id'],
            attributeDefId: (int)$row['attribute_def_id'],
            resultStatus: ResultStatus::from($row['result_status'])
        );
    }
    */
}