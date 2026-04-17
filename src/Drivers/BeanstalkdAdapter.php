<?php

namespace MaherElGamil\Periscope\Drivers;

use Illuminate\Queue\BeanstalkdQueue;
use MaherElGamil\Periscope\Contracts\DriverAdapter;
use Throwable;

class BeanstalkdAdapter implements DriverAdapter
{
    public function __construct(
        protected string $connection,
        protected BeanstalkdQueue $queue,
    ) {}

    public function connection(): string
    {
        return $this->connection;
    }

    public function pending(string $queue): ?int
    {
        return $this->stat($queue, 'current-jobs-ready');
    }

    public function delayed(string $queue): ?int
    {
        return $this->stat($queue, 'current-jobs-delayed');
    }

    public function reserved(string $queue): ?int
    {
        return $this->stat($queue, 'current-jobs-reserved');
    }

    public function queues(): ?array
    {
        try {
            $tubes = $this->queue->getPheanstalk()->listTubes();
        } catch (Throwable) {
            return null;
        }

        return array_values(array_map(fn ($tube) => (string) $tube, $tubes));
    }

    protected function stat(string $queue, string $key): ?int
    {
        try {
            $stats = $this->queue->getPheanstalk()->statsTube($queue);
        } catch (Throwable) {
            return null;
        }

        $value = is_array($stats) ? ($stats[$key] ?? null) : ($stats->$key ?? null);

        return $value === null ? null : (int) $value;
    }
}
