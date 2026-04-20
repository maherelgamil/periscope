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
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-sky-500/20 text-sky-300">P</span>
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
