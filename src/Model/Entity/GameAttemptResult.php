<?php declare(strict_types = 1);

namespace App\Model\Entity;

use LDAP\Result;

final class GameAttemptResult {
    public function __construct(
        private int $id,
        private int $attemptId,
        private int $attributeDefId,
        //private ResultStatus $resultStatus
    ) {}

    public function getId() : int {
        return $this->id;
    }

    public function getAttemptId() : int {
        return $this->attemptId;
    }

    public function getAttributeDefId() : int {
        return $this->attributeDefId;
    }

    /*
    public function getResultStatus() : ResultStatus {
        return $this->resultStatus;
    }
    
    public function isCorrect() : bool {
        return $this->resultStatus === ResultStatus::Correct;
    }

    public function isPartial() : bool {
        return $this->resulStatus === ResultStatus::Partial;
    }

    public function isWrong() : bool {
        return $this->resultStatus === ResultStatus::Wrong;
    }
    */
}