<?php

namespace MaherElGamil\Periscope\Support;

use Illuminate\Support\Facades\Date;
use MaherElGamil\Periscope\Models\Worker;
use MaherElGamil\Periscope\Repositories\WorkerRepository;
use Throwable;

class Heartbeat
{
    protected ?int $lastBeatAt = null;

    public function __construct(protected WorkerRepository $workers) {}

    public function boot(string $name, string $connection, array $queues): void
    {
        $this->lastBeatAt = null;

        $this->safely(fn () => $this->workers->heartbeat($name, [
            'hostname' => gethostname() ?: null,
            'pid' => getmypid() ?: null,
            'connection' => $connection,
            'queues' => $queues,
            'status' => Worker::STATUS_RUNNING,
            'started_at' => Date::now(),
        ]));
    }

    public function tick(string $name, string $connection, array $queues): void
    {
        $interval = max(1, (int) config('periscope.workers.heartbeat_seconds', 15));
        $now = time();

        if ($this->lastBeatAt !== null && ($now - $this->lastBeatAt) < $interval) {
            return;
        }

        $this->lastBeatAt = $now;

        $this->safely(fn () => $this->workers->heartbeat($name, [
            'hostname' => gethostname() ?: null,
            'pid' => getmypid() ?: null,
            'connection' => $connection,
            'queues' => $queues,
            'status' => Worker::STATUS_RUNNING,
        ]));
    }

    public function stop(string $name): void
    {
        $this->safely(fn () => $this->workers->heartbeat($name, [
            'status' => Worker::STATUS_STOPPED,
        ]));
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
