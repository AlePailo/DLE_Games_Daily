<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class Franchise {
    public function __construct(
        private int $id,
        private string $name,
        private ?string $slug,
        private string $description,
        private bool $isActive, 
        private string $iconUrl,
        private string $bgImageUrl,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private array $attributeDefinitions = []
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getSlug() : string {
        return $this->slug;
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function getIsActive() : bool {
        return $this->isActive;
    }

    public function getIconUrl() : string {
        return $this->iconUrl;
    }

    public function getBgImageUrl() : string {
        return $this->bgImageUrl;
    }

    public function getCreatedAt() : \DateTimeImmutable {
        return $this->createdAt;
    }

    public function getUpdatedAt() : \DateTimeImmutable {
        return $this->updatedAt;
    }

    public function getAttributeDefinitions() : array {
        return $this->attributeDefinitions;
    }

    public function hasAttributeDefinitions() : bool {
        return !empty($this->attributeDefinitions);
    }

    public function isNew($days = 30) : bool {
        return $this->createdAt >= new \DateTimeImmutable(" -{$days} days");
    }
}