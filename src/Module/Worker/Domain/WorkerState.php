<?php

namespace App\Module\Worker\Domain;

readonly class WorkerState
{
    public function __construct(
        public string $name,

        /**
         * @var int[]
         */
        public array $pids,
    ) {
    }
}
