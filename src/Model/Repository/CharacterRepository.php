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

    public function findRandomIdByFranchise(int $franchiseId, array $excludedIds) : ?int {
        if(empty($excludedIds)) {
            $stmt = $this->pdo->prepare("SELECT id FROM characters WHERE franchise_id = :franchise_id ORDER BY RAND() LIMIT 1");
            $stmt->execute(['franchise_id' => $franchiseId]);
        } else {
            $placeholders = implode(',', array_fill(0, count($excludedIds), '?'));

            $stmt = $this->pdo->prepare("SELECT id FROM characters WHERE franchise_id = ? AND id NOT IN($placeholders) ORDER BY RAND() LIMIT 1");
            $stmt->execute(array_merge([$franchiseId], $excludedIds));
        }
        
        $res = $stmt->fetchColumn();
        return $res !== false ? (int)$res : null;
    }

    public function findByIdWithAttributes(int $id) : ?Character {
        $stmt = $this->pdo->prepare("SELECT c.id, c.name, c.image_url, ad.attribute_key, ca.value
                                        FROM characters c
                                        JOIN character_attributes ca ON ca.characters_id = c.id
                                        JOIN attribute_definitions ad ON ad.id = ca.attribute_definition_id
                                        WHERE c.id = :id
                                        ORDER BY ad.display_order");
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll();

        if(empty($rows)) return null;

        return $this->mapCharacter([
            'id' => $rows[0]['id'],
            'name' => $rows[0]['name'],
            'image_url' => $rows[0]['image_url'],
            'attributes' => array_column($rows, 'value', 'attribute_key')
        ]);
    }

    private function mapCharacters(array $rows) : array {
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

    private function mapCharacter(array $data) : Character {
        return new Character (
            id: (int)$data['id'],
            name: $data['name'],
            imageUrl: $data['image_url'],
            attributes: $data['attributes']
        );
    }
}