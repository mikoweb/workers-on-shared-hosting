<?php

namespace App\Module\Worker\UI\CLI;

use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\Worker\Application\Interaction\Command\StopWorker\StopWorkerCommand;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:worker:stop',
)]
class WorkerStopCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly WorkerStateRepository $workerStateRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('worker_name', InputArgument::REQUIRED, 'Worker name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $workerName = $input->getArgument('worker_name');
        $state = $this->workerStateRepository->findByName($workerName);

        if (is_null($state)) {
            $io->error(sprintf('Worker `%s` was not started.', $workerName));

            return Command::FAILURE;
        }

        $this->commandBus->dispatch(new StopWorkerCommand($workerName));

        $io->success(sprintf('%d instances of worker `%s` have been stopped.', count($state->pids), $state->name));

        return Command::SUCCESS;
    }
}
