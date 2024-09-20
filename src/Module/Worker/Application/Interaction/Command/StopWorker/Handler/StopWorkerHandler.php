<?php

namespace App\Module\Worker\Application\Interaction\Command\StopWorker\Handler;

use App\Core\Application\Process\ProcessUtils;
use App\Module\Worker\Application\Interaction\Command\StopWorker\StopWorkerCommand;
use App\Module\Worker\Infrastructure\Persistence\WorkerStatePersistence;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

readonly class StopWorkerHandler
{
    public function __construct(
        private WorkerStateRepository $workerStateRepository,
        private WorkerStatePersistence $workerStatePersistence,
    ) {
    }

    #[AsMessageHandler(bus: 'command_bus')]
    public function handle(StopWorkerCommand $command): void
    {
        $pids = $this->workerStateRepository->findByName($command->workerName)?->pids ?? [];

        foreach ($pids as $pid) {
            ProcessUtils::killProcess($pid);
        }

        $this->workerStatePersistence->removeWorkerStateByName($command->workerName);
    }
}
