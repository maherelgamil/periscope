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

                $this->sleepWithSignalCheck(2);
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
        // Signals are handled via pcntl_sigtimedwait() in sleepWithSignalCheck()
        // No need for async signals or signal handlers
    }

    protected function sleepWithSignalCheck(int $seconds): void
    {
        $start = time();

        while (time() - $start < $seconds && ! $this->shouldStop) {
            if (function_exists('pcntl_sigtimedwait') && function_exists('pcntl_sigprocmask')) {
                $signals = [SIGINT, SIGTERM];
                pcntl_sigprocmask(SIG_BLOCK, $signals);
                $info = [];
                $result = pcntl_sigtimedwait($signals, $info, 100_000);
                pcntl_sigprocmask(SIG_UNBLOCK, $signals);

                if ($result !== false && $result > 0) {
                    $this->stop();
                    break;
                }
            } else {
                usleep(100_000);
            }

            foreach ($this->supervisors as $supervisor) {
                $supervisor->drainOutput();
            }
        }
    }
}
