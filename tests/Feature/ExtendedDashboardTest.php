<?php

use Illuminate\Support\Facades\Queue;
use MaherElGamil\Periscope\Models\AlertRecord;
use MaherElGamil\Periscope\Models\JobAttempt;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\ScheduleRun;

it('renders the alerts page with records', function () {
    AlertRecord::query()->create([
        'key' => 'failure_spike',
        'title' => 'Queue failure spike',
        'severity' => 'error',
        'message' => '15 failures in 5 minutes',
        'context' => ['count' => 15],
        'channels' => ['slack'],
        'fired_at' => now(),
    ]);

    $this->get('/periscope/alerts')
        ->assertOk()
        ->assertSeeText('Queue failure spike')
        ->assertSeeText('slack');
});

it('renders the schedules page', function () {
    ScheduleRun::query()->create([
        'command' => 'periscope:snapshot',
        'expression' => '0 * * * *',
        'status' => 'completed',
        'runtime_ms' => 218,
        'started_at' => now()->subMinutes(5),
        'finished_at' => now()->subMinutes(5)->addMilliseconds(218),
    ]);

    $this->get('/periscope/schedules')
        ->assertOk()
        ->assertSeeText('periscope:snapshot');
});

it('renders the batches page without a batches table', function () {
    $this->get('/periscope/batches')
        ->assertOk()
        ->assertSeeText('Batches');
});

it('renders the performance page', function () {
    MonitoredJob::query()->create([
        'uuid' => 'perf-1', 'name' => 'App\\Jobs\\Fast',
        'connection' => 'redis', 'queue' => 'default',
        'status' => 'completed', 'attempts' => 1,
        'runtime_ms' => 120, 'wait_ms' => 500,
        'finished_at' => now(),
    ]);

    $this->get('/periscope/performance')
        ->assertOk()
        ->assertSeeText('Performance');
});

it('renders the exceptions drill-down page', function () {
    MonitoredJob::query()->create([
        'uuid' => 'exc-1', 'name' => 'App\\Jobs\\Boom',
        'connection' => 'redis', 'queue' => 'default',
        'status' => 'failed', 'attempts' => 1,
    ]);

    JobAttempt::query()->create([
        'job_uuid' => 'exc-1',
        'attempt' => 1,
        'status' => 'failed',
        'exception_class' => 'RuntimeException',
        'exception_message' => 'boom',
        'exception' => "RuntimeException: boom in /app/Boom.php:12\n#0 ...",
        'runtime_ms' => 42,
        'started_at' => now()->subSecond(),
        'finished_at' => now(),
    ]);

    $this->get('/periscope/exceptions/show?class=RuntimeException&message=boom')
        ->assertOk()
        ->assertSeeText('RuntimeException')
        ->assertSeeText('boom');
});

it('returns 404 for the exception drill-down without a class', function () {
    $this->get('/periscope/exceptions/show')->assertNotFound();
});

it('re-dispatches a completed job from the detail page', function () {
    Queue::fake();

    MonitoredJob::query()->create([
        'uuid' => 'redispatch-1',
        'job_id' => 'job-xyz',
        'name' => 'App\\Jobs\\Example',
        'connection' => 'sync',
        'queue' => 'default',
        'status' => 'completed',
        'attempts' => 1,
        'payload' => json_encode(['uuid' => 'redispatch-1', 'displayName' => 'App\\Jobs\\Example']),
    ]);

    $this->post('/periscope/jobs/redispatch-1/retry')
        ->assertRedirect('/periscope/jobs/redispatch-1');
});
