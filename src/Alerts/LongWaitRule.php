<?php

namespace MaherElGamil\Periscope\Alerts;

use MaherElGamil\Periscope\Models\MonitoredJob;

class LongWaitRule implements Rule
{
    public function evaluate(array $config): ?Alert
    {
        $defaultMs = (int) ($config['threshold_ms'] ?? 30_000);
        $minutes = (int) ($config['minutes'] ?? 5);
        $perQueue = (array) ($config['per_queue'] ?? []);
        $since = now()->subMinutes($minutes);

        $rows = MonitoredJob::query()
            ->selectRaw('connection, queue, AVG(wait_ms) as avg_wait')
            ->whereNotNull('wait_ms')
            ->where('started_at', '>=', $since)
            ->groupBy('connection', 'queue')
            ->get();

        $breaches = [];
        $worst = ['queue' => null, 'avg' => 0];

        foreach ($rows as $row) {
            $avg = (int) $row->avg_wait;
            $key = $row->connection.':'.$row->queue;
            $threshold = (int) ($perQueue[$key]['threshold_ms'] ?? $perQueue[$row->queue]['threshold_ms'] ?? $defaultMs);

            if ($avg > $worst['avg']) {
                $worst = ['queue' => "{$row->connection}/{$row->queue}", 'avg' => $avg];
            }

            if ($avg >= $threshold) {
                $breaches[] = sprintf('%s/%s: %d ms ≥ %d ms', $row->connection, $row->queue, $avg, $threshold);
            }
        }

        if ($breaches === []) {
            return null;
        }

        return new Alert(
            key: 'long_wait',
            title: 'Queue wait time is high',
            message: 'Wait thresholds breached: '.implode(', ', $breaches),
            severity: 'warning',
            context: ['minutes' => $minutes, 'breaches' => $breaches, 'worst' => $worst],
        );
    }
}
