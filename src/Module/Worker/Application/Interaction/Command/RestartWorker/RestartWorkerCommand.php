<?php

namespace App\Module\Worker\Application\Interaction\Command\RestartWorker;

use App\Core\Infrastructure\Interaction\Command\CommandInterface;

readonly class RestartWorkerCommand implements CommandInterface
{
    public function __construct(
        public string $workerName,
    ) {
    }
}
