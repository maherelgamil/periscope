<?php

use MaherElGamil\Periscope\Models\QueueMetric;

it('rolls minute metrics into hourly buckets', function () {
    $bucket = now()->subHours(2)->startOfMinute();

    QueueMetric::query()->create([
        'connection' => 'redis', 'queue' => 'default', 'period' => 'minute',
        'bucket' => $bucket,
        'queued' => 2, 'processed' => 1, 'failed' => 0, 'runtime_ms_sum' => 100, 'wait_ms_sum' => 50,
    ]);
    QueueMetric::query()->create([
        'connection' => 'redis', 'queue' => 'default', 'period' => 'minute',
        'bucket' => $bucket->copy()->addMinute(),
        'queued' => 3, 'processed' => 2, 'failed' => 1, 'runtime_ms_sum' => 200, 'wait_ms_sum' => 60,
    ]);

    $this->artisan('periscope:snapshot', ['--older-than' => 60])->assertSuccessful();

    expect(QueueMetric::query()->where('period', 'minute')->count())->toBe(0);

    $hour = QueueMetric::query()->where('period', 'hour')->first();
    expect($hour->queued)->toBe(5)
        ->and($hour->processed)->toBe(3)
        ->and($hour->failed)->toBe(1);
});
