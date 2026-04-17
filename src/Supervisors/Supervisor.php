<?php

namespace MaherElGamil\Periscope\Supervisors;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class Supervisor
{
    /** @var array<int, Process> */
    protected array $processes = [];

    public function __construct(
        public readonly string $name,
        public readonly array $config,
        protected string $basePath,
    ) {}

    public function ensureRunning(): void
    {
        $target = max(1, (int) ($this->config['processes'] ?? 1));

        foreach ($this->processes as $i => $process) {
            if (! $process->isRunning()) {
                unset($this->processes[$i]);
            }
        }

        while (count($this->processes) < $target) {
            $this->processes[] = $this->spawn(count($this->processes));
        }
    }

    /**
     * @return array<int, array{pid: ?int, running: bool, name: string}>
     */
    public function status(): array
    {
        $snapshot = [];

        foreach ($this->processes as $i => $process) {
            $snapshot[] = [
                'name' => $this->workerName($i),
                'pid' => $process->getPid(),
                'running' => $process->isRunning(),
            ];
        }

        return $snapshot;
    }

    public function terminate(int $graceSeconds = 15): void
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->signal(SIGTERM);
            }
        }

        $deadline = time() + $graceSeconds;

        while (time() < $deadline) {
            $running = 0;

            foreach ($this->processes as $process) {
                if ($process->isRunning()) {
                    $running++;
                }
            }

            if ($running === 0) {
                break;
            }

            usleep(200_000);
        }

        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->stop(0, SIGKILL);
            }
        }

        $this->processes = [];
    }

    protected function spawn(int $index): Process
    {
        $php = (new PhpExecutableFinder)->find(false) ?: 'php';

        $args = [
            $php,
            'artisan',
            'periscope:supervise',
            $this->config['connection'] ?? 'redis',
            '--name='.$this->workerName($index),
            '--queue='.$this->normalizedQueue(),
            '--timeout='.($this->config['timeout'] ?? 60),
            '--tries='.($this->config['tries'] ?? 1),
            '--sleep='.($this->config['sleep'] ?? 3),
            '--memory='.($this->config['memory'] ?? 128),
            '--backoff='.($this->config['backoff'] ?? 0),
            '--max-jobs='.($this->config['max_jobs'] ?? 0),
            '--max-time='.($this->config['max_time'] ?? 0),
            '--rest='.($this->config['rest'] ?? 0),
        ];

        $process = new Process($args, $this->basePath);
        $process->setTimeout(null);
        $process->start();

        return $process;
    }

    protected function workerName(int $index): string
    {
        $host = gethostname() ?: 'worker';

        return sprintf('%s-%s-%d', $host, $this->name, $index);
    }

    protected function normalizedQueue(): string
    {
        $queue = $this->config['queue'] ?? 'default';

        return is_array($queue) ? implode(',', $queue) : (string) $queue;
    }
}
