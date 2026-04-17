# Changelog

All notable changes to `periscope` will be documented in this file.

## [Unreleased]

## [0.3.0] — 2026-04-17

### Added
- **Exception drill-down** — click any grouped exception to see every occurrence with pagination, sample stack trace, and time-window selector
- **Alert history page** — every fired alert persisted to `periscope_alerts` with severity, channels, and context; dismissible from the UI
- **Scheduled command monitoring** — `RecordScheduleLifecycle` listener subscribes to `ScheduledTaskStarting/Finished/Failed/Skipped` events; new Schedules page shows runtime, status, and cron expression
- **Batch tracking** — reads Laravel's `job_batches` table; new Batches page with progress bar, pending/failed counts, and cancel action
- **Performance percentiles page** — p50/p95/p99 of runtime and wait per queue, computed in-PHP for driver portability
- **Memory tracking** — `memory_peak_bytes` captured on every attempt and displayed on the job detail page
- **Retry / re-dispatch button** on the job detail page (failed jobs use `queue:retry`; others re-push the stored payload)
- **Webhook alert notifier** alongside mail and Slack, with documented payload shape
- **Visibility-aware polling** — all `wire:poll` directives now use `.visible` modifier to pause when the tab is hidden

### Changed
- `MonitoredJob::attempts()` relation renamed to `history()` to avoid collision with the `attempts` integer column
- `RedisAdapter::queues()` replaced `KEYS` with non-blocking `SCAN`
- `forget` action in the failed-jobs table now also removes Laravel's native `failed_jobs` row
- Failed jobs page gained pagination, search across name/uuid/exception, queue filter, and checkbox-driven bulk retry/forget
- Supervisors gained `balance: 'auto'` mode — processes allocated per queue proportional to live depth, clamped to `min_processes` / `max_processes`

### Fixed
- Job detail "Attempts" tile rendered the relation JSON instead of the integer column

## [0.2.0] — 2026-04-17

### Added
- **Per-attempt tracking** — new `periscope_job_attempts` table records every retry with its runtime and exception; job detail page shows the attempt timeline
- **Exceptions page** — groups failures by class + message with occurrence count, affected jobs, first/last seen, and a time-window selector
- **Metrics endpoint** — `/periscope/metrics` (Prometheus text format) and `/periscope/metrics.json` for external monitoring, with independent middleware config
- **Auto-balance supervisor** — `balance: 'auto'` allocates processes per queue proportional to live queue depth, bounded by `min_processes` / `max_processes`
- **Webhook alert notifier** alongside mail and Slack
- **Failed jobs polish** — pagination, search across name/uuid/exception, queue filter, checkbox-driven bulk retry/forget
- Dashboard HTTP smoke tests and metrics-endpoint tests in the Pest suite

### Changed
- `MonitoredJob::attempts()` relation renamed to `history()` to avoid collision with the `attempts` integer column
- `RedisAdapter::queues()` uses `SCAN` instead of `KEYS` for non-blocking queue discovery
- Failed-job `forget` now also removes Laravel's native `failed_jobs` record

## [0.1.0] — 2026-04-17

First tagged release.

### Added

**Core telemetry**
- Universal queue event subscriber (`JobQueued`, `JobProcessing`, `JobProcessed`, `JobFailed`) with wait/runtime calculation
- Per-minute metrics rollup and hourly snapshot via `periscope:snapshot`
- Retention + prune via `periscope:prune` (separate windows for completed jobs, failed jobs, minute metrics, hour metrics)

**Driver adapters**
- `DatabaseAdapter`, `RedisAdapter` (with non-blocking `SCAN` for queue discovery), `SqsAdapter`, `BeanstalkdAdapter`
- `NullAdapter` fallback for unknown drivers
- `QueueSize` service with per-connection adapter caching

**Workers**
- `periscope:supervise` wraps `queue:work` with heartbeat reporting
- `periscope:workers:sweep` marks stale workers past their heartbeat threshold
- Config-driven worker pools via `supervisors` block
- `periscope:start` master process: spawns + restarts children, signal-handled graceful shutdown
- `periscope:pause` / `periscope:continue` / `periscope:terminate` for deploy-friendly lifecycle control

**Dashboard (Livewire 4 + Tailwind 4)**
- Overview with counters, averages, and 60-minute throughput chart
- Queues page using live driver adapters
- Jobs page with status/queue/tag filters, URL-bound search, pagination
- Job detail page with timeline, tags, payload, and full exception
- Failed jobs page with retry and forget actions (purges Laravel's `failed_jobs` row too)
- Workers page with live heartbeat polling
- Dark-themed compiled CSS bundle (no CDN dependency)

**Alerts**
- Rules: `failure_spike`, `long_wait`, `stale_worker`
- Mail + Slack notifiers via Laravel's notification system
- Per-rule cooldown to prevent flooding
- `periscope:alerts:check` command

**Configuration & install**
- `viewPeriscope` gate
- Configurable path, domain, middleware, storage connection/prefix, payload size limit
- `periscope:install` publishes config, migrations, and compiled assets
- Publishable view overrides via `periscope-views` tag

**Testing**
- Pest + Orchestra Testbench suite covering event lifecycle, prune, and snapshot
