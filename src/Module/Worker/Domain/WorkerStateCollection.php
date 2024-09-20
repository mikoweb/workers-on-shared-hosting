<?php

namespace App\Module\Worker\Domain;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<WorkerState>
 */
class WorkerStateCollection extends AbstractCollection
{
    public function getType(): string
    {
        return WorkerState::class;
    }
}
