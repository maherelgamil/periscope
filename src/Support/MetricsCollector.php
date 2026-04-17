<?php

namespace MaherElGamil\Periscope\Support;

use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Models\QueueMetric;
use MaherElGamil\Periscope\Models\Worker;

class MetricsCollector
{
    public function __construct(protected QueueSize $queueSize) {}

    /**
     * @return array{
     *     jobs: array<int, array{connection: string, queue: string, processed: int, failed: int, queued: int}>,
     *     runtime: array<int, array{connection: string, queue: string, runtime_ms_sum: int, wait_ms_sum: int}>,
     *     queues: array<int, array{connection: string, queue: string, pending: ?int, delayed: ?int, reserved: ?int}>,
     *     workers: array{running: int, stale: int, stopped: int},
     *     jobs_current: array{queued: int, running: int, completed: int, failed: int}
     * }
     */
    public function collect(): array
    {
        $metrics = QueueMetric::query()
            ->select(['connection', 'queue'])
            ->selectRaw('SUM(queued) as queued_sum, SUM(processed) as processed_sum, SUM(failed) as failed_sum, SUM(runtime_ms_sum) as runtime_ms_sum, SUM(wait_ms_sum) as wait_ms_sum')
            ->groupBy('connection', 'queue')
            ->get();

        $jobs = $metrics->map(fn ($m) => [
            'connection' => $m->connection,
            'queue' => $m->queue,
            'processed' => (int) $m->processed_sum,
            'failed' => (int) $m->failed_sum,
            'queued' => (int) $m->queued_sum,
        ])->all();

        $runtime = $metrics->map(fn ($m) => [
            'connection' => $m->connection,
            'queue' => $m->queue,
            'runtime_ms_sum' => (int) $m->runtime_ms_sum,
            'wait_ms_sum' => (int) $m->wait_ms_sum,
        ])->all();

        $queues = [];

        foreach ($this->configuredQueues() as $connection => $queuesList) {
            foreach ((array) $queuesList as $queue) {
                if ($queue === '*') {
                    foreach ($this->queueSize->adapter($connection)->queues() ?? [] as $discovered) {
                        $queues[] = ['connection' => $connection, 'queue' => $discovered] + $this->queueSize->sizes($connection, $discovered);
                    }

                    continue;
                }

                $queues[] = ['connection' => $connection, 'queue' => $queue] + $this->queueSize->sizes($connection, $queue);
            }
        }

        $workerStatuses = Worker::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $jobStatuses = MonitoredJob::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $tagCounts = $this->topTags();

        return [
            'jobs' => $jobs,
            'runtime' => $runtime,
            'queues' => $queues,
            'workers' => [
                'running' => (int) ($workerStatuses['running'] ?? 0),
                'stale' => (int) ($workerStatuses['stale'] ?? 0),
                'stopped' => (int) ($workerStatuses['stopped'] ?? 0),
            ],
            'jobs_current' => [
                'queued' => (int) ($jobStatuses['queued'] ?? 0),
                'running' => (int) ($jobStatuses['running'] ?? 0),
                'completed' => (int) ($jobStatuses['completed'] ?? 0),
                'failed' => (int) ($jobStatuses['failed'] ?? 0),
            ],
            'tags' => $tagCounts,
        ];
    }

    /**
     * @return array<int, array{tag: string, processed: int, failed: int}>
     */
    protected function topTags(int $limit = 20): array
    {
        $since = now()->subHour();

        $rows = MonitoredJob::query()
            ->select(['tags', 'status'])
            ->whereNotNull('tags')
            ->where('finished_at', '>=', $since)
            ->limit(5000)
            ->get();

        $counts = [];

        foreach ($rows as $row) {
            $tags = $row->tags ?? [];
            foreach ($tags as $tag) {
                $counts[$tag] ??= ['processed' => 0, 'failed' => 0];
                $bucket = $row->status === 'failed' ? 'failed' : 'processed';
                $counts[$tag][$bucket]++;
            }
        }

        uasort($counts, fn ($a, $b) => ($b['processed'] + $b['failed']) <=> ($a['processed'] + $a['failed']));

        $result = [];
        foreach (array_slice($counts, 0, $limit, true) as $tag => $data) {
            $result[] = ['tag' => $tag, 'processed' => $data['processed'], 'failed' => $data['failed']];
        }

        return $result;
    }

    protected function configuredQueues(): array
    {
        $configured = (array) config('periscope.queues', []);

        if ($configured !== []) {
            return $configured;
        }

        $default = config('queue.default');

        return [$default => [config("queue.connections.$default.queue", 'default')]];
    }
}
