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

    public function findBySlug(string $slug): ?Franchise {
        $stmt = $this->pdo->prepare("SELECT * FROM franchises WHERE slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ? $this->mapFranchise($row) : null;
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
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
    }

    public function mapAttributeDefinition(array $row): AttributeDefinition {
        return new AttributeDefinition (
            id: (int) $row['id'],
            key: $row['attribute_key'],
            label: $row['attribute_label'],
            displayOrder: (int) $row['display_order']
        );
    }
}