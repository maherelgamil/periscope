<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MaherElGamil\Periscope\Supervisors\Master;
use MaherElGamil\Periscope\Supervisors\Supervisor;
use MaherElGamil\Periscope\Support\QueueSize;
use Throwable;

class StartCommand extends Command
{
    protected $signature = 'periscope:start {--supervisor=* : Run only the named supervisors (defaults to all)}';

    protected $description = 'Start all configured Periscope supervisors and keep them alive';

    public function handle(QueueSize $queueSize): int
    {
        $env = app()->environment();
        $envSupervisors = (array) config("periscope.environments.{$env}.supervisors", []);
        $configured = $envSupervisors !== []
            ? $envSupervisors
            : (array) config('periscope.supervisors', []);

        $only = $this->option('supervisor');

        if ($configured === []) {
            $this->components->warn('No supervisors configured. Define them in config/periscope.php.');

            return self::SUCCESS;
        }

        // Clean up stale worker records from previous runs
        $this->call('periscope:workers:sweep');

        $master = new Master(base_path());
        $booted = [];

        $forwarder = function (string $supervisor, string $queue, string $type, string $buffer) {
            foreach (preg_split("/\r?\n/", rtrim($buffer, "\r\n")) as $line) {
                if ($line === '') {
                    continue;
                }

                $prefix = sprintf('<fg=gray>[%s/%s]</>', $supervisor, $queue);
                $this->output->writeln($prefix.' '.$line);
            }
        };

        foreach ($configured as $name => $config) {
            if ($only !== [] && ! in_array($name, $only, true)) {
                continue;
            }

            $supervisor = new Supervisor($name, $config, base_path(), $queueSize);
            $supervisor->forwardOutput($forwarder);
            $master->add($supervisor);

            $processes = max(1, (int) ($config['processes'] ?? 1));
            $balance = ($config['balance'] ?? null) === 'auto' ? 'auto' : 'static';
            $booted[] = "{$name} ({$processes}p, {$balance})";
        }

        $this->components->info('Periscope started successfully. Watching: '.implode(', ', $booted));

        $previousPaused = false;

        try {
            $master->run(function (array $status, bool $paused) use (&$previousPaused) {
                if ($paused && ! $previousPaused) {
                    $this->components->warn('Periscope paused — run `periscope:continue` to resume.');
                } elseif (! $paused && $previousPaused) {
                    $this->components->info('Periscope resumed.');
                }

                $previousPaused = $paused;

                if ($this->output->isVerbose()) {
                    foreach ($status as $name => $workers) {
                        $running = count(array_filter($workers, fn ($w) => $w['running']));
                        $this->components->twoColumnDetail($name, "{$running}/".count($workers).' running');
                    }
                }
            });
        } catch (Throwable $e) {
            Log::error('Periscope master crashed', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->components->error('Periscope master crashed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Periscope stopped.');

        return self::SUCCESS;
    }
}
