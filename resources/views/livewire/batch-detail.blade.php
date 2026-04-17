<div wire:poll.visible.5s class="space-y-6">
    @if ($batch)
        @php
            $done = $batch->total_jobs - $batch->pending_jobs;
            $pct = $batch->total_jobs > 0 ? (int) round($done / $batch->total_jobs * 100) : 0;
            $status = $batch->cancelled_at ? 'cancelled' : ($batch->finished_at ? ($batch->failed_jobs > 0 ? 'failed' : 'completed') : 'running');
        @endphp
        @php($running = ! $batch->finished_at && ! $batch->cancelled_at)
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ $batch->name ?: 'Unnamed batch' }}</h1>
                <div class="mt-1 font-mono text-xs text-slate-500">{{ $batch->id }}</div>
            </div>
            <div class="flex items-center gap-2">
                @if ($running)
                    <button wire:click="cancel" wire:confirm="Cancel this batch?"
                        class="rounded-md bg-rose-500/20 px-3 py-1.5 text-xs font-medium text-rose-300 hover:bg-rose-500/30">Cancel</button>
                @endif
                @if ($batch->failed_jobs > 0)
                    <button wire:click="retryFailed"
                        class="rounded-md bg-sky-500/20 px-3 py-1.5 text-xs font-medium text-sky-300 hover:bg-sky-500/30">Retry failed</button>
                @endif
                @if (! $running)
                    <button wire:click="delete" wire:confirm="Remove this batch record?"
                        class="rounded-md bg-slate-700/60 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700">Delete</button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach ([
                'Status' => null,
                'Total' => number_format($batch->total_jobs),
                'Pending' => number_format($batch->pending_jobs),
                'Failed' => number_format($batch->failed_jobs),
                'Progress' => $pct.'%',
                'Created' => \Illuminate\Support\Carbon::createFromTimestamp($batch->created_at)->toDateTimeString(),
                'Finished' => $batch->finished_at ? \Illuminate\Support\Carbon::createFromTimestamp($batch->finished_at)->diffForHumans() : '—',
                'Cancelled' => $batch->cancelled_at ? \Illuminate\Support\Carbon::createFromTimestamp($batch->cancelled_at)->diffForHumans() : '—',
            ] as $label => $value)
                <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</div>
                    <div class="mt-1 text-sm text-white">
                        @if ($label === 'Status')
                            @include('periscope::partials.status-badge', ['status' => $status])
                        @else
                            {{ $value }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="h-2 overflow-hidden rounded-full bg-slate-800">
            <div class="h-full {{ $batch->failed_jobs > 0 ? 'bg-rose-400/70' : 'bg-emerald-400/70' }}" style="width: {{ $pct }}%"></div>
        </div>
    @else
        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-6 text-center text-slate-400">
            Batch <code>{{ $batchId }}</code> not found — it may have been pruned.
        </div>
    @endif

    <div>
        <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-400">Jobs in this batch</h2>
        <div class="overflow-hidden rounded-xl border border-slate-800">
            <table class="w-full divide-y divide-slate-800 text-left text-sm">
                <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-4 py-2">Job</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Attempts</th>
                        <th class="px-4 py-2">Runtime</th>
                        <th class="px-4 py-2">Finished</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse ($jobs as $job)
                        <tr class="hover:bg-slate-900/40">
                            <td class="px-4 py-2">
                                <a href="{{ route('periscope.jobs.show', $job->uuid) }}" class="font-medium text-sky-300 hover:underline">{{ $job->name }}</a>
                                <div class="font-mono text-[11px] text-slate-500">{{ $job->uuid }}</div>
                            </td>
                            <td class="px-4 py-2">@include('periscope::partials.status-badge', ['status' => $job->status])</td>
                            <td class="px-4 py-2 text-slate-300">{{ $job->attempts }}</td>
                            <td class="px-4 py-2 text-slate-300">{{ $job->runtime_ms !== null ? number_format($job->runtime_ms).' ms' : '—' }}</td>
                            <td class="px-4 py-2 text-slate-400">{{ $job->finished_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No jobs recorded for this batch yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $jobs->links() }}</div>
    </div>
</div>
