<?php

namespace MaherElGamil\Periscope\Alerts;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Cache;
use MaherElGamil\Periscope\Notifications\Channels\WebhookChannel;
use MaherElGamil\Periscope\Notifications\PeriscopeAlert;

class AlertManager
{
    /** @var array<string, class-string<Rule>> */
    protected array $rules = [
        'failure_spike' => FailureSpikeRule::class,
        'long_wait' => LongWaitRule::class,
        'stale_worker' => StaleWorkerRule::class,
    ];

    public function __construct(protected Application $app) {}

    /**
     * Evaluate every configured rule and return the alerts that fired.
     *
     * @return array<int, Alert>
     */
    public function evaluate(): array
    {
        $configured = (array) config('periscope.alerts.rules', []);
        $alerts = [];

        foreach ($configured as $key => $config) {
            $class = $this->rules[$key] ?? null;

            if ($class === null || ($config['enabled'] ?? true) === false) {
                continue;
            }

            /** @var Rule $rule */
            $rule = $this->app->make($class);
            $alert = $rule->evaluate($config);

            if ($alert !== null && $this->shouldFire($alert, (int) ($config['cooldown_minutes'] ?? 15))) {
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    public function dispatch(Alert $alert): void
    {
        $channels = (array) config('periscope.alerts.channels', []);

        if ($channels === []) {
            return;
        }

        $this->registerWebhookChannel();

        $notifiable = new AnonymousNotifiable;

        foreach ((array) config('periscope.alerts.routes', []) as $channel => $route) {
            if ($route) {
                $notifiable->route($channel, $route);
            }
        }

        $notifiable->notify(new PeriscopeAlert($alert));
    }

    protected function registerWebhookChannel(): void
    {
        $manager = $this->app->make(ChannelManager::class);

        $manager->extend('webhook', fn () => $this->app->make(WebhookChannel::class));
    }

    protected function shouldFire(Alert $alert, int $cooldownMinutes): bool
    {
        $cacheKey = 'periscope:alert:'.$alert->key;

        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, now()->timestamp, now()->addMinutes(max(1, $cooldownMinutes)));

        return true;
    }
}
