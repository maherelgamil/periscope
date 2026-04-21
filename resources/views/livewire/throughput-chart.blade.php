<div wire:poll.visible.10s class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
    <div class="mb-3 flex items-center justify-between">
        <div>
            <div class="text-xs uppercase tracking-wide text-slate-400">Throughput</div>
            <div class="text-sm text-slate-300">Last {{ $minutes }} minutes</div>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-400">
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-emerald-400"></span> Processed</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-sm bg-rose-400"></span> Failed</span>
        </div>
    </div>

    @php
        $hasData = $series->contains(fn ($p) => $p['processed'] > 0 || $p['failed'] > 0);
    @endphp
    @if ($hasData)
        <div class="flex h-40 items-end gap-[2px]">
            @foreach ($series as $point)
                @php
                    $processedHeight = (int) round(($point['processed'] / $max) * 100);
                    $failedHeight = (int) round(($point['failed'] / $max) * 100);
                @endphp
                <div class="group relative flex h-full flex-1 items-end gap-[1px]">
                    <div class="w-1/2 rounded-t bg-emerald-400/70" style="height: {{ $processedHeight }}%"></div>
                    <div class="w-1/2 rounded-t bg-rose-400/70" style="height: {{ $failedHeight }}%"></div>
                    <div class="pointer-events-none absolute -top-8 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-[10px] text-slate-200 group-hover:block">
                        {{ $point['label'] }} · {{ $point['processed'] }}✓ / {{ $point['failed'] }}✗
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex h-40 flex-col items-center justify-center gap-2 text-slate-600">
            <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
            <span class="text-sm">No throughput data yet</span>
        </div>
    @endif
</div>
