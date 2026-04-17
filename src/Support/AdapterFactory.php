<?php

namespace MaherElGamil\Periscope\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Queue\BeanstalkdQueue;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\RedisQueue;
use Illuminate\Queue\SqsQueue;
use MaherElGamil\Periscope\Contracts\DriverAdapter;
use MaherElGamil\Periscope\Drivers\BeanstalkdAdapter;
use MaherElGamil\Periscope\Drivers\DatabaseAdapter;
use MaherElGamil\Periscope\Drivers\NullAdapter;
use MaherElGamil\Periscope\Drivers\RedisAdapter;
use MaherElGamil\Periscope\Drivers\SqsAdapter;
use Throwable;

class AdapterFactory
{
    public function __construct(protected Application $app) {}

    public function make(string $connection): DriverAdapter
    {
        $queueConfig = (array) config("queue.connections.$connection", []);
        $driver = $queueConfig['driver'] ?? null;

        try {
            return match ($driver) {
                'database' => new DatabaseAdapter(
                    $connection,
                    $queueConfig['connection'] ?? config('database.default'),
                    $queueConfig['table'] ?? 'jobs',
                ),
                'redis' => new RedisAdapter(
                    $connection,
                    $this->app->make(RedisFactory::class),
                    $queueConfig['connection'] ?? 'default',
                ),
                'sqs' => new SqsAdapter($connection, $this->resolveQueue($connection, SqsQueue::class)),
                'beanstalkd' => new BeanstalkdAdapter($connection, $this->resolveQueue($connection, BeanstalkdQueue::class)),
                default => new NullAdapter($connection),
            };
        } catch (Throwable) {
            return new NullAdapter($connection);
        }
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $expected
     * @return T
     */
    protected function resolveQueue(string $connection, string $expected)
    {
        $queue = $this->app->make(QueueFactory::class)->connection($connection);

        if (! $queue instanceof $expected) {
            throw new \RuntimeException("Queue connection [$connection] is not a $expected instance.");
        }

        return $queue;
    }
}
