<?php

namespace App\Module\Worker\UI\CLI;

use App\Core\Infrastructure\Bus\CommandBusInterface;
use App\Module\Worker\Application\Interaction\Command\RestartWorker\RestartWorkerCommand;
use App\Module\Worker\Infrastructure\Repository\WorkerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:worker:restart',
)]
class WorkerRestartCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly WorkerRepository $workerRepository,
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
        $this->commandBus->dispatch(new RestartWorkerCommand($workerName));
        $worker = $this->workerRepository->findByName($workerName);

        $io->success(sprintf('%d instances of worker `%s` were restarted.', $worker->instancesNumber, $worker->name));

        return Command::SUCCESS;
    }
}
