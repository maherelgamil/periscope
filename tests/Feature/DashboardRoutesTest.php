<?php

use Illuminate\Support\Facades\Gate;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\Worker;

beforeEach(function () {
    Gate::define('viewPeriscope', fn () => true);
});

it('renders the overview page', function () {
    $this->get('/periscope')
        ->assertOk()
        ->assertSeeText('Overview');
});

it('renders the queues page', function () {
    $this->get('/periscope/queues')
        ->assertOk()
        ->assertSeeText('Queues');
});

it('renders the jobs page', function () {
    MonitoredJob::query()->create([
        'uuid' => 'test-uuid', 'name' => 'App\\Jobs\\Example',
        'connection' => 'redis', 'queue' => 'default',
        'status' => 'completed', 'attempts' => 1, 'finished_at' => now(),
    ]);

    $this->get('/periscope/jobs')
        ->assertOk()
        ->assertSeeText('App\\Jobs\\Example');
});

it('renders the job detail page', function () {
    MonitoredJob::query()->create([
        'uuid' => 'detail-uuid', 'name' => 'App\\Jobs\\Detail',
        'connection' => 'redis', 'queue' => 'default',
        'status' => 'completed', 'attempts' => 1,
    ]);

    $this->get('/periscope/jobs/detail-uuid')
        ->assertOk()
        ->assertSeeText('App\\Jobs\\Detail');
});

it('returns 404 for unknown job uuid', function () {
    $this->get('/periscope/jobs/nope-uuid')->assertNotFound();
});

it('renders the failed page', function () {
    $this->get('/periscope/failed')
        ->assertOk()
        ->assertSeeText('Failed jobs');
});

it('renders workers on the overview page', function () {
    Worker::query()->create([
        'name' => 'test-worker', 'status' => 'running', 'last_heartbeat_at' => now(),
    ]);

    $this->get('/periscope')
        ->assertOk()
        ->assertSeeText('Workers');
});

it('renders the exceptions page', function () {
    $this->get('/periscope/exceptions')
        ->assertOk()
        ->assertSeeText('Exceptions');
});
