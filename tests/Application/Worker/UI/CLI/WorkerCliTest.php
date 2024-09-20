<?php

namespace App\Tests\Application\Worker\UI\CLI;

use App\Core\Application\Process\ProcessUtils;
use App\Module\Worker\Domain\Worker;
use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Infrastructure\Reader\WorkersReader;
use App\Module\Worker\Infrastructure\Repository\JsonWorkerStateRepository;
use App\Module\Worker\Infrastructure\Repository\WorkerRepository;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use App\Module\Worker\Infrastructure\Repository\YamlWorkerRepository;
use App\Tests\ApplicationTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class WorkerCliTest extends ApplicationTestCase
{
    public function testCli(): void
    {
        WorkersReader::setWorkersFilename('workers.test.yaml');
        $app = new Application(self::$kernel);

        $this->checkStopAll($app);

        $this->checkStartNonExistentWorker($app, 'Not Found 1');
        $this->checkStartNonExistentWorker($app, 'Not Found 2');

        $this->checkStartWorker($app, 'Test 1');
        $this->checkStopWorker($app, 'Test 1');

        $this->checkStartWorker($app, 'Test 2');
        $this->checkStopWorker($app, 'Test 2');

        $pidsAfterStart = $this->checkStartWorker($app, 'Test 1');
        $pidsAfterStart2 = $this->checkStartWorker($app, 'Test 1');
        $this->assertEquals($pidsAfterStart2, $pidsAfterStart);

        $pidsAfterStart = $this->checkStartWorker($app, 'Test 1');
        $this->assertEquals($pidsAfterStart, $pidsAfterStart2);
        $this->assertNotEmpty($pidsAfterStart);

        $pidsAfterRestart = $this->checkRestartWorker($app, 'Test 1');

        foreach ($pidsAfterStart as $i => $pid) {
            $this->assertNotEquals($pidsAfterRestart[$i], $pid);
        }

        $this->checkStopWorker($app, 'Test 1');

        $this->checkStopAll($app);

        $pidsAfterStart = $this->checkStartAll($app);
        $pidsAfterStart2 = $this->checkStartAll($app);
        $pidsAfterRestart = $this->checkRestartAll($app);

        $this->assertEquals($pidsAfterStart2, $pidsAfterStart);
        $this->assertNotEmpty($pidsAfterStart);

        foreach ($pidsAfterStart as $i => $pid) {
            $this->assertNotEquals($pidsAfterRestart[$i], $pid);
        }

        $this->checkStopAll($app);
    }

    private function checkStopAll(Application $app): void
    {
        $command = $this->getCommand($app, 'app:worker:stop-all');
        $command->execute([]);
        $command->assertCommandIsSuccessful();
        $states = $this->getWorkerStateRepository()->findAll();
        $this->assertEquals(0, $states->count());
    }

    /**
     * @return int[]
     */
    private function checkStartWorker(Application $app, string $workerName): array
    {
        $command = $this->getCommand($app, 'app:worker:start');
        $command->execute(['worker_name' => $workerName]);
        $command->assertCommandIsSuccessful();

        $state = $this->getWorkerStateRepository()->findByName($workerName);
        $worker = $this->getWorkerRepository()->findByName($workerName);
        $this->assertNotNull($state);
        $this->assertEquals($workerName, $state->name);
        $this->assertCount($worker->instancesNumber, $state->pids);

        $this->assertStringContainsString(
            sprintf('[OK] %d instances of worker `%s` are running.', $worker->instancesNumber, $worker->name),
            $command->getDisplay()
        );

        $processes = ProcessUtils::getProcesses();
        foreach ($state->pids as $pid) {
            $this->assertArrayHasKey($pid, $processes);
        }

        return $state->pids;
    }

    private function checkStartNonExistentWorker(Application $app, string $workerName): void
    {
        $command = $this->getCommand($app, 'app:worker:start');

        try {
            $command->execute(['worker_name' => $workerName]);
            $this->fail('It shouldn\'t start');
        } catch (HandlerFailedException $exception) {
            $this->assertStringContainsString(
                sprintf('Worker with name `%s` not found', $workerName),
                $exception->getMessage(),
            );

            $this->assertInstanceOf(HandlerFailedException::class, $exception);
        }

        $state = $this->getWorkerStateRepository()->findByName($workerName);
        $worker = $this->getWorkerRepository()->findByName($workerName);
        $this->assertNull($state);
        $this->assertNull($worker);
    }

    private function checkStopWorker(Application $app, string $workerName): void
    {
        $command = $this->getCommand($app, 'app:worker:stop');
        $command->execute(['worker_name' => $workerName]);
        $command->assertCommandIsSuccessful();

        $state = $this->getWorkerStateRepository()->findByName($workerName);
        $worker = $this->getWorkerRepository()->findByName($workerName);
        $this->assertNull($state);
        $this->assertNotNull($worker);

        $this->assertStringContainsString(
            sprintf('[OK] %d instances of worker `%s` have been stopped.', $worker->instancesNumber, $worker->name),
            $command->getDisplay(),
        );
    }

    /**
     * @return int[]
     */
    private function checkRestartWorker(Application $app, string $workerName): array
    {
        $command = $this->getCommand($app, 'app:worker:restart');
        $command->execute(['worker_name' => $workerName]);
        $command->assertCommandIsSuccessful();

        $state = $this->getWorkerStateRepository()->findByName($workerName);
        $worker = $this->getWorkerRepository()->findByName($workerName);
        $this->assertNotNull($state);
        $this->assertEquals($workerName, $state->name);
        $this->assertCount($worker->instancesNumber, $state->pids);

        $this->assertStringContainsString(
            sprintf('[OK] %d instances of worker `%s` were restarted.', $worker->instancesNumber, $worker->name),
            $command->getDisplay()
        );

        $processes = ProcessUtils::getProcesses();
        foreach ($state->pids as $pid) {
            $this->assertArrayHasKey($pid, $processes);
        }

        return $state->pids;
    }

    /**
     * @return int[]
     */
    private function checkStartAll(Application $app): array
    {
        $command = $this->getCommand($app, 'app:worker:start-all');
        $command->execute([]);
        $command->assertCommandIsSuccessful();

        $workers = $this->getWorkerRepository()->findAll();

        $this->assertStringContainsString(
            sprintf('[OK] %d Workers are running.', $workers->count()),
            $command->getDisplay(),
        );

        /** @var Worker $worker */
        foreach ($workers as $worker) {
            $state = $this->getWorkerStateRepository()->findByName($worker->name);
            $this->assertNotNull($state);
            $this->assertEquals($worker->name, $state->name);
            $this->assertCount($worker->instancesNumber, $state->pids);
        }

        $processes = ProcessUtils::getProcesses();
        $pids = [];

        /** @var WorkerState $workerState */
        foreach ($this->getWorkerStateRepository()->findAll() as $workerState) {
            foreach ($workerState->pids as $pid) {
                $pids[] = $pid;
                $this->assertArrayHasKey($pid, $processes);
            }
        }

        return $pids;
    }

    /**
     * @return int[]
     */
    private function checkRestartAll(Application $app): array
    {
        $command = $this->getCommand($app, 'app:worker:restart-all');
        $command->execute([]);
        $command->assertCommandIsSuccessful();

        $workers = $this->getWorkerRepository()->findAll();

        $this->assertStringContainsString(
            sprintf('[OK] %d Workers were restarted.', $workers->count()),
            $command->getDisplay(),
        );

        /** @var Worker $worker */
        foreach ($workers as $worker) {
            $state = $this->getWorkerStateRepository()->findByName($worker->name);
            $this->assertNotNull($state);
            $this->assertEquals($worker->name, $state->name);
            $this->assertCount($worker->instancesNumber, $state->pids);
        }

        $processes = ProcessUtils::getProcesses();
        $pids = [];

        /** @var WorkerState $workerState */
        foreach ($this->getWorkerStateRepository()->findAll() as $workerState) {
            foreach ($workerState->pids as $pid) {
                $pids[] = $pid;
                $this->assertArrayHasKey($pid, $processes);
            }
        }

        return $pids;
    }

    private function getCommand(Application $app, string $name): CommandTester
    {
        $command = $app->find($name);

        return new CommandTester($command);
    }

    private function getWorkerRepository(): WorkerRepository
    {
        return $this->getContainer()->get(YamlWorkerRepository::class);
    }

    private function getWorkerStateRepository(): WorkerStateRepository
    {
        return $this->getContainer()->get(JsonWorkerStateRepository::class);
    }
}
