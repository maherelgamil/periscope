<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Component;
use MaherElGamil\Periscope\Models\QueueMetric;

class ThroughputChart extends Component
{
    public int $minutes = 60;

    public function render()
    {
        $since = now()->subMinutes($this->minutes)->startOfMinute();

        $rows = QueueMetric::query()
            ->where('period', 'minute')
            ->where('bucket', '>=', $since)
            ->orderBy('bucket')
            ->get(['bucket', 'processed', 'failed']);

        $buckets = collect(range(0, $this->minutes - 1))
            ->map(fn ($n) => now()->subMinutes($this->minutes - 1 - $n)->startOfMinute())
            ->map(fn ($ts) => [
                'label' => $ts->format('H:i'),
                'bucket' => $ts->format('Y-m-d H:i:00'),
                'processed' => 0,
                'failed' => 0,
            ]);

        $indexed = $rows->groupBy(fn ($r) => $r->bucket->format('Y-m-d H:i:00'))
            ->map(fn ($group) => [
                'processed' => (int) $group->sum('processed'),
                'failed' => (int) $group->sum('failed'),
            ]);

        $series = $buckets->map(function ($row) use ($indexed) {
            $data = $indexed->get($row['bucket']);

            return [
                'label' => $row['label'],
                'processed' => $data['processed'] ?? 0,
                'failed' => $data['failed'] ?? 0,
            ];
        });

        $max = max(1, $series->max('processed'), $series->max('failed'));

        return view('periscope::livewire.throughput-chart', [
            'series' => $series,
            'max' => $max,
        ]);
    }
}
