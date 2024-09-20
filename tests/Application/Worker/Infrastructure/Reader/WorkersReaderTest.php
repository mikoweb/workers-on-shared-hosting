<?php

namespace App\Tests\Application\Worker\Infrastructure\Reader;

use App\Module\Worker\Domain\Worker;
use App\Module\Worker\Infrastructure\Reader\WorkersReader;
use App\Tests\ApplicationTestCase;

class WorkersReaderTest extends ApplicationTestCase
{
    public function testWorker1(): void
    {
        $reader = $this->getReader();
        $workers = $reader->read();
        $worker = $workers->where('name', 'Test 1')->first();

        $this->assertNotNull($worker);
        $this->assertInstanceOf(Worker::class, $worker);
        $this->assertEquals('Test 1', $worker->name);
        $this->assertEquals(5, $worker->instancesNumber);
        $this->assertEquals('__APP_PATH__', $worker->workingDirectory);
        $this->assertEquals(['php', 'tests/test_worker.php'], $worker->command);
    }

    public function testWorker2(): void
    {
        $reader = $this->getReader();
        $workers = $reader->read();
        $worker = $workers->where('name', 'Test 2')->first();

        $this->assertNotNull($worker);
        $this->assertInstanceOf(Worker::class, $worker);
        $this->assertEquals('Test 2', $worker->name);
        $this->assertEquals(10, $worker->instancesNumber);
        $this->assertEquals('__APP_PATH__', $worker->workingDirectory);
        $this->assertEquals(['php', 'tests/test_worker.php'], $worker->command);
    }

    private function getReader(): WorkersReader
    {
        WorkersReader::setWorkersFilename('workers.test.yaml');

        return $this->getService(WorkersReader::class);
    }
}
