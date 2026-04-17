<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use MaherElGamil\Periscope\Repositories\WorkerRepository;

class MarkStaleWorkersCommand extends Command
{
    protected $signature = 'periscope:workers:sweep';

    protected $description = 'Mark workers as stale when their heartbeat has timed out';

    public function handle(WorkerRepository $workers): int
    {
        $threshold = now()->subSeconds((int) config('periscope.workers.stale_after_seconds', 60));

        $count = $workers->markStale($threshold);

        $this->components->info("Marked {$count} worker(s) as stale.");

        return self::SUCCESS;
    }
}
