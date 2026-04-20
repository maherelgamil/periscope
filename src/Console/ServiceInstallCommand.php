<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ServiceInstallCommand extends Command
{
    protected $signature = 'periscope:service:install
        {--user= : The system user to run Periscope as (default: current user)}
        {--force : Overwrite existing service file}';

    protected $description = 'Install Periscope as a systemd service so it starts on boot';

    public function handle(Filesystem $files): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->components->error('Systemd is not available on Windows.');

            return self::FAILURE;
        }

        $servicePath = '/etc/systemd/system/periscope.service';

        if ($files->exists($servicePath) && ! $this->option('force')) {
            $this->components->warn("Service file already exists at {$servicePath}. Pass --force to overwrite.");

            return self::SUCCESS;
        }

        $user = $this->option('user') ?: get_current_user();
        $php = PHP_BINARY;
        $artisan = base_path('artisan');
        $workDir = base_path();

        $unit = <<<UNIT
        [Unit]
        Description=Periscope Queue Worker
        After=network.target redis.service postgresql.service
        Wants=redis.service

        [Service]
        Type=simple
        User={$user}
        WorkingDirectory={$workDir}
        ExecStart={$php} {$artisan} periscope:start
        Restart=always
        RestartSec=5
        StandardOutput=journal
        StandardError=journal
        SyslogIdentifier=periscope

        [Install]
        WantedBy=multi-user.target
        UNIT;

        // Write via temp file since /etc/systemd requires root
        $tmp = sys_get_temp_dir().'/periscope.service';
        $files->put($tmp, $unit);

        $this->components->info("Generated service file at {$tmp}");
        $this->newLine();
        $this->components->twoColumnDetail('Run these commands to install', '');
        $this->line("  <fg=cyan>sudo cp {$tmp} {$servicePath}</>");
        $this->line('  <fg=cyan>sudo systemctl daemon-reload</>');
        $this->line('  <fg=cyan>sudo systemctl enable periscope</>');
        $this->line('  <fg=cyan>sudo systemctl start periscope</>');
        $this->newLine();
        $this->line('To check status:  <fg=cyan>sudo systemctl status periscope</>');
        $this->line('To view logs:     <fg=cyan>sudo journalctl -u periscope -f</>');

        return self::SUCCESS;
    }
}
