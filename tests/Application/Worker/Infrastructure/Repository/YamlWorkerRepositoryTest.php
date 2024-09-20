<?php

namespace App\Tests\Application\Worker\Infrastructure\Repository;

use App\Module\Worker\Domain\Worker;
use App\Module\Worker\Domain\WorkerCollection;
use App\Module\Worker\Infrastructure\Repository\WorkerRepository;
use App\Module\Worker\Infrastructure\Repository\YamlWorkerRepository;
use App\Tests\ApplicationTestCase;

class YamlWorkerRepositoryTest extends ApplicationTestCase
{
    public function testFindByName(): void
    {
        $this->assertNull($this->getRepository()->findByName('NOT FOUND'));
        $this->assertInstanceOf(Worker::class, $this->getRepository()->findByName('Test 1'));
        $this->assertInstanceOf(Worker::class, $this->getRepository()->findByName('Test 2'));
    }

    public function testFindAll(): void
    {
        $this->assertInstanceOf(WorkerCollection::class, $this->getRepository()->findAll());
        $this->assertGreaterThan(0, $this->getRepository()->findAll()->count());
    }

    private function getRepository(): WorkerRepository
    {
        return $this->getService(YamlWorkerRepository::class);
    }
}
