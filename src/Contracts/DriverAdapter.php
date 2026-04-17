<?php

namespace MaherElGamil\Periscope\Contracts;

interface DriverAdapter
{
    /**
     * The connection name this adapter is bound to.
     */
    public function connection(): string;

    /**
     * Jobs ready to be processed on the given queue.
     */
    public function pending(string $queue): ?int;

    /**
     * Jobs scheduled for the future on the given queue.
     */
    public function delayed(string $queue): ?int;

    /**
     * Jobs currently claimed by a worker on the given queue.
     */
    public function reserved(string $queue): ?int;

    /**
     * Whether this adapter can enumerate the queues on its connection.
     *
     * @return array<int, string>|null
     */
    public function queues(): ?array;
}
