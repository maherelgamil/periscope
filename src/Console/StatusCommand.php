<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use MaherElGamil\Periscope\Support\MetricsCollector;

class StatusCommand extends Command
{
    protected $signature = 'periscope:status';

    protected $description = 'Print a summary of Periscope telemetry to the terminal';

    public function handle(MetricsCollector $collector): int
    {
        $data = $collector->collect();

        $this->components->twoColumnDetail('<fg=cyan>Jobs</>', '');
        foreach ($data['jobs_current'] as $status => $count) {
            $this->components->twoColumnDetail('  '.ucfirst($status), number_format($count));
        }

        $this->newLine();
        $this->components->twoColumnDetail('<fg=cyan>Workers</>', '');
        foreach ($data['workers'] as $status => $count) {
            $color = $status === 'stale' && $count > 0 ? 'red' : ($status === 'running' ? 'green' : 'gray');
            $this->components->twoColumnDetail('  '.ucfirst($status), "<fg={$color}>".number_format($count).'</>');
        }

        if ($data['queues'] !== []) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=cyan>Queue depth</>', '<fg=gray>pending · delayed · reserved</>');
            foreach ($data['queues'] as $q) {
                $depth = sprintf('%s · %s · %s',
                    $q['pending'] ?? '—',
                    $q['delayed'] ?? '—',
                    $q['reserved'] ?? '—',
                );
                $this->components->twoColumnDetail("  {$q['connection']}/{$q['queue']}", $depth);
            }
        }

        if ($data['jobs'] !== []) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=cyan>Lifetime totals</>', '<fg=gray>processed · failed · queued</>');
            foreach ($data['jobs'] as $row) {
                $this->components->twoColumnDetail(
                    "  {$row['connection']}/{$row['queue']}",
                    sprintf('%s · <fg=red>%s</> · %s',
                        number_format($row['processed']),
                        number_format($row['failed']),
                        number_format($row['queued']),
                    ),
                );
            }
        }

        return self::SUCCESS;
    }
}
