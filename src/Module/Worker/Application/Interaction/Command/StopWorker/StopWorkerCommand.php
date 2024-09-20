<?php

namespace App\Module\Worker\Application\Interaction\Command\StopWorker;

use App\Core\Infrastructure\Interaction\Command\CommandInterface;

readonly class StopWorkerCommand implements CommandInterface
{
    public function __construct(
        public string $workerName,
    ) {
    }
}
