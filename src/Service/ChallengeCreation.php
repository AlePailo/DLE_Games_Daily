<?php declare(strict_types = 1);

namespace App\Service;

enum ChallengeCreation {
    case Created;
    case AlreadyExists;
    case NoCharacters;
}