<?php

namespace MaherElGamil\Periscope\Drivers;

use Illuminate\Support\Facades\DB;
use MaherElGamil\Periscope\Contracts\DriverAdapter;

class DatabaseAdapter implements DriverAdapter
{
    public function __construct(
        protected string $connection,
        protected string $databaseConnection,
        protected string $table = 'jobs',
    ) {}

    public function connection(): string
    {
        return $this->connection;
    }

    public function pending(string $queue): ?int
    {
        return $this->baseQuery($queue)
            ->whereNull('reserved_at')
            ->where('available_at', '<=', $this->now())
            ->count();
    }

    public function delayed(string $queue): ?int
    {
        return $this->baseQuery($queue)
            ->whereNull('reserved_at')
            ->where('available_at', '>', $this->now())
            ->count();
    }

    public function reserved(string $queue): ?int
    {
        return $this->baseQuery($queue)
            ->whereNotNull('reserved_at')
            ->count();
    }

    public function queues(): ?array
    {
        return DB::connection($this->databaseConnection)
            ->table($this->table)
            ->select('queue')
            ->distinct()
            ->pluck('queue')
            ->filter()
            ->values()
            ->all();
    }

    protected function baseQuery(string $queue)
    {
        return DB::connection($this->databaseConnection)
            ->table($this->table)
            ->where('queue', $queue);
    }

    protected function now(): int
    {
        return time();
    }
}
