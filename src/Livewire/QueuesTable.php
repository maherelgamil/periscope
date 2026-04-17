<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Component;
use MaherElGamil\Periscope\Support\QueueSize;

class QueuesTable extends Component
{
    public function render(QueueSize $sizes)
    {
        $rows = [];

        foreach ($this->configuredQueues() as $connection => $queues) {
            foreach ((array) $queues as $queue) {
                if ($queue === '*') {
                    $discovered = $sizes->adapter($connection)->queues() ?? [];

                    foreach ($discovered as $name) {
                        $rows[] = $this->row($sizes, $connection, $name);
                    }

                    continue;
                }

                $rows[] = $this->row($sizes, $connection, $queue);
            }
        }

        return view('periscope::livewire.queues-table', [
            'rows' => $rows,
        ]);
    }

    protected function configuredQueues(): array
    {
        $configured = (array) config('periscope.queues', []);

        if ($configured !== []) {
            return $configured;
        }

        $default = config('queue.default');
        $queue = config("queue.connections.$default.queue", 'default');

        return [$default => [$queue]];
    }

    protected function row(QueueSize $sizes, string $connection, string $queue): array
    {
        return [
            'connection' => $connection,
            'queue' => $queue,
        ] + $sizes->sizes($connection, $queue);
    }
}
