<?php

use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\QueueMetric;

it('prunes old completed and failed jobs and metrics', function () {
    config()->set('periscope.retention.completed_jobs', 1);
    config()->set('periscope.retention.failed_jobs', 1);
    config()->set('periscope.retention.metrics_minute', 1);
    config()->set('periscope.retention.metrics_hour', 1);

    MonitoredJob::query()->create([
        'uuid' => 'old-completed',
        'name' => 'Old',
        'connection' => 'redis',
        'queue' => 'default',
        'status' => 'completed',
        'attempts' => 1,
        'finished_at' => now()->subHours(5),
    ]);

    MonitoredJob::query()->create([
        'uuid' => 'fresh-completed',
        'name' => 'Fresh',
        'connection' => 'redis',
        'queue' => 'default',
        'status' => 'completed',
        'attempts' => 1,
        'finished_at' => now(),
    ]);

    QueueMetric::query()->create([
        'connection' => 'redis',
        'queue' => 'default',
        'period' => 'minute',
        'bucket' => now()->subHours(5),
        'queued' => 1,
        'processed' => 1,
        'failed' => 0,
        'runtime_ms_sum' => 0,
        'wait_ms_sum' => 0,
    ]);

    $this->artisan('periscope:prune')->assertSuccessful();

    expect(MonitoredJob::query()->where('uuid', 'old-completed')->exists())->toBeFalse()
        ->and(MonitoredJob::query()->where('uuid', 'fresh-completed')->exists())->toBeTrue()
        ->and(QueueMetric::query()->count())->toBe(0);
});
