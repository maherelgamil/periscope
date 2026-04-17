<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'periscope:install {--force : Overwrite existing files}';

    protected $description = 'Install the Periscope configuration and migrations';

    public function handle(): int
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

        $this->newLine();
        $this->components->success('Periscope installed. Run `php artisan migrate` to create the tables.');

        return self::SUCCESS;
    }
}
