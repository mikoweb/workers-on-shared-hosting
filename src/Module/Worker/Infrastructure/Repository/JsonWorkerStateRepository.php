<?php

namespace App\Module\Worker\Infrastructure\Repository;

use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Domain\WorkerStateCollection;
use App\Module\Worker\Infrastructure\Reader\WorkersStateReader;
use Ramsey\Collection\Exception\NoSuchElementException;

readonly class JsonWorkerStateRepository implements WorkerStateRepository
{
    public function __construct(
        private WorkersStateReader $workersStateReader,
    ) {
    }

    public function findAll(): WorkerStateCollection
    {
        return $this->workersStateReader->read();
    }

    public function findByName(string $name): ?WorkerState
    {
        try {
            return $this->findAll()->where('name', $name)->first();
        } catch (NoSuchElementException) {
            return null;
        }
    }
}
