<?php

namespace App\Module\Worker\UI\CLI;

use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\Worker\Application\Interaction\Command\StartWorker\StartWorkerCommand;
use App\Module\Worker\Domain\Worker;
use App\Module\Worker\Infrastructure\Repository\WorkerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:worker:start-all',
)]
class WorkerStartAllCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly WorkerRepository $workerRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $workers = $this->workerRepository->findAll();

        /** @var Worker $worker */
        foreach ($workers as $worker) {
            $this->commandBus->dispatch(new StartWorkerCommand($worker->name));
        }

        $io->success(sprintf('%d Workers are running.', $workers->count()));

        return Command::SUCCESS;
    }
}
