<?php

namespace App\Module\Worker\Domain;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

readonly class Worker
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,

        #[Assert\GreaterThan(0)]
        #[SerializedName('instances_number')]
        public int $instancesNumber,

        #[Assert\NotBlank]
        #[SerializedName('working_directory')]
        public string $workingDirectory,

        /**
         * @var string[]
         */
        #[Assert\NotBlank]
        #[Assert\All([
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ])]
        public array $command,
    ) {
    }
}
