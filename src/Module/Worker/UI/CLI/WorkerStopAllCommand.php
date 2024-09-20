<?php

namespace App\Module\Worker\UI\CLI;

use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\Worker\Application\Interaction\Command\StopWorker\StopWorkerCommand;
use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:worker:stop-all',
)]
class WorkerStopAllCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly WorkerStateRepository $workerStateRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $states = $this->workerStateRepository->findAll();

        /** @var WorkerState $state */
        foreach ($states as $state) {
            $this->commandBus->dispatch(new StopWorkerCommand($state->name));
        }

        $io->success(sprintf('%d Workes have been stopped.', $states->count()));

        return Command::SUCCESS;
    }
}
