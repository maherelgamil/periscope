<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\Worker;

class OverviewStats extends Component
{
    #[Computed]
    public function totals(): array
    {
        $since = now()->subHour();

        return [
            'running' => MonitoredJob::query()->where('status', MonitoredJob::STATUS_RUNNING)->count(),
            'queued' => MonitoredJob::query()->where('status', MonitoredJob::STATUS_QUEUED)->count(),
            'completed_last_hour' => MonitoredJob::query()
                ->where('status', MonitoredJob::STATUS_COMPLETED)
                ->where('finished_at', '>=', $since)
                ->count(),
            'failed_last_hour' => MonitoredJob::query()
                ->where('status', MonitoredJob::STATUS_FAILED)
                ->where('finished_at', '>=', $since)
                ->count(),
            'workers_running' => Worker::query()->where('status', Worker::STATUS_RUNNING)->count(),
            'workers_stale' => Worker::query()->where('status', Worker::STATUS_STALE)->count(),
        ];
    }

    #[Computed]
    public function isActive(): bool
    {
        return Worker::query()->where('status', Worker::STATUS_RUNNING)->exists();
    }

    #[Computed]
    public function avgRuntimeMs(): int
    {
        return (int) MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_COMPLETED)
            ->where('finished_at', '>=', now()->subHour())
            ->avg('runtime_ms');
    }

    #[Computed]
    public function avgWaitMs(): int
    {
        return (int) MonitoredJob::query()
            ->whereNotNull('wait_ms')
            ->where('started_at', '>=', now()->subHour())
            ->avg('wait_ms');
    }

    public function render()
    {
        return view('periscope::livewire.overview-stats');
    }
}
