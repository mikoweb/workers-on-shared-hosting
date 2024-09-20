<?php

namespace App\Module\Worker\Application\Interaction\Command\StartWorker\Handler;

use App\Core\Application\Exception\NotFoundException;
use App\Module\Worker\Application\Interaction\Command\StartWorker\StartWorkerCommand;
use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Infrastructure\Persistence\WorkerStatePersistence;
use App\Module\Worker\Infrastructure\Repository\WorkerRepository;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use Devium\Processes\Processes;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Process;

readonly class StartWorkerHandler
{
    public function __construct(
        private WorkerRepository $workerRepository,
        private WorkerStateRepository $workerStateRepository,
        private WorkerStatePersistence $workerStatePersistence,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    #[AsMessageHandler(bus: 'command_bus')]
    public function handle(StartWorkerCommand $command): void
    {
        $worker = $this->workerRepository->findByName($command->workerName);

        if (is_null($worker)) {
            throw new NotFoundException(sprintf('Worker with name `%s` not found.', $command->workerName));
        }

        $pids = $this->checkProcesses($command->workerName);
        $toCreate = $worker->instancesNumber - count($pids);

        for ($i = 0; $i < $toCreate; ++$i) {
            $process = new Process($worker->command, $worker->workingDirectory);
            $process->setOptions(['create_new_console' => true]);
            $process->start();

            $pids[] = $process->getPid();
        }

        $this->workerStatePersistence->putWorkerState(new WorkerState($command->workerName, $pids));
    }

    /**
     * @return int[]
     */
    private function checkProcesses(string $workerName): array
    {
        $processes = (new Processes(true))->get();
        $pids = $this->workerStateRepository->findByName($workerName)?->pids ?? [];

        return array_values(array_filter($pids, fn (int $pid) => isset($processes[$pid])));
    }
}
