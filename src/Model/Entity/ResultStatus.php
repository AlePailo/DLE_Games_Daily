<?php declare(strict_types = 1);

namespace App\Model\Entity;

enum ResultStatus : string {
    case Correct = "Correct";
    case Partial = "Partial";
    case Wrong = "Wrong";
}