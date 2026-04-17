<?php

namespace MaherElGamil\Periscope\Alerts;

use MaherElGamil\Periscope\Models\MonitoredJob;

class FailureSpikeRule implements Rule
{
    public function evaluate(array $config): ?Alert
    {
        $defaultThreshold = (int) ($config['threshold'] ?? 10);
        $minutes = (int) ($config['minutes'] ?? 5);
        $perQueue = (array) ($config['per_queue'] ?? []);
        $since = now()->subMinutes($minutes);

        $rows = MonitoredJob::query()
            ->selectRaw('connection, queue, COUNT(*) as c')
            ->where('status', MonitoredJob::STATUS_FAILED)
            ->where('finished_at', '>=', $since)
            ->groupBy('connection', 'queue')
            ->get();

        $breaches = [];
        $globalCount = 0;

        foreach ($rows as $row) {
            $globalCount += (int) $row->c;
            $key = $row->connection.':'.$row->queue;
            $threshold = (int) ($perQueue[$key]['threshold'] ?? $perQueue[$row->queue]['threshold'] ?? $defaultThreshold);

            if ((int) $row->c >= $threshold) {
                $breaches[] = sprintf('%s/%s: %d ≥ %d', $row->connection, $row->queue, $row->c, $threshold);
            }
        }

        if ($breaches === [] && $globalCount < $defaultThreshold) {
            return null;
        }

        $message = $breaches !== []
            ? 'Failure thresholds breached: '.implode(', ', $breaches)
            : "{$globalCount} job(s) failed in the last {$minutes} minutes (threshold: {$defaultThreshold}).";

        return new Alert(
            key: 'failure_spike',
            title: 'Queue failure spike',
            message: $message,
            severity: 'error',
            context: ['count' => $globalCount, 'minutes' => $minutes, 'breaches' => $breaches],
        );
    }
}
