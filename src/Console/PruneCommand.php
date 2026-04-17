<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Repositories\JobRepository;
use MaherElGamil\Periscope\Repositories\MetricRepository;

class PruneCommand extends Command
{
    protected $signature = 'periscope:prune';

    protected $description = 'Prune old Periscope telemetry based on retention settings';

    public function handle(JobRepository $jobs, MetricRepository $metrics): int
    {
        $completedBefore = now()->subHours((int) config('periscope.retention.completed_jobs', 24));
        $failedBefore = now()->subHours((int) config('periscope.retention.failed_jobs', 168));
        $minuteBefore = now()->subHours((int) config('periscope.retention.metrics_minute', 6));
        $hourBefore = now()->subHours((int) config('periscope.retention.metrics_hour', 720));

        $completed = $jobs->prune(MonitoredJob::STATUS_COMPLETED, $completedBefore);
        $failed = $jobs->prune(MonitoredJob::STATUS_FAILED, $failedBefore);
        $minuteDeleted = $metrics->pruneByPeriod('minute', $minuteBefore);
        $hourDeleted = $metrics->pruneByPeriod('hour', $hourBefore);

        $this->components->info("Pruned {$completed} completed, {$failed} failed, {$minuteDeleted} minute metrics, {$hourDeleted} hour metrics.");

        return self::SUCCESS;
    }
}
