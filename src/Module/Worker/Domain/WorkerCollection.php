<?php

namespace App\Module\Worker\Domain;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Worker>
 */
class WorkerCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Worker::class;
    }
}
