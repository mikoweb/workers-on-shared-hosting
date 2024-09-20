<?php

namespace App\Module\Worker\Infrastructure\Repository;

use App\Module\Worker\Domain\Worker;
use App\Module\Worker\Domain\WorkerCollection;
use App\Module\Worker\Infrastructure\Reader\WorkersReader;
use Ramsey\Collection\Exception\NoSuchElementException;

readonly class YamlWorkerRepository implements WorkerRepository
{
    public function __construct(
        private WorkersReader $workersReader,
    ) {
    }

    public function findAll(): WorkerCollection
    {
        return $this->workersReader->read();
    }

    public function findByName(string $name): ?Worker
    {
        try {
            return $this->workersReader->read()->where('name', $name)->first();
        } catch (NoSuchElementException) {
            return null;
        }
    }
}
