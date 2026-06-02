<?php declare(strict_types = 1);

namespace App\Model\Entity;

enum ResultStatus : string {
    case CORRECT = "Correct";
    case PARTIAL = "Partial";
    case WRONG = "Wrong";
}