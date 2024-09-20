<?php

namespace App\Module\Worker\Application\Interaction\Command\RestartWorker\Handler;

use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\Worker\Application\Interaction\Command\RestartWorker\RestartWorkerCommand;
use App\Module\Worker\Application\Interaction\Command\StartWorker\StartWorkerCommand;
use App\Module\Worker\Application\Interaction\Command\StopWorker\StopWorkerCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

readonly class RestartWorkerHandler
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    #[AsMessageHandler(bus: 'command_bus')]
    public function handle(RestartWorkerCommand $command): void
    {
        $this->commandBus->dispatch(new StopWorkerCommand($command->workerName));
        $this->commandBus->dispatch(new StartWorkerCommand($command->workerName));
    }
}
