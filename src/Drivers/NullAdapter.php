<?php

namespace MaherElGamil\Periscope\Drivers;

use MaherElGamil\Periscope\Contracts\DriverAdapter;

class NullAdapter implements DriverAdapter
{
    public function __construct(protected string $connection) {}

    public function connection(): string
    {
        return $this->connection;
    }

    public function pending(string $queue): ?int
    {
        return null;
    }

    public function delayed(string $queue): ?int
    {
        return null;
    }

    public function reserved(string $queue): ?int
    {
        return null;
    }

    public function queues(): ?array
    {
        return null;
    }
}
