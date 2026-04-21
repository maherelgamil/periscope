<?php

namespace MaherElGamil\Periscope\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\Worker;
use MaherElGamil\Periscope\Support\QueueSize;

class WorkloadTable extends Component
{
    public function render(QueueSize $sizes)
    {
        $runningWorkers = Worker::query()
            ->where('status', Worker::STATUS_RUNNING)
            ->get(['queues']);

        $rows = [];

        foreach ($this->configuredQueues() as $connection => $queues) {
            foreach ((array) $queues as $queue) {
                if ($queue === '*') {
                    $discovered = $sizes->adapter($connection)->queues() ?? [];

                    foreach ($discovered as $name) {
                        $rows[] = $this->row($sizes, $runningWorkers, $connection, $name);
                    }

                    continue;
                }

                $rows[] = $this->row($sizes, $runningWorkers, $connection, $queue);
            }
        }

        return view('periscope::livewire.workload-table', ['rows' => $rows]);
    }

    /** @param Collection<int, Worker> $runningWorkers */
    protected function row(QueueSize $sizes, $runningWorkers, string $connection, string $queue): array
    {
        $pending = $sizes->adapter($connection)->pending($queue)
            ?? MonitoredJob::query()->where('status', MonitoredJob::STATUS_QUEUED)->where('queue', $queue)->count();

        $processes = $runningWorkers->filter(
            fn ($w) => is_array($w->queues) && in_array($queue, $w->queues)
        )->count();

        $oldestQueuedAt = MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_QUEUED)
            ->where('queue', $queue)
            ->min('queued_at');

        return [
            'queue' => $queue,
            'jobs' => $pending ?? 0,
            'processes' => $processes,
            'wait' => $oldestQueuedAt ? Carbon::parse($oldestQueuedAt)->diffForHumans(null, true) : null,
        ];
    }

    protected function configuredQueues(): array
    {
        $configured = (array) config('periscope.queues', []);

        if ($configured !== []) {
            return $configured;
        }

        $default = config('queue.default');
        $queue = config("queue.connections.$default.queue", 'default');

        return [$default => [$queue]];
    }
}
