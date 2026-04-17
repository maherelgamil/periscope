<?php

use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\QueueMetric;

it('exposes prometheus metrics', function () {
    QueueMetric::query()->create([
        'connection' => 'redis', 'queue' => 'default', 'period' => 'minute',
        'bucket' => now(),
        'queued' => 10, 'processed' => 8, 'failed' => 2,
        'runtime_ms_sum' => 1500, 'wait_ms_sum' => 5000,
    ]);

    $response = $this->get('/periscope/metrics');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8')
        ->assertSee('periscope_jobs_processed_total{connection="redis",queue="default"} 8', escape: false)
        ->assertSee('periscope_jobs_failed_total{connection="redis",queue="default"} 2', escape: false)
        ->assertSee('periscope_runtime_ms_sum{connection="redis",queue="default"} 1500', escape: false);
});

it('exposes json metrics', function () {
    MonitoredJob::query()->create([
        'uuid' => 'j1', 'name' => 'X', 'connection' => 'redis', 'queue' => 'default',
        'status' => 'completed', 'attempts' => 1, 'finished_at' => now(),
    ]);

    $this->getJson('/periscope/metrics.json')
        ->assertOk()
        ->assertJsonStructure([
            'jobs', 'runtime', 'queues', 'workers' => ['running', 'stale', 'stopped'],
            'jobs_current' => ['queued', 'running', 'completed', 'failed'],
        ])
        ->assertJsonPath('jobs_current.completed', 1);
});
