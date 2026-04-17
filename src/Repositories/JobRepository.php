<?php

namespace MaherElGamil\Periscope\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MaherElGamil\Periscope\Models\MonitoredJob;

class JobRepository
{
    public function query(): Builder
    {
        return MonitoredJob::query();
    }

    public function findByUuid(string $uuid): ?MonitoredJob
    {
        return MonitoredJob::query()->where('uuid', $uuid)->first();
    }

    public function create(array $attributes): MonitoredJob
    {
        return MonitoredJob::query()->create($attributes);
    }

    public function updateByUuid(string $uuid, array $attributes): int
    {
        return MonitoredJob::query()->where('uuid', $uuid)->update($attributes);
    }

    public function countByStatus(string $status): int
    {
        return MonitoredJob::query()->where('status', $status)->count();
    }

    public function prune(string $status, \DateTimeInterface $before): int
    {
        return MonitoredJob::query()
            ->where('status', $status)
            ->where('finished_at', '<', $before)
            ->delete();
    }
}
