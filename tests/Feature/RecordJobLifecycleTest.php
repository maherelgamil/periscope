<?php

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Str;
use MaherElGamil\Periscope\Models\MonitoredJob;

function fakeJobContract(string $uuid, string $queue = 'default', string $name = 'App\\Jobs\\Example', int $attempts = 1): object
{
    return new class($uuid, $queue, $name, $attempts) implements Job
    {
        public function __construct(
            protected string $uuid,
            protected string $queue,
            protected string $name,
            protected int $attempts,
        ) {}

        public function uuid(): ?string
        {
            return $this->uuid;
        }

        public function getQueue(): ?string
        {
            return $this->queue;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function attempts(): int
        {
            return $this->attempts;
        }

        public function getJobId(): string
        {
            return 'job-'.$this->uuid;
        }

        public function getRawBody(): string
        {
            return json_encode(['uuid' => $this->uuid, 'displayName' => $this->name]);
        }

        public function payload(): array
        {
            return ['uuid' => $this->uuid, 'displayName' => $this->name];
        }

        public function fire(): void {}

        public function release($delay = 0): void {}

        public function delete(): void {}

        public function isDeleted(): bool
        {
            return false;
        }

        public function isDeletedOrReleased(): bool
        {
            return false;
        }

        public function isReleased(): bool
        {
            return false;
        }

        public function hasFailed(): bool
        {
            return false;
        }

        public function markAsFailed(): void {}

        public function fail($e = null): void {}

        public function maxTries(): ?int
        {
            return null;
        }

        public function maxExceptions(): ?int
        {
            return null;
        }

        public function backoff(): ?int
        {
            return null;
        }

        public function retryUntil(): ?int
        {
            return null;
        }

        public function timeout(): ?int
        {
            return null;
        }

        public function getConnectionName(): string
        {
            return 'sync';
        }

        public function getResolvedName(): string
        {
            return $this->name;
        }

        public function resolveName(): string
        {
            return $this->name;
        }

        public function resolveQueuedJobClass(): string
        {
            return $this->name;
        }
    };
}

it('records a job through its lifecycle', function () {
    $uuid = (string) Str::uuid();

    event(new JobQueued(
        connectionName: 'redis',
        queue: 'default',
        id: 'job-'.$uuid,
        job: null,
        payload: json_encode(['uuid' => $uuid, 'displayName' => 'App\\Jobs\\Example']),
        delay: 0,
    ));

    expect(MonitoredJob::query()->where('uuid', $uuid)->value('status'))->toBe('queued');

    event(new JobProcessing('redis', fakeJobContract($uuid)));

    expect(MonitoredJob::query()->where('uuid', $uuid)->value('status'))->toBe('running');

    event(new JobProcessed('redis', fakeJobContract($uuid)));

    $job = MonitoredJob::query()->where('uuid', $uuid)->first();
    expect($job->status)->toBe('completed')
        ->and($job->finished_at)->not->toBeNull();
});

it('captures failure exception', function () {
    $uuid = (string) Str::uuid();

    event(new JobQueued(
        connectionName: 'redis',
        queue: 'default',
        id: 'job-'.$uuid,
        job: null,
        payload: json_encode(['uuid' => $uuid, 'displayName' => 'App\\Jobs\\Example']),
        delay: 0,
    ));

    event(new JobProcessing('redis', fakeJobContract($uuid)));

    event(new JobFailed('redis', fakeJobContract($uuid), new RuntimeException('boom')));

    $job = MonitoredJob::query()->where('uuid', $uuid)->first();

    expect($job->status)->toBe('failed')
        ->and($job->exception)->toContain('boom');
});
