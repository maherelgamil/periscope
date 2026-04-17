<?php

namespace MaherElGamil\Periscope\Drivers;

use Illuminate\Queue\SqsQueue;
use MaherElGamil\Periscope\Contracts\DriverAdapter;
use Throwable;

class SqsAdapter implements DriverAdapter
{
    public function __construct(
        protected string $connection,
        protected SqsQueue $queue,
    ) {}

    public function connection(): string
    {
        return $this->connection;
    }

    public function pending(string $queue): ?int
    {
        return $this->attribute($queue, 'ApproximateNumberOfMessages');
    }

    public function delayed(string $queue): ?int
    {
        return $this->attribute($queue, 'ApproximateNumberOfMessagesDelayed');
    }

    public function reserved(string $queue): ?int
    {
        return $this->attribute($queue, 'ApproximateNumberOfMessagesNotVisible');
    }

    public function queues(): ?array
    {
        return null;
    }

    protected function attribute(string $queue, string $name): ?int
    {
        try {
            $result = $this->queue->getSqs()->getQueueAttributes([
                'QueueUrl' => $this->queue->getQueue($queue),
                'AttributeNames' => [$name],
            ]);
        } catch (Throwable) {
            return null;
        }

        $value = $result->get('Attributes')[$name] ?? null;

        return $value === null ? null : (int) $value;
    }
}
