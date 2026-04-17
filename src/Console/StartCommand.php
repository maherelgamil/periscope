<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use MaherElGamil\Periscope\Supervisors\Master;
use MaherElGamil\Periscope\Supervisors\Supervisor;

class StartCommand extends Command
{
    protected $signature = 'periscope:start {--supervisor=* : Run only the named supervisors (defaults to all)}';

    protected $description = 'Start all configured Periscope supervisors and keep them alive';

    public function handle(): int
    {
        $configured = (array) config('periscope.supervisors', []);
        $only = $this->option('supervisor');

        if ($configured === []) {
            $this->components->warn('No supervisors configured. Define them in config/periscope.php.');

            return self::SUCCESS;
        }

        $master = new Master(base_path());

        foreach ($configured as $name => $config) {
            if ($only !== [] && ! in_array($name, $only, true)) {
                continue;
            }

            $master->add(new Supervisor($name, $config, base_path()));

            $processes = max(1, (int) ($config['processes'] ?? 1));
            $this->components->info("Booting supervisor [{$name}] with {$processes} process(es).");
        }

        $master->run(function (array $status, bool $paused) {
            foreach ($status as $name => $workers) {
                $running = count(array_filter($workers, fn ($w) => $w['running']));
                $label = $paused ? 'paused' : "{$running}/".count($workers).' running';
                $this->components->twoColumnDetail($name, $label);
            }
        });

        $this->components->info('Periscope supervisors terminated.');

        return self::SUCCESS;
    }
}
