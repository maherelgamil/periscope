<?php

namespace MaherElGamil\Periscope\Listeners;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Date;
use MaherElGamil\Periscope\Models\ScheduleRun;
use Throwable;

class RecordScheduleLifecycle
{
    /** @var array<string, int> */
    protected array $runs = [];

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(ScheduledTaskStarting::class, [self::class, 'handleStarting']);
        $events->listen(ScheduledTaskFinished::class, [self::class, 'handleFinished']);
        $events->listen(ScheduledTaskFailed::class, [self::class, 'handleFailed']);
        $events->listen(ScheduledTaskSkipped::class, [self::class, 'handleSkipped']);
    }

    public function handleStarting(ScheduledTaskStarting $event): void
    {
        $this->safely(function () use ($event) {
            $run = ScheduleRun::query()->create([
                'command' => $this->description($event),
                'expression' => $event->task->expression ?? null,
                'status' => ScheduleRun::STATUS_RUNNING,
                'started_at' => Date::now(),
            ]);

            $this->runs[$this->key($event)] = $run->id;
        });
    }

    public function handleFinished(ScheduledTaskFinished $event): void
    {
        $this->finish($event, ScheduleRun::STATUS_COMPLETED);
    }

    public function handleFailed(ScheduledTaskFailed $event): void
    {
        $this->finish($event, ScheduleRun::STATUS_FAILED, (string) ($event->exception ?? ''));
    }

    public function handleSkipped(ScheduledTaskSkipped $event): void
    {
        $this->safely(function () use ($event) {
            ScheduleRun::query()->create([
                'command' => $this->description($event),
                'expression' => $event->task->expression ?? null,
                'status' => ScheduleRun::STATUS_SKIPPED,
                'finished_at' => Date::now(),
            ]);
        });
    }

    protected function finish(object $event, string $status, ?string $exception = null): void
    {
        $this->safely(function () use ($event, $status, $exception) {
            $id = $this->runs[$this->key($event)] ?? null;

            if ($id === null) {
                return;
            }

            $run = ScheduleRun::query()->find($id);

            if ($run === null) {
                return;
            }

            $now = Date::now();
            $runtime = $run->started_at
                ? max(0, (int) ($now->getPreciseTimestamp(3) - $run->started_at->getPreciseTimestamp(3)))
                : null;

            $run->fill([
                'status' => $status,
                'runtime_ms' => $runtime,
                'finished_at' => $now,
                'exception' => $exception,
            ])->save();

            unset($this->runs[$this->key($event)]);
        });
    }

    protected function description(object $event): string
    {
        $task = $event->task;

        if (isset($task->description) && $task->description) {
            return (string) $task->description;
        }

        if (method_exists($task, 'command') && $task->command) {
            return (string) $task->command;
        }

        return $task->command ?? 'closure';
    }

    protected function key(object $event): string
    {
        return spl_object_hash($event->task).'|'.($event->task->expression ?? '');
    }

    protected function safely(callable $cb): void
    {
        try {
            $cb();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
