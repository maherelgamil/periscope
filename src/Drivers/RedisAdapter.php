<?php

namespace MaherElGamil\Periscope\Drivers;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use MaherElGamil\Periscope\Contracts\DriverAdapter;

class RedisAdapter implements DriverAdapter
{
    public function __construct(
        protected string $connection,
        protected RedisFactory $redis,
        protected string $redisConnection,
        protected string $prefix = 'queues:',
    ) {}

    public function connection(): string
    {
        return $this->connection;
    }

    public function pending(string $queue): ?int
    {
        return (int) $this->client()->llen($this->key($queue));
    }

    public function delayed(string $queue): ?int
    {
        return (int) $this->client()->zcard($this->key($queue).':delayed');
    }

    public function reserved(string $queue): ?int
    {
        return (int) $this->client()->zcard($this->key($queue).':reserved');
    }

    public function queues(): ?array
    {
        $client = $this->client();
        $names = [];
        $cursor = '0';

        do {
            $result = $client->scan($cursor, ['match' => $this->prefix.'*', 'count' => 200]);

            if ($result === false || ! is_array($result) || count($result) < 2) {
                break;
            }

            [$cursor, $keys] = $result;

            foreach ((array) $keys as $key) {
                $name = str_starts_with($key, $this->prefix) ? substr($key, strlen($this->prefix)) : $key;

                if (str_contains($name, ':')) {
                    $name = strstr($name, ':', true);
                }

                if ($name !== '' && $name !== false) {
                    $names[$name] = true;
                }
            }
        } while ((string) $cursor !== '0');

        return array_keys($names);
    }

    protected function client()
    {
        return $this->redis->connection($this->redisConnection);
    }

    protected function key(string $queue): string
    {
        return $this->prefix.$queue;
    }
}
