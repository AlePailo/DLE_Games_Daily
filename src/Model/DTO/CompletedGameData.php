<?php declare(strict_types = 1);

namespace App\Model\DTO;

use App\Model\Entity\Character;
use App\Model\DTO\UserStatsData;

readonly class CompletedGameData implements \JsonSerializable {
    public function __construct(
        public Character $character,
        public ?UserStatsData $stats = null,
        public ?int $attemptsCount = null
    ){}

    public function jsonSerialize() : array {
        $data = [
            'correct_char' => [
                'name' => $this->character->getName(),
                'image_url' => $this->character->getImageUrl(),
                'attributes' => $this->character->getAttributes()
            ]
        ];

        if($this->stats !== null) $data['stats'] = $this->stats;
        if($this->attemptsCount !== null) $data['attempts_count'] = $this->attemptsCount;

        return $data;
    }
}