<?php

namespace WinLocalInc\Chjs\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use WinLocalInc\Chjs\ChjsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $loadEnvironmentVariables = false;

    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function ($modelname) {
            $match = [];
            preg_match('/\\\\([^\\\\]+)$/', $modelname, $match);

            return 'WinLocalInc\\Chjs\\Tests\\Database\\Factories\\'.$match[1].'Factory';
        });
    }

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
