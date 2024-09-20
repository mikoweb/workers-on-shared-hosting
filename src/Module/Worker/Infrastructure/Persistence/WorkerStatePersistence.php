<?php

namespace App\Module\Worker\Infrastructure\Persistence;

use App\Core\Application\Path\AppPathResolver;
use App\Core\Application\Validation\ValidationUtils;
use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Domain\WorkerStateCollection;
use App\Module\Worker\Infrastructure\Reader\WorkersStateReader;
use App\Module\Worker\Infrastructure\Repository\WorkerStateRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class WorkerStatePersistence
{
    public function __construct(
        private WorkerStateRepository $workerStateRepository,
        private AppPathResolver $appPathResolver,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    public function putWorkerState(WorkerState $workerState): void
    {
        ValidationUtils::validateData($workerState, $this->validator);

        /** @var WorkerStateCollection $state */
        $state = $this->workerStateRepository->findAll()
            ->filter(fn (WorkerState $item) => $item->name !== $workerState->name);

        $state->add($workerState);
        $this->save($state);
    }

    public function removeWorkerStateByName(string $name): void
    {
        /** @var WorkerStateCollection $state */
        $state = $this->workerStateRepository->findAll()->filter(fn (WorkerState $item) => $item->name !== $name);

        $this->save($state);
    }

    public function save(WorkerStateCollection $state): void
    {
        $fs = new Filesystem();
        $fs->dumpFile(
            $this->appPathResolver->getStorePath(WorkersStateReader::STATE_FILENAME),
            $this->serializer->serialize(array_values($state->toArray()), 'json'),
        );
    }
}
