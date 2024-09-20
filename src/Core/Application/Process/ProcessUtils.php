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

    /**
     * @return array<int|string, mixed[]>
     */
    public static function getProcesses(): array
    {
        return (new ProcessList(true, true))->rescan()->get();
    }
}
