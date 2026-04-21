<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Periscope — @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('vendor/periscope/periscope.css') }}">
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">
    <div class="min-h-full">
        <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <a href="{{ route('periscope.overview') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                    </span>
                    Periscope
                </a>
                <nav class="flex items-center gap-1 text-sm">
                    @foreach ([
                        'periscope.overview' => 'Overview',
                        'periscope.queues' => 'Queues',
                        'periscope.jobs' => 'Jobs',
                        'periscope.failed' => 'Failed',
                        'periscope.exceptions' => 'Exceptions',
                        'periscope.alerts' => 'Alerts',
                        'periscope.schedules' => 'Schedules',
                        'periscope.batches' => 'Batches',
                        'periscope.performance' => 'Performance',
                    ] as $route => $label)
                        <a href="{{ route($route) }}"
                           class="rounded-md px-3 py-1.5 transition
                           {{ request()->routeIs($route) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">
            @if (session('periscope.message'))
                <div class="mb-4 rounded-md border border-sky-500/30 bg-sky-500/10 px-4 py-2 text-sm text-sky-200">
                    {{ session('periscope.message') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
