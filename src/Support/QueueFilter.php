<?php

namespace MaherElGamil\Periscope\Support;

class QueueFilter
{
    public function isSilencedJob(?string $name): bool
    {
        if ($name === null || $name === '') {
            return false;
        }

        foreach ((array) config('periscope.silenced', []) as $pattern) {
            $regex = '/^'.str_replace(['\\*', '\\?'], ['.*', '.'], preg_quote($pattern, '/')).'$/';
            if (preg_match($regex, $name)) {
                return true;
            }
        }

        return false;
    }

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
