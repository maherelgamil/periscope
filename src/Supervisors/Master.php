<?php

namespace MaherElGamil\Periscope\Supervisors;

use Closure;
use Illuminate\Support\Facades\Cache;

class Master
{
    public const PID_KEY = 'periscope:master:pid';

    public const PAUSE_KEY = 'periscope:master:paused';

    public const DEPLOY_TAG_KEY = 'periscope:deploy-tag';

    /** @var array<int, Supervisor> */
    protected array $supervisors = [];

    protected bool $shouldStop = false;

    public function __construct(protected string $basePath) {}

    public function add(Supervisor $supervisor): void
    {
        $this->supervisors[] = $supervisor;
    }

    public function run(Closure $reporter): void
    {
        $this->registerSignals();
        Cache::forever(self::PID_KEY, getmypid());
        $deployTag = Cache::get(self::DEPLOY_TAG_KEY);

        try {
            while (! $this->shouldStop) {
                $paused = (bool) Cache::get(self::PAUSE_KEY, false);
                $currentTag = Cache::get(self::DEPLOY_TAG_KEY);

                if ($currentTag !== $deployTag) {
                    foreach ($this->supervisors as $supervisor) {
                        $supervisor->terminate();
                    }
                    $deployTag = $currentTag;
                }

                foreach ($this->supervisors as $supervisor) {
                    if ($paused) {
                        $supervisor->terminate(5);
                    } else {
                        $supervisor->ensureRunning();
                    }
                }

                $reporter($this->status(), $paused);

                for ($i = 0; $i < 20 && ! $this->shouldStop; $i++) {
                    usleep(100_000);
                    $this->dispatchPendingSignals();

                    foreach ($this->supervisors as $supervisor) {
                        $supervisor->drainOutput();
                    }
                }
            }

            foreach ($this->supervisors as $supervisor) {
                $supervisor->terminate();
            }
        } finally {
            Cache::forget(self::PID_KEY);
        }
    }

    /**
     * @return array<string, array<int, array{pid: ?int, running: bool, name: string}>>
     */
    public function status(): array
    {
        $out = [];

        foreach ($this->supervisors as $supervisor) {
            $out[$supervisor->name] = $supervisor->status();
        }

        return $out;
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    protected function registerSignals(): void
    {
        if (! function_exists('pcntl_signal')) {
            return;
        }

        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, fn () => $this->stop());
        pcntl_signal(SIGINT, fn () => $this->stop());
    }

    protected function dispatchPendingSignals(): void
    {
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }
    }
}
