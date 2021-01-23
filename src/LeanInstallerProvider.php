<?php

declare(strict_types=1);

namespace Lean\Installer;

use Illuminate\Support\ServiceProvider;
use Lean\Installer\Commands\Setup;

class LeanInstallerProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Setup::class,
            ]);
        }
    }
}
