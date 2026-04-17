<?php

namespace MaherElGamil\Periscope\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MaherElGamil\Periscope\Models\Worker;

class WorkerRepository
{
    public function query(): Builder
    {
        return Worker::query();
    }

    public function heartbeat(string $name, array $attributes): Worker
    {
        return tap(Worker::query()->firstOrNew(['name' => $name]), function (Worker $worker) use ($attributes) {
            $worker->fill($attributes);
            $worker->last_heartbeat_at = now();
            $worker->save();
        });
    }

    public function markStale(\DateTimeInterface $threshold): int
    {
        return Worker::query()
            ->where('status', Worker::STATUS_RUNNING)
            ->where('last_heartbeat_at', '<', $threshold)
            ->update(['status' => Worker::STATUS_STALE]);
    }
}
