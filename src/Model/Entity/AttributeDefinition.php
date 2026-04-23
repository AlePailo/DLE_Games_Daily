<?php declare(strict_types = 1);

namespace App\Model\Entity;

final class AttributeDefinition {

    public function __construct(
        private string $key,
        private string $label,
        private int $displayOrder
    ) {}

    public function getKey() : string {
        return $this->key;
    }

    public function getLabel() : string {
        return $this->label;
    }

    public function getDisplayOrder() : int {
        return $this->displayOrder;
    }
}