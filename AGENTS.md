# Agent Guide — Periscope

Context for AI coding agents working on this package. Focuses on the decisions that aren't obvious from reading the code.

## What Periscope is, exactly

A **driver-agnostic** queue monitor. The defining architectural choice: telemetry comes from **Laravel queue events**, not from reading driver internals. That's why it works with `redis`, `database`, `sqs`, `beanstalkd`, and `sync` — the events fire regardless. Horizon reads Redis data structures directly; we don't.

When making changes, preserve this: **never introduce driver-specific code in the listener or the core storage layer.** Driver-specific code is confined to `src/Drivers/*Adapter.php`.

## Key tables & models

| Table | Model | What it holds |
|---|---|---|
| `periscope_jobs` | `MonitoredJob` | One row per job across its whole lifetime. `attempts` is an **integer column**, not a relation (see gotcha below). |
| `periscope_job_attempts` | `JobAttempt` | One row per attempt. The relation on `MonitoredJob` is `history()`, not `attempts()`. |
| `periscope_metrics` | `QueueMetric` | Minute/hour rollups. `period` is `'minute'` or `'hour'`. Minute buckets are written by the listener, hour buckets by `periscope:snapshot`. |
| `periscope_workers` | `Worker` | Heartbeat rows. Only populated when workers run through `periscope:supervise` or `periscope:start`. |
| `periscope_alerts` | `AlertRecord` | Fired alert history. |
| `periscope_schedules` | `ScheduleRun` | Scheduled command runs. |

Batches are **not** stored by Periscope — the Batches page reads Laravel's native `job_batches` table, so that dependency is optional (the page renders an info banner if the table is missing).

### Gotcha: `attempts` vs `history`

`MonitoredJob::$attempts` is the integer **column** (how many times this job has been tried). The hasMany relation to `JobAttempt` is deliberately named `history()` to avoid colliding. If you ever add `$job->attempts` to a view, you get the integer. If you add `$job->history`, you get the Collection. Both are correct; know which one you want.

## Listener invariants

`src/Listeners/RecordJobLifecycle.php` is the heart of the package. Rules:

1. **Every DB write wrapped in `$this->safely(...)`.** Telemetry failures must never propagate to the queue worker.
2. **Silenced jobs check at both entry points** (`handleQueued` and `handleProcessing`). Jobs in `periscope.silenced` are ignored.
3. **`QueueFilter::shouldRecord()` runs first.** It gates by the `queues` config allowlist before any work.
4. **Don't add new events to the subscriber without wrapping in `safely()` and respecting the filter.**

## Driver adapter contract

`src/Contracts/DriverAdapter.php` has three required methods: `pending()`, `delayed()`, `reserved()`. Adapters **must**:

- Return `null` on any failure (caller treats null as "unknown") — never throw
- Never block the request for long (Redis uses `SCAN`, not `KEYS`; SQS uses the explicit attributes API)
- Be constructible with just `(string $connection, ...$driver-specific deps)` — no service-locator lookups

If you add a new adapter, wire it into `Support/AdapterFactory::make()`.

## Supervisor architecture

- **Master** (`src/Supervisors/Master.php`) owns the event loop. Tracks PID + pause + deploy-tag via cache keys. Drains child stdout every tick.
- **Supervisor** (`src/Supervisors/Supervisor.php`) owns a pool of child processes *per queue* when `balance: 'auto'`, or a single multi-queue pool in static mode.
- **Children** are invocations of `periscope:supervise`, which in turn wraps `queue:work` and tacks on heartbeat bookkeeping.

When you add a new supervisor config key:
1. Add to the commented example in `config/periscope.php`
2. Read it via `$this->config['your_key'] ?? default` in `Supervisor`
3. If it needs to affect the child process, forward via `spawn()` as a CLI flag

## Livewire conventions

- Components live in `src/Livewire/`, views in `resources/views/livewire/`
- Registered in `PeriscopeServiceProvider::registerLivewire()` with a `periscope.` prefix
- **Always** use `wire:poll.visible.Xs` — never bare `wire:poll` — so inactive tabs don't churn the DB
- User-selectable filters use `#[Url(as: 'short-name')]` so the page state lives in the querystring (shareable links, back-button works)
- Pagination via `WithPagination`; date-range filters via `datetime-local` inputs

## Routes & middleware

- Dashboard routes go through `config('periscope.middleware')` → `['web', Authorize::class]`
- `/metrics` and `/health` **bypass** the dashboard gate via `Route::withoutMiddleware(config('periscope.middleware'))` — they're meant for scrapers and load balancers. They have their own `middleware` config key.
- The gate `viewPeriscope` has a local-only default in `PeriscopeServiceProvider::registerGate()` that's only registered if nothing else defined it first. The scaffolded `App\Providers\PeriscopeServiceProvider` overrides it.

## CSS / Tailwind

- Source: `resources/css/periscope.css`
- Build: `npm run build` → `public/periscope.css` (committed, **not** gitignored)
- The layout pulls the published asset from `asset('vendor/periscope/periscope.css')`
- We do **not** use a CDN; ship the bundle. Only palette is slate/sky/rose/emerald/amber — don't introduce new colors without rebuilding.

## Tests

- Orchestra Testbench + in-memory SQLite
- `tests/TestCase::setUp()` runs every migration and registers a permissive `Gate::before` so all HTTP tests bypass authorization
- The web middleware group **does** exist in testbench but `Authorize::class` is stripped via `config('periscope.middleware', [])` in `defineEnvironment`
- Keep the suite under ~1s local. If a test starts an actual worker or sleeps, something's wrong.

## Release workflow

Pre-1.0 we're loose:

1. Edit `CHANGELOG.md` under `## [Unreleased]` as you work
2. When releasing: move entries under `## [x.y.z] — YYYY-MM-DD`
3. Commit, `git tag -a vx.y.z -m "vx.y.z"`, push both
4. `gh release create vx.y.z --latest --title "vx.y.z" --notes "..."`
5. Packagist auto-syncs via webhook; verify at packagist.org/packages/maherelgamil/periscope

If you change migrations and someone might already have installed the previous version, **flag it as an upgrade note in the changelog** — direct them to `php artisan migrate:fresh`. Once we hit 1.0, alter-table migrations become mandatory.

## Things to avoid

- **Adding BC shims** — pre-1.0, breaking changes are fine, just flag them
- **Alter-table migrations** — fold into the original create migration
- **Driver-specific code outside `src/Drivers/`**
- **Reading `env()` outside `config/`** — read `config('periscope.*')` everywhere else
- **Inline `{ what }` comments** — names do that. Comment only non-obvious *why*
- **New npm dependencies** — Tailwind (build) and Playwright (scripts-only) are the only ones
- **New migrations as `add_xyz_columns`** before 1.0 — consolidate into the create

## Quick commands

```bash
# Lint + test before every commit
../../vendor/bin/pint && ./vendor/bin/pest

# Rebuild CSS after view edits
npm run build

# Regenerate demo GIF
PERISCOPE_URL=http://your-app.test/periscope ./scripts/capture-demo.mjs
```
