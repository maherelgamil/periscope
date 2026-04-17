<?php

namespace MaherElGamil\Periscope\Alerts;

use MaherElGamil\Periscope\Models\MonitoredJob;

class LongWaitRule implements Rule
{
    public function evaluate(array $config): ?Alert
    {
        $thresholdMs = (int) ($config['threshold_ms'] ?? 30_000);
        $minutes = (int) ($config['minutes'] ?? 5);

        $avg = (int) MonitoredJob::query()
            ->whereNotNull('wait_ms')
            ->where('started_at', '>=', now()->subMinutes($minutes))
            ->avg('wait_ms');

        if ($avg < $thresholdMs) {
            return null;
        }

        return new Alert(
            key: 'long_wait',
            title: 'Queue wait time is high',
            message: "Average wait over last {$minutes} minutes is {$avg} ms (threshold: {$thresholdMs} ms).",
            severity: 'warning',
            context: ['avg_ms' => $avg, 'minutes' => $minutes, 'threshold_ms' => $thresholdMs],
        );
    }
}
