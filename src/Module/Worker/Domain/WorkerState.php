<?php

namespace App\Module\Worker\Domain;

use Symfony\Component\Validator\Constraints as Assert;

readonly class WorkerState
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,

        /**
         * @var int[]
         */
        #[Assert\All([
            new Assert\Type('integer'),
        ])]
        public array $pids,
    ) {
    }
}
