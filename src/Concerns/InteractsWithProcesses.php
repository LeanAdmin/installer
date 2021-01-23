<?php

declare(strict_types=1);

namespace Lean\Installer\Concerns;

use Illuminate\Support\ProcessUtils;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

trait InteractsWithProcesses
{
    protected function composer(array $command): void
    {
        $command = array_merge($this->findComposer(), $command);

        $process = $this->getProcess($command);

        $process->run();
        $process->wait();
    }

    protected function findComposer(): array
    {
        if ($this->files->exists('/composer.phar')) {
            return [$this->phpBinary(), 'composer.phar'];
        }

        return ['composer'];
    }

    protected function phpBinary(): string
    {
        return ProcessUtils::escapeArgument((string) (new PhpExecutableFinder)->find(false));
    }

    protected function getProcess(array $command): Process
    {
        return (new Process($command))->setTimeout(null);
    }
}
