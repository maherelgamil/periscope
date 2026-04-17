<?php

namespace MaherElGamil\Periscope\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MaherElGamil\Periscope\Models\QueueMetric;

class MetricRepository
{
    public function query(): Builder
    {
        return QueueMetric::query();
    }

    public function increment(string $connection, string $queue, string $period, \DateTimeInterface $bucket, array $deltas): void
    {
        $metric = QueueMetric::query()->firstOrCreate(
            [
                'connection' => $connection,
                'queue' => $queue,
                'period' => $period,
                'bucket' => $bucket,
            ],
            [
                'queued' => 0,
                'processed' => 0,
                'failed' => 0,
                'runtime_ms_sum' => 0,
                'wait_ms_sum' => 0,
            ]
        );

        foreach ($deltas as $column => $amount) {
            if ($amount !== 0) {
                $metric->increment($column, $amount);
            }
        }
    }

    public function prune(\DateTimeInterface $before): int
    {
        return QueueMetric::query()->where('bucket', '<', $before)->delete();
    }

    public function pruneByPeriod(string $period, \DateTimeInterface $before): int
    {
        return QueueMetric::query()
            ->where('period', $period)
            ->where('bucket', '<', $before)
            ->delete();
    }
}
