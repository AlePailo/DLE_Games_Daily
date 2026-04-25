<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\Character;

class CharacterRepository implements ICharacterRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function findByFranchiseId(int $franchiseId): array {
        $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.image_url, ad.attribute_key, ca.value
                                        FROM characters c
                                        JOIN character_attributes ca ON ca.character_id = c.id
                                        JOIN attribute_definitions ad ON ad.id = ca.attribute_definition_id
                                        WHERE c.franchise_id = :franchise_id
                                        ORDER BY ad.display_order");
        $stmt->execute(['franchise_id' => $franchiseId]);

        return $this->mapCharacters($stmt->fetchAll());
    }

    public function mapCharacters(array $rows) : array {
        $grouped = [];
        foreach($rows as $row) {
            $id = (int) $row['id'];
            if(!isset($grouped[$id])) {
                $grouped[$id] = [
                    'id' => $id,
                    'name' => $row['name'],
                    'image_url' => $row['image_url'],
                    'attributes' => []
                ];
            }
            $grouped[$id]['attributes'][$row['attribute_key']] = $row['value'];
        }

        return array_map([$this, 'mapCharacter'], $grouped);
    }

    public function mapCharacter(array $data) : Character {
        return new Character (
            id: $data['id'],
            name: $data['name'],
            imageUrl: $data['image_url'],
            attributes: $data['attributes']
        );
    }
}