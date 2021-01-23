<?php

declare(strict_types=1);

namespace Lean\Installer\Concerns;

use Exception;

trait RunsInstallCommand
{
    protected function phpArtisanLeanInstall(): void
    {
        $install = $this->getProcess(['php', 'artisan', 'lean:install']);
        $install->run();
        $install->wait();

        if (! $install->isSuccessful()) {
            $this->error('The command `php artisan lean:install` failed.');

            $this->output->write($install->getErrorOutput());

            throw new Exception('Install command failed.');
        } else {
            $this->output->write($install->getOutput());
        }
    }
}
