<?php declare(strict_types = 1);

namespace App\Model\Entity;

enum Provider : string {
    case Google = "google";
    case X = "x";
    case Facebook = "facebook";
}