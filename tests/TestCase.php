<?php

namespace Lean\Installer\Tests;

use Lean\Installer\LeanInstallerProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LeanInstallerProvider::class,
        ];
    }
}
