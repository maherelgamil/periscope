<?php

namespace MaherElGamil\Periscope\Supervisors;

use MaherElGamil\Periscope\Support\QueueSize;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class Supervisor
{
    /** @var array<string, array<int, Process>> */
    protected array $processes = [];

    public function __construct(
        public readonly string $name,
        public readonly array $config,
        protected string $basePath,
        protected ?QueueSize $queueSize = null,
    ) {}

    public function ensureRunning(): void
    {
        $targets = $this->computeTargets();

        foreach ($targets as $queue => $target) {
            $this->processes[$queue] ??= [];

            foreach ($this->processes[$queue] as $i => $process) {
                if (! $process->isRunning()) {
                    unset($this->processes[$queue][$i]);
                }
            }

            $this->processes[$queue] = array_values($this->processes[$queue]);

            while (count($this->processes[$queue]) < $target) {
                $this->processes[$queue][] = $this->spawn($queue, count($this->processes[$queue]));
            }

            while (count($this->processes[$queue]) > $target) {
                $process = array_pop($this->processes[$queue]);
                if ($process && $process->isRunning()) {
                    $process->signal(SIGTERM);
                }
            }
        }
    }

    /**
     * @return array<int, array{pid: ?int, running: bool, name: string, queue: string}>
     */
    public function status(): array
    {
        $snapshot = [];

        foreach ($this->processes as $queue => $processes) {
            foreach ($processes as $i => $process) {
                $snapshot[] = [
                    'name' => $this->workerName($queue, $i),
                    'queue' => $queue,
                    'pid' => $process->getPid(),
                    'running' => $process->isRunning(),
                ];
            }
        }

        return $snapshot;
    }

    public function terminate(int $graceSeconds = 15): void
    {
        $all = [];

        foreach ($this->processes as $processes) {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->signal(SIGTERM);
                }
                $all[] = $process;
            }
        }

        $deadline = time() + $graceSeconds;

        while (time() < $deadline) {
            $running = 0;

            foreach ($all as $process) {
                if ($process->isRunning()) {
                    $running++;
                }
            }

            if ($running === 0) {
                break;
            }

            usleep(200_000);
        }

        foreach ($all as $process) {
            if ($process->isRunning()) {
                $process->stop(0, SIGKILL);
            }
        }

        $this->processes = [];
    }

    /**
     * @return array<string, int> queue => target process count
     */
    protected function computeTargets(): array
    {
        $queues = $this->normalizedQueues();
        $balance = $this->config['balance'] ?? null;
        $total = max(1, (int) ($this->config['processes'] ?? 1));

        if ($balance !== 'auto' || $this->queueSize === null) {
            // Non-balanced mode: a single pool listening to all queues (priority order).
            $joined = implode(',', $queues);

            return [$joined => $total];
        }

        $connection = $this->config['connection'] ?? 'redis';
        $min = max(1, (int) ($this->config['min_processes'] ?? 1));
        $max = max($min, (int) ($this->config['max_processes'] ?? $total));
        $adapter = $this->queueSize->adapter($connection);
        $pending = [];
        $totalPending = 0;

        foreach ($queues as $queue) {
            $depth = max(0, (int) ($adapter->pending($queue) ?? 0));
            $pending[$queue] = $depth;
            $totalPending += $depth;
        }

        if ($totalPending === 0) {
            return array_fill_keys($queues, $min);
        }

        $targets = [];
        $allocated = 0;

        foreach ($queues as $queue) {
            $share = (int) round(($pending[$queue] / $totalPending) * $max);
            $targets[$queue] = max($min, min($max, $share));
            $allocated += $targets[$queue];
        }

        // Trim to max if rounding pushed us over
        while ($allocated > $max) {
            arsort($targets);
            foreach ($targets as $queue => $count) {
                if ($count > $min) {
                    $targets[$queue]--;
                    $allocated--;
                    break;
                }
            }
        }

        return $targets;
    }

    protected function spawn(string $queue, int $index): Process
    {
        $php = (new PhpExecutableFinder)->find(false) ?: 'php';

        $args = [
            $php,
            'artisan',
            'periscope:supervise',
            $this->config['connection'] ?? 'redis',
            '--name='.$this->workerName($queue, $index),
            '--queue='.$queue,
            '--timeout='.($this->config['timeout'] ?? 60),
            '--tries='.($this->config['tries'] ?? 1),
            '--sleep='.($this->config['sleep'] ?? 3),
            '--memory='.($this->config['memory'] ?? 128),
            '--backoff='.($this->config['backoff'] ?? 0),
            '--max-jobs='.($this->config['max_jobs'] ?? 0),
            '--max-time='.($this->config['max_time'] ?? 0),
            '--rest='.($this->config['rest'] ?? 0),
        ];

        if (($nice = $this->config['nice'] ?? null) !== null && ! $this->isWindows()) {
            array_unshift($args, 'nice', '-n', (string) $nice);
        }

        $process = new Process($args, $this->basePath);
        $process->setTimeout(null);
        $process->start();

        return $process;
    }

    protected function isWindows(): bool
    {
        return str_starts_with(strtolower(PHP_OS_FAMILY), 'win');
    }

    protected function workerName(string $queue, int $index): string
    {
        $host = gethostname() ?: 'worker';
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $queue);

        return sprintf('%s-%s-%s-%d', $host, $this->name, trim((string) $slug, '-'), $index);
    }

    /**
     * @return array<int, string>
     */
    protected function normalizedQueues(): array
    {
        $queue = $this->config['queue'] ?? 'default';
        $queues = is_array($queue) ? $queue : explode(',', (string) $queue);

        return array_values(array_filter(array_map('trim', $queues)));
    }
}
