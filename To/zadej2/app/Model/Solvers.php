<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

/**
 * @phpstan-type SolverArray array{name: string, class: int, time: double, points: double}
 */
class Solvers extends Model
{

    /**
     * @return SolverArray[]|ActiveRow[]
     */
    public function getAll(): array {
        return $this->explorer->table('solvers')->order('points DESC')->order('time DESC')->limit(10)->fetchAll();
    }

    /**
     * @param SolverArray $data
     * @return int The ID of the inserted row
     */
    public function insert(array $data): int
    {
        $this->explorer->table('solvers')->insert($data);
        return (int)$this->explorer->getInsertId();
    }
}