# Periscope

[![Latest Version on Packagist](https://img.shields.io/packagist/v/maherelgamil/periscope.svg?style=flat-square)](https://packagist.org/packages/maherelgamil/periscope)
[![Tests](https://img.shields.io/github/actions/workflow/status/maherelgamil/periscope/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/maherelgamil/periscope/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/maherelgamil/periscope.svg?style=flat-square)](https://packagist.org/packages/maherelgamil/periscope)
[![License](https://img.shields.io/packagist/l/maherelgamil/periscope.svg?style=flat-square)](LICENSE)

> See into any queue.

Periscope is a universal queue monitor for Laravel — a driver-agnostic alternative to Laravel Horizon. It works with **any** queue driver: Redis, database, SQS, Beanstalkd, and more.

## Why Periscope?

Laravel Horizon is excellent — but Redis-only. Periscope brings the same caliber of observability to every queue driver by collecting telemetry through Laravel's built-in queue events rather than depending on driver-specific internals.

![Overview](docs/screenshots/overview.png)

<details>
<summary>More screenshots</summary>

### Jobs
![Jobs](docs/screenshots/jobs.png)

### Exceptions (grouped)
![Exceptions](docs/screenshots/exceptions.png)

### Failed
![Failed](docs/screenshots/failed.png)

### Workers
![Workers](docs/screenshots/workers.png)

### Queues (live driver sizes)
![Queues](docs/screenshots/queues.png)

</details>

## Features (planned)

- Real-time dashboard (Livewire v4 + Tailwind v4)
- Works with all queue drivers: `database`, `redis`, `sqs`, `beanstalkd`, `sync`
- Per-queue throughput, wait time, failure rate
- Job inspector (payload, attempts, runtime, exception trace)
- Failed jobs management (retry, bulk actions)
- Worker heartbeat & stale-worker detection
- Tag-based filtering via `Queueable::tags()`
- Rolling metrics with configurable retention
- Optional alerts (Slack, mail) on failure spikes or long waits
- Authorization gate (`viewPeriscope`) for dashboard access

## Installation

```bash
composer require maherelgamil/periscope
php artisan periscope:install
php artisan migrate
```

Then visit `/periscope` in your browser.

### Scheduling

Add these to your app's scheduler (`routes/console.php`):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('periscope:workers:sweep')->everyMinute();
Schedule::command('periscope:snapshot')->hourly();
Schedule::command('periscope:alerts:check')->everyFiveMinutes();
Schedule::command('periscope:prune')->daily();
```

### Running workers

Wrap your workers with the supervise command so Periscope can track heartbeats:

```bash
# Single worker
php artisan periscope:supervise redis --queue=default,emails

# Or run a pool defined in config/periscope.php
php artisan periscope:start
```

### Supervisors

Define named worker pools in `config/periscope.php`:

```php
'supervisors' => [
    'default' => [
        'connection' => 'redis',
        'queue' => ['default', 'emails'],
        'processes' => 4,
        'tries' => 1,
        'timeout' => 60,
        'sleep' => 1,
    ],
    'notifications' => [
        'connection' => 'redis',
        'queue' => ['notifications'],
        'processes' => 2,
    ],
],
```

Run `php artisan periscope:start` to boot all pools. The master process auto-restarts any child that crashes and shuts every worker down cleanly on SIGTERM/SIGINT.

### Metrics endpoint (Prometheus / JSON)

Periscope exposes aggregated telemetry for external monitoring:

- `/periscope/metrics` — Prometheus text format (scrape target)
- `/periscope/metrics.json` — JSON for custom integrations

Both bypass the dashboard gate by default. Protect them for production via `periscope.metrics.middleware` (IP allowlist, token guard, etc.) or disable entirely with `PERISCOPE_METRICS_ENABLED=false`.

Example Prometheus scrape config:

```yaml
scrape_configs:
  - job_name: periscope
    metrics_path: /periscope/metrics
    static_configs:
      - targets: ['your-app.test']
```

### Authorizing the dashboard

By default the dashboard is only accessible in `local`. Override the gate in a service provider:

```php
Gate::define('viewPeriscope', fn ($user) => $user?->is_admin === true);
```

## Requirements

- PHP 8.2+
- Laravel 11 / 12 / 13
- Livewire 4

---

## Roadmap / TODO

### Phase 1 — Foundation
- [x] Service provider, config, publishable assets
- [x] `periscope:install` command
- [x] Migrations: `periscope_jobs`, `periscope_metrics`, `periscope_workers`
- [x] Models + repository layer
- [x] `periscope:prune` command
- [ ] Base tests + CI setup

### Phase 2 — Event collection
- [x] `RecordJobLifecycle` listener (JobQueued, JobProcessing, JobProcessed, JobFailed)
- [x] Payload storage with size limits
- [x] Tag extraction from queueable jobs
- [x] Runtime & wait-time calculation
- [x] `periscope:prune` command for retention
- [x] Queue filter config respected (per-connection allowlist)
- [ ] `JobExceptionOccurred` / `JobRetryRequested` handling

### Phase 3 — Driver adapters
- [x] `DriverAdapter` contract
- [x] `DatabaseAdapter` (jobs table queries)
- [x] `RedisAdapter` (LLEN / ZCARD)
- [x] `SqsAdapter` (GetQueueAttributes)
- [x] `BeanstalkdAdapter` (stats-tube)
- [x] `NullAdapter` fallback
- [x] `AdapterFactory` + `QueueSize` service with per-connection adapter resolution

### Phase 4 — Workers
- [x] `periscope:supervise` command wrapping queue workers
- [x] Heartbeat writer (configurable interval)
- [x] Stale-worker detection (`periscope:workers:sweep`)
- [x] Worker metadata (hostname, pid, queues, started_at)

### Phase 5 — Dashboard (Livewire)
- [x] Routes + authorization gate (`viewPeriscope`)
- [x] Base layout + navigation
- [x] Overview page (counters, avg runtime & wait)
- [x] Jobs page (list, filter, search)
- [x] Job detail page (payload, exception, timeline)
- [x] Failed jobs page with retry/forget actions
- [x] Workers page
- [x] Queues page (live driver sizes)
- [ ] Metrics page with charts (Phase 6)

### Phase 6 — Metrics aggregation
- [x] `periscope:snapshot` scheduled command
- [x] Per-minute and per-hour rollups
- [x] Throughput chart on overview page
- [x] Configurable retention per aggregation level (`metrics_minute`, `metrics_hour`)

### Phase 7 — Alerts
- [x] Alert rules: failure spike, long wait, stale worker
- [x] Slack notifier
- [x] Mail notifier
- [x] `periscope:alerts:check` command with cooldown
- [ ] Webhook notifier (channel hook — users can add their own)

### Phase 8 — Polish
- [x] Tag filtering on jobs page
- [x] Search across jobs (name, uuid, job_id)
- [x] Dark theme by default
- [x] Pest feature tests (lifecycle, prune, snapshot)
- [x] Publishable config + views + migrations
- [ ] Browser test coverage for dashboard
- [ ] Screenshots in README

### Phase 9 — Release
- [x] LICENSE
- [x] CHANGELOG
- [ ] Packagist release
- [ ] Documentation site

## License

MIT
