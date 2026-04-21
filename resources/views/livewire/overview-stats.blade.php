<div wire:poll.visible.5s class="space-y-3">
    {{-- Row 1 — primary KPIs --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-slate-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                Jobs Per Minute
            </div>
            <div class="mt-3 text-4xl font-bold tabular-nums text-white">{{ number_format($this->jobsPerMinute) }}</div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-slate-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                Jobs Past Hour
            </div>
            <div class="mt-3 text-4xl font-bold tabular-nums text-white">{{ number_format($this->jobsPastHour) }}</div>
        </div>

        @php($failed7 = $this->failedPast7Days)
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-slate-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                Failed Past 7 Days
            </div>
            <div class="mt-3 text-4xl font-bold tabular-nums text-white">{{ number_format($failed7) }}</div>
        </div>

        @php($active = $this->isActive)
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 px-6 py-5 shadow-xl shadow-black/20">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-slate-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                Status
            </div>
            <div class="mt-3 flex items-center gap-3">
                @if ($active)
                    <span class="relative flex h-3 w-3 shrink-0">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400"></span>
                    </span>
                    <span class="text-4xl font-bold text-white">Active</span>
                @else
                    <span class="relative inline-flex h-3 w-3 shrink-0 rounded-full bg-slate-600"></span>
                    <span class="text-4xl font-bold text-white">Inactive</span>
                @endif
            </div>
        </div>

    </div>

    {{-- Row 2 — secondary metrics --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

        <div class="rounded-xl border border-slate-800/70 bg-slate-900/40 px-5 py-4">
            <div class="flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-widest text-slate-600">
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                Total Processes
            </div>
            <div class="mt-2 text-2xl font-semibold tabular-nums text-slate-400">{{ number_format($this->totalProcesses) }}</div>
        </div>

        <div class="rounded-xl border border-slate-800/70 bg-slate-900/40 px-5 py-4">
            <div class="flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-widest text-slate-600">
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                Max Wait Time
            </div>
            <div class="mt-2 text-2xl font-semibold text-slate-400">{{ $this->maxWaitQueue ?? '—' }}</div>
        </div>

        <div class="rounded-xl border border-slate-800/70 bg-slate-900/40 px-5 py-4">
            <div class="flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-widest text-slate-600">
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/></svg>
                Max Runtime
            </div>
            <div class="mt-2 text-2xl font-semibold text-slate-400">{{ $this->maxRuntimeQueue ?? '—' }}</div>
        </div>

        <div class="rounded-xl border border-slate-800/70 bg-slate-900/40 px-5 py-4">
            <div class="flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-widest text-slate-600">
                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                Max Throughput
            </div>
            <div class="mt-2 text-2xl font-semibold text-slate-400">{{ $this->maxThroughputQueue ?? '—' }}</div>
        </div>

    </div>
</div>
