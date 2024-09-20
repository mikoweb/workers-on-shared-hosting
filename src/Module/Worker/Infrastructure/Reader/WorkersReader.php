<?php

namespace App\Module\Worker\Infrastructure\Reader;

use App\Core\Application\Exception\NotFoundException;
use App\Core\Application\Path\AppPathResolver;
use App\Core\Application\Validation\ValidationUtils;
use App\Module\Worker\Domain\Worker;
use App\Module\Worker\Domain\WorkerCollection;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WorkersReader
{
    private static string $workersFilename = 'workers.yaml';
    private ?WorkerCollection $workers = null;

    public function __construct(
        private readonly AppPathResolver $appPathResolver,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function read(): WorkerCollection
    {
        if (is_null($this->workers)) {
            $filePath = $this->appPathResolver->getAppPath(self::$workersFilename);

            if (!file_exists($filePath)) {
                throw new NotFoundException(sprintf('Not found %s!', self::$workersFilename));
            }

            $typeClass = Worker::class;
            $workers = $this->serializer->deserialize(file_get_contents($filePath), "{$typeClass}[]", 'yaml');
            ValidationUtils::validateData($workers, $this->validator);

            $this->workers = new WorkerCollection($workers);
        }

        return $this->workers;
    }

    public static function setWorkersFilename(string $workersFilename): void
    {
        self::$workersFilename = $workersFilename;
    }
}
