<?php

namespace MaherElGamil\Periscope\Alerts;

use MaherElGamil\Periscope\Models\Worker;

class StaleWorkerRule implements Rule
{
    public function evaluate(array $config): ?Alert
    {
        $count = Worker::query()->where('status', Worker::STATUS_STALE)->count();

        if ($count === 0) {
            return null;
        }

        return new Alert(
            key: 'stale_worker',
            title: 'Stale worker detected',
            message: "{$count} worker(s) have missed their heartbeat.",
            severity: 'error',
            context: ['count' => $count],
        );
    }
}
