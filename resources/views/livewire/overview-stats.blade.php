<div wire:poll.visible.5s class="space-y-6">
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
        @php($totals = $this->totals)
        @foreach ([
            'Queued' => $totals['queued'],
            'Running' => $totals['running'],
            'Completed / hr' => $totals['completed_last_hour'],
            'Failed / hr' => $totals['failed_last_hour'],
            'Workers' => $totals['workers_running'],
            'Stale workers' => $totals['workers_stale'],
        ] as $label => $value)
            <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</div>
                <div class="mt-1 text-2xl font-semibold text-white">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <div class="text-xs uppercase tracking-wide text-slate-400">Avg runtime (last hour)</div>
            <div class="mt-1 text-2xl font-semibold text-white">{{ number_format($this->avgRuntimeMs) }} ms</div>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
            <div class="text-xs uppercase tracking-wide text-slate-400">Avg wait (last hour)</div>
            <div class="mt-1 text-2xl font-semibold text-white">{{ number_format($this->avgWaitMs) }} ms</div>
        </div>
    </div>
</div>
