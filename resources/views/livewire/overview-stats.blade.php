<div wire:poll.visible.5s class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900/60">
    {{-- Row 1 --}}
    <div class="grid grid-cols-2 divide-x divide-slate-800 md:grid-cols-4">
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Jobs Per Minute</div>
            <div class="mt-1 text-3xl font-semibold text-white">{{ number_format($this->jobsPerMinute) }}</div>
        </div>
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Jobs Past Hour</div>
            <div class="mt-1 text-3xl font-semibold text-white">{{ number_format($this->jobsPastHour) }}</div>
        </div>
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Failed Jobs Past 7 Days</div>
            <div class="mt-1 text-3xl font-semibold text-white">{{ number_format($this->failedPast7Days) }}</div>
        </div>
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Status</div>
            @php($active = $this->isActive)
            <div class="mt-1 flex items-center gap-2">
                @if ($active)
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="shrink-0 text-emerald-400">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12l3 3 5-5" />
                    </svg>
                    <span class="text-3xl font-semibold text-emerald-400">Active</span>
                @else
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="shrink-0 text-slate-500">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                    <span class="text-3xl font-semibold text-slate-400">Inactive</span>
                @endif
            </div>
        </div>
    </div>

    <div class="border-t border-slate-800"></div>

    {{-- Row 2 --}}
    <div class="grid grid-cols-2 divide-x divide-slate-800 md:grid-cols-4">
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Total Processes</div>
            <div class="mt-1 text-3xl font-semibold text-white">{{ number_format($this->totalProcesses) }}</div>
        </div>
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Max Wait Time</div>
            <div class="mt-1 text-3xl font-semibold text-white">{{ $this->maxWaitQueue ?? '-' }}</div>
        </div>
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Max Runtime</div>
            <div class="mt-1 text-3xl font-semibold text-white">{{ $this->maxRuntimeQueue ?? '-' }}</div>
        </div>
        <div class="px-6 py-5">
            <div class="text-sm text-slate-400">Max Throughput</div>
            <div class="mt-1 text-3xl font-semibold text-white">{{ $this->maxThroughputQueue ?? '-' }}</div>
        </div>
    </div>
</div>
