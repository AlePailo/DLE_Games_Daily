<?php declare(strict_types = 1);

namespace App\Service;

use App\Model\Entity\Character;
use App\Model\Entity\ResultStatus;

class CharacterComparisonService {
    public function compare(Character $guessed, Character $solution) : array {
        $result = [];

        foreach($solution->getAttributes() as $key => $correctValue) {
            $guessedValue = $guessed->getAttribute($key) ?? '';

            $status = ($guessedValue === $correctValue) ? ResultStatus::CORRECT : ResultStatus::WRONG;

            //TODO: implement partially correct check (future update)

            $result[$key] = [
                'value' => $guessedValue,
                'status' => $status instanceof ResultStatus ? $status->value : $status
            ];
        }

        return $result;
    }
}