<div wire:poll.visible.5s class="space-y-4">
    {{-- Row 1 --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Jobs Per Minute</div>
            <div class="mt-3 text-4xl font-bold tabular-nums text-white">{{ number_format($this->jobsPerMinute) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Jobs Past Hour</div>
            <div class="mt-3 text-4xl font-bold tabular-nums text-white">{{ number_format($this->jobsPastHour) }}</div>
        </div>

        @php($failed7 = $this->failedPast7Days)
        <div @class([
            'rounded-2xl border px-6 py-5 shadow-xl shadow-black/20',
            'border-rose-500/30 bg-rose-500/5' => $failed7 > 0,
            'border-slate-800 bg-slate-900/60' => $failed7 === 0,
        ])>
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Failed Past 7 Days</div>
            <div @class([
                'mt-3 text-4xl font-bold tabular-nums',
                'text-rose-400' => $failed7 > 0,
                'text-white' => $failed7 === 0,
            ])>{{ number_format($failed7) }}</div>
        </div>

        @php($active = $this->isActive)
        <div @class([
            'rounded-2xl border px-6 py-5 shadow-xl shadow-black/20',
            'border-emerald-500/30 bg-emerald-500/5' => $active,
            'border-slate-800 bg-slate-900/60' => ! $active,
        ])>
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Status</div>
            <div class="mt-3 flex items-center gap-3">
                @if ($active)
                    <span class="relative flex h-3 w-3 shrink-0">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400"></span>
                    </span>
                    <span class="text-4xl font-bold text-emerald-400">Active</span>
                @else
                    <span class="relative inline-flex h-3 w-3 shrink-0 rounded-full bg-slate-600"></span>
                    <span class="text-4xl font-bold text-slate-500">Inactive</span>
                @endif
            </div>
        </div>

    </div>

    {{-- Row 2 --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Total Processes</div>
            <div class="mt-3 text-3xl font-semibold tabular-nums text-slate-200">{{ number_format($this->totalProcesses) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Max Wait Time</div>
            <div class="mt-3 text-3xl font-semibold text-slate-200">{{ $this->maxWaitQueue ?? '—' }}</div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Max Runtime</div>
            <div class="mt-3 text-3xl font-semibold text-slate-200">{{ $this->maxRuntimeQueue ?? '—' }}</div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="text-xs font-medium uppercase tracking-widest text-slate-500">Max Throughput</div>
            <div class="mt-3 text-3xl font-semibold text-slate-200">{{ $this->maxThroughputQueue ?? '—' }}</div>
        </div>

    </div>
</div>
