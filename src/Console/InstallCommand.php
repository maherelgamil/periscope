<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'periscope:install {--force : Overwrite existing files}';

    protected $description = 'Install the Periscope configuration, migrations, and application service provider';

    public function handle(Filesystem $files): int
    {
        $this->components->info('Publishing Periscope configuration...');

        $this->call('vendor:publish', [
            '--tag' => 'periscope-config',
            '--force' => $this->option('force'),
        ]);

        $this->components->info('Publishing Periscope migrations...');

        $this->call('vendor:publish', [
            '--tag' => 'periscope-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->components->info('Publishing Periscope assets...');

        $this->call('vendor:publish', [
            '--tag' => 'periscope-assets',
            '--force' => true,
        ]);

        $this->publishServiceProvider($files);
        $this->registerServiceProvider($files);

        $this->newLine();
        $this->components->success('Periscope installed. Run `php artisan migrate` to create the tables.');

        return self::SUCCESS;
    }

    protected function publishServiceProvider(Filesystem $files): void
    {
        $destination = app_path('Providers/PeriscopeServiceProvider.php');

        if ($files->exists($destination) && ! $this->option('force')) {
            $this->components->info('Keeping existing app/Providers/PeriscopeServiceProvider.php (pass --force to overwrite).');

            return;
        }

        $files->ensureDirectoryExists(dirname($destination));
        $files->copy(__DIR__.'/../../stubs/PeriscopeServiceProvider.stub', $destination);

        $this->components->info('Wrote app/Providers/PeriscopeServiceProvider.php.');
    }

    protected function registerServiceProvider(Filesystem $files): void
    {
        $bootstrap = base_path('bootstrap/providers.php');

        if (! $files->exists($bootstrap)) {
            $this->components->warn('bootstrap/providers.php not found — add App\\Providers\\PeriscopeServiceProvider to config/app.php manually.');

            return;
        }

        $contents = $files->get($bootstrap);
        $marker = 'App\\Providers\\PeriscopeServiceProvider::class';

        if (str_contains($contents, $marker)) {
            return;
        }

        $updated = preg_replace(
            '/return\s*\[(.*?)\];/s',
            "return [\n    App\\Providers\\PeriscopeServiceProvider::class,$1];",
            $contents,
            1,
        );

        if ($updated === null || $updated === $contents) {
            $this->components->warn('Could not automatically register App\\Providers\\PeriscopeServiceProvider — add it to bootstrap/providers.php manually.');

            return;
        }

        $files->put($bootstrap, $updated);
        $this->components->info('Registered App\\Providers\\PeriscopeServiceProvider in bootstrap/providers.php.');
    }
}
