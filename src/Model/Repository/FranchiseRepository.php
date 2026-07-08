<?php declare(strict_types = 1);

namespace App\Model\Repository;

use App\Model\Entity\Franchise;
use App\Model\Entity\AttributeDefinition;

class FranchiseRepository implements IFranchiseRepository {
    public function __construct(
        private \PDO $pdo
    ) {}

    public function findAll(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM franchises ORDER BY name");
        $stmt->execute();
        return array_map([$this, 'mapFranchise'], $stmt->fetchAll());
    }

    public function findAllActive() : array {
        $stmt = $this->pdo->prepare("SELECT * FROM franchises WHERE is_active = true");
        $stmt->execute();
        return array_map([$this, 'mapFranchise'], $stmt->fetchAll());
    }

    public function findBySlug(string $slug): ?Franchise {
        $stmt = $this->pdo->prepare("SELECT * FROM franchises WHERE slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ? $this->mapFranchise($row) : null;
    }

    public function findBySlugWithAttributes(string $slug) : ?Franchise {
        $stmt = $this->pdo->prepare("
        SELECT f.*, ad.id as def_id, ad.attribute_key, ad.attribute_label, ad.display_order
        FROM franchises f LEFT JOIN attribute_definitions ad ON ad.franchise_id = f.id
        WHERE f.slug = :slug
        AND (ad.is_active = 1 OR ad.id IS NULL)
        ORDER BY ad.display_order");
        $stmt->execute(['slug' => $slug]);
        $rows = $stmt->fetchAll();

        if(empty($rows)) return null;

        $attributes = [];
        foreach($rows as $row) {
            if($row['def_id'] !== null) {
                $attributes[] = $this->mapAttributeDefinition($row);
            }
        }

        return $this->mapFranchise($rows[0], $attributes);
    }

    public function findAllFavouritesByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM franchises f INNER JOIN user_favourites uf ON f.id = uf.franchise_id WHERE uf.user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return array_map([$this, 'mapFranchise'], $stmt->fetchAll());
    }


    public function mapFranchise(array $row, array $attributes = []) : Franchise {
        return new Franchise (
            id: (int) $row['id'],
            name: $row['name'],
            slug: $row['slug'],
            description: $row['description'],
            isActive: (bool) $row['is_active'],
            iconUrl: $row['icon_url'],
            bgImageUrl: $row['bg_image_url'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at']),
            attributeDefinitions: $attributes
        );
    }

    public function mapAttributeDefinition(array $row): AttributeDefinition {
        return new AttributeDefinition (
            id: (int) $row['def_id'] ?? $row['id'],
            key: $row['attribute_key'],
            label: $row['attribute_label'],
            displayOrder: (int) $row['display_order']
        );
    }
}