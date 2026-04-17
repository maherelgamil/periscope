<?php

namespace MaherElGamil\Periscope;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use MaherElGamil\Periscope\Alerts\AlertManager;
use MaherElGamil\Periscope\Console\CheckAlertsCommand;
use MaherElGamil\Periscope\Console\ContinueCommand;
use MaherElGamil\Periscope\Console\InstallCommand;
use MaherElGamil\Periscope\Console\MarkStaleWorkersCommand;
use MaherElGamil\Periscope\Console\PauseCommand;
use MaherElGamil\Periscope\Console\PruneCommand;
use MaherElGamil\Periscope\Console\SnapshotCommand;
use MaherElGamil\Periscope\Console\StartCommand;
use MaherElGamil\Periscope\Console\SuperviseCommand;
use MaherElGamil\Periscope\Console\TerminateCommand;
use MaherElGamil\Periscope\Listeners\RecordJobLifecycle;
use MaherElGamil\Periscope\Livewire\FailedJobsTable;
use MaherElGamil\Periscope\Livewire\JobsTable;
use MaherElGamil\Periscope\Livewire\OverviewStats;
use MaherElGamil\Periscope\Livewire\QueuesTable;
use MaherElGamil\Periscope\Livewire\ThroughputChart;
use MaherElGamil\Periscope\Livewire\WorkersTable;
use MaherElGamil\Periscope\Support\AdapterFactory;
use MaherElGamil\Periscope\Support\QueueSize;

class PeriscopeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/periscope.php', 'periscope');

        $this->app->singleton(AdapterFactory::class);
        $this->app->singleton(QueueSize::class);
        $this->app->singleton(AlertManager::class);
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerCommands();
        $this->registerListeners();
        $this->registerViews();
        $this->registerGate();
        $this->registerRoutes();
        $this->registerLivewire();
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'periscope');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/periscope'),
        ], 'periscope-views');
    }

    protected function registerGate(): void
    {
        if (! Gate::has('viewPeriscope')) {
            Gate::define('viewPeriscope', fn ($user = null) => app()->environment('local'));
        }
    }

    protected function registerRoutes(): void
    {
        if (! config('periscope.enabled', true)) {
            return;
        }

        Route::group([
            'domain' => config('periscope.domain'),
            'prefix' => config('periscope.path', 'periscope'),
            'middleware' => config('periscope.middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    protected function registerLivewire(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        Livewire::component('periscope.overview-stats', OverviewStats::class);
        Livewire::component('periscope.jobs-table', JobsTable::class);
        Livewire::component('periscope.failed-jobs-table', FailedJobsTable::class);
        Livewire::component('periscope.workers-table', WorkersTable::class);
        Livewire::component('periscope.queues-table', QueuesTable::class);
        Livewire::component('periscope.throughput-chart', ThroughputChart::class);
    }

    protected function registerListeners(): void
    {
        if (! config('periscope.enabled', true)) {
            return;
        }

        $this->app->make(Dispatcher::class)->subscribe(RecordJobLifecycle::class);
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/periscope.php' => config_path('periscope.php'),
        ], 'periscope-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'periscope-migrations');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/periscope'),
        ], 'periscope-assets');
    }

    protected function registerMigrations(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            PruneCommand::class,
            SuperviseCommand::class,
            MarkStaleWorkersCommand::class,
            SnapshotCommand::class,
            CheckAlertsCommand::class,
            StartCommand::class,
            TerminateCommand::class,
            PauseCommand::class,
            ContinueCommand::class,
        ]);
    }
}
