# Agent Guide

Conventions for AI coding agents (Claude, Cursor, Copilot, etc.) working on this package. Keep this file under ~200 lines so it can be loaded into context without bloat.

## Project layout

```
periscope/
├── config/              # Package config (published to config/periscope.php)
├── database/migrations/ # 6 create-table migrations, no alter-table migrations
├── docs/screenshots/    # Dashboard screenshots used in README
├── public/              # Compiled periscope.css — commit, don't gitignore
├── resources/
│   ├── css/             # Tailwind source (resources/css/periscope.css)
│   └── views/           # Blade templates + Livewire view components
├── routes/web.php       # Dashboard, metrics, and health routes
├── scripts/             # capture-demo.mjs (Playwright), make-gif.sh
├── src/
│   ├── Alerts/          # Alert + rule classes, AlertManager
│   ├── Console/         # All artisan commands
│   ├── Contracts/       # DriverAdapter interface
│   ├── Drivers/         # Database/Redis/Sqs/Beanstalkd/Null adapters
│   ├── Http/Controllers # DashboardController, MetricsController, HealthController
│   ├── Listeners/       # RecordJobLifecycle, RecordScheduleLifecycle
│   ├── Livewire/        # All dashboard components
│   ├── Models/          # Eloquent models for periscope_* tables
│   ├── Notifications/   # PeriscopeAlert + Channels/WebhookChannel
│   ├── Repositories/    # Job, Metric, Worker repositories
│   ├── Supervisors/     # Supervisor + Master (process supervision)
│   └── Support/         # Adapter factory, QueueSize, metrics collector, etc.
├── stubs/               # PeriscopeServiceProvider.stub scaffolded by install
└── tests/
    ├── Feature/         # Pest feature tests
    ├── Pest.php
    └── TestCase.php     # Orchestra Testbench-based base test
```

## Conventions

### PHP
- Min PHP 8.2, typed properties + return types everywhere
- Constructor property promotion preferred for services
- `declare(strict_types=1)` is **not** used — match existing style
- No inline comments for obvious code; add them only for non-obvious invariants

### Tailwind
- Source: `resources/css/periscope.css`
- Compile with `npm run build` → `public/periscope.css` (commit the output)
- Only use the dark-slate palette already in the views — a proper light/dark theme needs dedicated work, tracked separately

### Blade / Livewire
- Dashboard pages live in `resources/views/{page}.blade.php` and include a Livewire component
- Livewire views in `resources/views/livewire/`
- Use `wire:poll.visible` (never bare `wire:poll`) so inactive tabs don't churn
- Livewire component names prefixed with `periscope.` (e.g. `periscope.jobs-table`) — register in `PeriscopeServiceProvider::registerLivewire()`
- URL-bind all user-selectable filters with `#[Url(as: '...')]`

### Config
- `config/periscope.php` is the single source of truth
- Env-aware overrides go in `periscope.environments.{env}.*`
- Never read `env()` outside config — code reads `config('periscope.*')`

### Database
- Tables prefixed `periscope_` via `config('periscope.storage.table_prefix')`
- **Do not** ship alter-table migrations pre-1.0 — fold schema changes into the original create migration
- Models use `getTable()` + `getConnectionName()` to honor the prefix and storage connection

### Events & listeners
- The listener is `RecordJobLifecycle` subscribing to `JobQueued` / `JobProcessing` / `JobProcessed` / `JobFailed` / `JobExceptionOccurred`
- All DB writes in the listener go through `safely()` to swallow exceptions — telemetry must never break the queue

## Commands

```bash
# Lint (always run before committing PHP changes)
../../vendor/bin/pint

# Test
./vendor/bin/pest

# Rebuild CSS after view changes
npm run build

# Capture the demo GIF (Playwright-driven)
PERISCOPE_URL=http://your-app.test/periscope ./scripts/capture-demo.mjs
```

## Adding a new dashboard page

1. Create a Livewire component in `src/Livewire/MyPage.php`
2. Create the Blade view in `resources/views/livewire/my-page.blade.php`
3. Create a wrapper view in `resources/views/my-page.blade.php` (`@extends('periscope::layout')`)
4. Add a controller method in `Http/Controllers/DashboardController.php`
5. Register the route in `routes/web.php`
6. Register the Livewire component in `PeriscopeServiceProvider::registerLivewire()`
7. Add a nav link in `resources/views/layout.blade.php`
8. Add a feature test in `tests/Feature/`

## Adding a new artisan command

1. Create the class in `src/Console/`
2. Register it in `PeriscopeServiceProvider::registerCommands()`
3. Add a row to the README "Commands" table
4. Add a feature test using `$this->artisan(...)->assertSuccessful()`

## Testing

- Pest + Orchestra Testbench; in-memory SQLite
- `TestCase::setUp()` registers a permissive `viewPeriscope` gate via `Gate::before`
- HTTP tests use Testbench's default web middleware stack (no app `web` group)
- The suite should stay under 1 second locally — if it grows, flag it

## Releases

1. Update `CHANGELOG.md` under `[Unreleased]` as you go
2. When cutting a release: move unreleased entries under `[x.y.z] — YYYY-MM-DD`
3. Commit, tag `vX.Y.Z`, push both
4. `gh release create vX.Y.Z --latest --title "vX.Y.Z" --notes "..."` mirroring the changelog section
5. Packagist auto-syncs via webhook; verify at https://packagist.org/packages/maherelgamil/periscope

Pre-1.0 we're generous with breaking changes — mark them clearly in the changelog but don't feel obliged to bump major.

## Things to avoid

- Adding backwards-compatibility shims for pre-1.0 changes
- Writing alter-table migrations (fold into the original create)
- Introducing new npm dependencies beyond Tailwind + Playwright (scripts-only)
- Comments that explain *what* the code does (names do that) — only explain non-obvious *why*
- Documenting internal tracking tasks in the README (use `CHANGELOG.md` / GitHub Issues)
