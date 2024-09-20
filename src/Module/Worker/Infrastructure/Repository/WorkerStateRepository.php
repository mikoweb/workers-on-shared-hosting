<?php

namespace App\Module\Worker\Infrastructure\Repository;

use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Domain\WorkerStateCollection;

interface WorkerStateRepository
{
    public function findAll(): WorkerStateCollection;
    public function findByName(string $name): ?WorkerState;
}
