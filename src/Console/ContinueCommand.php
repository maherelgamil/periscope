<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use MaherElGamil\Periscope\Supervisors\Master;

class ContinueCommand extends Command
{
    protected $signature = 'periscope:continue';

    protected $description = 'Resume the Periscope master after a pause';

    public function handle(): int
    {
        Cache::forget(Master::PAUSE_KEY);

        $this->components->info('Periscope supervisors resumed.');

        return self::SUCCESS;
    }
}
