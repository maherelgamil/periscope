<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use MaherElGamil\Periscope\Supervisors\Master;

class TerminateCommand extends Command
{
    protected $signature = 'periscope:terminate';

    protected $description = 'Signal the running Periscope master process to shut down';

    public function handle(): int
    {
        $pid = Cache::get(Master::PID_KEY);

        if (! $pid || ! function_exists('posix_kill')) {
            $this->components->warn('No Periscope master is running.');

            return self::FAILURE;
        }

        if (! posix_kill((int) $pid, SIGTERM)) {
            $this->components->error("Failed to signal master process (pid {$pid}).");

            return self::FAILURE;
        }

        $this->components->info("Sent SIGTERM to Periscope master (pid {$pid}).");

        return self::SUCCESS;
    }
}
