<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use MaherElGamil\Periscope\Supervisors\Master;

class PauseCommand extends Command
{
    protected $signature = 'periscope:pause';

    protected $description = 'Pause the Periscope master: stop spawning workers and drain running ones';

    public function handle(): int
    {
        Cache::forever(Master::PAUSE_KEY, true);

        $this->components->info('Periscope supervisors paused. Run `periscope:continue` to resume.');

        return self::SUCCESS;
    }
}
