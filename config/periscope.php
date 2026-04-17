<?php

use MaherElGamil\Periscope\Http\Middleware\Authorize;

return [

    /*
    |--------------------------------------------------------------------------
    | Periscope Path
    |--------------------------------------------------------------------------
    |
    | The URI path at which the Periscope dashboard will be served.
    |
    */

    'path' => env('PERISCOPE_PATH', 'periscope'),

    /*
    |--------------------------------------------------------------------------
    | Periscope Domain
    |--------------------------------------------------------------------------
    */

    'domain' => env('PERISCOPE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | When disabled, Periscope will not record any telemetry and the dashboard
    | will not be registered. Useful for local environments.
    |
    */

    'enabled' => env('PERISCOPE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | The database connection and table prefix Periscope should use when
    | persisting job telemetry, metrics, and worker heartbeats.
    |
    */

    'storage' => [
        'connection' => env('PERISCOPE_DB_CONNECTION'),
        'table_prefix' => 'periscope_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware applied to the Periscope dashboard routes. The default
    | includes a gate check against the `viewPeriscope` ability.
    |
    */

    'middleware' => ['web', Authorize::class],

    /*
    |--------------------------------------------------------------------------
    | Queues Monitored
    |--------------------------------------------------------------------------
    |
    | The queue connections and queues Periscope should watch. Use `*` to
    | monitor every queue on a given connection.
    |
    */

    'queues' => [
        // 'redis' => ['*'],
        // 'database' => ['default'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention (hours)
    |--------------------------------------------------------------------------
    |
    | How long Periscope should retain records before they are pruned.
    |
    */

    'retention' => [
        'completed_jobs' => env('PERISCOPE_RETENTION_COMPLETED', 24),
        'failed_jobs' => env('PERISCOPE_RETENTION_FAILED', 24 * 7),
        'metrics_minute' => env('PERISCOPE_RETENTION_METRICS_MINUTE', 6),
        'metrics_hour' => env('PERISCOPE_RETENTION_METRICS_HOUR', 24 * 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payload
    |--------------------------------------------------------------------------
    |
    | Controls how Periscope stores raw job payloads. Payloads larger than
    | `max_size` bytes will be truncated.
    |
    */

    'payload' => [
        'store' => true,
        'max_size' => 65_536,
    ],

    /*
    |--------------------------------------------------------------------------
    | Worker Heartbeat
    |--------------------------------------------------------------------------
    */

    'workers' => [
        'heartbeat_seconds' => 15,
        'stale_after_seconds' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supervisors
    |--------------------------------------------------------------------------
    |
    | Named worker pools spawned by the `periscope:start` command. Each
    | supervisor forks `processes` child workers against the given connection
    | and queue(s). Children are restarted automatically if they crash.
    |
    */

    'supervisors' => [
        // 'default' => [
        //     'connection' => 'redis',
        //     'queue' => ['default'],
        //     'processes' => 2,
        //     'tries' => 1,
        //     'timeout' => 60,
        //     'sleep' => 3,
        //     'memory' => 128,
        //     'backoff' => 0,
        //     'max_jobs' => 0,
        //     'max_time' => 0,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    |
    | Rules are keyed by name. Supported keys: failure_spike, long_wait,
    | stale_worker. Channels use Laravel's notification channel names
    | (mail, slack, webhook, etc.). Routes map channel → destination.
    |
    */

    'alerts' => [
        'channels' => array_filter(explode(',', (string) env('PERISCOPE_ALERT_CHANNELS', ''))),

        'routes' => [
            'mail' => env('PERISCOPE_ALERT_MAIL'),
            'slack' => env('PERISCOPE_ALERT_SLACK_WEBHOOK'),
        ],

        'rules' => [
            'failure_spike' => [
                'enabled' => true,
                'threshold' => 10,
                'minutes' => 5,
                'cooldown_minutes' => 15,
            ],
            'long_wait' => [
                'enabled' => true,
                'threshold_ms' => 30_000,
                'minutes' => 5,
                'cooldown_minutes' => 15,
            ],
            'stale_worker' => [
                'enabled' => true,
                'cooldown_minutes' => 30,
            ],
        ],
    ],

];
