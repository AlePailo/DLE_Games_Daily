<?php declare(strict_types = 1);

namespace App\Core;

final class Transaction {
    public function __construct(private \PDO $pdo) {}

    public function run(callable $work) : mixed {
        $this->pdo->beginTransaction();
        try {
            $result = $work();
            $this->pdo->commit();
            return $result;
        } catch(\Throwable $e) {
            if($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}