<?php

namespace App\Module\Worker\Infrastructure\Repository;

use App\Module\Worker\Domain\Worker;
use App\Module\Worker\Domain\WorkerCollection;

interface WorkerRepository
{
    public function findAll(): WorkerCollection;
    public function findByName(string $name): ?Worker;
}
