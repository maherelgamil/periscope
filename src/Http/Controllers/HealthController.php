<?php

namespace MaherElGamil\Periscope\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\Worker;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $config = (array) config('periscope.health', []);

        $staleWorkers = Worker::query()->where('status', Worker::STATUS_STALE)->count();

        $window = max(1, (int) ($config['failure_window_minutes'] ?? 5));
        $since = now()->subMinutes($window);

        $failed = MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_FAILED)
            ->where('finished_at', '>=', $since)
            ->count();

        $completed = MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_COMPLETED)
            ->where('finished_at', '>=', $since)
            ->count();

        $total = $completed + $failed;
        $failureRate = $total > 0 ? $failed / $total : 0.0;

        $checks = [
            'stale_workers' => [
                'value' => $staleWorkers,
                'threshold' => (int) ($config['max_stale_workers'] ?? 0),
                'ok' => $staleWorkers <= (int) ($config['max_stale_workers'] ?? 0),
            ],
            'failure_rate' => [
                'value' => round($failureRate, 4),
                'threshold' => (float) ($config['max_failure_rate'] ?? 0.25),
                'window_minutes' => $window,
                'ok' => $failureRate <= (float) ($config['max_failure_rate'] ?? 0.25),
            ],
        ];

        $status = collect($checks)->every('ok') ? 'ok' : 'unhealthy';
        $httpStatus = $status === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'checks' => $checks,
            'generated_at' => now()->toIso8601String(),
        ], $httpStatus);
    }
}
