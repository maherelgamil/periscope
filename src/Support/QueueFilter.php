<?php

namespace MaherElGamil\Periscope\Support;

class QueueFilter
{
    public function shouldRecord(string $connection, string $queue): bool
    {
        $configured = (array) config('periscope.queues', []);

        if ($configured === []) {
            return true;
        }

        $queues = $configured[$connection] ?? null;

        if ($queues === null) {
            return false;
        }

        $queues = (array) $queues;

        return in_array('*', $queues, true) || in_array($queue, $queues, true);
    }
}
