<?php

namespace MaherElGamil\Periscope\Alerts;

use MaherElGamil\Periscope\Models\MonitoredJob;

class FailureSpikeRule implements Rule
{
    public function evaluate(array $config): ?Alert
    {
        $threshold = (int) ($config['threshold'] ?? 10);
        $minutes = (int) ($config['minutes'] ?? 5);

        $count = MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_FAILED)
            ->where('finished_at', '>=', now()->subMinutes($minutes))
            ->count();

        if ($count < $threshold) {
            return null;
        }

        return new Alert(
            key: 'failure_spike',
            title: 'Queue failure spike',
            message: "{$count} job(s) failed in the last {$minutes} minutes (threshold: {$threshold}).",
            severity: 'error',
            context: ['count' => $count, 'minutes' => $minutes, 'threshold' => $threshold],
        );
    }
}
