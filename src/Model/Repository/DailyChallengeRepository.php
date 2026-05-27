<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\DailyChallenge;

class DailyChallengeRepository implements IDailyChallengeRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function findByFranchiseAndDate(int $franchiseId, \DateTimeImmutable $date): ?DailyChallenge
    {
        $stmt = $this->pdo->prepare("SELECT * FROM daily_challenges WHERE franchise_id = :franchise_id AND challenge_date = :challenge_date");
        $stmt->execute(['franchise_id' => $franchiseId, 'challenge_date' => $date->format('Y-m-d')]);
        $res = $stmt->fetch();

        return $res ? $this->mapDailyChallenge($res) : null;
    }

    public function findRecentCharactersIds(int $franchiseId, int $limit) : array {
        $stmt = $this->pdo->prepare("SELECT character_id FROM daily_challenges WHERE franchise_id = :franchise_id ORDER BY challenge_date DESC LIMIT :limit");
        $stmt->execute(['franchise_id' => $franchiseId, 'limit' => $limit]);
        $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $res;
    }

    public function create(int $franchiseId, int $characterId) : void {
        $stmt = $this->pdo->prepare("INSERT INTO daily_challenges(franchise_id, character_id, challenge_date) VALUES (:franchise_id, character_id, CURDATE())");
        $stmt->execute(['franchise_id' => $franchiseId, 'character_id' => $characterId]);
    }

    private function mapDailyChallenge(array $row) : DailyChallenge {
        return new DailyChallenge(
            id: (int)$row['id'],
            franchiseId: (int)$row['franchise_id'],
            characterId: (int)$row['character_id'],
            challengeDate: new \DateTimeImmutable($row['challenge_date']),
            createdAt: new \DateTimeImmutable($row['created_at'])
        );
    }
}