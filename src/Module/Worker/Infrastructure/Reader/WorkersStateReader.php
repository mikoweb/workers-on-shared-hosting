<?php

namespace App\Module\Worker\Infrastructure\Reader;

use App\Core\Application\Path\AppPathResolver;
use App\Core\Application\Validation\ValidationUtils;
use App\Module\Worker\Domain\WorkerState;
use App\Module\Worker\Domain\WorkerStateCollection;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WorkersStateReader
{
    public const string STATE_FILENAME = 'workers_state.json';

    public function __construct(
        private readonly AppPathResolver $appPathResolver,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function read(): WorkerStateCollection
    {
        $filePath = $this->appPathResolver->getStorePath(self::STATE_FILENAME);

        if (!file_exists($filePath)) {
            return new WorkerStateCollection();
        }

        $typeClass = WorkerState::class;
        $state = $this->serializer->deserialize(file_get_contents($filePath), "{$typeClass}[]", 'json');
        ValidationUtils::validateData($state, $this->validator);

        return new WorkerStateCollection($state);
    }
}
