<?php declare(strict_types = 1);

namespace App\Model\Repository;

class UserFavouritesRepository implements IUserFavouritesRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function add(int $userId, int $franchiseId) : void {
        $stmt = $this->pdo->prepare("INSERT INTO user_favourites(user_id, franchise_id) VALUES(:user_id, :franchise_id)");
        $stmt->execute(['user_id' => $userId, 'franchise_id' => $franchiseId]);
    }

    public function remove(int $userId, int $franchiseId) : void {
        $stmt = $this->pdo->prepare("DELETE FROM user_favourites WHERE user_id = :user_id AND franchise_id = :franchise_id");
        $stmt->execute(['user_id' => $userId, 'franchise_id' => $franchiseId]);
    }

    public function isFavourite(int $userId, int $franchiseId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM user_favourites WHERE franchise_id = :franchise_id AND user_id = :user_id");
        $stmt->execute(['user_id' => $userId, 'franchise_id' => $franchiseId]);

        return (bool)$stmt->fetchColumn();
    }

    public function findFranchiseIdsByUser(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT franchise_id FROM user_favourites WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);

        $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return $res;
    }
}