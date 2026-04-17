<?php

namespace MaherElGamil\Periscope\Tests;

use Illuminate\Support\Facades\Gate;
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

        Gate::before(fn () => true);
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
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('periscope.middleware', []);
        $app['config']->set('periscope.metrics.middleware', []);
    }
}
