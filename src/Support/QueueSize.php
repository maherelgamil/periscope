<?php

namespace MaherElGamil\Periscope\Support;

use MaherElGamil\Periscope\Contracts\DriverAdapter;

class QueueSize
{
    /** @var array<string, DriverAdapter> */
    protected array $cache = [];

    public function __construct(protected AdapterFactory $factory) {}

    public function adapter(string $connection): DriverAdapter
    {
        return $this->cache[$connection] ??= $this->factory->make($connection);
    }

    /**
     * @return array{pending: ?int, delayed: ?int, reserved: ?int}
     */
    public function sizes(string $connection, string $queue): array
    {
        $adapter = $this->adapter($connection);

        return [
            'pending' => $adapter->pending($queue),
            'delayed' => $adapter->delayed($queue),
            'reserved' => $adapter->reserved($queue),
        ];
    }
}
