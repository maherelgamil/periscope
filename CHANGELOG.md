# Changelog

All notable changes to `periscope` will be documented in this file.

## [Unreleased]

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
