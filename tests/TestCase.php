<?php

namespace MaherElGamil\Periscope\Tests;

use Livewire\LivewireServiceProvider;
use MaherElGamil\Periscope\PeriscopeServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->artisan('migrate')->run();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            PeriscopeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
