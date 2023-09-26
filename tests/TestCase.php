<?php

namespace WinLocalInc\Chjs\Tests;

use WinLocalInc\Chjs\ChjsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $loadEnvironmentVariables = false;

    protected function getPackageProviders($app)
    {
        return [ChjsServiceProvider::class];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsWithoutRollbackFrom(__DIR__.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations');
        $this->loadMigrationsWithoutRollbackFrom(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations');
    }
}
