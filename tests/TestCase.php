<?php

namespace WinLocalInc\Chjs\Tests;

use WinLocalInc\Chjs\ChjsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [ChjsServiceProvider::class];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(['--path' => [
            __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations',
            __DIR__.DIRECTORY_SEPARATOR.'Database'.DIRECTORY_SEPARATOR.'Migrations',
        ],
        ]);
    }
}
