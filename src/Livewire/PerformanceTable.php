<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use MaherElGamil\Periscope\Models\MonitoredJob;

class PerformanceTable extends Component
{
    #[Url(as: 'hours')]
    public int $hours = 24;

    public function render()
    {
        $since = now()->subHours(max(1, $this->hours));

        $queues = MonitoredJob::query()
            ->select('connection', 'queue')
            ->distinct()
            ->where('finished_at', '>=', $since)
            ->where('status', MonitoredJob::STATUS_COMPLETED)
            ->get();

        $rows = [];

        foreach ($queues as $q) {
            $runtimes = MonitoredJob::query()
                ->where('connection', $q->connection)
                ->where('queue', $q->queue)
                ->where('status', MonitoredJob::STATUS_COMPLETED)
                ->where('finished_at', '>=', $since)
                ->whereNotNull('runtime_ms')
                ->limit(5000)
                ->orderByDesc('finished_at')
                ->pluck('runtime_ms')
                ->sort()
                ->values()
                ->all();

            $waits = MonitoredJob::query()
                ->where('connection', $q->connection)
                ->where('queue', $q->queue)
                ->where('status', MonitoredJob::STATUS_COMPLETED)
                ->where('finished_at', '>=', $since)
                ->whereNotNull('wait_ms')
                ->limit(5000)
                ->orderByDesc('finished_at')
                ->pluck('wait_ms')
                ->sort()
                ->values()
                ->all();

            $rows[] = [
                'connection' => $q->connection,
                'queue' => $q->queue,
                'count' => count($runtimes),
                'runtime_p50' => $this->percentile($runtimes, 0.5),
                'runtime_p95' => $this->percentile($runtimes, 0.95),
                'runtime_p99' => $this->percentile($runtimes, 0.99),
                'wait_p50' => $this->percentile($waits, 0.5),
                'wait_p95' => $this->percentile($waits, 0.95),
                'wait_p99' => $this->percentile($waits, 0.99),
            ];
        }

        return view('periscope::livewire.performance-table', ['rows' => $rows]);
    }

    protected function percentile(array $sorted, float $p): ?int
    {
        $n = count($sorted);

        if ($n === 0) {
            return null;
        }

        $index = (int) ceil($p * $n) - 1;

        return (int) $sorted[max(0, min($n - 1, $index))];
    }
}
