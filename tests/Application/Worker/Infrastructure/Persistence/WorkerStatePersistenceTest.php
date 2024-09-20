<?php

namespace App\Tests\Application\Worker\Infrastructure\Persistence;

use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Infrastructure\Persistence\WorkerStatePersistence;
use App\Module\Worker\Infrastructure\Repository\JsonWorkerStateRepository;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use App\Tests\ApplicationTestCase;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class WorkerStatePersistenceTest extends ApplicationTestCase
{
    public function testPersistence(): void
    {
        $persiste = $this->getPersistence();
        $persiste->putWorkerState(new WorkerState('_Test_1', [1, 2, 3]));
        $persiste->putWorkerState(new WorkerState('_Test_2', [3, 4, 5, 6]));

        $repository = $this->getRepository();
        $state = $repository->findByName('_Test_1');
        $this->assertInstanceOf(WorkerState::class, $state);
        $this->assertEquals('_Test_1', $state->name);
        $this->assertEquals([1, 2, 3], $state->pids);

        $state = $repository->findByName('_Test_2');
        $this->assertInstanceOf(WorkerState::class, $state);
        $this->assertEquals('_Test_2', $state->name);
        $this->assertEquals([3, 4, 5, 6], $state->pids);

        $persiste->putWorkerState(new WorkerState('_Test_1', [7]));
        $state = $repository->findByName('_Test_1');
        $this->assertEquals([7], $state->pids);

        $persiste->putWorkerState(new WorkerState('_Test_1', []));
        $state = $repository->findByName('_Test_1');
        $this->assertEquals([], $state->pids);

        $persiste->removeWorkerStateByName('_Test_1');
        $persiste->removeWorkerStateByName('_Test_2');
        $persiste->removeWorkerStateByName('_Test_3');

        $this->assertNull($repository->findByName('_Test_1'));
        $this->assertNull($repository->findByName('_Test_2'));
        $this->assertNull($repository->findByName('_Test_3'));
    }

    public function testStringPidValidationFail(): void
    {
        $persiste = $this->getPersistence();
        $this->expectException(ValidationFailedException::class);
        // @phpstan-ignore-next-line
        $persiste->putWorkerState(new WorkerState('TEST', ['5435']));
    }

    public function testEmptyNameValidationFail(): void
    {
        $persiste = $this->getPersistence();
        $this->expectException(ValidationFailedException::class);
        $persiste->putWorkerState(new WorkerState('', []));
    }

    public function getRepository(): WorkerStateRepository
    {
        return $this->getContainer()->get(JsonWorkerStateRepository::class);
    }

    public function getPersistence(): WorkerStatePersistence
    {
        return $this->getService(WorkerStatePersistence::class);
    }
}
