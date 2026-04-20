<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\Worker;

class OverviewStats extends Component
{
    #[Computed]
    public function jobsPerMinute(): int
    {
        return MonitoredJob::query()
            ->where('queued_at', '>=', now()->subMinute())
            ->count();
    }

    #[Computed]
    public function jobsPastHour(): int
    {
        return MonitoredJob::query()
            ->where('queued_at', '>=', now()->subHour())
            ->count();
    }

    #[Computed]
    public function failedPast7Days(): int
    {
        return MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_FAILED)
            ->where('finished_at', '>=', now()->subDays(7))
            ->count();
    }

    #[Computed]
    public function isActive(): bool
    {
        return Worker::query()->where('status', Worker::STATUS_RUNNING)->exists();
    }

    #[Computed]
    public function totalProcesses(): int
    {
        return Worker::query()->where('status', Worker::STATUS_RUNNING)->count();
    }

    #[Computed]
    public function maxWaitQueue(): ?string
    {
        return MonitoredJob::query()
            ->whereNotNull('wait_ms')
            ->where('started_at', '>=', now()->subHour())
            ->orderByDesc('wait_ms')
            ->value('queue');
    }

    #[Computed]
    public function maxRuntimeQueue(): ?string
    {
        return MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_COMPLETED)
            ->whereNotNull('runtime_ms')
            ->where('finished_at', '>=', now()->subHour())
            ->orderByDesc('runtime_ms')
            ->value('queue');
    }

    #[Computed]
    public function maxThroughputQueue(): ?string
    {
        return MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_COMPLETED)
            ->where('finished_at', '>=', now()->subHour())
            ->selectRaw('queue, count(*) as total')
            ->groupBy('queue')
            ->orderByDesc('total')
            ->value('queue');
    }

    public function render()
    {
        return view('periscope::livewire.overview-stats');
    }
}
