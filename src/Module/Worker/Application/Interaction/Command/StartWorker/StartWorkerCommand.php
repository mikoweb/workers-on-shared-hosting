<?php

namespace App\Module\Worker\Application\Interaction\Command\StartWorker;

use App\Core\Infrastructure\Interaction\Command\CommandInterface;

readonly class StartWorkerCommand implements CommandInterface
{
    public function __construct(
        public string $workerName,
    ) {
    }
}
