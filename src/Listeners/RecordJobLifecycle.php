<?php

namespace MaherElGamil\Periscope\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use MaherElGamil\Periscope\Models\JobAttempt;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Repositories\JobRepository;
use MaherElGamil\Periscope\Repositories\MetricRepository;
use MaherElGamil\Periscope\Support\ExceptionSignature;
use MaherElGamil\Periscope\Support\QueueFilter;
use MaherElGamil\Periscope\Support\TagExtractor;
use Throwable;

class RecordJobLifecycle
{
    public function __construct(
        protected JobRepository $jobs,
        protected MetricRepository $metrics,
        protected TagExtractor $tagExtractor,
        protected QueueFilter $filter,
    ) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(JobQueued::class, [self::class, 'handleQueued']);
        $events->listen(JobProcessing::class, [self::class, 'handleProcessing']);
        $events->listen(JobProcessed::class, [self::class, 'handleProcessed']);
        $events->listen(JobFailed::class, [self::class, 'handleFailed']);
        $events->listen(JobExceptionOccurred::class, [self::class, 'handleException']);
    }

    public function handleException(JobExceptionOccurred $event): void
    {
        $queue = $event->job->getQueue() ?? 'default';

        if (! $this->filter->shouldRecord($event->connectionName, $queue)) {
            return;
        }

        $uuid = $event->job->uuid();

        if ($uuid === null) {
            return;
        }

        $this->safely(function () use ($event, $uuid) {
            $attempt = JobAttempt::query()
                ->where('job_uuid', $uuid)
                ->where('attempt', $event->job->attempts())
                ->first();

            if ($attempt === null) {
                return;
            }

            $now = Date::now();
            $runtimeMs = $attempt->started_at
                ? max(0, (int) ($now->getPreciseTimestamp(3) - $attempt->started_at->getPreciseTimestamp(3)))
                : null;

            $sig = ExceptionSignature::fromThrowable($event->exception);

            $attempt->fill([
                'status' => JobAttempt::STATUS_FAILED,
                'finished_at' => $now,
                'runtime_ms' => $runtimeMs,
                'memory_peak_bytes' => memory_get_peak_usage(true),
                'exception' => (string) $event->exception,
                'exception_class' => $sig['class'],
                'exception_message' => $sig['message'],
            ])->save();
        });
    }

    public function handleQueued(JobQueued $event): void
    {
        $queue = $event->queue ?? 'default';

        if (! $this->filter->shouldRecord($event->connectionName, $queue)) {
            return;
        }

        $payload = $this->decodePayload($event->payload ?? null);
        $name = $payload['displayName'] ?? $this->jobName($event->job);

        if ($this->filter->isSilencedJob($name)) {
            return;
        }

        $uuid = $this->uuidFromPayload($payload) ?? (string) Str::uuid();

        $this->safely(function () use ($event, $queue, $payload, $uuid) {
            MonitoredJob::query()->updateOrCreate(
                ['uuid' => $uuid],
                [
                    'job_id' => $this->stringOrNull($event->id),
                    'batch_id' => $payload['data']['batchId'] ?? null,
                    'name' => $payload['displayName'] ?? $this->jobName($event->job) ?? 'unknown',
                    'connection' => $event->connectionName,
                    'queue' => $queue,
                    'status' => MonitoredJob::STATUS_QUEUED,
                    'attempts' => 0,
                    'tags' => $this->tagExtractor->fromQueuedEvent($event),
                    'payload' => $this->storePayload($event->payload ?? null),
                    'queued_at' => Date::now(),
                ]
            );

            $this->metrics->increment(
                $event->connectionName,
                $queue,
                'minute',
                $this->minuteBucket(),
                ['queued' => 1]
            );
        });
    }

    public function handleProcessing(JobProcessing $event): void
    {
        $queue = $event->job->getQueue() ?? 'default';

        if (! $this->filter->shouldRecord($event->connectionName, $queue)) {
            return;
        }

        if ($this->filter->isSilencedJob($event->job->getName())) {
            return;
        }

        $uuid = $event->job->uuid();

        if ($uuid === null) {
            return;
        }

        $this->safely(function () use ($event, $queue, $uuid) {
            $now = Date::now();
            $attributes = [
                'status' => MonitoredJob::STATUS_RUNNING,
                'attempts' => $event->job->attempts(),
                'started_at' => $now,
            ];

            $existing = $this->jobs->findByUuid($uuid);

            if ($existing) {
                if ($existing->queued_at) {
                    $attributes['wait_ms'] = max(0, (int) ($now->getPreciseTimestamp(3) - $existing->queued_at->getPreciseTimestamp(3)));
                }

                $existing->fill($attributes)->save();

                return;
            }

            $payload = $event->job->payload();

            MonitoredJob::query()->create([
                'uuid' => $uuid,
                'job_id' => $this->stringOrNull($event->job->getJobId()),
                'batch_id' => $payload['data']['batchId'] ?? null,
                'name' => $payload['displayName'] ?? $event->job->getName(),
                'connection' => $event->connectionName,
                'queue' => $queue,
                'status' => MonitoredJob::STATUS_RUNNING,
                'attempts' => $event->job->attempts(),
                'tags' => $this->tagExtractor->fromJob($event->job),
                'payload' => $this->storePayload($event->job->getRawBody()),
                'started_at' => $now,
            ]);
        });

        $this->safely(function () use ($event, $uuid) {
            JobAttempt::query()->updateOrCreate(
                ['job_uuid' => $uuid, 'attempt' => $event->job->attempts()],
                [
                    'status' => JobAttempt::STATUS_RUNNING,
                    'started_at' => Date::now(),
                ]
            );
        });
    }

    public function handleProcessed(JobProcessed $event): void
    {
        $queue = $event->job->getQueue() ?? 'default';
        $uuid = $event->job->uuid();

        $this->finalize($event->connectionName, $queue, $uuid, MonitoredJob::STATUS_COMPLETED);

        if ($uuid === null) {
            return;
        }

        $this->safely(function () use ($event, $uuid) {
            $attempt = JobAttempt::query()
                ->where('job_uuid', $uuid)
                ->where('attempt', $event->job->attempts())
                ->first();

            if ($attempt === null) {
                return;
            }

            $now = Date::now();
            $runtimeMs = $attempt->started_at
                ? max(0, (int) ($now->getPreciseTimestamp(3) - $attempt->started_at->getPreciseTimestamp(3)))
                : null;

            $attempt->fill([
                'status' => JobAttempt::STATUS_COMPLETED,
                'finished_at' => $now,
                'runtime_ms' => $runtimeMs,
                'memory_peak_bytes' => memory_get_peak_usage(true),
            ])->save();
        });
    }

    public function handleFailed(JobFailed $event): void
    {
        $this->finalize(
            $event->connectionName,
            $event->job->getQueue() ?? 'default',
            $event->job->uuid(),
            MonitoredJob::STATUS_FAILED,
            (string) $event->exception
        );
    }

    protected function finalize(string $connection, string $queue, ?string $uuid, string $status, ?string $exception = null): void
    {
        if ($uuid === null || ! $this->filter->shouldRecord($connection, $queue)) {
            return;
        }

        $this->safely(function () use ($connection, $queue, $uuid, $status, $exception) {
            $job = $this->jobs->findByUuid($uuid);
            $now = Date::now();

            $runtimeMs = null;

            if ($job && $job->started_at) {
                $runtimeMs = max(0, (int) ($now->getPreciseTimestamp(3) - $job->started_at->getPreciseTimestamp(3)));
            }

            $attributes = [
                'status' => $status,
                'finished_at' => $now,
                'runtime_ms' => $runtimeMs,
                'memory_peak_bytes' => memory_get_peak_usage(true),
            ];

            if ($exception !== null) {
                $attributes['exception'] = $exception;
                $sig = ExceptionSignature::fromTraceString($exception);
                $attributes['exception_class'] = $sig['class'];
                $attributes['exception_message'] = $sig['message'];
            }

            if ($job) {
                $job->fill($attributes)->save();
            } else {
                MonitoredJob::query()->create(array_merge($attributes, [
                    'uuid' => $uuid,
                    'name' => 'unknown',
                    'connection' => $connection,
                    'queue' => $queue,
                    'attempts' => 0,
                ]));
            }

            $deltas = [
                $status === MonitoredJob::STATUS_FAILED ? 'failed' : 'processed' => 1,
            ];

            if ($runtimeMs !== null) {
                $deltas['runtime_ms_sum'] = $runtimeMs;
            }

            if ($job && $job->wait_ms !== null) {
                $deltas['wait_ms_sum'] = $job->wait_ms;
            }

            $this->metrics->increment($connection, $queue, 'minute', $this->minuteBucket(), $deltas);
        });
    }

    protected function decodePayload(?string $payload): array
    {
        if ($payload === null || $payload === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function uuidFromPayload(array $payload): ?string
    {
        $uuid = $payload['uuid'] ?? null;

        return is_string($uuid) && $uuid !== '' ? $uuid : null;
    }

    protected function jobName(mixed $job): ?string
    {
        if (is_object($job)) {
            return get_class($job);
        }

        return is_string($job) ? $job : null;
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    protected function storePayload(?string $raw): ?string
    {
        if (! config('periscope.payload.store', true) || $raw === null) {
            return null;
        }

        $max = (int) config('periscope.payload.max_size', 65_536);

        if ($max > 0 && strlen($raw) > $max) {
            return substr($raw, 0, $max);
        }

        return $raw;
    }

    protected function minuteBucket(): \DateTimeInterface
    {
        return Date::now()->startOfMinute();
    }

    protected function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
