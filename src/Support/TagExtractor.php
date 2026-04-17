<?php

namespace MaherElGamil\Periscope\Support;

use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Events\JobQueued;
use Throwable;

class TagExtractor
{
    /**
     * @return array<int, string>
     */
    public function fromPayload(array $payload): array
    {
        $command = $this->unserializeCommand($payload['data']['command'] ?? null);

        if ($command === null) {
            return [];
        }

        return $this->tagsFor($command);
    }

    /**
     * @return array<int, string>
     */
    public function fromJob(JobContract $job): array
    {
        return $this->fromPayload($job->payload());
    }

    /**
     * @return array<int, string>
     */
    public function fromQueuedEvent(JobQueued $event): array
    {
        if (is_object($event->job)) {
            return $this->tagsFor($event->job);
        }

        return $this->fromPayload((array) $event->payload());
    }

    protected function unserializeCommand(mixed $serialized): ?object
    {
        if (! is_string($serialized) || $serialized === '') {
            return null;
        }

        try {
            $value = @unserialize($serialized);
        } catch (Throwable) {
            return null;
        }

        return is_object($value) ? $value : null;
    }

    /**
     * @return array<int, string>
     */
    protected function tagsFor(object $command): array
    {
        if (method_exists($command, 'tags')) {
            try {
                $tags = $command->tags();
            } catch (Throwable) {
                return [];
            }

            return is_array($tags) ? array_values(array_filter(array_map('strval', $tags))) : [];
        }

        return [];
    }
}
