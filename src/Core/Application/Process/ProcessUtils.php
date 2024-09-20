<?php

namespace App\Core\Application\Process;

use Symfony\Component\Process\Process;

class ProcessUtils
{
    public static function killProcess(int $pid): void
    {
        $command = strtolower(PHP_OS_FAMILY) === 'windows'
            ? ['taskkill', '/pid', $pid, '/f']
            : ['kill', '-9', $pid];

        $process = new Process($command);
        $process->mustRun();
    }
}
