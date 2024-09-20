<?php

namespace App\Module\Worker\Application\Interaction\Command\StopWorker\Handler;

use App\Module\Worker\Application\Interaction\Command\StopWorker\StopWorkerCommand;
use App\Module\Worker\Infrastructure\Persistence\WorkerStatePersistence;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;

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
            $this->killProcess($pid);
        }

        $this->workerStatePersistence->removeWorkerStateByName($command->workerName);
    }

    private function killProcess(int $pid): void
    {
        $command = strtolower(PHP_OS_FAMILY) === 'windows'
            ? ['taskkill', '/pid', $pid, '/f']
            : ['kill', '-9', $pid];

        $process = new Process($command);
        $process->mustRun();
    }
}
