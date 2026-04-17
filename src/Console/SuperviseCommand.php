<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Str;
use MaherElGamil\Periscope\Support\Heartbeat;

class SuperviseCommand extends Command
{
    protected $signature = 'periscope:supervise
        {connection? : The queue connection to work}
        {--name= : The unique name of the supervised worker}
        {--queue= : The queues the worker should consume}
        {--once : Process the next job on the queue then stop}
        {--stop-when-empty : Stop when the queue is empty}
        {--timeout=60 : Seconds a child process can run before being killed}
        {--tries=1 : Number of times to attempt a job before logging it as failed}
        {--backoff=0 : Number of seconds to wait before retrying a failed job}
        {--memory=128 : The memory limit in megabytes}
        {--sleep=3 : Number of seconds to sleep when no job is available}
        {--max-jobs=0 : The number of jobs to process before stopping}
        {--max-time=0 : The maximum seconds the worker should run}
        {--rest=0 : Number of seconds to rest between jobs}';

    protected $description = 'Run a queue worker with Periscope heartbeat reporting';

    public function handle(Dispatcher $events, Heartbeat $heartbeat): int
    {
        $connection = $this->argument('connection') ?: config('queue.default');
        $queues = $this->normalizeQueues($connection, $this->option('queue'));
        $name = $this->option('name') ?: $this->generateName($connection);

        $heartbeat->boot($name, $connection, $queues);

        $events->listen(Looping::class, function (Looping $event) use ($heartbeat, $name, $connection, $queues) {
            $heartbeat->tick($name, $event->connectionName ?? $connection, $queues);
        });

        $events->listen(WorkerStopping::class, function () use ($heartbeat, $name) {
            $heartbeat->stop($name);
        });

        register_shutdown_function(function () use ($heartbeat, $name) {
            $heartbeat->stop($name);
        });

        return $this->call('queue:work', [
            'connection' => $connection,
            '--name' => $name,
            '--queue' => implode(',', $queues),
            '--once' => $this->option('once'),
            '--stop-when-empty' => $this->option('stop-when-empty'),
            '--timeout' => $this->option('timeout'),
            '--tries' => $this->option('tries'),
            '--backoff' => $this->option('backoff'),
            '--memory' => $this->option('memory'),
            '--sleep' => $this->option('sleep'),
            '--max-jobs' => $this->option('max-jobs'),
            '--max-time' => $this->option('max-time'),
            '--rest' => $this->option('rest'),
        ]);
    }

    protected function normalizeQueues(string $connection, ?string $queue): array
    {
        if ($queue === null || $queue === '') {
            $queue = config("queue.connections.$connection.queue", 'default');
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $queue))));
    }

    protected function generateName(string $connection): string
    {
        return sprintf('%s-%s-%s', gethostname() ?: 'worker', $connection, Str::random(6));
    }
}
