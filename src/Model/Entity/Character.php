<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class Character {
    public function __construct(
        private int $id,
        private string $name,
        private string $imageUrl,
        private array $attributes
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getImageUrl() : string {
        return $this->imageUrl;
    }

    public function getAttributes() : array {
        return $this->attributes;
    }

    public function getAttribute($key) : ?string {
        return $this->attributes[$key] ?? null;
    }

    public function hasAttribute($key) : bool {
        return array_key_exists($key, $this->attributes);
    }
}